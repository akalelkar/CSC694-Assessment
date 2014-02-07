<?php

namespace Outcomes;

use Outcomes\Model\Enroll;
use Outcomes\Model\EnrollTable;
use Outcomes\Model\Student;
use Outcomes\Model\StudentTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
                'factories' =>  array(
                    'Outcomes\Model\StudentTable' => function($sm) {
                        $dbAdapter = $sm->get('dbAdapter');
                        $table = new StudentTable($dbAdapter);
                        return $table;
                    },
                ),
      
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}