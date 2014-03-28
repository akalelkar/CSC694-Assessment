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

/*
INSERT INTO `assessment`.`plans`
(
    -- `id`, -- auto incremented
    `created_ts`,
    `submitted_ts`,
    `modified_ts`,
    `deactivated_ts`,
    `created_user`,
    `submitted_user`,
    `modified_user`,
    `deactivated_user`,
    `draft_flag`,
    `meta_flag`,
    `funding_flag`,
    `meta_description`,
    `year`,
    `assessment_method`,
    `population`,
    `sample_size`,
    `assessment_date`,
    `cost`,
    `analysis_type`,
    `administrator`,
    `analysis_method`,
    `scope`,
    `feedback_text`,
    `feedback`,
    `active_flag`
)
VALUES
(
    -- <{id: }>, auto incremented
    '1970-01-01 00:00:01',
    '1970-01-01 00:00:01',
    '1970-01-01 00:00:01',
    '1970-01-01 00:00:01',
    19,
    19,
    19,
    19,
    0,
    0,
    0,
    'meta_description',
    2014,
    'assessment_method:',
    'population:',
    'sample_size:',
    'assessment_date:',
    'cost:',
    'analysis_type:',
    'administrator:',
    'analysis_method:',
    'scope:',
    'feedback_text:',
    0,
    0
)
;
*/
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
		
	// create an automic database operation for the tuple insert and retreival of the auto-generated primary key		
	$connection = $this->adapter->getDriver()->getConnection();
	$connection->beginTransaction();

	// perform the insert
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();

	// get the primary key id
	$rowId = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
		
	// finish the transaction		
	$connection->commit();
  
        return $rowId;
    }    
    
    
    /**
     * Insert a tuple into the plan outcomes table
     */
    public function insertPlanOutcome($outcomeId, $planId)
    {
/*	
INSERT INTO `assessment`.`plan_outcomes`
(
	-- `id`,  auto incremented
	`plan_id`,
	`outcome_id`
)
VALUES
(
	-- <{id: }>,   auto incremented
	6016,
	1
)
;
*/	

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
/*
 INSERT INTO `assessment`.`plan_documents`
(
-- `id`, auto incremented
	`created_ts`,
	`created_user`,
	`file_name`,
	`file_ext`,
	`file_description`,
	`file_document`,
	`file_size`,
	`file_type`,
	`plan_id`
)
VALUES
(
	-- <{id: }>,  auto incremented
	'1970-01-01 00:00:01',
	19,
	'file_name:',
	'file_ext:',
	'file_description:',
	'file_document:',
	26,
	'file_type:',
	6016
)
;
*/
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
/*
INSERT INTO `assessment`.`plan_programs`
(
    -- `id`, auto increment
    `plan_id`,
    `program_id`
)
VALUES
(
    -- <{id: }>,
    6016,
    1
);
*/
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
/*
update assessment.plans
set meta_description = 'Hello',
    modified_ts = '1970-01-01 00:00:01',
    active_flag = 1
where id = 1175
;
*/
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

    
    /*
     * Update the active flag in plans table setting it to in-active (0)
     */
    public function updatePlanActiveByPlanId($id, $userId)
    {
/*
UPDATE `assessment`.`plans`
SET deactivated_ts = '1970-01-01 00:00:01',
    deactivated_user = 19,
    active_flag = 0
WHERE id = 6016
;
*/
	// database timestamp format    
        //"1970-01-01 00:00:01";
      
	// create the sytem timestamp
	$currentTimestamp = date("Y-m-d H:i:s", time());
	
        $sql = new Sql($this->adapter);
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
    }    
    
    
    /*
     * Update the active flag in reports table by the plan id setting it to in-active (0)
     */
    public function updateReportsActiveByPlanId($id, $userId)
    {
/*
UPDATE `assessment`.`reports`
SET 
    deactivated_ts = '1970-01-01 00:00:01',
    deactivated_user = 19,
    active_flag = 0
WHERE plan_id = 6016
;
*/
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
/*
DELETE FROM `assessment`.`plan_documents`
WHERE id = 1
;
*/
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
/*    
SELECT MIN(year) as year from assessment.plans
;
 */   
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
/*
    select
	p.id, 
	pd.file_name,
	pd.file_ext,
	pd.file_description
FROM `assessment`.`plans` p 
	inner join `assessment`.`plan_documents` pd
		on p.id = pd.plan_id
where p.id = 6026
;
*/
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
/*
 SELECT
    id,
    created_ts,
    created_user,
    file_name,
    file_ext,
    file_description,
    file_document,
    file_size,
    file_type,
    plan_id
FROM assessment.plan_documents
where id = 26
;
*/
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
/*
select * from assessment.plans
where id = 1175
;
*/
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
/*
SELECT 
    o.id,
    pl.id, 
    o.outcome_text 
from assessment.outcomes o	
    inner join assessment.plan_outcomes po
	on po.outcome_id = o.id
    inner join assessment.plans pl
	on pl.id = po.plan_id
where pl.id = 1175
group by o.id, pl.id, o.outcome_text
;
*/	
	
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
    
    // if the action is view do not return plans that are in a draft status
    if (strtolower($action) == "view") {
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
/*
// view action select where draft flag is 0 or null
SELECT 
    p.name, 
    o.id, 
    pl.id, 
    o.outcome_text 
from assessment.units un
    inner join assessment.programs p
	on p.unit_id = un.id
    inner join assessment.outcomes o
	on o.program_id = p.id
    inner join assessment.plan_outcomes po
	on po.outcome_id = o.id
    inner join assessment.plans pl
	on pl.id = po.plan_id
where un.id = 'CSC'
  and p.name = 'BS Computer Science'
  and pl.year = 2010
  and pl.active_flag = 1
  and (pl.draft_flag = 0 or pl.draft_flag is null)
group by p.name, o.id, pl.id, o.outcome_text
;

// modify action can see all plans
SELECT 
    p.name, 
    o.id, 
    pl.id, 
    o.outcome_text 
from assessment.units un
    inner join assessment.programs p
	on p.unit_id = un.id
    inner join assessment.outcomes o
	on o.program_id = p.id
    inner join assessment.plan_outcomes po
	on po.outcome_id = o.id
    inner join assessment.plans pl
	on pl.id = po.plan_id
where un.id = 'CSC'
  and p.name = 'BS Computer Science'
  and pl.year = 2010
  and pl.active_flag = 1
group by p.name, o.id, pl.id, o.outcome_text
;
*/

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
/*
SELECT 
    p.name,
    o.id, 
    o.outcome_text 
from assessment.units un
    inner join assessment.programs p
	on p.unit_id = un.id
    inner join assessment.outcomes o
	on o.program_id = p.id
where un.id = 'CSC'
  and p.name = 'BS Computer Science'
group by p.name, o.id, o.outcome_text
;
*/	
                
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
/*
select id from `assessment`.`programs`
where name in ('BA Computer Science','BS Computer Science')
;
*/
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
/*
 SELECT 
	max(pl.year) as year
from assessment.plans pl
	inner join assessment.plan_programs pp
		on pp.plan_id = pl.id
	inner join assessment.programs p
		on pp.program_id = p.id
	inner join assessment.units un
		on un.id = p.unit_id
where un.id = 'CSC'
	and p.name in ('BA Computer Science','BS Computer Science')
;
*/
        $sql = new Sql($this->adapter);
	$select = $sql->select()
                      ->columns(array('year' => new Expression('MAX(plans.year)')))
		      ->from('plans', array('id' => 'plans.id'))
		      ->join('plan_programs', 'plan_programs.plan_id = plans.id', array())
		      ->join('programs', 'plan_programs.program_id = programs.id', array())
		      ->join('units', 'programs.unit_id = units.id', array())		      		  
                      ->where(array('units.id' => $unitId, 'programs.name' => $names, 'plans.meta_flag' => 1))
		   ;

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

	// create a single row
	$row = $resultSet->current();   

        return $row;
    }
}