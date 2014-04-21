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

    // add privileges to various privs tables and possibly update user role
    public function addPrivileges($id, $privs, $role){
        // adds the privileges for that role by inserting a tuple in the
        // corresponding privs table
        // if new role, the role is added to the user_roles table
        // $privs is an array of unit_ids to add

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
        
        // get user logged in        
        $namespace = new Container('user');

        // create an atomic database transaction to update roles and possibly privileges
        $connection = $this->adapter->getDriver()->getConnection();
	$connection->beginTransaction();
        
        // add privileges to appropriate table
        foreach ($privs as $priv){
    var_dump($priv);
            // add privs to table
            $insert = $sql->insert()
                          ->into($table)
                          ->values(array('user_id'=>$id, 'unit_id'=>$priv,
                                         'created_ts'=>date('Y-m-d h:i:s', time()),
                                         'created_user'=>$namespace->userID, 'active_flag'=>1))
            ;
            $insertString = $sql->getSqlStringForSqlObject($insert);
            $this->adapter->query($insertString, Adapter::QUERY_MODE_EXECUTE);
        }
        
        // check if the user has this role, if not add
        $select = $sql->select()
                    ->from('user_roles')
                    ->where(array('user_id' => $id, 'role' => $role))
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
       
        if ($result->count() == 0){
            // need to add the role
            $data = array(
                'user_id' => $id,
                'role' => $role,
                'created_user' => $namespace->userID,
                'created_ts' => date('Y-m-d h:i:s', time()),
                'active_flag' => 1
            );
            $sql = new Sql($this->adapter);
            $insert = $sql->insert('user_roles');
            $insert->values($data);
            $insertString = $sql->getSqlStringForSqlObject($insert);
            $this->adapter->query($insertString, Adapter::QUERY_MODE_EXECUTE);
        }
        else{ // check that role is active
            foreach($result as $r){
                if ($r['active_flag'] == 0){
                    // reactivate role
                    $data = array(
                        'created_user' => $namespace->userID,
                        'created_ts' => date('Y-m-d h:i:s', time()),
                        'active_flag' => 1
                    );
                    $update = $sql->update('user_roles')
                              ->set($data)
                              ->where(array('user_id' => $id, 'role' => $role))
                    ;
                    $updateString = $sql->getSqlStringForSqlObject($update);
                    $this->adapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
                }
            }
        }
  
        // finish the transaction		
	$connection->commit();
      
    }
    
    // remove privileges from various privs tables and possibly update user role
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

        // get user logged in
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