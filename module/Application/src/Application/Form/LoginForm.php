<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('login');
        $this->setAttribute('method', 'post');
        //$this->setAttribute('action', 'authenticate');
        
        $this->add(array(
            'name' => 'userName',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => 'User ID ',                
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'class' => 'form-control'
                
            ),
            'options' => array(
                'label' => 'Password ',                
            ),
            
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Login',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}