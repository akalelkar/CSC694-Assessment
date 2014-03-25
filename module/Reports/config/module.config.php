<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

 return array(
     'controllers' => array(
         'invokables' => array(
             'Reports\Controller\Reports' => 'Reports\Controller\ReportsController',
         ),
     ),
     'view_manager' => array(
         'template_path_stack' => array(
             'reports' => __DIR__ . '/../view',
         ),
         'strategies' => array(
            'ViewJsonStrategy',
        ),
     ),
       'router' => array(
         'routes' => array(
             'default' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/reports[/][:action]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         'controller' => 'Reports\Controller\Reports',
                         'action'     => 'index',
                     ),
                 ),
             ),
             
             'index' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/reports',
                     'constraints' => array(
                     ),
                     'defaults' => array(
                         'controller' => 'Reports\Controller\Reports',
                        'action'     => 'index',
                     ),
                 ),
             ),
              
             
             'id' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/reports[/][:action][/][:id]',
                     'constraints' => array(
                     ),
                     'defaults' => array(
                         'controller' => 'Reports\Controller\Reports',
                     ),
                 ),
             ),
             
             'reports' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/reports[/][:action]',
                     'constraints' => array(
                     ),
                     'defaults' => array(
                         'controller' => 'Reports\Controller\Reports',
                          'action'     => 'index',

                     ),
                 ),
             ),
         ),
     ),
 );