<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;


use Admin\Model\Queries;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Session\Container;
use Application\Authentication\AuthUser;

class QueriesController extends AbstractActionController
{
   protected $tableResults;
   
   public function onDispatch(\Zend\Mvc\MvcEvent $e) {
       /* $validUser = new AuthUser();
        if (!$validUser->Validate())
        {
            return $this->redirect()->toRoute('application');
        }
        else {
            $namespace = new Container('user');
            if($namespace->role != 1)
            {
              return $this->redirect()->toRoute('application');
            }
       */
            return parent::onDispatch($e);
       // }
               
    }

   public function indexAction()
   {
      // get the session variables
      $namespace = new Container('user');
      $userID = $namespace->userID;
      $role = $namespace->role;
      $startYear = $namespace->startYear;
      return new ViewModel(array('startYear' => $startYear,
                                 'role' => $role));
   }
 
   // Show programs that don't have any plans for a specific year 
   public function getQuery1Action()
   {
      
      $namespace = new Container('user');
      $appStartYear = $namespace->appStartYear;
    
      $resultsarray = '';
      
      // get year from route
      $year = $this->params()->fromRoute('id', 0);
      
      $results = $this->getAdminQueries()->getProgramsMissingPlansForYear($year, $appStartYear);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }
      $totalDisplayed = $results->count();
      $totalPrograms = $this->getAdminQueries()->getActiveProgramsCount($year, $appStartYear);

      $partialView = new ViewModel(array('querytitle' => 'Programs Missing Plans For ' . $year,
                                         'programs' => $resultsarray,
                                         'totalPrograms' => $totalPrograms,
                                         'totalDisplayed' => $totalDisplayed));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that don't have any reports for a specific year 
   public function getQuery2Action(){
      
      $namespace = new Container('user');
      $appStartYear = $namespace->appStartYear;
      
      $resultsarray = '';
      
      // get year from route
      $year = $this->params()->fromRoute('id', 0);
      
      // reports for year 2014 (school year 2013-2014) are for plans entered 2012-2013
      // need to subtract 1 from year to grab last year's plans
      $year = $year - 1;
      // Get a tuple for each report missing
      // This information is what is displayed in the view
      $results = $this->getAdminQueries()->getMissingReportsForYear($year, $appStartYear);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }
      
      // get a count of the # of programs missing reports - counts program only once regardless
      // of # of missing reports - this is used to display the counts shown at top of report
      $totalMissingPrograms = $this->getAdminQueries()->getProgramsMissingReportsForYear($year, $appStartYear)->count();
      // get count of missing reports 
      $totalDisplayed = $results->count();
      // get total number of active programs for year
      $totalPrograms = $this->getAdminQueries()->getActiveProgramsCount($year, $appStartYear);

      // get total number of plans submitted for year
      $totalPlans = $this->getAdminQueries()->getPlansCountForYear($year);
      
      $partialView = new ViewModel(array('querytitle' => 'Programs Missing Reports For ' . $year,
                                         'programs' => $resultsarray,
                                         'totalDisplayed' => $totalDisplayed,
                                         'totalMissingPrograms' => $totalMissingPrograms,
                                         'totalPrograms' => $totalPrograms,
                                         'totalPlans' => $totalPlans));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that are conducting meta assessment
   public function getQuery3Action(){
      
      $resultsarray = '';
      
      // get year from route
      $year = $this->params()->fromRoute('id', 0);
      
      $results = $this->getAdminQueries()->getProgramsDoingMetaAssessment($year);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }

      $partialView = new ViewModel(array('querytitle' => 'Programs Conducting Meta Assessment For ' . $year,
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that are requesting funding
   public function getQuery4Action(){
      
      $resultsarray = '';
      
      // get year from route
      $year = $this->params()->fromRoute('id', 0);
      
      $results = $this->getAdminQueries()->getProgramsNeedingFunding($year);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }

      $partialView = new ViewModel(array('querytitle' => 'Programs Requesting Funding For ' . $year,
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs with modified outcomes
   public function getQuery5Action(){
      
      $resultsarray = '';
      
      // get year from route
      $fromDate = $this->params()->fromRoute('id', 0);

      $results = $this->getAdminQueries()->getProgramsWithModifiedOutcomes($fromDate);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }
      // format date for output
      $fromDate = substr($fromDate, 0, 2) . '-' .
                  substr($fromDate, 2, 2) . '-' .
                  substr($fromDate, 4);

      $partialView = new ViewModel(array('querytitle' => 'Programs With Modified Outcomes Since ' . $fromDate,
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that have added or modified a report for a previous year
   public function getQuery6Action(){
      
      $resultsarray = '';
      
      // determine current school year
      date_default_timezone_set('America/Chicago');
      $currentMonth = date('m', time());
      $currentYear = date('Y', time());
      
      // determine current school year July 1 - June 31
      // year in plans table is spring term year (2013-2014 school year is entered as 2014)
      if ($currentMonth > 6){
         $currentYear = $currentYear + 1;
      }
      $results = $this->getAdminQueries()->getProgramsModifiedLastYearsPlans($currentYear);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }

      $partialView = new ViewModel(array('querytitle' => 'Programs That Modified Last Year\'s Plans ',
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that have added or modified a report for the previous year
   public function getQuery7Action(){
      
      $resultsarray = '';
      
      // determine current school year
      date_default_timezone_set('America/Chicago');
      $currentMonth = date('m', time());
      $currentYear = date('Y', time());
      
      // determine current school year July 1 - June 31
      // year in plans table is winter/spring term year (2013-2014 school year is entered as 2014)
      if ($currentMonth > 6){
         $currentYear = $currentYear + 1;
      }
      $results = $this->getAdminQueries()->getProgramsModifiedLastYearsReports($currentYear);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }

      $partialView = new ViewModel(array('querytitle' => 'Programs That Modified Last Year\'s Reports ',
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that have not yet been reviewed by liaisons
   public function getQuery8Action(){
      
      $resultsarray = '';
      
      // get year from route
      $year = $this->params()->fromRoute('id', 0);
      
      $results = $this->getAdminQueries()->getProgramsNeedingFeedback($year);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }

      $partialView = new ViewModel(array('querytitle' => 'Programs Needing Feedback For ' . $year,
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }
   
   // Show programs that have changed their assessors
   public function getQuery9Action(){
      
      $resultsarray = '';
      
      // get year from route
      $fromDate = $this->params()->fromRoute('id', 0);
      
      $results = $this->getAdminQueries()->getProgramsWhoChangedAssessors($fromDate);
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }

       // format date for output
      $fromDate = substr($fromDate, 0, 2) . '-' .
                  substr($fromDate, 2, 2) . '-' .
                  substr($fromDate, 4);
      
      $partialView = new ViewModel(array('querytitle' => 'Programs That Changed Assessors For ' . $fromDate,
                                         'programs' => $resultsarray));
      $partialView->setTerminal(true);
      return $partialView;
   }

   // Show programs that have not yet submitted learning outcomes
   public function getQuery10Action()
   {      
      $namespace = new Container('user');
      $appStartYear = $namespace->appStartYear;
          
      $resultsarray = '';
      
      // get year from route
      $year = $this->params()->fromRoute('id', 0);
      
      $results = $this->getAdminQueries()->getProgramsMissingOutcomes();
      
      // iterate over database results forming a php array
      foreach ($results as $result){
          $resultsarray[] = $result;
      }
      $totalDisplayed = $results->count();
      $totalPrograms = $this->getAdminQueries()->getActiveProgramsCount($year, $appStartYear);

      $partialView = new ViewModel(array('querytitle' => 'Programs Missing Learning Outcomes ',
                                         'programs' => $resultsarray,
                                         'totalDisplayed' => $totalDisplayed,
                                         'totalPrograms' => $totalPrograms));
      
      $partialView->setTerminal(true);
      return $partialView;
   }

   // establishes the dbadapter link for all user queries
    public function getAdminQueries()
    {
      if (!$this->tableResults) {
         $this->tableResults = $this->getServiceLocator()
                                    ->get('Admin\Model\Queries');
      }
      return $this->tableResults;
    }

    public function getGenericQueries()
    {
        if (!$this->tableResults) {
            $this->tableResults = $this->getServiceLocator()
                                       ->get('Application\Model\AllTables');
                    
        }
        return $this->tableResults;
    }
   
}
