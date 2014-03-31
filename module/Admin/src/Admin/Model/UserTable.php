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

    
    public function removePrivileges($id, $privs, $role){
        // removes the privileges for that role by inactivating the privileges
        // if none remain, then that role is also inactivated for the user
        // $privs is an array of unit_ids to remove

        $sql = new Sql($this->adapter);
        if ($role == 2){
            $table = 'liaison_privs';
        }
        else if ($role == 3){
            $table = 'chair_privs';
        }
        else{
            $table = 'assessor_privs';
        }
        
        $where = new \Zend\Db\Sql\Where();
       	$where->equalTo('user_id', $id)
              ->and
              ->in('unit_id', $privs)
        ;

        $namespace = new Container('user');
        
        $data = array(
                    'active_flag' => 0,
                    'deactivated_ts' =>  date('Y-m-d h:i:s', time()),
                    'deactivated_user' =>  $namespace->userID
        );
       
        // create an atomic database transaction to update roles and possibly privileges
        $connection = $this->adapter->getDriver()->getConnection();
	$connection->beginTransaction();
         
        $update = $sql->update($table)
                      ->set($data)
                      ->where($where)
        ;

        $updateString = $sql->getSqlStringForSqlObject($update);
        $this->adapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
        
        // check if user has any privileges left for that role
        $select = $sql->select()
                      ->columns(array('id'))
		      ->from($table)
                      ->where(array('user_id' => $id, 'active_flag' => 1))
	;
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if ($result->count() == 0){
            // no privileges for that role - inactivate user role
            $update = $sql->update('user_roles')
                          ->set($data)
                          ->where(array('user_id' => $id, 'role' => $role));

            $updateString = $sql->getSqlStringForSqlObject($update);
            $this->adapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
        }

        // finish the transaction		
	$connection->commit();
      
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
     * Get user's privileges based on a role
     */
    public function getUserPrivs($user, $role)
    {
        $sql = new Sql($this->adapter);
        if ($role == 2){ // liaison
            $table = 'liaison_privs';
        }
        else if ($role == 3){ // chair
            $table = 'chair_privs';
        }
        else{ // assessor
            $table = 'assessor_privs';
        }
        $select = $sql->select()
                ->from(array('t' => $table))
                ->columns(array('unit_id'))
                ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                ->join('user_roles', 'user_roles.user_id = t.user_id', array())
                ->where(array(
                    't.user_id' => $user,
                    'user_roles.role' => $role,
                    't.active_flag' => 1))
                ->order('t.unit_id');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $privs = array();
        foreach($result as $row){
            $privs[$row['unit_id']] = $row['unit_id'];
        }
        return $privs;
        
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