<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Mock\Controller\Mock' => 'Mock\Controller\MockController',
        ),
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'mock' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/mock[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Mock\Controller\Mock',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'mock' => __DIR__ . '/../view',
        ),
    ),
);