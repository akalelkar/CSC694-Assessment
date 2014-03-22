<?php

namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\session\container;
use Zend\Debug\Debug;

class UserTable extends AbstractTableGateway
{
    public $adapter;

    public function __construct(Adapter $adapter)
    {
        $namespace = new Container('user');
        $this->adapter = $adapter;
        $this->table = 'users';
        $this->initialize();
    }

    /*
     * Returns all users in the user database
     */
    public function fetchAll()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from($this->table)
                      ->where(array('active_flag' => 1)) 
        ;
                      
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $users = array();
        foreach($result as $row){
            //add roles to user
            $roles = $this->getRoles($row['id']);
            $row['user_roles'] = $roles;
            //create user object to return
            $user = new User();
            $user->exchangeArray($row);
            $users[] = $user;
            
        }
        return $users;
    }
    
    /*
     *  Get users by roles 
     *  return array user_id=> first_name . last_name
     */
    public function fetchUsersByRole($roles)
    {

        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->columns(array('id','first_name','last_name'))
                      ->from(array('u' =>$this->table))
                      ->join(array('ur' =>'user_roles'), 'u.id = ur.user_id')
                      ->where(array('role'=>$roles))
                      ->where(array('users.active_flag' => 1))
                      ->where(array('user_roles.active_flag' => 1))
        ;
                      
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $results = array();
        foreach($result as $key => $value){
            $results[$value['full_name']] = $value['first_name'] .' '.$value['last_name'];
        }
        return $results;
    }
    
    /*
     *  Get all roles by user id
     *  @id - the user id
     */
    public function getRoles($id)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                    ->from('user_roles')
                    ->where(array(
                        'user_id' => $id,
                        'active_flag' => 1));
        
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $roles = array();
        foreach($result as $row){
            $roles[$row['role']]['id'] = $row['id'];
            $roles[$row['role']]['term'] = $this->Generic()->getRoleTerm($row['role']);
        }
        return $roles;
    }
    
    /*
     * gets roles by user id
     */
    public function getRolesById($id, $active = true)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                    ->from('user_roles')
                    ->columns(array('role' => 'role'));
        if($active){
            $select->where(array('user_id' => $id,'active_flag' => 1));
        }else{
            $select->where(array('user_id' => $id,'active_flag' => 0));
        }
                
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $roles = array();
        foreach($result as $key => $value){
            $roles[] = $value['role'];
        }
        return $roles;
    }
    
    /*
     * deletes all roles a user has
     * @id - the user id
     */
    public function deleteRoles($id)
    {
        $sql = new Sql($this->adapter);
        $delete = $sql->delete('user_roles')
                       ->where(array('user_id = ?' => $id));
        $deleteString = $sql->getSqlStringForSqlObject($delete);
        $this->adapter->query($deleteString, Adapter::QUERY_MODE_EXECUTE);
    }
    
    /*
     *  Adds roles to a user
     * @userID - id to the user object
     * @roles - array of role ids
     */
    function addRoles($userID,$roles)
    {
        $namespace = new Container('user');
        
       //add role(s)
       foreach($roles as $row => $value){
            $role = array(
                'user_id' => $userID,
                'role' => $value,
                'created_user' => $namespace->userID,
                'created_ts' => date('Y-d-m h:i:s', time()),
                'active_flag' => 1
            );
            $sql = new Sql($this->adapter);
            $insert = $sql->insert('user_roles');
            $insert->values($role);
            $insertString = $sql->getSqlStringForSqlObject($insert);
            $this->adapter->query($insertString, Adapter::QUERY_MODE_EXECUTE);
       }
    }
    
    /*
     *  Updates Users Roles
     * @userID - id to the user object
     * @roles - array of role ids
     */
    function updateRoles($userID,$roles)
    {
        //get the active roles the user previously had
        $ActiveRoles = $this->getRolesById($userID, true);

        
        //get all inactive roles the user previously had
        $InActiveRoles = $this->getRolesById($userID, false);

        
        //create a list of all the roles a previously had
        $allRoles = array_merge($ActiveRoles,$InActiveRoles);

        
        //determines if user has been given a new role
        $newRoles = array_diff($roles, $allRoles);

        //add any new roles
        if(!empty($newRoles)){
           $this->addRoles($userID,$newRoles);
        }
        
        //update role(s) that were re-enabled/disabled
        $disableRoles = array_diff($ActiveRoles, $roles);
        $reenabledRoles = array_diff($roles, $ActiveRoles);
        
        print_r($reenabledRoles);
        
        if(!empty($disableRoles)){
            foreach($disableRoles as $key => $value){
                $this->updateRole($userID,$value, 'disable');
            }
        }
        if(!empty($reenabledRoles)){
            foreach($reenabledRoles as $key => $value){
                $this->updateRole($userID,$value, 'enable');
            }
        }
    }
    
    /*
     * enable or disable a role
     */
    function updateRole($userID,$role, $action)
    {
        $namespace = new Container('user');
        switch($action){
            case 'disable':
                $data = array(
                        'active_flag' => 0,
                        'deactivated_ts' =>  date('Y-m-d h:i:s', time()),
                        'deactivated_user' =>  $namespace->userID
                    );
                break;
            
            case 'enable':
                $data = array(
                        'active_flag' => 1,
                    );
                break;
        }

        $sql = new Sql($this->adapter);
        $update = $sql->update('user_roles')
                      ->set($data)
                       ->where(array('user_id = ?' => $userID, 'role = ?' => $role));
        $updateString = $sql->getSqlStringForSqlObject($update);
        $this->adapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
    }
    
   

    /*
     * Get user by id
     * @returns null if no user is found or the user object
     */
    public function getUser($id)
    {
        $id = (int) $id;
        $rowset = $this->select(array('id' => $id));
        
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        $roles = $this->getRoles($row['id']);
        $row['user_roles'] = $roles;
        $user = new User();
        $user->exchangeArray($row);
        return $user;
    }
    
    /*
     * Gets user by email address
     * @returns null if no user is found or the user object
     */
    public function getUserByEmail($email)
    {
        $rowset = $this->select(array('email' => $email));
        
        $row = $rowset->current();
        if (!$row) {
            return null;
        }
        $roles = $this->getRoles($row['id']);
        $row['user_roles'] = $roles;
        $user = new User();
        $user->exchangeArray($row);
        return $user;
    }
    
    /*
     * Get unit privs
     */
    public function getUserUnitPrivs($user,$table)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                    ->from($table)
                    ->columns(array('unit_id'))
                    ->where(array(
                        'user_id' => $user,
                        'active_flag' => 1));
        
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $privs = array();
        foreach($result as $row){
            $privs[] = $row['unit_id'];
        }
        return $privs;
        
    }
    

    /*
     * Saves a user
     */
    public function saveUser(User $user)
    {
        //build the data array to add to users table
        $data = array(
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_init' => $user->middle_init,
            'email' => $user->email,
            'datatel_id' => 0
        );
        
        //just put users roles into a new array  
        if(isset($user->role_0) && $user->role_0 != '')
        {
            $roles[] = $user->role_0;
        }
        if(isset($user->role_1) && $user->role_1 != '')
        {
            $roles[] = $user->role_1;
        }
        if(isset($user->role_2) && $user->role_2 != '')
        {
            $roles[] = $user->role_2;
        }
        if(isset($user->role_3) && $user->role_3 != '')
        {
            $roles[] = $user->role_3;
        }
        
        //get the user id
        $id = (int)$user->id;
        
        
        //if user doesn't exists
        if ($id == 0 OR empty($id)) {
            //insert user
            try {
                $this->insert($data);
            } catch (\Exception $e)  {
                var_dump($e->getMessage());
            }
            
            //get the new user id
            $id = $this->adapter->getDriver()->getLastGeneratedValue();
            
            //add role(s)
            $this->addRoles($id,$roles);
            
            //add any liaison or unit privs
            if(isset($user->unit_privs) && !empty($user->unit_privs))
            {
                foreach($user->unit_privs as $key => $value)
                {
                   $this->UnitTable()->addPriv($value,$id,'unit_privs');
                }
            }
            if(isset($user->liaison_privs) && !empty($user->liaison_privs))
            {
                foreach($user->liaison_privs as $key => $value)
                {
                    $this->UnitTable()->addPriv($value,$id,'liaison_privs');
                }
            }            
            
        } else {
            if ($this->getUser($id)) {
                
                //update user information
                $this->update($data, array('id' => $id));
                
                //update and add roles
                $this->updateRoles($user->id,$roles);
                
                
                //update unit and liaison privs
                if(isset($user->unit_privs) && !empty($user->unit_privs))
                {
                    foreach($user->unit_privs as $key => $value)
                    {
                        $this->UnitTable()->updatePrivs($value,array($id),'unit_privs');
                    }
                }
                if(isset($user->liaison_privs) && !empty($user->liaison_privs))
                {
                    foreach($user->unit_privs as $key => $value)
                    {
                        $this->UnitTable()->updatePrivs($value,array($id),'liaison_privs');
                    }
                }
                
  
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    /*
     * deletes a user
     */
    public function deleteUser($id)
    {

       //delete user roles first
       $this->deleteRoles($id);
       
       //delete unit privs
       $this->UnitTable()->deletePriv($id,'unit_privs');
       $this->UnitTable()->deletePriv($id,'liaison_privs');
       
       //delete the user
       $sql = new Sql($this->adapter);
       $delete = $sql->delete('users')
                       ->where(array('id = ?' => $id));
       $deleteString = $sql->getSqlStringForSqlObject($delete);
       $this->adapter->query($deleteString, Adapter::QUERY_MODE_EXECUTE);
    }
    
    /*
     * Instantiate Generic Class
     */
    public function Generic()
    {
        return new Generic($this->adapter);
    }
    
    /*
     * Instantiate UnitTable Class
     */
    public function UnitTable()
    {
        return new UnitTable($this->adapter);
    }
    
}