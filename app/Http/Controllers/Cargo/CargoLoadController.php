<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargo;
use App\Models\TerminalActivitySetList;
use App\Models\TerminalTimesheetData;
use Illuminate\Http\Request;

class CargoLoadController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "TerminalTimesheetData";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	public function loadDetail(Request $request){
		$postData 				= $request->all();
		$id 					= $postData['id'];
		$tab					= isset($postData['tab'])?$postData['tab']:$this->detailModel;
		$detailModel			= "App\Models\\$tab";
		$detailTable	 		= $detailModel::getTableName();
		$results 				= $this->getProperties($detailTable);
		$originAttrCase = \Helper::setGetterUpperCase();
		$dataSet 				= $this->getDetailData($id,$postData,$results['properties']);
		\Helper::setGetterCase($originAttrCase);
		
		$results['dataSet'] 	= $dataSet;
	
		return response()->json([$this->detailModel => $results]);
	}
	
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
		    			->whereDate("$dcTable.DATE_LOAD",'>=',$occur_date)
    					->whereDate("$dcTable.DATE_LOAD",'<=',$date_end)
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
    	$dataSet = TerminalTimesheetData::where("$terminalTimesheetData.IS_LOAD",'=',1)
						    			->where("$terminalTimesheetData.PARENT_ID",'=',$id)
						    			->select(
						    					"$terminalTimesheetData.*",
						    					"$terminalTimesheetData.ID as DT_RowId",
		 				    					"$terminalTimesheetData.ID as $terminalTimesheetData"
						    					)
				    					->get();
    	return $dataSet;
    }
    
    
    public function activities(Request $request){
    	$postData 				= $request->all();
    	$set_id					= $postData['id'];
    	$terminalActivitySetList 		= TerminalActivitySetList::getTableName();
    	$dataSet 						= TerminalActivitySetList::where("SET_ID",'=',$set_id)
									    	->select("ACTIVITY_ID","ACTIVITY_ID as $terminalActivitySetList")
									    	->get();
    	$results = ['updatedData'	=>['TerminalTimesheetData'=>$dataSet],
    				'postData'		=>$postData];
    	return response()->json($results);
    }
}
