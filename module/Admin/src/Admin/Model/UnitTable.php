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
                           ->having(array('count(*) < 2'))
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
        // array must be created as key=>value pair where both key and value are the unit_id
        // so when accessing values from multi select you get the unit_id and not the array subscript
        foreach($results as $result){
            $resultsArray[$result['id']] = $result['id'];
        }
        return $resultsArray;
    }
    
       
    /*
     * Add a record to one of the two priv tables, liaison_priv or unit_priv
     */
    function addPriv($id,$user,$table)
    {
        $namespace = new Container('user');
        
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
     *  Update an existsing priv record, disable or enable
     */
    public function updatePriv($id,$user,$action,$table)
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
        $update = $sql->update($table)
                      ->set($data)
                       ->where(array('user_id = ?' => $user, 'unit_id = ?' => $id));
        $updateString = $sql->getSqlStringForSqlObject($update);
        $this->adapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
    }
    
    /*
     * Update privs when Unit is updated
     */
    public function updatePrivs($id,$users,$table)
    {
        foreach($users as $key => $value)
        {     
            //get existing active liason ids
            $previousActivePrivs = $this->getUnitPrivs($id,$table,1);
            
            //get existing inactive liason ids
            $previousInactivePrivs = $this->getUnitPrivs($id,$table,0);
            
             //get all previous inactive liason ids
            $previousPrivs = $this->getUnitPrivs($id,$table,array(1,0));
            
            $newPrivs = array_diff(array($value),$previousPrivs);
            
            
            if(!empty($newPrivs)){
                foreach($newPrivs as $key => $value)
                {
                     $this->addPriv($id,$value,$table);
                }
            }
            
            //update role(s) that were re-enabled/disabled
            $disablePrivs = array_diff($previousActivePrivs, array($value));
            $reenabledPrivs = array_diff(array($value), $previousActivePrivs);
            
            if(!empty($disablePrivs)){
                //disable priv
                foreach($disablePrivs as $key => $value){
                    $this->updatePriv($id,$value, 'disable',$table);
                }
            }
            //reenable priv
            if(!empty($reenabledPrivs)){
                foreach($reenabledPrivs as $key => $value){
                    $this->updatePriv($id,$value, 'enable',$table);
                }
            }
        } 
    }
    
    
    /*
     * Saves a unit
     */
    public function saveUnit(Unit $unit)
    {
        $namespace = new Container('user');
        
        //build save data array
        $data = array(
            'id' => $unit->id,
            'type' => $unit->type,
            'active_flag' => ($unit->active_flag)? $unit->active_flag: 0,
        );
              
        //deactivating an existing program
        if(!$unit->active_flag){
            $data['deactivated_ts'] =  date('Y-m-d h:i:s', time());
            $data['deactivated_user'] =  $namespace->userID;
        }
   
        //get the user id
        $id = $unit->id;
        
        $exists = $this->getUnit($id);
        
        //if program doesn't exists
        if (!$exists) {
            $data['created_ts'] =  date('Y-m-d h:i:s', time());
            $data['created_user'] = $namespace->userID;     
            
            //insert unit
            $this->insert($data);
            
            //get assessor/liaison
            $assessor = (isset($unit->assessor_1))? $unit->assessor_1:'';
            $liaison = (isset($unit->liaison_1))? $unit->liaison_1:'';
            
            //add priv records
            if(!empty($assessor))
            {
                $this->addPriv($id,$assessor,'unit_privs');
            }
            
            if(!empty($liaison))
            {
                $this->addPriv($id,$liaison,'liaison_privs');
            }

        } else {
            if ($this->getUnit($id)) {
                
                //update unit
                $this->update($data, array('id' => $id));
                
                //get assessor/liaison
                $assessors[] = (isset($unit->assessor_1))? $unit->assessor_1:'';
                $liaisons[] = (isset($unit->liaison_1))? $unit->liaison_1:'';            

                //update privs
                $this->updatePrivs($id,$liaisons,'liaison_privs');
                $this->updatePrivs($id,$assessors,'unit_privs');
  
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    /*
     * Delete a unit
     */
    public function deleteUnit($id)
    {
        $this->delete(array('id' => $id));
    }
}