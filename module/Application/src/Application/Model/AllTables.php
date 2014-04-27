<?php

namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


// This class must be included in the factories array in
// this modules Module.php

// This class holds the database method calls.
// The class variable $table references a base table used in the
// queries.  This variable is required in the class but is not required
// to be used in all database calls.  For example, the from clause could
// explicitly reference a table (->from('student')).

class AllTables extends AbstractTableGateway
{
    public $adapter;
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
  
    // Retrieves all active units
    public function getUnits()
    {   
        $sql = new Sql($this->adapter);
        
       // $sql->beginTransaction();
        $select = $sql->select()
                      ->from('units')
                      ->where(array('active_flag' => 1));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        // dumping $result will not show any rows returned
        // you must iterate over $result to retrieve query results
        
        return $result;
    }
    
    
    public function getUnitsByPrivId($userID)
    {
        // get units available for user from unit_privs and liaison_privs
        $sql = new Sql($this->adapter);
        $select1 = $sql->select()
                    ->from('chair_privs')
                    ->columns(array('id' => 'unit_id'))
                    ->where(array('chair_privs.user_id' => $userID, 'active_flag' => 1));
                    
        $select2 = $sql->select()
                    ->from('liaison_privs')
                    ->columns(array('id' => 'unit_id'))
                    ->where(array('liaison_privs.user_id' => $userID, 'active_flag' => 1));
        
        // combine first two
        $select1->combine($select2);
        
        // get third
        $select3 = $sql->select()
                    ->from('assessor_privs')
                    ->columns(array('id' => 'unit_id'))
                    ->where(array('assessor_privs.user_id' => $userID, 'active_flag' => 1));

        // union results from all three selects
        $select = $sql->select()
                      ->from(array('select1and2' => $select1));
        $select->combine($select3);
        


        $statement = $sql->prepareStatementForSqlObject($select1);
        $result = $statement->execute();
        
        // dumping $result will not show any rows returned
        // you must iterate over $result to retrieve query results
        
        return $result;
    }
        
    // Retrieves all active programs for a given unit id
    public function getProgramsByUnitId($unitid)
    {   
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from('programs')
                      ->where(array('unit_id' => $unitid))
                      ->where(array('active_flag' => 1));
                      
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        // dumping $result will not show any rows returned
        // you must iterate over $result to retrieve query results
        
        return $result;
    }
    
    //retrieves the user id, email address, and datatelID from the DB
    public function getUserInformation($userID)
    {
        $sql = new Sql($this->adapter);
        
        $select = $sql->select()
                      ->from('users')
                      ->columns(array('id' => 'id',
                                      'datatel_id' => 'datatel_id',
                                      'email' => 'email'))
                      ->where(array('datatel_id' => $userID));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        return $result;
    }
    
    //retrieves the role of the user given their userID
    public function getUserRole($userID)
    {
        $sql = new Sql($this->adapter);
        
        $select = $sql->select()
                      ->from('user_roles')
                      ->columns(array('role' => 'role'))                                      
                      ->where(array('user_id' => $userID));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        return $result;
    }
}