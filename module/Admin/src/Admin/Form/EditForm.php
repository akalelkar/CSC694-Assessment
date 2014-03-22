<?php
/*
 *  Edit Form
 */

namespace Admin\Form;

use Zend\Form\Form;
use Admin\Model\UserTable;
use Zend\Db\Adapter\Adapter;
use Zend\session\container;

class EditForm extends Form
{
    protected $sm;
    
    public function __construct($url,$args)
    {
        $namespace = new Container('user');
        
        // we want to ignore the name passed
        parent::__construct('edit');        
 
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $url);
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'id',
            ),
        ));
        $this->add(array(
            'name' => 'full_name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'full_name',
                'readonly' => TRUE,
                'value' => $args['full_name'],
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        
        
     $count = $args['count'];   
     for($i=0; $i< $count; $i++)
     {
        $this->add(array(
            'name' => 'role_'.$i,
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control user-roles',
                'id' => 'role_'.$i,
            ),
            'options' => array(
                'empty_option' => 'Choose Role',
                'value_options' => $args['roles'],
            ),
        ));  
     }  
       $this->add(array(
            'name' => 'liaison_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control hide',
                'id' => 'liaison_privs',
                'multiple' => 'multiple',
                'disabled' => 'disabled'
            ),
            'options' => array(
                'value_options' => $args['liaison_privs'],
            ),
        ));  
        $this->add(array(
            'name' => 'unit_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control hide',
                'id' => 'unit_privs',
                'multiple' => 'multiple',
                'disabled' => 'disabled'
            ),
            'options' => array(
                'value_options' => $args['unit_privs'],
            ),
        ));  

         
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class'=> 'btn btn-primary btn-lg',
            ),
        ));
    }    
}