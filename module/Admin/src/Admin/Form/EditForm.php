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
        //$url = $url . $args['action'];
        //$this->setAttribute('action', $url);
    
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
        
        $this->add(array(
            'name' => 'add_liaison_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'add_liaison_privs',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'value_options' => $args['liaison_privs'],
             ),
        ));  
        $this->add(array(
            'name' => 'add_chair_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'add_chair_privs',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'value_options' => $args['chair_privs'],
            ),
        ));  
        
        $this->add(array(
            'name' => 'add_assessor_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'add_assessor_privs',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'value_options' => $args['assessor_privs'],
            ),
        ));  
        
        $this->add(array(
            'name' => 'user_assessor_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'user_assessor_privs',
                'multiple' => 'multiple',
                'disabled' => 'disabled',
            ),
            'options' => array(
                'value_options' => $args['user_assessor_privs'],
            ),
        ));  
         
        $this->add(array(
            'name' => 'user_chair_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'user_chair_privs',
                'multiple' => 'multiple',
                'disabled' => 'disabled',
            ),
            'options' => array(
                'value_options' => $args['user_chair_privs'],
            ),
        ));  
        
        $this->add(array(
            'name' => 'user_liaison_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'user_liaison_privs',
                'multiple' => 'multiple',
                'disabled' => 'disabled',
            ),
            'options' => array(
                'value_options' => $args['user_liaison_privs'],
            ),
        ));  
        
        $this->add(array(
            'name' => 'remove_assessor_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'remove_assessor_privs',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'value_options' => $args['user_assessor_privs'],
            ),
        ));  
         
        $this->add(array(
            'name' => 'remove_chair_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'remove_chair_privs',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'value_options' => $args['user_chair_privs'],
            ),
        ));  
        
        $this->add(array(
            'name' => 'remove_liaison_privs',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'remove_liaison_privs',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'value_options' => $args['user_liaison_privs'],
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Submit Changes',
                'id' => 'submit',
                'class'=> 'btn btn-success btn-md',
            ),
        ));
       
    }    
}