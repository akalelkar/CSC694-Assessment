<?php
/*
 *  User Form
 */

namespace Admin\Form;

use Zend\Form\Form;
use Admin\Model\UserTable;
use Zend\Db\Adapter\Adapter;
use Zend\session\container;

class UserForm extends Form
{
    protected $sm;
    
    public function __construct($name = null,$args)
    {
        $namespace = new Container('user');
        
        // we want to ignore the name passed
        parent::__construct('user');        
 
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'id',
            ),
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'first_name',
                'readonly' => TRUE,
            ),
            'options' => array(
                'label' => 'First Name',
            ),
        ));
        $this->add(array(
            'name' => 'middle_init',          
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'middle_init',
                'maxlength' => 1,
                'size' => 1,
                'style' => 'width:60px;',
                'readonly' => TRUE,
            ),
            'options' => array(
                'label' => 'M.I.',
            ),
        ));
        $this->add(array(
            'name' => 'email',          
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'email',
                'readonly' => TRUE,
            ),
            'options' => array(
                'label' => 'Email',
            ),
        ));
        $this->add(array(
            'name' => 'last_name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'last_name',
                'readonly' => TRUE,
            ),
            'options' => array(
                'label' => 'Last Name',
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