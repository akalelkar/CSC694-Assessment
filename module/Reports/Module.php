<?php

namespace Reports;

use Reports\Model\Reports;
use Reports\Model\ReportsTable;
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
        /*return array(
            'factories' => array(
                'Reports\Model\ReportsTable' => function($sm) {
                    $tableGateway = $sm->get('AdminTableGateway');
                    $table = new AdminTable($tableGateway);
                    return $table;
                },
                'AdminTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Admin());
                    return new TableGateway('Admin', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
        */
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}