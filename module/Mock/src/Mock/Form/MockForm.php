<?php

namespace Mock\Form;

use Zend\Form\Form;

error_reporting(E_ALL);
ini_set('display_errors',1);

class MockForm extends Form
{
    public function __construct($sname = null)
    {
        // we want to ignore the sname passed
        parent::__construct('mock');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'sid',
            'attributes' => array(
                'type' => 'hidden',
               
        
            ),
        ));
        $this->add(array(
            'name' => 'major',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Major',
            ),
        ));
        $this->add(array(
            'name' => 'sname',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'credits',
            'attributes' => array(
                'type' => 'int',
            ),
            'options' => array(
                'label' => 'Credits',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Go',
                'sid' => 'submitbutton',
            ),
        ));
    }
}
