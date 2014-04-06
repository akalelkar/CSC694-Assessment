<?php

namespace Plans\Model\Entity;

/**
 * Entity used to store the outcome results from the database
 */
class Outcome {

    protected $outcomeId;
    protected $planId;
    protected $outcomeText;
    protected $programName;
    protected $type;

    public function __construct($outcomeId, $outcomeText, $type, $planId, $programName) {
        $this->programName = $programName;
        $this->outcomeId = $outcomeId;
        $this->planId = $planId;
        $this->outcomeText = $outcomeText;
        $this->type = $type;
    }

    public function getProgramName() {
        return $this->programName;
    }
    
    public function getOutcomeId() {
        return $this->outcomeId;
    }
    
    public function getPlanId() {
        return $this->planId;
    }

    public function getOutcomeText() {
        return $this->outcomeText;
    }

    public function getType() {
        return $this->type;
    }  

}