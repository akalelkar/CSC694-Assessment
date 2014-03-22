<?php
/**
 * Plans
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

 return array(
     'controllers' => array(
         'invokables' => array(
             'Plans\Controller\Plans' => 'Plans\Controller\PlansController',
         ),
     ),
     'view_manager' => array(
         'template_path_stack' => array(
             'plans' => __DIR__ . '/../view',
         ),
         'strategies' => array(
            'ViewJsonStrategy',
        ),
     ),
       'router' => array(
         'routes' => array(
             'plans' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/plans[/][:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Plans\Controller\Plans',
                         'action'     => 'index',
                     ),
                 ),
             ),
           
         ),
     ),
 );