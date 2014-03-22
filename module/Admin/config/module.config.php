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
             'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
             'Admin\Controller\User' => 'Admin\Controller\UserController',
             'Admin\Controller\Program' => 'Admin\Controller\ProgramController',
            # 'Admin\Controller\Unit' => 'Admin\Controller\UnitController',
             'Admin\Controller\Queries' => 'Admin\Controller\QueriesController',
         ),
     ),
     'view_manager' => array(
         'template_path_stack' => array(
             'admin' => __DIR__ . '/../view',
         ),
     ),
       'router' => array(
         'routes' => array(
             'admin' => array(
                 'type'    => 'literal',
                 'options' => array(
                     'route'    => '/admin',
                     'defaults' => array(
                         '__NAMESPACE__' => 'Admin\Controller',
                         'controller' => 'Admin',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'update' => array(
                 'type'    => 'literal',
                 'options' => array(
                     'route'    => '/admin/user',
                     'defaults' => array(
                         '__NAMESPACE__' => 'Admin\Controller',
                         'controller' => 'User',
                         'action'     => 'update',
                     ),
                 ),
             ),
             'user' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/admin/user[/][page/:page][:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                         'page' => '[0-9]+',
                     ),
                     'defaults' => array(
                         '__NAMESPACE__' => 'Admin\Controller',
                         'controller' => 'User',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'program' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/admin/programs[/][page/:page][:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         '__NAMESPACE__' => 'Admin\Controller',
                         'controller' => 'Program',
                         'action'     => 'index',
                     ),
                 ),
             ),
          /*   'unit' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/admin/units[/][page/:page][:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                     'defaults' => array(
                         '__NAMESPACE__' => 'Admin\Controller',
                         'controller' => 'Unit',
                         'action'     => 'index',
                     ),
                 ),
             ),*/
             'query' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/admin/query[/][:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         '__NAMESPACE__' => 'Admin\Controller',
                         'controller' => 'Queries',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),
 );