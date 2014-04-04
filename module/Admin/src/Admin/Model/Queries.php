<?php

namespace Admin\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;

// This class must appear in the Module.php file in this module.

class Queries extends AbstractTableGateway
{
    public $adapter;
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    // query 1
    public function getProgramsMissingPlansForYear($year)
    {
        $sql = new Sql($this->adapter);
        
        // get programs that have a plan
        $select1 = $sql->select()
                      ->from('programs')
                      ->columns(array('id'))
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array())
                      ->where(array('plans.year' => $year))
                   
        ;
        // get programs that are not in the set above
        $select2 = $sql->select()
                       ->from('programs')
                       ->columns(array('id', 'unit_id', 'name'))
                       ->where(new NotIn('programs.id', $select1))
                       ->where(array('programs.active_flag' => 1))
                       ->order(array('unit_id'))
                   
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select2);
        $result = $statement->execute();
        
        // dumping $result will not show any rows returned
        // you must iterate over $result to retrieve query results
        
        return $result;
    }
    
    // query 2
    public function getProgramsMissingReportsForYear($year)
    {
        $sql = new Sql($this->adapter);
        
        // get plan ids for all reports
        $reportsselect = $sql->select()
                            ->from('reports')
                            ->columns(array('plan_id'))
        ;
  
        // get programs that have a plan for the selected year but are not in above set
        $select = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name'))
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array('id'))
                      ->where(array('plans.year' => $year))
                      ->where(array('programs.active_flag' => 1))
                      ->where(new NotIn('plans.id', $reportsselect))
                      ->order(array('programs.id'))
                      
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        return $result;
    }
    
    // query 3
    public function getProgramsDoingMetaAssessment($year)
    {
        $sql = new Sql($this->adapter);
        
        // get programs that have a meta-assessment plan for the selected year 
        $select = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name'))
                      ->join('plan_programs', 'plan_programs.program_id = programs.id', array())
                      ->join('plans', 'plans.id = plan_programs.plan_id', array())
                      ->where(array('plans.year' => $year))
                      ->where(array('plans.meta_flag' => 1))
                      ->where(array('programs.active_flag' => 1))
                      ->order(array('programs.id'))
                   
        ;
       
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        return $result;
    }
    
    // query 4
    public function getProgramsNeedingFunding($year)
    {
        $sql = new Sql($this->adapter);
        
        // get programs requesting funding for plans 
        $select = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name'))
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array())
                      ->where(array('programs.active_flag' => 1))
                      ->where(array('plans.year' => $year))
                      ->where(array('plans.funding_flag' => 1))
                      ->order(array('programs.id'))
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
      
        return $result;
    }
    
    // query 5
    public function getProgramsWithModifiedOutcomes($fromDate)
    {
        $sql = new Sql($this->adapter);
        $where = new Where();
        
        // creates constants to display in queries - note use of quotes
        // this forces this to appear as string constant in select clause
        $deactivated = new \Zend\Db\Sql\Predicate\Expression("'Deactivated'");
        $created = new \Zend\Db\Sql\Predicate\Expression("'Created'");
    
        // user chosen fromDate arrives in mmddyyyy format
        // strtotime requires dd-mm-yyyy format
        $fromDate = substr($fromDate, 2, 2) . '-' .
                    substr($fromDate, 0, 2) . '-' .
                    substr($fromDate, 4);
        $fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
        
        // get programs that deactivated outcomes since fromdate
        $select1 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $deactivated))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('outcomes', 'outcomes.program_id = programs.id',array())
                      ->join('users', 'users.id = outcomes.deactivated_user', array('last_name', 'first_name'))
                      ->where(array('outcomes.active_flag' => 0))
                      ->where($where->isNotNull('outcomes.deactivated_ts'))
                      ->where($where->greaterThan('outcomes.deactivated_ts', $fromDate))
                      ->order(array('programs.unit_id'))
        ;
        
        // get programs that added outcomes since fromdate
        $select2 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $created))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('outcomes', 'outcomes.program_id = programs.id',array())
                      ->join('users', 'users.id = outcomes.created_user', array('last_name', 'first_name'))
                      ->where($where->isNotNull('outcomes.created_ts'))
                      ->where($where->greaterThan('outcomes.created_ts', $fromDate))
                      ->order(array('programs.unit_id'))
                   
        ;
        // union results
        $select2->combine($select1);
        
        $statement = $sql->prepareStatementForSqlObject($select2);
        $result = $statement->execute();
      
        return $result;
    }
    
    // query 6
    public function getProgramsModifiedLastYearsPlans($currentYear)
    {
        $sql = new Sql($this->adapter);
    
        $previousYear = $currentYear - 1;
        $where = new Where();
        // get programs that have changed a plan this year but plan year is previous year
        $select = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name'))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array('id'))
                      // instantiating new where must come first
                      ->where($where->like('plans.modified_ts', $currentYear . '%'))
                      ->where(array('plans.year' => $previousYear))
                      ->order(array('programs.id'))
                   
        ; 
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
      
        return $result;
    }
    
    // query 7
    public function getProgramsModifiedLastYearsReports($currentYear)
    {
        $sql = new Sql($this->adapter);
    
        // create constants for report
        $created = new \Zend\Db\Sql\Predicate\Expression("'Created'");
        $modified = new \Zend\Db\Sql\Predicate\Expression("'Modified'");
        
        // reports for current year should match  previous year's plan
        // this query shows reports that were entered or modified that match a plan two year's ago
        $previousYear = $currentYear - 2;
        $where = new Where();
         // get programs that have a created timestamp of this year but plan year is past two years
        $select1 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $created))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array())
                      ->join('reports', 'reports.plan_id = plans.id',array('id'))
                      // instantiating new where must come first
                      ->where($where->like('reports.created_ts', $currentYear . '%'))
                      ->where(array('plans.year' => $previousYear))
                      ->order(array('programs.id'))
                   
        ; 
        // get programs that have a modified timestamp of this year but plan year is past two years
        $select2 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $modified))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array())
                      ->join('reports', 'reports.plan_id = plans.id',array('id'))
                      // instantiating new where must come first
                      ->where($where->like('reports.modified_ts', $currentYear . '%'))
                      ->where(array('plans.year' => $previousYear))
                      ->order(array('programs.id'))
                   
        ;
        $select1->combine($select2);
        $statement = $sql->prepareStatementForSqlObject($select1);
        $result = $statement->execute();
      
        return $result;
    }
    
    // query 8
    public function getProgramsNeedingFeedback($year)
    {
        $sql = new Sql($this->adapter);
        $where = new Where();
        
        // creates constants to display in queries - note use of quotes
        // this forces this to appear as string constant in select clause
        $plan = new \Zend\Db\Sql\Predicate\Expression("'Plan'");
        $report = new \Zend\Db\Sql\Predicate\Expression("'Report'");
    
         // get programs that have a plan missing feedback
         // make sure plan is not a draft and feedback is 0
        $select1 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $plan))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array())
                      ->join('liaison_privs', 'liaison_privs.unit_id = programs.unit_id', array())
                      ->join('users', 'users.id = liaison_privs.user_id', array('first_name', 'last_name'))
                      ->where(array('plans.year' => $year))
                      ->where(array('plans.draft_flag' => 0))
                      ->where(array('plans.feedback' => 0))
                      ->order(array('programs.id'))
                   
        ; 
        // get programs with a report needing feedback
        // make sure report is not a draft and feedback is 0
        $select2 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $report))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('plan_programs', 'plan_programs.program_id = programs.id',array())
                      ->join('plans', 'plans.id = plan_programs.plan_id',array())
                      ->join('liaison_privs', 'liaison_privs.unit_id = programs.unit_id', array())
                      ->join('users', 'users.id = liaison_privs.user_id', array('first_name', 'last_name'))
                      ->join('reports', 'reports.plan_id = plans.id', array())
                      ->where(array('plans.year' => $year))
                      ->where(array('reports.draft_flag' => 0))
                      ->where(array('reports.feedback' => 0))
                      ->order(array('programs.id'))
                   
        ;
        
        $select1->combine($select2);
        $statement = $sql->prepareStatementForSqlObject($select1);
        $result = $statement->execute();
      
        return $result;
    }
    
    // query 9
    public function getProgramsWhoChangedAssessors($fromDate)
    {
 
        $sql = new Sql($this->adapter);
        $where = new Where();
        // date arrives in mmddyyyy format
        // strtotime requires dd-mm-yyyy format
        $fromDate = substr($fromDate, 2, 2) . '-' .
                    substr($fromDate, 0, 2) . '-' .
                    substr($fromDate, 4);
        $fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
        
        // creates constants to display in queries - note use of quotes
        // this forces this to appear as string constant in select clause
        $deactivated = new \Zend\Db\Sql\Predicate\Expression("'Deactivated'");
        $created = new \Zend\Db\Sql\Predicate\Expression("'Created'");
        
        // get deactivated assessor roles
        $select1 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $deactivated))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('assessor_privs', 'assessor_privs.unit_id = programs.unit_id',array())
                      ->join('user_roles', 'user_roles.user_id = assessor_privs.user_id',array())
                      // grab user responsible for deactivating assessor
                      ->join('users', 'users.id = assessor_privs.deactivated_user', array('last_name', 'first_name'))
                      ->where($where->isNotNull('assessor_privs.deactivated_ts'))
                      ->where($where->greaterThan('assessor_privs.deactivated_ts', $fromDate))
                      // liaison role = 4
                      ->where(array('user_roles.role' => 4))
                      ->order(array('programs.id'))
        ;
        
        // get newly created assessor roles
        $select2 = $sql->select()
                      ->from('programs')
                      ->columns(array('unit_id', 'name', 'type' => $created))
                      ->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT)
                      ->join('assessor_privs', 'assessor_privs.unit_id = programs.unit_id',array())
                      ->join('user_roles', 'user_roles.user_id = assessor_privs.user_id',array())
                      // grab user responsible for deactivating assessor
                      ->join('users', 'users.id = assessor_privs.created_user', array('last_name', 'first_name'))
                      ->where($where->isNotNull('assessor_privs.created_ts'))
                      ->where($where->greaterThan('assessor_privs.created_ts', $fromDate))
                      // liaison role = 4
                      ->where(array('user_roles.role' => 4))
                      ->order(array('programs.id'))
        ;
        // union results
        $select1->combine($select2);
        
        $statement = $sql->prepareStatementForSqlObject($select1);
        $result = $statement->execute();
      
        return $result;
    }   
    
    // gets active programs count
    public function getActiveProgramsCount()
    {
        $sql = new Sql($this->adapter);
    
        // get count of active programs 
        $select = $sql->select()
                      ->from('programs')
                      ->where(array('active_flag' => 1))
        ; 
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->count();
    }
  
    // gets total plans count for year
    public function getPlansCountForYear($year)
    {
        $sql = new Sql($this->adapter);
    
        // get count of active programs 
        $select = $sql->select()
                      ->from('plans')
                      ->where(array('year' => $year))
        ; 
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->count();
    }
    
}