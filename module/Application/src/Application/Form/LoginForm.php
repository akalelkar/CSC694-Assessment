<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Validator\Regex;

class LoginForm extends Form
{
    public function __construct($url)
    {
        // we want to ignore the name passed
        parent::__construct('login');
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $url);

        $this->add(array(
            'name' => 'userName',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => 'User ID ',                
            ),
            'validators' => array(
                     array(
                           'name' => 'Regex',
                           'options' => array(
                                'pattern'   =>  '[a-zA-Z][a-zA-Z0-9]*',
                         ))
            ),
            'filters' => array(
                         array('StringTrim')
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