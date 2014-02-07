<?php

namespace Review;

use Review\Model\Enroll;
use Review\Model\EnrollTable;
use Review\Model\Student;
use Review\Model\StudentTable;
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
        // set up each model table in factories
        return array(
                        
                'factories' =>  array(
                    'Review\Model\StudentTable' => function($sm) {
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