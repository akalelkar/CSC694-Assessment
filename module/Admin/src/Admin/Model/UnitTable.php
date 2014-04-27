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
     * get unit by id
     */
     // called by addUnit
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
     * get all divisions
     */
    // Called from ProgramController editAction, addUnit methods
    public function getDivisionsForSelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from('divisions')
                      ->columns(array('division'))
        ;
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
    
        $resultsArray = array();
        // array must be created as key=>value pair where both key and value are the unit_id
        // so when accessing values from multi select you get the division and not the array subscript
        foreach($results as $result){
            $resultsArray[$result['division']] = $result['division'];
        }
        return $resultsArray;
    }
    
    /*
     * get all active units
     */
    // Called from ProgramController editAction, addProgram methods
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
     * get units for privileges select
     */
    // Called from ProgramController editAction method
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
     * Adds a new unit - called from addunitAction in program controller
     */
    // Called from ProgramController addunitAction method
    public function addUnit(Unit $unit)
    {
        $namespace = new Container('user');

        $data = array(
            'id' => strtoupper($unit->unit_id),
            'type' => 1,
            'active_flag' => 1,
            'division' => $unit->division,
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