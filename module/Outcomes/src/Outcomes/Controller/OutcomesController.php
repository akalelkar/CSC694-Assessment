<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Outcomes\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Application\Authentication\AuthUser;


class OutcomesController extends AbstractActionController
{ 
   protected $tableResults;

   public function onDispatch(\Zend\Mvc\MvcEvent $e) 
   {
        $validUser = new AuthUser();
        if (!$validUser->Validate()) {
            return $this->redirect()->toRoute('home');
        }
        return parent::onDispatch($e);
   }
   
   public function indexAction()
   {
      $results = $this->getGenericQueries()->getUnits();
      // iterate over database results forming a php array
      foreach ($results as $result){
         $unitarray[] = $result;
      }
      
      // pass all the units into the view
      return new ViewModel(array(
                'units' => $unitarray,
      ));      
   }
    
   // Gets list of available units (departments)
   // based on user role and privileges.
   public function getUnitsAction()
   {      
      // get action from id in url
      $actionChosen = $this->params()->fromRoute('id', 0);
     
      // get units for that action
      if ($actionChosen == 'View'){
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
   
   // Gets list of active programs based on the unit selected.
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
 
    // Gets list of outcomes based on the unit/program selected.
    // Also handles processing user request.  This way, after the request is processed
    // the getOutcomes view is rendered showing the change.
    public function getOutcomesAction()
    {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      $userEmail = $namespace->userEmail;
      $role = $namespace->role;
      $datatelID = $namespace->datatelID;
      
      // get program that's selected from id in url
      $programSelected = $this->params()->fromRoute('id', 0);
      $request = $this->getRequest();
      
      // this gets flipped to 0 if no permissions are found when 'Submit' is clicked on the left side
      $adminFlag = 1;
      $userAction = $request->getPost('action');
   
      // different code needs to run depending on how this action is being called
      switch($userAction){
         
         case "submitClick":
            $unitId = $request->getPost('unitId');
            // admin flag only needs to be checked here because only someone with permissions can get there from the other actions
            $adminFlag = $this->getOutcomesQueries()->checkPermissions($userID, $unitId);
            break;
      
         case "add":
            // create the outcome in the database
            $outcomeText = $request->getPost('outcomeText');
            $this->getOutcomesQueries()->addOutcome($programSelected, $outcomeText, $userID);
            break;
            
         case "edit":
            // deactivate the outcoming being 'edited' and create a new one
            $oidToDeactivate = $request->getPost('oidToDeactivate');
            $outcomeText = $request->getPost('outcomeText');
            $this->getOutcomesQueries()->editOutcome($programSelected, $outcomeText, $oidToDeactivate, $userID);
            break;
         
         case "delete":
            $outcomeId = $request->getPost('oid');
            $this->getOutcomesQueries()->deactivateOutcome($outcomeId, $userID);
            break;
         
         default:
            // no extra steps needed - 'back' would fall into this category
            break;
      }
      
      // get outcomes for the selected program
      $results = $this->getOutcomesQueries()->getAllActiveOutcomesForProgram($programSelected);
      
      // pass in the selected program, its outcomes and whether or not the user has admin rights
      $partialView = new ViewModel(array(
         'outcomes' => $results,
         'programId' => $programSelected,
         'adminFlag' => $adminFlag, 
      ));
      // ignore the layout template
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   
   // Called when the user selects the add button.
   // The getOutcomes.phtml file uses an ajax call to invoke this method.
   // This method will render the addOutcomes.phtml file.
   public function addOutcomeAction()
   {
      // get programs from id in url
      $programChosen = $this->params()->fromRoute('id', 0);
         
      // render the addOutcome screen
      $partialView = new ViewModel(array(
         'programChosen' => $programChosen,
      ));
      // ignore the layout template
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Called when the user selects the modify button.
   // The getOutcomes.phtml file uses an ajax call to invoke this method.
   // This method will render the editOutcomes.phtml file.
   public function editOutcomeAction()
   {
      // get programs from id in url
      $programChosen = $this->params()->fromRoute('id', 0);
      
      // need this to pull out POST data
      $request = $this->getRequest();
      
      // get the outcome id from post data then get the outcome from the id
      $outcomeId = $request->getPost('oid');
      $outcomeText = $request->getPost('text');
      $outcomeNumber = $request->getPost('number');

      $partialView = new ViewModel(array(
         'outcomeId' => $outcomeId,
         'outcomeText' => $outcomeText,
         'programChosen' => $programChosen,
         'outcomeNumber' => $outcomeNumber,
      ));
      
      // ignore the layout template
      $partialView->setTerminal(true);
      return $partialView;
   }
      
   public function getGenericQueries()
   {
      if (!$this->tableResults){
         $this->tableResults = $this->getServiceLocator()
                                       ->get('Application\Model\AllTables');                   
      }
      return $this->tableResults;
   }
    
   public function getOutcomesQueries()
   {
      if (!$this->tableResults) {
         $this->tableResults = $this->getServiceLocator()
                       ->get('Outcomes\Model\OutcomesTable');                
      }
      return $this->tableResults;
   }
}
