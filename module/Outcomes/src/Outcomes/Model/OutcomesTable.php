<?php

namespace Outcomes\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;


// This class must be included in the factories array in
// this modules Module.php

// This class holds the database method calls.
// The class variable $table references a base table used in the
// queries.  This variable is required in the class but is not required
// to be used in all database calls.  For example, the from clause could
// explicitly reference a table (->from('student')).

class OutcomesTable extends AbstractTableGateway
{
  
    protected $table = 'outcomes';
    public $adapter;
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    public function getAllActiveOutcomesForProgram($programId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()
                      ->from('outcomes')
                      ->where('program_id =' . $programId)
                      ->where('active_flag = 1');                
         
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        return $result;   
    }
     
    // inserts a new outcome in the database
    public function addOutcome($programId, $outcomeText, $userId)
    {
        $sql = new Sql($this->adapter);
        
        // store data from the outcome object that was passed in - timestamp works on its own   
        $data = array('program_id' => $programId,
		      'outcome_text' => $outcomeText,
                      'active_flag' => 1,
                      'created_user' => $userId,
        );
            
        // excecute the SQL
        $insert = $sql->insert('outcomes');
        $insert->values($data);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    } 
    
    // swaps an existing outcome's active flag from 1 to 0
    public function deactivateOutcome($outcomeId, $userId)
    {
        // Get current time for deactivated timestamp
        $now = date("Y-m-d H:i:s", time());
        
        $sql = new Sql($this->adapter);
        // switch the active flag from 0 to 1 then update only for the id passed in
	$update = $sql->update()
			->table('outcomes')
			->set(array('active_flag' => 0,
                                    'deactivated_user' => $userId,
                                    'deactivated_ts' => $now,
			))
			->where(array('id' => $outcomeId
        ));                    
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    // creates a new outcome and deactivates an old one
    public function editOutcome($programId, $newOutcomeText, $deactivatedOutcomeId, $userId)
    {
        $sql = new Sql($this->adapter);       
        $this->addOutcome($programId, $newOutcomeText, $userId);
        $this->deactivateOutcome($deactivatedOutcomeId, $userId);       
    }
    
    // return 1 if the user has permission to modify the unit's outcomes
    public function checkPermissions($userID, $unitID)
    {
        $sql = new Sql($this->adapter);
        $where = new Where();
        
        // check the chair privs table to see is the user exists with the correct unit priv
        $select1 = $sql->select()
                      ->from('chair_privs')
                      ->columns(array('id'))
                      ->where(array('chair_privs.user_id' => $userID))
                      ->where(array('chair_privs.unit_id' => $unitID))
		      ->where(array('chair_privs.active_flag' => 1))
        ;
        
        $statement = $sql->prepareStatementForSqlObject($select1);
        $result = $statement->execute();
        
        // if $result not empty return true
        if ($result->count() > 0)
        {
            return true;
        } 
      
        // check the liason privs table to see if the user exists with the correct unit priv
        $select2 = $sql->select()
                      ->from('liaison_privs')
                      ->columns(array('id'))
                      ->where(array('liaison_privs.user_id' => $userID))
                      ->where(array('liaison_privs.unit_id' => $unitID))
        	      ->where(array('liaison_privs.active_flag' => 1))
        ;
        
	$statement = $sql->prepareStatementForSqlObject($select2);
        $result = $statement->execute();
        
        // check the assessor privs table to see if the user exists with the correct unit priv
        $select3 = $sql->select()
                      ->from('assessor_privs')
                      ->columns(array('id'))
                      ->where(array('assessor_privs.user_id' => $userID))
                      ->where(array('assessor_privs.unit_id' => $unitID))
        	      ->where(array('assessor_privs.active_flag' => 1))
        ;
        
	$statement = $sql->prepareStatementForSqlObject($select3);
        $result = $statement->execute();
 
        // if $result not empty return true
        if ($result->count() > 0)
        {
            return true;
        } 
        
        // check is the user's role gives him / her admin access to modify every unit
        $select2 = $sql->select()
                      ->from('user_roles')
                      ->columns(array('id'))
                      ->where(array('user_roles.user_id' => $userID))
                      ->where(array('user_roles.role' => 1))
        	      ->where(array('user_roles.active_flag' => 1))
        ;

        $statement = $sql->prepareStatementForSqlObject($select2);
        $result = $statement->execute();
        
	// if $result not empty return true
        if ($result->count() > 0)
        {
            return true;
        } else {
            // if you get to this point, the user isn't allowed to make any changes
            return false;
        }
    }
}