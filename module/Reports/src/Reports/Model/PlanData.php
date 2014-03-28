<?php
namespace Reports\Model;

// Simple class for holding plan data to make passing to view easier
class PlanData 
{
    public $id; // Plan id
    public $metaFlag; // Has meta?
    public $hasReport; // Has a report?
    public $descriptions; // Text of either outcome or meta assessment
    
    public function __construct($id, $meta, $hasReport)
    {
        $this->id = $id;
        $this->metaFlag = $meta;
        $this->hasReport = $hasReport;
        $this->descriptions = array();
    }
}