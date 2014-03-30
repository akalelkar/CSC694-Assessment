<?php

namespace Plans\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\DB\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
use Plans\Model\Entity;

class DatabaseSql extends AbstractTableGateway
{
    protected $table = 'users';
    public $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
  
// Sample dump used in debugging, used as needed    
//        foreach ($result as $data) :
//            var_dump($data);
//        endforeach;
//        exit();


/********** All insert queries *********/
    
    /**
     * Insert a new tuple into the plans table
     */
    public function insertPlan($metaFlag,$metaDescription,$year,$assessmentMethod,$population,$sampleSize,$assessmentDate,$cost,$fundingFlag,$analysisType,$administrator,$analysisMethod,$scope,$feedbackText,$feedbackFlag,$draftFlag,$userId)
    {

	// database timestamp format    
        //"1970-01-01 00:00:01";
      
	// create the sytem timestamp
	$currentTimestamp = date("Y-m-d H:i:s", time());
	
	// set the submitted timestamp and user id for submitted plans only
	$submittedTimestamp = null;
	$submittedUserId = null;
	if ($draftFlag == "0") {
	    $submittedTimestamp = $currentTimestamp;
	    $submittedUserId = $userId;
	}
      
	$sql = new Sql($this->adapter);
	$data = array('created_ts' => $currentTimestamp,
		      'submitted_ts' => $submittedTimestamp,
		      'modified_ts' => $currentTimestamp,		      
		      'created_user' => $userId,
		      'submitted_user' => $submittedUserId,
		      'modified_user' => $userId,
		      'meta_flag' => $metaFlag,
		      'meta_description' => trim($metaDescription),
		      'year' => trim($year),
		      'assessment_method' => trim($assessmentMethod),
		      'population' => trim($population),
		      'sample_size' => trim($sampleSize),
		      'assessment_date' => trim($assessmentDate),
		      'cost' => trim($cost),
		      'funding_flag' => trim($fundingFlag),
		      'analysis_type' => trim($analysisType),
		      'administrator' => trim($administrator),
		      'analysis_method' => trim($analysisMethod),
		      'scope' => trim($scope),
		      'feedback_text' => trim($feedbackText),
		      'feedback' => trim($feedbackFlag),
		      'draft_flag' => trim($draftFlag),
		      'active_flag' => 1,
		    );
		
	$insert = $sql->insert('plans');
	$insert->values($data);		    
		
	
	// perform the insert
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();

	// get the primary key id
	$rowId = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
		
	return $rowId;
    }    
    
    
    /**
     * Insert a tuple into the plan outcomes table
     */
    public function insertPlanOutcome($outcomeId, $planId)
    {

        $sql = new Sql($this->adapter);
	$data = array('outcome_id' => $outcomeId,
		      'plan_id' => $planId);

	$insert = $sql->insert('plan_outcomes');
	$insert->values($data);		    
    
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }

    
    /**
     * Insert a tuple into the plan document table
     */
    public function insertPlanDocuments($planId, $fileName, $fileDescription, $userId, $fileDocument, $fileSize, $fileType)
    {
	// database timestamp format    
        //"1970-01-01 00:00:01";
	
	// create the sytem timestamp
	$currentTimestamp = date("Y-m-d H:i:s", time());
	
	/*
	 * split the file name into
	 *  1) File Name
	 *  2) File Ext
	 */
	$fileNameSplit = preg_split('/\./', $fileName, null, PREG_SPLIT_NO_EMPTY);
	    
        $sql = new Sql($this->adapter);
	$data = array('plan_id' => $planId,
		      'created_ts' => $currentTimestamp,
		      'created_user' => $userId,
		      'file_name' => $fileNameSplit[0],
		      'file_ext' => $fileNameSplit[1],
		      'file_description' => $fileDescription,
		      'file_document' => $fileDocument,
		      'file_size' => $fileSize,
		      'file_type' => $fileType,
		      );

	$insert = $sql->insert('plan_documents');
	$insert->values($data);		    
    
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }

    
    /**
     * Insert a tuple into the plan programs table
     */
    public function insertPlanPrograms($programId, $planId)
    {
        $sql = new Sql($this->adapter);
	$data = array('plan_id' => $planId,
		      'program_id' => $programId,
		      );
		      
	$insert = $sql->insert('plan_programs');
	$insert->values($data);		    
    
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }
    
    
/********** All update queries *********/    

    /**
     * Update a tuple on the plans table by id
     */
    public function updatePlanById($id,$metaFlag,$metaDescription,$assessmentMethod,$population,$sampleSize,$assessmentDate,$cost,$fundingFlag,$analysisType,$administrator,$analysisMethod,$scope,$feedbackText,$feedbackFlag,$draftFlag,$userId,$dbDraftFlag)
    {
	// database timestamp format    
        //"1970-01-01 00:00:01";
	
	// create the sytem timestamp
	$currentTimestamp = date("Y-m-d H:i:s", time());
	
	$sql = new Sql($this->adapter);
	
  	// if the existing plan was a draft and now it is submitted set the submit info
	// otherwise the submit info stays the same
	if ($dbDraftFlag == "1" && $draftFlag == "0") {
	    $submittedTimestamp = $currentTimestamp;
	    $submittedUserId = $userId;
	    
	    $update = $sql->update()
			  ->table('plans')
			  ->set(array('submitted_ts' => $submittedTimestamp,
				      'modified_ts' => $currentTimestamp,
				      'submitted_user' => $submittedUserId,
				      'modified_user' => $userId,				    
				      'meta_flag' => trim($metaFlag),
				      'meta_description' => trim($metaDescription),
				      'assessment_method' => trim($assessmentMethod),
				      'population' => trim($population),
				      'sample_size' => trim($sampleSize),
				      'assessment_date' => trim($assessmentDate),
				      'cost' => trim($cost),
				      'funding_flag' => trim($fundingFlag),
				      'analysis_type' => trim($analysisType),
				      'administrator' => trim($administrator),
				      'analysis_method' => trim($analysisMethod),
				      'scope' => trim($scope),
				      'feedback_text' => trim($feedbackText),
				      'feedback' => trim($feedbackFlag),
				      'draft_flag' => trim($draftFlag),
				))
			->where(array('id' => $id))
		    ;
	}
	else {
	    $update = $sql->update()
			  ->table('plans')
			  ->set(array('modified_ts' => $currentTimestamp,
  				      'modified_user' => $userId,				    
				      'meta_flag' => trim($metaFlag),
				      'meta_description' => trim($metaDescription),
				      'assessment_method' => trim($assessmentMethod),
				      'population' => trim($population),
				      'sample_size' => trim($sampleSize),
				      'assessment_date' => trim($assessmentDate),
				      'cost' => trim($cost),
				      'funding_flag' => trim($fundingFlag),
				      'analysis_type' => trim($analysisType),
				      'administrator' => trim($administrator),
				      'analysis_method' => trim($analysisMethod),
				      'scope' => trim($scope),
				      'feedback_text' => trim($feedbackText),
				      'feedback' => trim($feedbackFlag),
				      'draft_flag' => trim($draftFlag),
				))
			->where(array('id' => $id))
		    ;	    
	}
		    
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

    public function insertMetaPlan($metaDescription, $year, $draftFlag, $userID, $programsArray){
	
	$sql = new Sql($this->adapter);

	// create an atomic database transaction to update plan and possibly report
	$connection = $this->adapter->getDriver()->getConnection();
	$connection->beginTransaction();

	// insert into plan table and obtain the primary key of the insert
        $planId = $this->insertPlan(1, $metaDescription, $year, "","","","","","","","","","","","",$draftFlag, $userID);
     
        // get all the program ids based on the array of program
	$programIds = $this->getProgramIdsByProgram($programsArray);

	 // loop through the array of programs inserting each value into the meta plans table
	foreach ($programIds as $program) :
	   $this->insertPlanPrograms($program['programId'], $planId);
	 endforeach;

	// finish the transaction		
	$connection->commit();
      
    }
    /*
     * Update the active flag in plans table setting it to in-active (0)
     */
    public function updatePlanActiveByPlanId($id, $userId)
    {
	// database timestamp format    
        //"1970-01-01 00:00:01";
      
	// create the sytem timestamp
	$currentTimestamp = date("Y-m-d H:i:s", time());
	
	$sql = new Sql($this->adapter);

	// create an atomic database transaction to update plan and possibly report
	$connection = $this->adapter->getDriver()->getConnection();
	$connection->beginTransaction();

	// determine if plan is a draft
	$select = $sql->select()
		      ->from('plans')
		      ->columns(array('draft_flag', 'meta_flag'))
		      ->where(array('id' => $id))
	;
	$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
	foreach($result as $r){  // should only be one plan though
	    if ($r['draft_flag'] == 1){
		// delete plan - there won't be a report to deal with
	    	// first delete plan from plan_outcomes table - but only for outcomes plans
		if ($r['meta_flag'] == 0)
		{
		    $update = $sql->delete()
			      ->from('plan_outcomes')
			      ->where(array('plan_id' => $id))
		    ;
		    $statement = $sql->prepareStatementForSqlObject($update);
	            $statement->execute();
		}
		// next delete any associated plan_documents
		$update = $sql->delete()
			      ->from('plan_documents')
			      ->where(array('plan_id' => $id))
		;
		$statement = $sql->prepareStatementForSqlObject($update);
	        $statement->execute();
		
		// next delete plan from plan_programstable
		$update = $sql->delete()
			      ->from('plan_programs')
			      ->where(array('plan_id' => $id))
		;
		
		$statement = $sql->prepareStatementForSqlObject($update);
	        $statement->execute();
	
		// now delete plan
		$update = $sql->delete()
			      ->from('plans')
			      ->where(array('id' => $id))
		;
		$statement = $sql->prepareStatementForSqlObject($update);
	        $statement->execute();
	    }
	    else{
			
		$update = $sql->update()
			->table('plans')
			->set(array('active_flag' => 0,
				    'deactivated_ts' => $currentTimestamp,
				    'deactivated_user' => $userId,
				    ))
			->where(array('id' => $id))
		    ;
		$statement = $sql->prepareStatementForSqlObject($update);
		$statement->execute();
		$this->updateReportsActiveByPlanId($id, $userId);
	    }
	}
	// finish the transaction		
	$connection->commit();
  
    }    
    
    
    /*
     * Update the active flag in reports table by the plan id setting it to in-active (0)
     */
    public function updateReportsActiveByPlanId($id, $userId)
    {
	// database timestamp format    
        //"1970-01-01 00:00:01";
      
	// create the sytem timestamp
	$currentTimestamp = date("Y-m-d H:i:s", time());
	
        $sql = new Sql($this->adapter);
	$update = $sql->update()
			->table('reports')
			->set(array('active_flag' => 0,
				    'deactivated_ts' => $currentTimestamp,
				    'deactivated_user' => $userId,
				    ))
			->where(array('plan_id' => $id))
		    ;
		    
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
   
/********** All delete queries *********/    

    /**
     * Delete a tuple from the plan documents table
     */
    public function deletePlanDocuments($id)
    {
        $sql = new Sql($this->adapter);
	$delete = $sql->delete('plan_documents');
	$delete->where(array('id' => $id));		    
    
        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
    }
        

	
/********** All select queries *********/    

    /**
     * Get lowest year from the plans table
     */ 
    public function getLowYear()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
		      ->columns(array('year' => new Expression('MIN(plans.year)')))
                      ->from('plans')
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
	// create and return  a single row
	$row = $result->current();   
        return $row;
    }
    
    
    /**
     * Get a plan document by plan id
     */
    public function getPlanDocumentsByPlanId($planId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
		      ->columns(array('id' => new Expression('plan_documents.id'),
				      'file_name' => new Expression('plan_documents.file_name'),
				      'file_ext' => new Expression('plan_documents.file_ext'),
				      'file_description' => new Expression('plan_documents.file_description'),
				))
                      ->from('plans')
		      ->join('plan_documents', 'plan_documents.plan_id = plans.id')
		      ->where(array('plans.id' => $planId))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
	
        return $result;
    }
    
    
    /**
     * Get a plan document by the id
     */
    public function getPlanDocumentsById($Id)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from('plan_documents')
		      ->where(array('id' => $Id))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
	
	// create and return  a single row
	$row = $result->current();   
        return $row;
    }


    /**
     * Get a plan by plan id
     */
    public function getPlanByPlanId($planId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from('plans')
		      ->where(array('id' => $planId))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
	
	// create and return  a single row
	$row = $result->current();   
        return $row;
    }
    
    
    /**
     * Get all the outcomes by plan id
     */
    public function getOutcomesByPlanId($planId, $names)
    {
	
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->columns(array('outcomeId' => new Expression('outcomes.id'),
				      'planId' => new Expression('plans.id'),
				      'outcomeText' => new Expression('outcomes.outcome_text'),
				      ))
                      ->from('outcomes')
		      ->join('programs', 'programs.id = outcomes.program_id')
		      ->join('plan_outcomes', 'plan_outcomes.outcome_id = outcomes.id')
		      ->join('plans', 'plans.id = plan_outcomes.plan_id')		      
		      ->where(array('plans.id' => $planId, 'programs.name' => $names))
		      ->group (array('outcomeId' => new Expression('outcomes.id'),
				     'planId' => new Expression('plans.id'),
				     'outcomeText' => new Expression('outcomes.outcome_text'),
				      ))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        //create an array of entity objects to store the database results
	$entities = array();
        foreach ($resultSet as $row) {
            $entity = new Entity\Outcome("",$row['outcomeId'],$row['planId'],$row['outcomeText']);
            $entities[] = $entity;
        }
        return $entities;
    }
    
    /**
     * Get all the plans for the given deparment, program name, year, and action
     *
     * The view action cannot see the drafted plans
     * The modify action can see the drafted plans 
     */
    public function getPlans($unitId, $names, $year, $action)
    {
    $whereoutcomes = new \Zend\Db\Sql\Where();
    $wheremeta = new \Zend\Db\Sql\Where();
    
    // if the action is view or provide feedback do not return plans that are in a draft status
    if ($action == "View" || $action == "Provide Feedback") {
	$whereoutcomes	
	    ->equalTo('units.id', $unitId)
	    ->and
	    ->in('programs.name', $names)
	    ->and
	    ->equalTo('plans.year', $year)
	    ->and
    	    ->equalTo('plans.active_flag', 1)
	    ->and
	    ->equalTo('plans.meta_flag', 0)
	    ->and
	    ->nest()
	    ->equalTo('plans.draft_flag', 0)
	    ->or
	    ->isNull('plans.draft_flag')
	    ->unnest();
	$wheremeta	
	    ->equalTo('units.id', $unitId)
	    ->and
	    ->in('programs.name', $names)
	    ->and
	    ->equalTo('plans.year', $year)
	    ->and
    	    ->equalTo('plans.active_flag', 1)
	    ->and
	    ->equalTo('plans.meta_flag', 1)
	    ->and
	    ->nest()
	    ->equalTo('plans.draft_flag', 0)
	    ->or
	    ->isNull('plans.draft_flag')
	    ->unnest();
    }
    else {
	// modify can see all the plans
	$whereoutcomes
	    ->equalTo('units.id', $unitId)
	    ->and
	    ->in('programs.name', $names)
	    ->and
	    ->equalTo('plans.year', $year)
	    ->and
	    ->equalTo('plans.meta_flag', 0)
	    ->and
    	    ->equalTo('plans.active_flag', 1);
	    
	$wheremeta
	    ->equalTo('units.id', $unitId)
	    ->and
	    ->in('programs.name', $names)
	    ->and
	    ->equalTo('plans.year', $year)
	    ->and
	    ->equalTo('plans.meta_flag', 1)
	    ->and
    	    ->equalTo('plans.active_flag', 1);
    }



	$sql = new Sql($this->adapter);
	
	// get plans with outcomes
	$select = $sql->select()
                      ->columns(array('planId' => new Expression('plans.id'), 'year', 'meta_flag', 'draft_flag'))
		      ->from('plans', array('id' => 'plans.id'))
		      ->join('plan_outcomes','plans.id = plan_outcomes.plan_id', array())
                      ->join('outcomes','plan_outcomes.outcome_id = outcomes.id', array('text' => 'outcome_text'))
                      ->join('plan_programs', 'plan_programs.plan_id = plans.id', array())
		      ->join('programs', 'plan_programs.program_id = programs.id', array())
		      ->join('units', 'programs.unit_id = units.id', array())
		   ;
	$select->where($whereoutcomes);
	
	// get plans with meta assessment
	$select2 = $sql->select()
                      ->columns(array('planId' => new Expression('plans.id'), 'year', 'meta_flag', 'draft_flag', 'text' => 'meta_description'))
		      ->from('plans', array('id' => 'plans.id'))
		      ->join('plan_programs', 'plan_programs.plan_id = plans.id', array())
		      ->join('programs', 'plan_programs.program_id = programs.id', array())
		      ->join('units', 'programs.unit_id = units.id', array())
		   ;
	$select2->where($wheremeta);
    
	$select->combine($select2);
	
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result;
    }
    
    
    /**
     * Get all the outcomes for the given deparment, program name, year and action
     *
     * The view action cannot see the drafted plans
     * The modify action can see the drafted plans 
     */
    public function getOutcomes($unitId, $names, $year, $action)
    {

    $where = new \Zend\Db\Sql\Where();

    // if the action is view do not return outcomes that are is a draft status
    if (strtolower($action) == "view") {
	$where	
	    ->equalTo('units.id', $unitId)
	    ->and
	    ->in('programs.name', $names)
	    ->and
	    ->equalTo('plans.year', $year)
	    ->and
    	    ->equalTo('plans.active_flag', 1)
	    ->and
	    ->nest()
	    ->equalTo('plans.draft_flag', 0)
	    ->or
	    ->isNull('plans.draft_flag')
	    ->unnest();
    }
    else {
	$where
	    ->equalTo('units.id', $unitId)
	    ->and
	    ->in('programs.name', $names)
	    ->and
	    ->equalTo('plans.year', $year)
	    ->and
	    ->equalTo('plans.active_flag', 1);
    }
    
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->columns(array('program' => new Expression('programs.name'),
                                      'outcomeId' => new Expression('outcomes.id'),
				      'planId' => new Expression('plans.id'),
				      'outcomeText' => new Expression('outcomes.outcome_text'),
				      ))
                      ->from('units')
		      ->join('programs', 'programs.unit_id = units.id')
		      ->join('outcomes', 'outcomes.program_id = programs.id')
		      ->join('plan_outcomes', 'plan_outcomes.outcome_id = outcomes.id')
		      ->join('plans', 'plans.id = plan_outcomes.plan_id')		      
		      ->group (array('program' => new Expression('programs.name'),
                                     'outcomeId' => new Expression('outcomes.id'),
				     'planId' => new Expression('plans.id'),
				     'outcomeText' => new Expression('outcomes.outcome_text'),
				      ))
		   ;
		   $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
    
        //create an array of entity objects to store the database results
	$entities = array();
        foreach ($resultSet as $row) {
            $entity = new Entity\Outcome($row['program'],$row['outcomeId'],$row['planId'],$row['outcomeText']);
            $entities[] = $entity;
        }
        return $entities;
    }
    
    /**
     * get all the unique outcomes by department, program
     */
    public function getUniqueOutcomes($unitId, $names)
    {
                
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->columns(array('program' => new Expression('programs.name'),
                                      'outcomeId' => new Expression('outcomes.id'),
				      'outcomeText' => new Expression('outcomes.outcome_text'),
				      ))
                      ->from('units')
		      ->join('programs', 'programs.unit_id = units.id')
		      ->join('outcomes', 'outcomes.program_id = programs.id')
		      ->where(array('units.id' => $unitId, 'programs.name' => $names))
		      ->where(array('outcomes.active_flag' => 1))
		      ->group (array('program' => new Expression('programs.name'),
                                     'outcomeId' => new Expression('outcomes.id'),
				     'outcomeText' => new Expression('outcomes.outcome_text'),
				      ))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        //create an array of entity objects to store the database results
	$entities = array();
        foreach ($resultSet as $row) {
            $entity = new Entity\Outcome($row['program'], $row['outcomeId'], 0, $row['outcomeText']);
            $entities[] = $entity;
        }
        return $entities;
    }
    
    /**
     * get all the programs ids for the array of programs
     */
    public function getProgramIdsByProgram($names)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->columns(array('programId' => new Expression('programs.id'),
				      ))
                      ->from('programs')
		      ->where(array('programs.name' => $names,))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        return $resultSet;
    }

    
    /*
     * Get the last year a meta plan was entered, used for validation
     */
    public function getLastMetaYear($unitId, $names)
    {
        $sql = new Sql($this->adapter);
	$select = $sql->select()
                      ->columns(array('year' => new Expression('MAX(plans.year)')))
		      ->from('plans', array('id' => 'plans.id'))
		      ->join('plan_programs', 'plan_programs.plan_id = plans.id', array())
		      ->join('programs', 'plan_programs.program_id = programs.id', array())
		      ->join('units', 'programs.unit_id = units.id', array())		      		  
                      ->where(array('units.id' => $unitId, 'programs.name' => $names, 'plans.meta_flag' => 1, 'plans.active_flag' => 1))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

	// create a single row
	$row = $resultSet->current();   

        return $row;
    }
}