<?php

namespace Application\Form;

use Zend\Form\Form;

class ApplicationForm extends Form
{
    public function __construct($url, $name = null)
    {
        // we want to ignore the name passed
        parent::__construct('application');
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $url);

        $this->add(array(
            'name' => 'module',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Plans',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary btn-lg',
            ),
        ));
        
        $this->add(array(
            'name' => 'module',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Outcomes',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary btn-lg',
            ),
        ));
        
        $this->add(array(
            'name' => 'module',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Reports',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary btn-lg',
            ),
        ));
        
        $this->add(array(
            'name' => 'module',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Admin',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary btn-lg',
            ),
        ));
    }
}