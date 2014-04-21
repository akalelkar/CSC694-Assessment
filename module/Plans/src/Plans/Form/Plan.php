<?php

namespace Plans\Form;

use Zend\InputFilter;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\FileInput;

class Plan extends Form
{
    public $rows = 6;
    public $cols = 100;
        
    public function __construct($name)
    {
        // we want to ignore the name passed
        parent::__construct('plans');
        $this->setAttribute('method', 'post');
        $this->addElements();
    }
    
    public function addElements()
    {
        /********** Hidden elements used to pass data between the pages **********/
        $this->add(array(
            'name' => 'action',
            'attributes' => array(
                'id' => 'action',
                'type' => 'hidden',
            ),
        ));
                
        $this->add(array(
            'name' => 'unit',
            'attributes' => array(
                'id' => 'unit',
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'programs',
            'attributes' => array(
                'id' => 'programs',
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'year',
            'attributes' => array(
                'id' => 'year',
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'planId',
            'attributes' => array(
                'id' => 'planId',
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'dbMetaYear',
            'attributes' => array(
                'id' => 'dbMetaYear',
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'dbDraftFlag',
            'attributes' => array(
                'id' => 'dbDraftFlag',
                'type' => 'hidden',
            ),
        ));
        
        /********** Button used to select the plan to view, modify or provide feedback **********/
        $this->add(array(
            'name' => 'viewModifySelect',
            'attributes' => array(
                'id' => 'viewModifySelect',
                'class' => 'btn btn-primary btn-sm pull-right',           
            ),
            'options' => array(
                'label' => 'Select',
            ),
        ));
            
        /********** Button used to delete the plan from the modify page **********/
        $this->add(array(
            'name' => 'modifyDelete',
            'attributes' => array(
                'id' => 'modifyDelete',
                'class' => 'btn btn-warning btn-sm pull-right',           
            ),
            'options' => array(
                'label' => 'Delete',
            ),
        ));                
                
        /********** Radio button used to decide if the user want to add a meta description or not.  Values are "yes", "no" **********/                
        $this->add(array(
            'name' => 'metaFlag',
            'attributes' => array(
                'id' => 'metaFlag',
                'type' => 'Radio',
            ),
        ));
        
        /********** Radio button used to decide if the user needs funding **********/                
        $this->add(array(
            'type' => 'Radio',
            'name' => 'fundingFlag',
            'id' => 'fundingFlag',
            'options' => array(               
                'value_options' => array(
                    '1' => 'Yes',
                    '0' => 'No',
                ),
            ),
            'attributes' => array(
                'value' => '0'
            )
        ));
    
        /********** Radio button used to set the feedback status **********/                
        $this->add(array(
            'type' => 'Radio',
            'name' => 'feedbackFlag',
            'id' => 'feedbackFlag',            
            'options' => array(
                'separator' => '  ',
                'value_options' => array(
                    '1' => 'In Need of Revision',
                    '2' => 'Ready to be Carried Out',
                ),
            ),
            'attributes' => array(
                'value' => '1',
            )
        ));
        
        /********** Textarea element used for all the textarea boxes on the view page **********/
        $this->add(array(
            'name' => 'textAreaViewOnly',
            'attributes' => array(
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,
                'readonly' => TRUE,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
                        
        
        /********** Individually named textarea elements each text box is used for one attribute on the add and modify pages **********/                        
        $this->add(array(
            'name' => 'textAssessmentMethod',
            'attributes' => array(
                'id' => 'textAssessmentMethod',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                 
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textPopulation',
            'attributes' => array(
                'id' => 'textPopulation',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,              
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textSamplesize',
            'attributes' => array(
                'id' => 'textSamplesize',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        
        $this->add(array(
            'name' => 'textAssessmentDate',
            'attributes' => array(
                'id' => 'textAssessmentDate',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                 
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textCost',
            'attributes' => array(
                'id' => 'textCost',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,               
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textAnalysisType',
            'attributes' => array(
                'id' => 'textAnalysisType',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textAdministrator',
            'attributes' => array(
                'id' => 'textAdministrator',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textAnalysisMethod',
            'attributes' => array(
                'id' => 'textAnalysisMethod',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,               
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textScope',
            'attributes' => array(
                'id' => 'textScope',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textFeedback',
            'attributes' => array(
                'id' => 'textFeedback',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textFeedbackFlag',
            'attributes' => array(
                'id' => 'textFeedbackFlag',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                 
            ),
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'name' => 'textPlanStatus',
            'attributes' => array(
                'id' => 'textPlanStatus',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                 
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        
        $this->add(array(
            'name' => 'textMetaDescription',
            'attributes' => array(
                'id' => 'textMetaDescription',
                'class'=> 'textbox',
                'type' => 'textarea',
                'rows' => $this->rows,
                'cols' => $this->cols,                  
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        
        /********** Save Draft and Submit buttons used on the add, add meta, and update pages **********/
        $this->add(array(
            'name' => 'formSubmitPlan',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'formSavePlan',
                'id' => 'formSubmitPlan',
                'class' => 'btn btn-success btn-md pull-right',               
            ),
            'options' => array(
                'label' => 'Submit',
            ),
        ));
        
        $this->add(array(
            'name' => 'formSavePlan',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'formSaveDraft',
                'id' => 'formSavePlan',
                'onclick' => 'confirm("Caution! You are saving a draft not submitting a final plan.")',
                'class' => 'btn btn-primary btn-md pull-left',
            ),
            'options' => array(
                'label' => 'Save Draft',
            ),
        ));
        
        /********** File input used to upload files **********/
        $this->add(array(
            'name' => 'fileUpload0',
            'attributes' => array(
                'type'  => 'file',
                'id' => 'fileUpload0',
                'accept' => '.pdf',
            ),
            'options' => array(
                'label' => 'Upload File',
            ),
        ));

        $this->add(array(
            'name' => 'fileUpload1',
            'attributes' => array(
                'type'  => 'file',
                'id' => 'fileUpload1',
                'accept' => '.pdf',
            ),
            'options' => array(
                'label' => 'Upload File',
            ),
        ));
        
        /********** Textbox used for the file description **********/
        $this->add(array(
            'name' => 'textFileDescription0',
            'attributes' => array(
                'type'  => 'input',
                'id' => 'textFileDescription0',
            ),
        ));
        
        $this->add(array(
            'name' => 'textFileDescription1',
            'attributes' => array(
                'type'  => 'input',
                'id' => 'textFileDescription1',
            ),
        ));   
                
    }

    // not used left as a place holder
    public function createInputFilter()
    {
        return $inputFilter;
    }
}