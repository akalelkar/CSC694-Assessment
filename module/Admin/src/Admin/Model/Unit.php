<?php

namespace Admin\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Unit implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function exchangeArray($data)
    {
        foreach($data as $id => $value){
            $this->$id = ($value)? $value: null;
        }
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
     * create form input filter
     */
  public function getInputFilter()
    {
               
      if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
 
            
            $inputFilter->add($factory->createInput(array(
                'name' => 'unit_id',
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
                            'max' => 3,
                        ),
                    ),
                ),
            )));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}