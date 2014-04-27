<?php

namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\session\container;

class ProgramTable extends AbstractTableGateway {

    public $adapter;

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->table = 'programs';
        $this->initialize();
    }
    
    /*
     * Returns all programs 
     */
    // Called from ProgramController indexAction method
    public function fetchAll()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from($this->table)
                      ->join('users', 'users.id = programs.created_user', array('last_name', 'first_name'))
                      ->join('units', 'programs.unit_id = units.id', array('division'))
                      ->where(array('programs.active_flag' => 1))
                      ->order(array('division', 'programs.unit_id'))
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $programs = array();
        foreach($result as $row){
            //create programs array to return
            $program = new Program();
            $program->exchangeArray($row);
            $programs[] = $program;
        }

        return $programs;
    }

    /*
     * Get program by id
     */
    // Called from ProgramController editAction method
    public function getProgram($id) {
        $id = (int) $id;
        $rowset = $this->select(array('id' => $id));

        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        $program = new Program();
        $program->exchangeArray($row);
        return $program;
    }

    /*
     * Save a Program
     */
    // Called from ProgramController addprogramAction, editAction method
    public function saveProgram(Program $program) {
        $namespace = new Container('user');

        //build the new data array 
        $data = array(
            'unit_id' => $program->unit_id,
            'name' => $program->name,
            'active_flag' => 1,
        );

        //get the program id
        $id = (int) $program->id;

        //if program doesn't exists
        if ($id == 0) {
            $data['created_ts'] = date('Y-m-d h:i:s', time());
            $data['created_user'] = $namespace->userID;
            $this->insert($data);
        } else {
            if ($this->getProgram($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    /*
     * Delete Program
     */
    // Called from ProgramController deleteAction method
    public function deleteProgram($id) {
        $namespace = new Container('user');

        //deactivating an existing program
        
        $data = array();
        $data['deactivated_ts'] = date('Y-m-d h:i:s', time());
        $data['deactivated_user'] = $namespace->userID;
        $data['active_flag'] = 0;
        $this->update($data, array('id' => $id));
    }

}