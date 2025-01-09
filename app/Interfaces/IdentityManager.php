<?php
namespace App\Interfaces;

interface IdentityManager
{
    const COMPARISON_PURPOSE_INITIAL = 0;
    const COMPARISON_PURPOSE_MERGE = 1;
    const COMPARISON_PURPOSE_MERGE_TRUSTED = 2;

    const LOA_NONE = "http://cas.cuni.cz/LoA/none";
    const LOA_LOW  = "http://cas.cuni.cz/LoA/low";
    const LOA_SUBSTANTIAL = "http://cas.cuni.cz/LoA/substantial";
    const LOA_HIGH = "http://cas.cuni.cz/LoA/high";
    
    const IDENTITY_RESULT_SAME = 0;
    const IDENTITY_RESULT_DIFFERENT = 1;
    const IDENTITY_RESULT_UNKNOWN = 2;
    
    public function compareIdentity($data1, $data2, $purpose);
 
    public function getLastScore(); 
    
    public function getLastError();
    
    public function getRequiredScore($purpose);
        
    public function hasAllInformation($data);
}

