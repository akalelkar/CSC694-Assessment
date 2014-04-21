<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Reports\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Reports\Model\ReportTable;
use Reports\Model\PlanTable;
use Reports\Model\PlanData;
use Reports\Form;
use Reports\Form\Report;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Application\Authentication\AuthUser;

class ReportsController extends AbstractActionController
{
   // This holds table results for certain methods
   protected $tableResults;  
   
   public function onDispatch(\Zend\Mvc\MvcEvent $e) 
   {   $validUser = new AuthUser();
        if (!$validUser->Validate()){
            return $this->redirect()->toRoute('application');
        }
        else{
            return parent::onDispatch( $e );
        }
   }
   
   // Returns main index with left select options and blank right side
   public function indexAction()
   {

      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      $role = $namespace->role;
      $startYear = $namespace->startYear;
      
      // Get select form for passing selected data
      $sl = $this->getServiceLocator();

      // if general user - only view
      // get all units, since only view option is displayed
      if ($role == NULL){
         $results = $this->getGenericQueries()->getUnits();
         // iterate over database results forming a php array
         foreach ($results as $result){
             $unitarray[] = $result;
         }
         return new ViewModel(array(
             'useractions' => array('View'),
             'units' => $unitarray,
             'startYear' => $startYear,
         ));
      }
      else if ($role == 1 or $role == 2) // liaison or admin
      {
         return new ViewModel(array(
            'useractions' => array('View', 'Add', 'Modify', 'Provide Feedback'),
            'startYear' => $startYear,
            'role' => $role,
         ));
      }
      else{ // chair or assessor
         return new ViewModel(array(
            'useractions' => array('View', 'Add', 'Modify'),
            'startYear' => $startYear,));
      }
   }

   // This function is called by viewPlansAction to create an array of the plans
   // The array $plans is passed by reference as the second parameter.
   public function createPlans($results, &$plans){
         // Start with an empty plan
         $currPlan = new PlanData(null, null, null);
    
         // Loop through results, adding all outcomes for same plan id
         // to same planData, otherwise start a new planData
         foreach ($results as $result){
            // determine if report exists for plan (0-none, 1-draft, 2-submitted)
            $reportType = $this->getReports()->reportExists($result['id']);
            $test[] = $result;
            if(is_null($currPlan->id)){
               $currPlan = new PlanData($result['id'], $result['meta_flag'], $reportType);
            }elseif($currPlan->id != $result['id']){
               // new plan starting
               // add existing currPlan to array of $plans 
               array_push($plans, $currPlan);
               $currPlan = new PlanData($result['id'], $result['meta_flag'], $reportType);
            }
            array_push($currPlan->descriptions, $result['text']);
         }
         // Add last plan
         array_push($plans, $currPlan);
   }

   // Called to show all matching plans after selection is made on left nav
   public function viewPlansAction()
   {
      // Get post data which is json
      $jsonData = $this->getRequest()->getContent();
      
      // Get the plans that have outcomes
      $results = $this->getReports()->getPlansWithOutcomes($jsonData);
      $results2 = $this->getReports()->getPlansWithMeta($jsonData);
      
      // if no data display page with appropriate message
      if (count($results) == 0 && count($results2) == 0){
         $partialView = new ViewModel(array(
            'results' => false,
         ));
      }
      // data exists, show plans
      else{
               
         // Create array to hold the plans
         $finalplans = array();
            
         // If there are plans with outcomes add to array for display
         if(count($results) > 0){
             $this->createPlans($results, $finalplans);
         }
         // If there are plans with meta assessment add to array for display
         if(count($results2) > 0){
             $this->createPlans($results2, $finalplans);
         }
         // Get the action from the request json data
         $data = json_decode($jsonData, true);
         $action = $data['action'];
         $title = $data['unit'] . ': ' . $data['programNames'] . ': ' . $data['year'];
         // Create view with plan data
         $partialView = new ViewModel(array(
            'plans' => $finalplans, 'action' => $action, 'results' => true, 'title' => $title,
         ));
      }      
         // Set to terminal so ignores header/footer and return
      $partialView->setTerminal(true);
      return $partialView;
   
   }
    
   // Display addReport view - This is the view where the user enters the report data to add
   // See addNewReportAction for the code that grabs the user entered data and adds the report
   public function addReportAction()
   {
      // Get plan id from post data
      $planId = $this->params()->fromPost('id');
      
      // Get count of reports already associated with this plan
      $count = $this->getReports()->reportExists($planId);
      
      // If there is one, tell the view to alert user can't add
      if($count > 0){
         $partialView = new ViewModel(array(
            'results' => true,
         ));
         $partialView->setTerminal(true);
         return $partialView;
      }
   
      // If no reports exist, get plan data for selected plan
      $results = $this->getReports()->getPlanForAdd($planId);
      //$sl = $this->getServiceLocator();
      $form = new Report();
      //$form = $sl->get('FormElementManager')->get('Reports\forms\ReportForm');
      $descriptions = array();

      // Will only be 1 plan returned, but 1 or more rows for each outcome/assessment
      // Loop through and add outcomes/assesments to descriptions array
      foreach ($results as $result){
         array_push($descriptions, $result['text']);
         $planData[] = $result;
      }
      
      // Return addReport view with plan data
      $partialView = new ViewModel(array(
         'results' => false, 'form' => $form, 'planData' => $planData, 'descriptions' => $descriptions,
      ));
      
      // Remove headers/footers
      $partialView->setTerminal(true);
      return $partialView;
   }
    
   // Gets individual report details when user selects a plan they wish
   // to view the report for
   public function viewReportAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
      
      // Get plan id from post data
      $planId = $this->params()->fromPost('id');
      // Get report that correlates to that plan
      $results = $this->getReports()->getReport($planId);
      
      // If there is one
      if(count($results) > 0){
         
         // Store descriptions(outcomes or assessment) in array
         $descriptions = array();
         foreach ($results as $result){
           
            array_push($descriptions, $result['text']);
            $reportArray[] = $result;
         }
         
         // Get documents for the report
         $results = $this->getReports()->getDocuments($reportArray[0]['id']);
         
         $documentArray = array();
         foreach ($results as $result){
            $documentArray[] = $result;
         }
       
         // Make view and give it data
         $partialView = new ViewModel(array(
            'role' => $role,
            'report' => $reportArray, 'descriptions' => $descriptions, 'documents' => $documentArray, 'results' => true,
         ));
      
      // If there is no report, tell the view to alert user
      }else{
         $partialView = new ViewModel(array('results' => false));
      }
      
      // Return view without headers/footers
      $partialView->setTerminal(true);
      return $partialView;
   }
    
   // Called after user selects a plan they want to modify the report for
   // Displays report data associated with user selected plan 
   public function modifyReportAction()
   {
      
      // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
      
      // Get plan id from post data
      $planId = $this->params()->fromPost('id');
      
      // Get report data for this plan
      $results = $this->getReports()->getReport($planId);
      
      // If there is a report
      if(count($results) > 0){
         $descriptions = array();
         
         // Loop through results, 1 report, 1 or more outcomes/assessment
         foreach ($results as $result){
            array_push($descriptions, $result['text']);
            $reportArray[] = $result;
         }
         
         // Get a report form for putting in the view
         //$sl = $this->getServiceLocator();
         //$form = $sl->get('FormElementManager')->get('Reports\forms\ReportForm');
         $form = new Report();
         
         // Get documents for the report
         $results = $this->getReports()->getDocuments($reportArray[0]['id']);
      
         // Add results to an array
         $documentArray = array();
         foreach ($results as $result){
            $documentArray[] = $result;
         }
         
         // Create view, give it data
         $partialView = new ViewModel(array(
            'report' => $reportArray, 'descriptions' => $descriptions, 'form' => $form, 'results' => true,
            'role' => $role, 'documents' => $documentArray,
         ));
         
         
      // If there is no report, tell the view to alert user
      }else{
         $partialView = new ViewModel(array(
            'results' => false,
         ));
      }
      
      // Return view, no headers/footers
      $partialView->setTerminal(true);
      return $partialView;
   }
   
    
   // Called after user selects a report they want to provide feedback for
   // Displays report data associated with user selected plan 
   public function provideFeedbackAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $role = $namespace->role;
      
      // Get plan id from post data
      $planId = $this->params()->fromPost('id');
      
      // Get report data for this plan
      $results = $this->getReports()->getReport($planId);
    
      // If there is a report
      if(count($results) > 0){
         $descriptions = array();
       
         // Loop through results, 1 report, 1 or more outcomes/assessment
         foreach ($results as $result){
            array_push($descriptions, $result['text']);
            $reportArray[] = $result;
         }
         
         // Get a report form for putting in the view
         //$sl = $this->getServiceLocator();
         //$form = $sl->get('FormElementManager')->get('Reports\forms\ReportForm');
         $form = new Report();
         
         // Get documents for the report
         $results = $this->getReports()->getDocuments($reportArray[0]['id']);
      
         // Add results to an array
         $documentArray = array();
         foreach ($results as $result){
            $documentArray[] = $result;
         }
     
         // Create view, give it data
         $partialView = new ViewModel(array(
            'report' => $reportArray, 'descriptions' => $descriptions, 'form' => $form, 'results' => true,
            'role' => $role, 'documents' => $documentArray,
         ));
         
         
      // If there is no report, tell the view to alert user
      }else{
         $partialView = new ViewModel(array(
            'results' => false,
         ));
      }
      
      // Return view, no headers/footers
      $partialView->setTerminal(true);
      return $partialView;
   }
    
   // This is called after user makes modifications to the report data and
   // wants to send it to the database
   public function updateReportAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
    
      // Call method to update report, give arguments from post data
      // This receives a status of 0 for submit, 1 for draft, 2 for delete draft, 3 for delete report

      // ReportTable object handles the difference
      $this->getServiceLocator()->get('ReportTable')
               ->updateReport($this->params()->fromPost('id'),
                  $this->params()->fromPost('population'),
                  $this->params()->fromPost('results'),
                  $this->params()->fromPost('conclusions'),
                  $this->params()->fromPost('actions'),
                  $this->params()->fromPost('status'),
                  $userID
                 );

      $ctr = 0;
      $files = array();
      
      // Grab all the files from the post data and put into an array
      foreach($_FILES as $f ){
         
         // If they have content, proceed
         if(filesize($f['tmp_name']) > 0){
            // read the file into binary and store locally
            $tmpFile = fopen($f['tmp_name'], 'r');
            $content = fread($tmpFile, filesize($f['tmp_name']));
            fclose($tmpFile);
            $files[$ctr]['size'] = $f['size'];
            $files[$ctr]['content'] = $content;
            $files[$ctr]['description'] = $this->params()->fromPost('fileDescription' . $ctr);
   
            $fileParts = preg_split('/\./', $f['name'], null, PREG_SPLIT_NO_EMPTY);
            $files[$ctr]['name'] = $fileParts[0];
            $files[$ctr]['ext'] = $fileParts[1];
            
            $ctr++;
         }
      }
      
      // If there are files that need saving, send to saveFiles method
      if(count($files) > 0){
         $this->getServiceLocator()->get('ReportTable')->saveFiles($files,
                                                                $this->params()->fromPost('id'),
                                                                $userID);
      }
      
      // Get all files checked for deletion
      $deletes = $this->params()->fromPost('delete');
      
      // If some exist, then delete them
      if(count($deletes) > 0){
               $this->getServiceLocator()->get('ReportTable')->deleteFiles($deletes);
      }

      // Redirect user to index if successful
      $this->redirect()->toRoute('index');
   }
   
   
   // This is called from provide-Feedback.phtml for inserting feedback into existing report
      public function addFeedbackAction(){
      
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
   
      // Call method to update feedback
      // This receives a status of 0 for submit, 1 for draft, 2 for delete draft, 3 for delete report

      // ReportTable object handles the difference
      $this->getServiceLocator()->get('ReportTable')
               ->updateFeedback($this->params()->fromPost('id'),
                  $userID,
                  $this->params()->fromPost('feedbackText'),
                  $this->params()->fromPost('feedbackFlag'));
      

      // Redirect user to index if successful
      $this->redirect()->toRoute('index');
   }
   // This is called from addReport view for inserting new report
   public function addNewReportAction()
   {
      $namespace = new Container('user');
      $userID = $namespace->userID;
   
      // Call addReport method in ReportTable and give arguments from post data
      // Status goes to draft flag, 0 for no, 1 for draft
      $id = $this->getServiceLocator()->get('ReportTable')
               ->addReport($this->params()->fromPost('id'),
                  $this->params()->fromPost('population'),
                  $this->params()->fromPost('results'),
                  $this->params()->fromPost('conclusions'),
                  $this->params()->fromPost('actions'),
                  $this->params()->fromPost('status'),
                  $userID);

      $ctr = 0;
      $files = array();
      
      // Grab all files to add from post data
      foreach($_FILES as $f ){
         if(filesize($f['tmp_name']) > 0){
            // read the file
            $tmpFile      = fopen($f['tmp_name'], 'r');
            $content = fread($tmpFile, filesize($f['tmp_name']));
            fclose($tmpFile);
            $files[$ctr]['size'] = $f['size'];
   
            $files[$ctr]['content'] = $content;
            $files[$ctr]['description'] = $this->params()->fromPost('fileDescription' . $ctr);
   
            $fileParts = preg_split('/\./', $f['name'], null, PREG_SPLIT_NO_EMPTY);
            $files[$ctr]['name'] = $fileParts[0];
            $files[$ctr]['ext'] = $fileParts[1];
            
            $ctr++;
         }
      }
      
      // If there are some to add, save them
      if(count($files) > 0){
         $this->getServiceLocator()->get('ReportTable')->saveFiles($files,
                                                                $id,
                                                                $userID);
      }
      
      // When done, redirect to blank index page
      $this->redirect()->toRoute('index');
   }
    
   // Used to call methods in the ReportTable class from this controller
   public function getReports(){
      if (!$this->tableResults) {
            $this->tableResults = $this->getServiceLocator()
                       ->get('ReportTable');
      }
      return $this->tableResults;
   }
   
   // Used to call generic queries in Application module used by all modules
   public function getGenericQueries()
   {
      if (!$this->tableResults) {
          $this->tableResults = $this->getServiceLocator()
                                     ->get('Application\Model\AllTables');
      }
      return $this->tableResults;
   }
    
   // Creates list of available units (departments/programs)
   // based on user role and privileges.
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
   
   // Gets programs for given unit
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
   
   // Downloads file from the DB
   public function downloadAction()
   {
      // Get the document id from the route
      $id = $this->params()->fromRoute('id', '');
      
      // Get document from the DB
      $results = $this->getServiceLocator()->get('ReportTable')->getDocument($id);
      foreach ($results as $result){
           $document[] = $result;
      }
      
      // Get file name and blob data
      $filename = $document[0]['file_name'] . "." . $document[0]['file_ext'];
      $data = $document[0]['file_document'];

      // Stick the file in the response
      $response = $this->getResponse();
      $response->setContent($data);

      $headers = $response->getHeaders();
      $headers->clearHeaders()
              ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"');

      // Return the response to user for downloading
      return $this->response;
    }
}
