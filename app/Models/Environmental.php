<?php

namespace App\Models;

class Environmental extends Comments
{
    protected $table = 'ENVIRONMENTAL';
    public $timestamps = false;
    
    public static $localType 		= 'ENV_TYPE';
    public static $codeTableType 	= 'CodeEnvType';

    public static function getEntries($facility_id=null,$product_type = 0){
        $entries = CodeEnvType::where('ACTIVE','=',1)->select('ID','NAME')->orderBy('NAME')->get();
        return $entries;
    }
    
}
