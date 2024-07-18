<?php

namespace App\Http\Controllers;
use App\Models\CodeSafetyCategory;
use App\Models\FacilitySafetyCategory;


class SafetyController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Category','width'=>230];
	}
	
    public function getDataSet($postData,$safetyTable,$facility_id,$occur_date,$properties){

    	$codeSafetyCategory 	= CodeSafetyCategory::getTableName();
    	$facilitySafetyCategory = FacilitySafetyCategory::getTableName();
    	
    	//      	\DB::enableQueryLog();
    	$dataSet = CodeSafetyCategory::leftJoin($safetyTable, function($join) use ($codeSafetyCategory,$safetyTable,$occur_date,$facility_id){
				    		$join->on("$codeSafetyCategory.ID", '=', "$safetyTable.CATEGORY_ID");
				    		$join->where("$safetyTable.FACILITY_ID",'=',$facility_id);
				    		$join->where("$safetyTable.CREATED_DATE",'=',$occur_date);
				    	})
				    	->join($facilitySafetyCategory, function($join) use ($codeSafetyCategory,$facilitySafetyCategory,$facility_id){
				    		$join->on("$codeSafetyCategory.ID", '=', "$facilitySafetyCategory.SAFETY_CATEGORY_ID");
				    		$join->where("$facilitySafetyCategory.FACILITY_ID",'=',$facility_id);
				    	})
				    	->where("$codeSafetyCategory.active","=",1)
				    	->select(
				    			"$codeSafetyCategory.ID as X_CATEGORY_ID",
				    			"$codeSafetyCategory.ID as DT_RowId",
				    			"$codeSafetyCategory.NAME as $safetyTable",
				    			"$safetyTable.*"
				    			)
// 		    			->orderBy($safetyTable)
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
