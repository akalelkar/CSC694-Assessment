<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Model\AllTables;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\Adapter\Adapter as DbAdapter;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();

        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {   return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getServiceConfig()
    {
        // this creates dbAdapter to be used in all models
        // db defined in global.php
        return array(
            'factories' => array(
                'dbAdapter' => function($sm) {
                    $config = $sm->get('config');
                    $config = $config['db'];
                    $dbAdapter = new DbAdapter($config);
                    return $dbAdapter;
                },
                'Application\Model\AllTables' => function($sm) {
                        $dbAdapter = $sm->get('dbAdapter');
                        $table = new AllTables($dbAdapter);
                        return $table;
                    },
            ),
        );
    }
}
