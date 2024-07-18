<?php
namespace App\Http\Controllers\Cargo;

use App\Models\PdCargo;
use App\Models\Storage;

class CargoScheduleController extends CargoAdminController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	$storage_id 	= $postData['Storage'];
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	$pdCargo 		= PdCargo::getTableName();
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($pdCargo,function ($query) use ($pdCargo,$storage_id,$dcTable) {
								    		$query->on("$dcTable.CARGO_ID",'=',"$pdCargo.ID")
						    				->where("$pdCargo.STORAGE_ID",'=',$storage_id) ;
			    		})
    					->whereDate("$dcTable.SCHEDULE_DATE",'<=',$date_end)
    					->whereDate("$dcTable.SCHEDULE_DATE",'>=',$occur_date)
    					->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*"
    							) 
   		    			->orderBy("$dcTable")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
        $extraDataSet 	= $this->getExtraDataSet($dataSet);
    	 
    	return ['dataSet'		=> $dataSet,
    			'extraDataSet'	=> $extraDataSet
    	];
    }
}
