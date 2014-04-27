<?php

namespace Admin\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class User implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function exchangeArray($data)
    {
        foreach($data as $id => $value){
          $this->$id = ($value)? $value: null;
        }
        $this->user_roles = (isset($data['user_roles'])) ? $data['user_roles'] : null;
    }

     // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /*
     * Builds and returns input filter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'last_name',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 50,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'first_name',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 50,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'middle_init',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 1,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'EmailAddress',
                    ),
                ),
            )));
            
         for($i=0; $i<4; $i++)
         {
           $inputFilter->add($factory->createInput(array(
                'name' => 'role_'.$i,
                'required' => false,
               )));
         }  
           
           $inputFilter->add($factory->createInput(array(
                'name' => 'chair_privs',
                'required' => false,
               )));
           
           $inputFilter->add($factory->createInput(array(
                'name' => 'assessor_privs',
                'required' => false,
               )));
           $inputFilter->add($factory->createInput(array(
                'name' => 'liaison_privs',
                'required' => false,
               )));
         

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}