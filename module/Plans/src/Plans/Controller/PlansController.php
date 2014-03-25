<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Plans\Controller;

use Plans\Form;
use Plans\InputFilter;
use Plans\Form\Plan;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Zend\Http\Headers;
use Zend\Http\Response\Stream;
use Zend\Http\Response;
use Application\Authentication\AuthUser;


class PlansController extends AbstractActionController
{
   protected $tableResults;
   protected $tableResultsPlans;
   protected $sessionContainer;

   
   // TODO remove these attributes.  They will be set in the loggon page.  Just setting for testing
   // get these values from the session namespace
//   protected $testRole = null;
//   protected $testRole = 1; // admin
//   protected $testRole = 2; // liaison
//   protected $testRole = 3; // chair
//   protected $testRole = 4; // assessor
//   protected $testUserID = 19;   //9 = ACC, 19 = CSC, 135 = admin
//   protected $testUserEmail = ""; 
//   protected $testDatatelID = "GoodDatatelID";
//   protected $testDatatelID = null;

   
   /********** Constructors **********/

   public function __construct()
   {
      $this->sessionContainer = new Container('fileUpload');
   }

   
   /********** Database connection functions **********/

   /**
    * Used to access the plans specific SQL statements
    */
   public function getDatabaseData()
   {
       if (!$this->tableResultsPlans) {
           $this->tableResultsPlans = $this->getServiceLocator()
                                       ->get('Plans\Model\DatabaseSql');
       }
       return $this->tableResultsPlans;
   }

   
   /**
    * Used to access the generic SQL statements
    */  
   public function getGenericQueries()
   {
      if (!$this->tableResults) {
           $this->tableResults = $this->getServiceLocator()
                                      ->get('Application\Model\AllTables');
        }
        return $this->tableResults;
   }
      
      
   /********** Security supporting functions **********/
   
   /**
    * Make sure the user is valid
    */
   public function onDispatch(\Zend\Mvc\MvcEvent $e) 
   {
      $validUser = new AuthUser();
        if (!$validUser->Validate()){
            return $this->redirect()->toRoute('application');
        }
        else{
            return parent::onDispatch( $e );
        }
   }
   
   
   /********** Ajax supporting functions **********/      
      
   /**
    * Download the file from the server and create a response to send back to the client
    */
   public function downloadFileAction()
   {

      $id = $this->params()->fromRoute('id', '');
      
      $planDocument = $this->getDatabaseData()->getPlanDocumentsById($id);
      $fileSize = $planDocument['file_size'];
      $fileType = $planDocument['file_type'];
      $filename = $planDocument['file_name'] . "." . $planDocument['file_ext'];
      $fileDocument = $planDocument['file_document'];
      
      $response = $this->getResponse();
      $response->setContent($fileDocument);

      $headers = $response->getHeaders();
      $headers->clearHeaders()
              ->addHeaderLine('Content-Type', $fileType)
              ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"')
              ->addHeaderLine('Content-Length', $fileSize);
              
      return $this->response;
    }
    
    
   /**
    * Creates list of available units (departments/programs)
    * based on user role and privileges.
    *
    * Returns a Json object
    */
   public function getUnitsAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      $role = $namespace->role;
      
      // get action from id in url
      $actionChosen = $this->params()->fromRoute('id', 0);
     
      // get units for that action
      if ($actionChosen == 'View' || $role == 1){
         $results = $this->getGenericQueries()->getUnits();
      }
      else{
         $results = $this->getGenericQueries()->getUnitsByPrivId($userID);
      }
      
      // iterate through results forming a php array
      foreach ($results as $result){
         $unitData[] = $result;
      }
      
      // encode results as json object
      $jsonData = new JsonModel($unitData);
      return $jsonData;
   }
   
   
   /**
    * Creates a list of available programs base on the
    * user supplied department
    *
    * Returns a Json object
    */
   public function getProgramsAction()
   {
      // get unit from id in url
      $unitChosen = $this->params()->fromRoute('id', 0);
      
      // get programs for that unit
      $results = $this->getGenericQueries()->getProgramsByUnitId($unitChosen);
      
      // iterate through results forming a php array
      foreach ($results as $result){
         $programData[] = $result;
      }
      
      // encode results as json object
      $jsonData = new JsonModel($programData);
      return $jsonData;
   }
    
    
   /**
    * Creates a list of all the available years present
    * on the plans table
    *
    * Returns a Json object
    */
   public function getLowYearAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
      
      // get lowest year
      $lowYear = $this->getDatabaseData()->getLowYear();
  
      // encode results as json object
      $jsonData = new JsonModel(array('lowYear'=>$lowYear['year'],
                                      'role'=>$role,
                                      ));
        
      return $jsonData;
   }      
      
   /**
    * Used to physically delete the file from the server and dynamically create HTML to re-generate the
    * listing of files
    *
    * Returns a json object containing the dynamic HTML
    */
   public function deletefileAction()
   {
      // get the data from the request using json    
      $id = $_POST['id'];
      $planId = $_POST['planId'];
      
      // delete the file entry from the database
      $this->getDatabaseData()->deletePlanDocuments($id);
      
      // get the existing files left in the database after the deletion
      $planDocuments = $this->getDatabaseData()->getPlanDocumentsByPlanId($planId);
      
      // begin creating HTML to replace the content with the files left remaining
      $html = "<div>";
      
 	 // loop through all the plan documents
	 $planDocumentsCount = 0;
	 if (!empty($planDocuments)) {
            foreach ($planDocuments as $planDocument) :
               $planDocumentsCount++;
               
               // combine the file name and file ext
	       $filename = $planDocument['file_name'] . "." . $planDocument['file_ext'];

               // create the listing of files					
               $html = $html . " " . "				
	       <p>
                  <!-- Create a delete button for each row -->
                  <a class=\"btn btn-warning btn-xs\" id=\"deleteDocument\" name=\"deleteDocument\" value=\"" . $planDocument['id'] . "\"> 
		     Delete &raquo;
		  </a>
		  &nbsp;
		  &nbsp;
		  " . $planDocumentsCount .
                  ".  <a class=\"href\" id=\"downloadFile\" name=\"downloadFile\" value=\"" . $planDocument['id'] . "\" >"
                  . $filename .
                  
                  
                  " </a> Description: " . $planDocument['file_description'] . "</p> ";
	    endforeach;
	 }
      $html = $html . " " . "</div>";
                                       
      // encode results as json object
      $jsonData = new JsonModel(array(
                                      'data'=>$html,
                                      ));
      return $jsonData;
   }      
      
      
   /********** Functions called that will render pages **********/   
      
   /**
    * This action is called upon loading the inital plans home page
    */
   public function indexAction()
   {
      
      
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      $userEmail = $namespace->userEmail;
      $role = $namespace->role;
      $datatelID = $namespace->datatelID;
      $startyear = $namespace->startyear;
      // check to make sure the user is properly logged into the system
      if ($datatelID == null) {
          return $this->redirect()->toRoute('application');
      }
      
      // if general user - only view
      // get all units, since only view option is displayed
      // user in table with role - show actions
         // wait to populate units until action chosen
         
      if ($role == NULL)
      {        
         return new ViewModel(array(
            'useractions' => array('View'),
            'startyear' => $startyear,
            'role' => $role,
         ));
      }
      else if ($role == 1 or $role == 2) // liaison or admin
      {
         return new ViewModel(array(
            'useractions' => array('View', 'Add', 'Modify', 'Provide Feedback'),
            'startyear' => $startyear,
            'role' => $role,
         ));
      }
      else // chair or assessor
      {
         return new ViewModel(array(
            'useractions' => array('View', 'Add', 'Modify'),
            'startyear' => $startyear,
            'role' => $role,
         ));
      }
   }

   
   /**
    * Used to create the page that list all the selected plans for the view and modify actions.
    *
    * Returns a partial view
    */
   public function listPlansAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      $role = $namespace->role;
      
      // form for listing the plans
      $form = new Plan('listPlans');
      
      // get the data from the request using json 
      $action = $_POST['action'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];
      $planId = $_POST['planId'];

      // if there is a plan id present then it needs to be deleted from the database otherwise just list all the plans
      if($planId > 0 ){
    
         // update the active flag on the plans table to inactive
         $this->getDatabaseData()->updatePlanActiveByPlanId($planId, $userID);

         // update the active flag on the reports table to inactive         
         $this->getDatabaseData()->updateReportsActiveByPlanId($planId, $userID);
         
         // change the program string into an array for DB select
         $programsArray = $this->createProgramArray($programs);
   
         $programs = $programsArray;   
      }
 
      // create a partial view to send back to the caller
      $partialView = new ViewModel(array(
            'action' => $action,
            'unit' => $unit,
            'programs' => $programs,
            'year' => $year,
            'outcomes' => $this->getDatabaseData()->getOutcomes($unit, $programs, $year, $action),
            'plans' => $this->getDatabaseData()->getPlans($unit, $programs, $year, $action),
            'role'=> $role,
            'form'=>$form,
     ));
     
     $partialView->setTerminal(true);
     return $partialView;  
   }
   
   
   /**
    * Used to create the page that views the specific plan only
    * 
    * Returns a partial view
    */
   public function viewPlanAction()
   {
      // form for view only page
      $form = new Plan('viewPlan');
        
       // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
     
      // get the data from the request using json      
      $action = $_POST['action'];
      $planId = $_POST['planId'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];
      
      // change the program string into an array for DB select
      $programsArray = $this->createProgramArray($programs);
      
      // create a partial view to send back to the caller
      $partialView = new ViewModel(array(
         'planId' => $planId,
         'unit' => $unit,
         'programs' => $programs,
         'year' => $year,
         'outcomes' => $this->getDatabaseData()->getOutcomesByPlanId($planId, $programsArray),
         'plan' => $this->getDatabaseData()->getPlanByPlanId($planId),
         'planDocuments' => $this->getDatabaseData()->getPlanDocumentsByPlanId($planId),
         'form' => $form,
         'role' => $role,
      ));
      
      $partialView->setTerminal(true);
      return $partialView;  
   }
   
   
   /**
    * Used to create the base add page allowing the user to select "yes" or "no" for a meta plan
    *
    * Returns a partial view
    */
   public function addplanbaseAction()
   {
      // form for add plan base
      $form = new Plan('addPlan');
        
      // get the data from the request using json      
      $action = $_POST['action'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];               
      
      // get the most recent year for the meta plan selected, used for validation 
      $dbYear = $this->getDatabaseData()->getLastMetaYear($unit, $programs);
      
      // create a partial view to send back to the caller
      $partialView = new ViewModel(array(
         'action' => $action,
         'unit' => $unit,
         'programs' => $programs,
         'year' => $year,
         'dbMetaYear' => $dbYear['year'],
         'form' => $form,
      ));
   
      $partialView->setTerminal(true);
      return $partialView;  
   }
   
   /**
    * Used to create the meta add page
    *
    * Returns a partial view
    */
   public function addplanmetaAction()
   {
      // form for add meta page
      $form = new Plan('addPlanMeta');
      
      // get the data from the request using json    
      $action = $_POST['action'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];               
      
      // Initial Page Load, get request
      $partialView = new ViewModel(array(
         'action' => $action,
         'unit' => $unit,
         'programs' => $programs,
         'year' => $year,
         'form' => $form,
      ));
   
      $partialView->setTerminal(true);
      return $partialView;  
   }
   
   
   /**
    * Used for the meta insertion into the database and uploading of the files to the server
    *
    * Return is a redirect back to the main page
    */
   public function insertmetaAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      
      // form for inserting the meta 
      $form = new Plan('insertMetaPlan');
      
      // get the type of request
      $request = $this->getRequest();
      
      // get the form data
      $year = $request->getPost('year');
      $metaDescription = trim($request->getPost('textMetaDescription'));
      $programs = trim($request->getPost('programs'));
      $button = $request->getPost('formSubmitPlan'); // identify which button "Save" or "Draft" was pressed
      
      //turn programs into an array      
      $programsArray = $this->createProgramArray($programs);
      
      // set the draft flag
      $draftFlag = $this->getDraftFlag($button);
            
      // insert into plan table and obtain the primary key of the insert
      $planId = $this->getDatabaseData()->insertPlan(1, $metaDescription, $year, "","","","","","","","","","","","",$draftFlag, $userID);
     
      // get all the program ids based on the array of program
      $programIds = $this->getDatabaseData()->getProgramIdsByProgram($programsArray);

      // loop through the array of programs inserting each value into the meta plans table
      foreach ($programIds as $program) :
         $this->getDatabaseData()->insertPlanPrograms($program['programId'], $planId);
      endforeach;

      // upload files and save file name in DB
      $this->uploadFiles($request, $planId);
      
      return $this->redirect()->toRoute('plans');
   }
   
   
   /**
    * Used to create the add page
    *
    * Returns a partial view
    */
   public function addplanAction()
   {
      // form for adding a plan
      $form = new Plan('addPlan');
      
      // get the data from the request using json    
      $action = $_POST['action'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];
      
      //turn programs into an array
      $programsArray = $this->createProgramArray($programs);
      
      // get an array of outcome entities for each program
      // the outcomes array is an array of entity arrays
      foreach ($programsArray as $program) :
         $dbData = $this->getDatabaseData()->getUniqueOutcomes($unit, $program);
         $outcomes[] = $dbData;
      endforeach;

      // create a partial view to send back to the caller
      $partialView = new ViewModel(array(
            'action' => $action,
            'unit' => $unit,
            'programs' => $programs,
            'year' => $year,
            'outcomes' => $outcomes,
            'form' => $form ,
      ));
   
      $partialView->setTerminal(true);
      return $partialView; 
   }
   
   
   /**
    * Used for the insertion of the plan into the database and uploading the file to the server
    *
    * Return is a redirect back to the main page
    */
   public function insertplanAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      
      // form for uploading documents
      $form = new Plan('insertPlan');
    
      // get the type of request
      $request = $this->getRequest();
       
      // get the form data
      $button = $request->getPost('formSubmitPlan'); // identify which button "Save" or "Draft" was pressed
      $outcomeCount = $request->getPost('outcomeCount');
              
      // load the checked outcome box values into an array
      $outcomesSelected = 0;
      for ($x = 1; $x <= $outcomeCount; $x++)
      {
         $checkboxName = "checkboxOutcomes" . $x;
         $checkboxValue = $request->getPost($checkboxName);
            
         if ($checkboxValue != null) {
            $outcomeIds[] = $checkboxValue;
            $outcomesSelected++;
         }
      }
           
      // only process if there is at least one outcome selected     
      if ($outcomesSelected != 0) {
      
         // set the draft flag
         $draftFlag = $this->getDraftFlag($button);
                 
         // get the form data
         $year = $request->getPost('year');
         $assessmentMethod = trim($request->getPost('textAssessmentMethod'));
         $population = trim($request->getPost('textPopulation'));
         $sampleSize = trim($request->getPost('textSamplesize'));
         $assessmentDate = trim($request->getPost('textAssessmentDate'));
         $cost = trim($request->getPost('textCost'));
         $fundingFlag = trim($request->getPost('fundingFlag'));
         $analysisType = trim($request->getPost('textAnalysisType'));
         $administrator = trim($request->getPost('textAdministrator'));
         $analysisMethod = trim($request->getPost('textAnalysisMethod'));
         $scope = trim($request->getPost('textScope'));
         $feedback = trim($request->getPost('textFeedback'));
         $feedbackFlag = trim($request->getPost('feedbackFlag'));
         
         // insert into plan table and obtain the primary key of the insert
         $planId = $this->getDatabaseData()->insertPlan(0,"",$year, $assessmentMethod,$population,$sampleSize,$assessmentDate,$cost,$fundingFlag,$analysisType,$administrator,$analysisMethod,$scope,$feedback,$feedbackFlag,$draftFlag,$userID);                  
      
         // insert one entry for each outcome id selected              
         foreach ($outcomeIds as $outcomeId) :
            // insert into the outcome table
            $this->getDatabaseData()->insertPlanOutcome($outcomeId, $planId);
         endforeach;
                  
         // get the programs from the form and prepare them for insert       
         $programs = trim($request->getPost('programs'));

         //turn programs into an array
         $splitArray = preg_split("/,/", $programs);
         
         // trim whitespaces from the elements in the array
         foreach ($splitArray as $split) :
            $programsArray[] = trim($split);
         endforeach;
                  
         // get all the program ids based on the array of program
         $programIds = $this->getDatabaseData()->getProgramIdsByProgram($programsArray);
   
         // loop through the array of programs inserting each value into the plan programs table
         foreach ($programIds as $program) :
            $this->getDatabaseData()->insertPlanPrograms($program['programId'], $planId);
         endforeach;              
                  
         // upload files and save file name in DB
         $this->uploadFiles($request, $planId);
         
         return $this->redirect()->toRoute('plans');
      }
   }

   /**
    * Used for the modify plans page
    *
    * Returns a partial view
    */
   public function modifyPlanAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
      
      // form for modifly page
      $form = new Plan('modifyPlan');
        
      // get the data from the request using json            
      $planId = $_POST['planId'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];
      
      // change the program string into an array for DB select
      $programsArray = $this->createProgramArray($programs);
         
      // create a partial view to send back to the caller
      $partialView = new ViewModel(array(
         'planId' => $planId,
         'unit' => $unit,
         'programs' => $programs,
         'year' => $year,
         'outcomes' => $this->getDatabaseData()->getOutcomesByPlanId($planId, $programsArray),
         'plan' => $this->getDatabaseData()->getPlanByPlanId($planId),
         'planDocuments' => $this->getDatabaseData()->getPlanDocumentsByPlanId($planId),
         'role' => $role,
         'form' => $form,
      ));
   
      $partialView->setTerminal(true);
      return $partialView;    
   }
   
   /**
    * Used for the provide feedback page
    *
    * Returns a partial view
    */
   public function provideFeedbackAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
      
      // form for modify page
      $form = new Plan('provideFeedback');
        
      // get the data from the request using json            
      $planId = $_POST['planId'];
      $unit = $_POST['unit'];
      $programs = $_POST['programs'];
      $year = $_POST['year'];
      
      // change the program string into an array for DB select
      $programsArray = $this->createProgramArray($programs);
         
      // create a partial view to send back to the caller
      $partialView = new ViewModel(array(
         'planId' => $planId,
         'unit' => $unit,
         'programs' => $programs,
         'year' => $year,
         'outcomes' => $this->getDatabaseData()->getOutcomesByPlanId($planId, $programsArray),
         'plan' => $this->getDatabaseData()->getPlanByPlanId($planId),
         'planDocuments' => $this->getDatabaseData()->getPlanDocumentsByPlanId($planId),
         'role' => $role,
         'form' => $form,
      ));
   
      $partialView->setTerminal(true);
      return $partialView;    
   }
   
   /**
    * Used to update the plan in the database and upload the files to the server
    *
    * Return is a redirect back to the main page
    */
   public function updateplanAction()
   {
      
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      
      // form for updating the plan
      $form = new Plan('updatePlan');
    
      // get the type of request
      $request = $this->getRequest();

      // get the form data
      $button = $request->getPost('formSubmitPlan'); // identify which button "Save" or "Draft" was pressed             

      // set the draft flag
      $draftFlag = $this->getDraftFlag($button);
      
      // get all the data from the form
      $planId = trim($request->getPost('planId'));
      $metaDescription = trim($request->getPost('textMetaDescription'));
      $assessmentMethod = trim($request->getPost('textAssessmentMethod'));
      $population = trim($request->getPost('textPopulation'));
      $sampleSize = trim($request->getPost('textSamplesize'));
      $assessmentDate = trim($request->getPost('textAssessmentDate'));
      $cost = trim($request->getPost('textCost'));
      $fundingFlag = trim($request->getPost('fundingFlag'));
      $analysisType = trim($request->getPost('textAnalysisType'));
      $administrator = trim($request->getPost('textAdministrator'));
      $analysisMethod = trim($request->getPost('textAnalysisMethod'));
      $scope = trim($request->getPost('textScope'));
      $feedback = trim($request->getPost('textFeedback'));
      $feedbackFlag = trim($request->getPost('feedbackFlag'));
      $dbDraftFlag = trim($request->getPost('dbDraftFlag'));
      
      //update the database
      $this->getDatabaseData()->updatePlanById($planId,0,$metaDescription,$assessmentMethod,$population,$sampleSize,$assessmentDate,$cost,$fundingFlag,$analysisType,$administrator,$analysisMethod,$scope,$feedback,$feedbackFlag,$draftFlag,$userID,$dbDraftFlag);

      // upload files and save file name in DB
      $this->uploadFiles($request, $planId);
      
       return $this->redirect()->toRoute('plans');
   }

   
   /********** private functions supporting the controller **********/

   /**
    * Physically upload the files to the server and insert the entry into the database
    */
   private function uploadFiles($request, $planId)
   {
      $uploadedFiles = 2;
      
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      
      // loop through each upload element grabbing the file information
      for ($x = 0; $x < $uploadedFiles; $x++) {

         // get the file information
         $fileName = $_FILES['fileUpload' . $x]['name'];
         $tmpName  = $_FILES['fileUpload' . $x]['tmp_name'];
         $fileSize = $_FILES['fileUpload' . $x]['size'];
         $fileType = $_FILES['fileUpload' . $x]['type'];
   
         // check to make sure the file uploaded to the temp file on the server
         if ($fileSize > 0) {
   
            // read the file
            $fp      = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            fclose($fp);
                 
               var_dump($planId);        
            //get the file description
            $fileDescription = trim($request->getPost('textFileDescription' . $x));
               
            // insert the file and information into the database
            $this->getDatabaseData()->insertPlanDocuments($planId, $fileName, $fileDescription, $userID, $content, $fileSize, $fileType);
         }
      }
   }
   
   /**
    * Set and return the draft flag based on the type of button pressed
    */
   private function getDraftFlag($button)
   {
      // set the draft flag
      $draftFlag = 0;
      if ($button == "formSaveDraft") {
         $draftFlag = 1;
      }
      return $draftFlag;
   }
   
   /**
    * Turn a comma separated list of programs into an array
    *
    * return the array
    */
   private function createProgramArray($programs)
   {
      //turn programs into an array
      $splitArray = preg_split("/,/", $programs);
         
      // trim whitespaces from the elements in the array
      foreach ($splitArray as $split) :
         $programsArray[] = trim($split);
      endforeach;
      
      return $programsArray;
   }
}
