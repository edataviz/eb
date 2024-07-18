<?php
namespace App\Http\Controllers\Cargo;

use App\Models\PdCargo;
use App\Models\TerminalTimesheetData;

class CargoUnloadController extends CargoLoadController {
    
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if (!array_key_exists("Storage", $postData)) {
    		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
    	}
    	$storage_id		= $postData['Storage'];
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	 
    	$pdCargo 		= PdCargo::getTableName();
    	$dataSet = $mdl::join($pdCargo,
			    			"$dcTable.CARGO_ID",
			    			'=',
			    			"$pdCargo.ID")
		    			->whereDate("$dcTable.DATE_UNLOAD",'>=',$occur_date)
    					->whereDate("$dcTable.DATE_UNLOAD",'<=',$date_end)
				    	->where("$pdCargo.STORAGE_ID",'=',$storage_id)
    					->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    public function getDetailData($id,$postData,$properties){
    	$terminalTimesheetData 			= TerminalTimesheetData::getTableName();
    	$dataSet = TerminalTimesheetData::where("$terminalTimesheetData.IS_LOAD",'=',0)
						    			->where("$terminalTimesheetData.PARENT_ID",'=',$id)
						    			->select(
						    					"$terminalTimesheetData.*",
						    					"$terminalTimesheetData.ID as DT_RowId",
		 				    					"$terminalTimesheetData.ID as $terminalTimesheetData"
						    					)
				    					->get();
    	return $dataSet;
    }
}
