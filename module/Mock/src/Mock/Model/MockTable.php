<?php

namespace Mock\Model;

use Zend\Db\TableGateway\TableGateway;
error_reporting(E_ALL);
ini_set('display_errors',1);

class MockTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
       // var_dump($resultSet);
       // exit();
        return $resultSet;
    }

    public function getMock($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('sid' => $id));
        $row = $rowset->current();
        
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveMock(Mock $mock)
    {
        $data = array(
            'sname' => $mock->sname,
            'major' => $mock->major,
            'credits' => $mock->credits,
        
        );

        $id = (int)$mock->sid;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getMock($id)) {
                $this->tableGateway->update($data, array('sid' => $id));
            } else {
                throw new \Exception('Form sid does not exist');
            }
        }
    }

    public function deleteMock($id)
    {
        $this->tableGateway->delete(array('sid' => $id));
    }
}