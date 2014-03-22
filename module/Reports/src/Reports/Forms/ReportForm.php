<?PHP
namespace Reports\forms;

use Zend\Form\Form;
class ReportForm extends Form
{
    // Report form used for capturing report data from view
    public function init()
    {
        
        // Action data
        $this->add(array(
            'name' => 'actions',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'actions',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
            ),
        ));
        
        // Conclusion data
        $this->add(array(
            'name' => 'conclusions',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'conclusions',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
            ),
        ));
        
        // Result data
        $this->add(array(
            'name' => 'results',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'results',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
            ),
        ));
        
        // Population data
        $this->add(array(
            'name' => 'population',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'population',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
            ),
        ));
        
        // Status data
        $this->add(array(
            'name' => 'status',
            'attributes' => array(
                'type'  => 'text',
                'id' => 'status',
                'name' => 'status',
            ),
        ));
        
        // Method data
        $this->add(array(
            'name' => 'method',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'method',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
            ),
        ));
        
        // Feedback data
        $this->add(array(
            'name' => 'feedbackText',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'feedbackText',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
            ),
        ));
        
        // Feedback flag
        $this->add(array(
            'type' => 'Radio',
            'name' => 'feedbackFlag',
            'id' => 'feedbackFlag',
            'options' => array(               
                'value_options' => array(
                    '1' => 'In Need of Revision',
                    '2' => 'Approved',
                ),
            )
        ));
        
        // File input for attachments
        $this->add(array(
            'name' => 'fileUpload0',
            'attributes' => array(
                'type'  => 'file',
                'accept' => '.pdf',
                'id' => 'fileUpload0',
                'name' => 'fileUpload0',

            ),
            'options' => array(
                'label' => 'Upload File',
            ),
        ));
        
        $this->add(array(
            'name' => 'fileDescription0',
            'attributes' => array(
                'type'  => 'input',
                'id' => 'fileDescription0',
                'name' => 'fileDescription0',

            ),
        ));
        
        // File input for attachments
        $this->add(array(
            'name' => 'fileUpload1',
            'attributes' => array(
                'type'  => 'file',
                'accept' => '.pdf',
                'id' => 'fileUpload1',
                'name' => 'fileUpload1',

            ),
            'options' => array(
                'label' => 'Upload File',
            ),
        ));
        
        $this->add(array(
            'name' => 'fileDescription1',
            'attributes' => array(
                'type'  => 'input',
                'id' => 'fileDescription1',
                'name' => 'fileDescription1',
            ),
        ));
        
        // Report id
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'textarea',
                'id' => 'id',
                'class' => 'textbox',
                'rows' => '10',
                'cols' => '100',
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
}
?>
