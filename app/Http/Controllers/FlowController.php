<?php

namespace App\Http\Controllers;
use App\Models\CodeFlowPhase;
use App\Models\FlowHistory;
use App\Models\FlowDataForecast;
use App\Models\FlowDataPlan;
use App\Models\FlowDataAlloc;

class FlowController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->fdcModel = "FlowDataFdcValue";
		$this->idColumn = config("constants.flowId");
		$this->phaseColumn = config("constants.flFlowPhase");
	
		$this->valueModel = "FlowDataValue";
		$this->theorModel = "FlowDataTheor";
		$this->isApplyFormulaAfterSaving = true;
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	}
	
	public function getGroupFilter($postData){
		$filterGroups = array('productionFilterGroup'	=> [],
							 'frequenceFilterGroup'		=> ['CodeReadingFrequency','CodeFlowPhase']
						);
		 
		return $filterGroups;
	}
	
	public function compareEntryKeys($item,$element){
		$sameKey 	=$item&&$element&&
					array_key_exists("X_FLOW_ID", $item)&&
					array_key_exists("FL_FLOW_PHASE", $item)&&
					array_key_exists("X_FLOW_ID", $element)&&
					array_key_exists("FL_FLOW_PHASE", $element)&&
	    			$item["X_FLOW_ID"] 				== $element["X_FLOW_ID"]&&
					$item["FL_FLOW_PHASE"] 			== $element["FL_FLOW_PHASE"];
		if ($sameKey) {
			if (array_key_exists("OCCUR_DATE", $item)&&$item["OCCUR_DATE"]) {
				$sameKey = array_key_exists("OCCUR_DATE", $element)&&$item["OCCUR_DATE"]==$element["OCCUR_DATE"];
			}
		}
		return $sameKey;
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
	public function enableMergeMissData($model,$sourceModel,$editedData,$entryIndex){
		return true;
	}
	
	protected function sortByModel($editedData) {
		ksort($editedData);
		if(array_key_exists("FlowDataValue", $editedData)&&array_key_exists("FlowDataTheor", $editedData)){
			$FlowDataTheor	= $editedData["FlowDataTheor"];
			unset($editedData["FlowDataTheor"]);
			$editedData["FlowDataTheor"]	= $FlowDataTheor;
		}
		return $editedData;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$record_freq 	= $postData['CodeReadingFrequency'];
    	$phase_type 	= $postData['CodeFlowPhase'];
    	$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
    	$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
    	$allocType 	= array_key_exists('CodeAllocType', $postData)?$postData['CodeAllocType']:0;
    	
    	$flowHistory 	= FlowHistory::getTableName();
    	$codeFlowPhase 	= CodeFlowPhase::getTableName();
		$tablePlan = FlowDataPlan::getTableName();
		$tableForecast = FlowDataForecast::getTableName();
		$tableAlloc = FlowDataAlloc::getTableName();
    	 
    	$where = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($record_freq>0) {
    		$where["$flowHistory.RECORD_FREQUENCY"]= $record_freq;
//     		$where["$dcTable.RECORD_FREQUENCY"]= $record_freq;
    	}
    	if ($phase_type>0) {
    		$where['PHASE_ID']= $phase_type;
    	}
    	$startOfMonth	= $occur_date->copy();
    	$startOfMonth->startOfMonth();
    	if ($occur_date->ne($startOfMonth)) {
		    $where[]= ["$flowHistory.RECORD_FREQUENCY",'<>',6];
		}
    	//      	\DB::enableQueryLog();
		$columns	= $this->extractRespondColumns($dcTable,$properties);
		if (!$columns) $columns = [];
		array_push($columns,"$dcTable.OCCUR_DATE",
							"$flowHistory.name as $dcTable",
			    			"$dcTable.ID as DT_RowId",
			    			"$flowHistory.OBJECT_ID as ".config("constants.flowId"),
			    			"$flowHistory.OBJECT_ID as ID",
							"$dcTable.RECORD_STATUS",
			    			"$flowHistory.phase_id as FL_FLOW_PHASE",
			    			"$codeFlowPhase.name as PHASE_NAME",
			    			"$codeFlowPhase.CODE as PHASE_CODE");
		
		$query 	= $this->buildQuery("FlowHistory",$occur_date,$facility_id,$postData);
    	$dataSet = $query->join($codeFlowPhase,'PHASE_ID', '=', "$codeFlowPhase.ID")
				    	->where($where)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	//      					->where('OCCUR_DATE', '=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($flowHistory,$dcTable,$occur_date,$planType,$forecastType,$allocType,$tablePlan,$tableForecast,$tableAlloc){
				    		$join->on("$flowHistory.OBJECT_ID", '=', "$dcTable.flow_id");
				    		$join->where('OCCUR_DATE','=',$occur_date);
				    		if (($planType > 0 &&  ($dcTable == $tablePlan )))
			    				$join->where("$dcTable.PLAN_TYPE",'=',$planType);
							else if (($forecastType > 0 &&  ($dcTable == $tableForecast )))
		    					$join->where("$dcTable.FORECAST_TYPE",'=',$forecastType);
		    				else if (($allocType > 0 &&  ($dcTable == $tableAlloc )))
		    					$join->where("$dcTable.ALLOC_TYPE",'=',$allocType);
				    	})
				    	->select($columns)
		    			->orderBy($dcTable)
		    			->orderBy('FL_FLOW_PHASE')
// 		    			->take(3)
// 		    			->skip(3)
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
//     	\Helper::setGetterLowerCase();
    	return ['dataSet'=>$dataSet];
    	   
    }
    
    public function getObjectIds($dataSet,$postData,$properties){
    	$isContainCTV	= $this->containField($properties,"CTV");
    	$objectIds = $dataSet->map(function ($item, $key) use($isContainCTV) {
    		$keyFields	=   ["DT_RowId"			=> $item->DT_RowId,
		    				"FL_FLOW_PHASE"		=> $item->FL_FLOW_PHASE,
		    				"FLOW_ID"			=> $item->X_FLOW_ID,
		    				"X_FLOW_ID"			=> $item->X_FLOW_ID,
    						"OCCUR_DATE"		=> $item->OCCUR_DATE
    		];
    		if ($isContainCTV) $keyFields["CTV"] = $item->CTV;
    		return $keyFields;
    	});
    	return $objectIds;
    }
    
	public function getHistoryConditions($dcTable,$rowData,$row_id){
		$obj_id			= $rowData[config("constants.flowId")];
		return ['FLOW_ID'	=>	$obj_id];
	}

    public function getFirstProperty($dcTable){
    	return  ['data'=>$dcTable,'title'=>'Object name','width'=>300];
    }    
}
