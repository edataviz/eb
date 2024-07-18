<?php

namespace App\Http\Controllers;
use App\Models\Environmental;

class EnvironmentalController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
        $date_end	    = $date_end->modify('+1 day')->modify('-1 seconds');
    	$env_type 		= $postData['CodeEnvType'];
    	$comment 		= Environmental::getTableName();
    	
    	$columns	= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns = [];
    	array_push($columns,"$comment.ID as DT_RowId","$comment.ID");
    	
    	$dataSet 		= Environmental::where("FACILITY_ID","=",$facility_id)
				    	->where("ENV_TYPE","=",$env_type)
				    	->whereDate('CREATED_DATE', '<=', $date_end)
				    	->whereDate('CREATED_DATE', '>=', $occur_date)
// 				    	->whereBetween('CREATED_DATE', [$occur_date,$date_end])
				    	->select($columns)
 		    			->orderBy('CREATED_DATE')
 		    			->orderBy('ENV_CATEGORY')
		    			->get();
    	
    	return ['dataSet'=>$dataSet];
    }
}
