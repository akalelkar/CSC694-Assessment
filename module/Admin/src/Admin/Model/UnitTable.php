<?php

namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\session\container;
use Zend\Debug\Debug;

class UnitTable extends AbstractTableGateway
{
    public $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->table = 'units';
        $this->initialize();
    }

    /*
     * get all Units and either paginate or return result set
     */
    public function fetchAll($paginated=false)
    {
         if($paginated) {
            // create a new Select object for the table album
            $select = new Select('units');
            // create a new result set based on the Album entity
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Unit());
            // create a new pagination adapter object
            $paginatorAdapter = new DbSelect(
                // our configured select object
                $select,
                // the adapter to run it against
                $this->adapter,    
                #$this->tableGateway->getAdapter(),
                // the result set to hydrate
                $resultSetPrototype
            );
            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }
        $resultSet = $this->select();
        return $resultSet;   
    }

    /*
     * get all active units
     */
    public function getUnitsForSelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from('units')
                      ->columns(array('id'))
                      ->where(array('active_flag' => 1))
        ;
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
    
        $resultsArray = array();
        // array must be created as key=>value pair where both key and value are the unit_id
        // so when accessing values from multi select you get the unit_id and not the array subscript
        foreach($results as $result){
            $resultsArray[$result['id']] = $result['id'];
        }
        return $resultsArray;
    }
    
    /*
     * get unit by id
     */
    public function getUnit($id)
    {
        $rowset = $this->select(array('id' => $id));
        
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        $unit = new Unit();
        $unit->exchangeArray($row);
        return $unit;
    }
    
    /*
     * get units for privileges select
     */
    public function getPrivsForSelect($role)
    {
        $sql = new Sql($this->adapter);
        if ($role != 4){ // other than assessor
            $select = $sql->select()
                      ->from('units')
                      ->columns(array('id'))
                      ->where(array('active_flag' =>'1'))
            ;          
        }
        else{ // assessor
            $select1 = $sql->select()
                           ->from('assessor_privs')
                           ->columns(array('unit_id'))
                           ->group('unit_id')
                           ->having(array('count(*) = 2'))
            ;
            $select = $sql->select()
                          ->from('units')
                          ->columns(array('id'))
                          ->where(array('active_flag' =>1))
                          ->where(new NotIn('id', $select1))
                       
            ;         
        }
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
    
        $resultsArray = array();
        $resultsArray['None'] = 'None';
        // array must be created as key=>value pair where both key and value are the unit_id
        // so when accessing values from multi select you get the unit_id and not the array subscript
        foreach($results as $result){
            $resultsArray[$result['id']] = $result['id'];
        }
        return $resultsArray;
    }
    
       
    /*
     * Add a record to one of the priv tables: assessor_priv, liaison_priv or chair_priv
     */
    function addPriv($id,$user,$table)
    {
        $namespace = new Container('user');
        
        // create an atomic database transaction to update plan and possibly report
	$connection = $this->adapter->getDriver()->getConnection();
	$connection->beginTransaction();

        // check that active priv does not exist
        $select = $sql->select()
                 ->from($table)
                 ->columns(array('id'))
                 ->where(array('active_flag' =>'1'))
                 ->where(array('user_id' => $user))
                 ->where(array('unit_id' => $id))
        ;
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
    var_dump($results->count());
    exit();
        if ($results->count() == 0){
            $data = array(
                'user_id' => $user,
                'unit_id' => $id,
                'created_user' => $namespace->userID,
                'created_ts' => date('Y-m-d h:i:s', time()),
                'active_flag' => 1
            );
            $sql = new Sql($this->adapter);
            $insert = $sql->insert($table);
            $insert->values($data);
            $insertString = $sql->getSqlStringForSqlObject($insert);
            $this->adapter->query($insertString, Adapter::QUERY_MODE_EXECUTE);    
        }
        
        // finish the transaction		
	$connection->commit();
        
    }
    
    /*
     * remove privs
     */
    function deletePriv($user,$table)
    {
       //delete the user
       $sql = new Sql($this->adapter);
       $delete = $sql->delete($table)
                       ->where(array('user_id = ?' => $user));
       $deleteString = $sql->getSqlStringForSqlObject($delete);
       $this->adapter->query($deleteString, Adapter::QUERY_MODE_EXECUTE);   
    }
    
    
    
    /*
     * Saves a unit
     */
    public function saveUnit(Unit $unit)
    {
        $namespace = new Container('user');
        
        $data = array(
            'id' => strtoupper($unit->unit_id),
            'type' => 1,
            'active_flag' => 1,
        );

        //get the program id
        $id = $unit->unit_id;
  
        $exists = $this->getUnit($id);
        
        //if unit doesn't exists
        if (!$exists) {
            $data['created_ts'] = date('Y-m-d h:i:s', time());
            $data['created_user'] = $namespace->userID;
            $this->insert($data);
        } 
    }

}