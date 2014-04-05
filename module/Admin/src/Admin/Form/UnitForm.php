<?php
/*
 *  UnitForm
 */
namespace Admin\Form;

use Zend\Form\Form;

class UnitForm extends Form
{
    public function __construct()     
    {

        parent::__construct('unit');
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
            'name' => 'unit_id',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class'=> 'form-control',
                'id' => 'unit_id',
            ),
            'options' => array(
                'label' => 'Unit Name',
            ),
           
            'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 3,
                            'max' => 3,
                        ),
                    ),
                ),
        ));
        
        
        $this->add(array(
            'name' => 'unitsubmit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Add',
                'id' => 'unitsubmit',
                'class'=> 'btn btn-primary btn-md',
            ),
        ));
        
    }
}