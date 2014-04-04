<?php

namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Admin\Model\Admin;
use Zend\session\container;

class Generic extends AbstractTableGateway
{
    public $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        #$this->table = '';
        #$this->initialize();
    }
    public function getUnits()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                    ->from('units')
                    ->columns(array('id' => 'id'))
                    ->where(array('active_flag' => 1));
                    
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $results = array();
        if($result){
            foreach($result as $row => $value){
                $results[$value['id']]= $value['id'];
            }
        }
        return $results;
    }
    /*
     * Assigns a role id to a term
     */
    public function getRoleTerm($id){
        if(!$id){
            return;
        }  
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                    ->from('roles')
                    ->columns(array('name'))
                    ->where(array('id' => $id));
                    
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        // only one result returned
        foreach($result as $r){
            return $r['name'];
        }

    }
    /*
     * Get the role names except for user
     */
    public function getRoleTerms(){
        $roles = [];
        $sql = new Sql($this->adapter);
        $where = new \Zend\Db\Sql\Where();
        $where->notEqualTo('roles.id', 0);
	
        $select = $sql->select()
                    ->from('roles')
                    ->columns(array('name'))
        ;
        $select->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        // build array of terms
        foreach($result as $r){
            $roles[] = $r['name'];
        }
        return $roles;
    }

    public function Admin(){
        return new Admin($adapter);
    }
}