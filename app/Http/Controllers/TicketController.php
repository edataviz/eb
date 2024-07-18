<?php

namespace App\Http\Controllers;
use App\Models\Tank;
use App\Models\RunTicketFdcValue;
use App\Models\RunTicketValue;
use Carbon\Carbon;

class TicketController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "RunTicketFdcValue";
 		$this->valueModel = "RunTicketValue";
		$this->idColumn = 'ID';
		$this->phaseColumn = 'FLOW_PHASE';
		
		$this->keyColumns = [$this->phaseColumn,'TANK_ID','OCCUR_DATE','TICKET_NO'];
		
		$this->extraDataSetColumns = [	
										'PHASE_TYPE'		=>	'FLOW_ID',
		];
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
// 		return $mdlName=="RunTicketFdcValue";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
	public function enableMergeMissData($model,$sourceModel,$editedData,$entryIndex){
		return true;
	}
	
	public function getObjectIds($dataSet,$postData,$properties){
		$objectIds = $dataSet->map(function ($item, $key) {
			$odate = $item->OCCUR_DATE;
			$odate	= $odate instanceof Carbon ?$odate->toDateString():$odate;
			return ["DT_RowId"			=> $item->DT_RowId,
					"FLOW_PHASE"		=> $item->FLOW_PHASE,
					"TANK_ID"			=> $item->TANK_ID,
    				"ID"				=> $item->ID,
					"TICKET_NO"			=> $item->TICKET_NO,
    				"OCCUR_DATE"		=> $odate,
			];
		});
		return $objectIds;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id 		= $postData['Tank'];
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	 
    	$tank = Tank::getTableName();
    	
    	$columns	= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns = [];
    	array_push($columns,
    				"$tank.PRODUCT as FLOW_PHASE",
    				"$dcTable.ID as $dcTable",
    				"$dcTable.ID as ID",
    				"$dcTable.TANK_ID as OBJ_ID",
	    			"$dcTable.TANK_ID",
	    			"$dcTable.ID as DT_RowId",
	    			"$dcTable.OCCUR_DATE as T_OCCUR_DATE");
    	
    	
    	$wheres = ['TANK_ID' => $object_id];
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($tank,"$dcTable.TANK_ID", '=', "$tank.ID")
    					->where($wheres)
				    	->whereBetween('OCCUR_DATE', [$occur_date,$date_end])
				    	->select($columns) 
  		    			->orderBy("$dcTable.OCCUR_DATE")
  		    			->orderBy("$dcTable.LOADING_TIME")
  		    			->orderBy("$dcTable.TICKET_NO")
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
  		$extraDataSet 	= $this->getExtraDataSet($dataSet, $postData);
    	return ['dataSet'		=>$dataSet,
     			'extraDataSet'	=>$extraDataSet
    	];
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$postData){
    	$data 			= null;
    	$field			= 'NAME as '.\Helper::getConstantTextOverDbDriver("text");
    	switch ($sourceColumn) {
    		case 'PHASE_TYPE':
    			switch ($extraDataSetColumn) {
    				case 'TARGET_TANK':
    					$targetEloquent = "App\Models\Tank";
    					$data = $targetEloquent::where('PRODUCT','=',$sourceColumnValue)
    											->select(
					    							"ID as value",
		    										$field
					    						)
    											->get();
    					break;
    				case 'FLOW_ID':
//     					$facilityIds 	= \Helper::getAvailableFacilities();
    					$facilityIds 	= [$postData["Facility"]];
    					$query 			= \App\Models\Flow::where('PHASE_ID','=',$sourceColumnValue)
		    								->where('FDC_DISPLAY','=',1)
		    								->select(
				    								"ID as value",
		    										$field
    										);
    					if ($facilityIds&&is_array($facilityIds)) {
    						$query->whereIn("FACILITY_ID",$facilityIds);
    					}
    					$data 			= $query->get();
    					break;
    			}
    			break;
    	}
    	return $data;
    }
    
    public function compareEntryKeys($item,$element){
    	return 	$item&&$element&&
		    	array_key_exists("TANK_ID", $item)&&
		    	array_key_exists("OCCUR_DATE", $item)&&
		    	array_key_exists("TICKET_NO", $item)&&
		    	array_key_exists("TANK_ID", $element)&&
		    	array_key_exists("OCCUR_DATE", $element)&&
		    	array_key_exists("TICKET_NO", $element)&&
    			$item["TANK_ID"] 			== $element["TANK_ID"]&&
    			$item["OCCUR_DATE"] 		== $element["OCCUR_DATE"]&&
    			$item["TICKET_NO"] 			== $element["TICKET_NO"];
    }
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['TANK_ID'			=>	$rowData["TANK_ID"],
    	];
    }
    
    public function getHistoryData($mdl, $field,$rowData,$where, $limit){
    	$row_id			= $rowData['ID'];
    	if ($row_id<=0) return [];
    
    	$nameSelect	= config('database.default')==='sqlsrv'?
    	\DB::raw("(convert(varchar(10), OCCUR_DATE, 101) + ' ' + CAST(LOADING_TIME AS varchar) ) as OCCUR_DATE"):
    	\DB::raw("concat(concat(OCCUR_DATE,' '), LOADING_TIME) as OCCUR_DATE");
    	
    	$occur_date		= $rowData['OCCUR_DATE'];
    	$history 		= $mdl::where($where)
					    	->whereDate('OCCUR_DATE', '<', $occur_date)
					    	->whereNotNull($field)
					    	->orderBy('OCCUR_DATE','desc')
					    	->skip(0)->take($limit)
					    	->select($nameSelect,"$field as VALUE")
					    	->get();
    	return $history;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$row = Tank::where(['ID'=>$rowData['TANK_ID']])
    	->select('NAME')
    	->first();
    	$obj_name		= $row?$row->NAME:"";
    	return $obj_name;
    }
}
