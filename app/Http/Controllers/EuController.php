<?php

namespace App\Http\Controllers;
use App\Models\CodeEventType;
use App\Models\CodeFlowPhase;
use App\Models\CodeStatus;
use App\Models\EnergyUnitHistory;
use App\Models\EnergyUnitCompDataAlloc;
use App\Models\EnergyUnitDataAlloc;
use App\Models\EnergyUnitDataForecast;
use App\Models\EnergyUnitDataPlan;
use App\Models\EnergyUnitStatus;
use App\Models\EuPhaseConfigHistory;

class EuController extends CodeController {
    protected $isOracle = false;
	public function __construct() {
		parent::__construct();
		$this->isOracle = config('database.default')==='oracle';
		$this->isApplyFormulaAfterSaving = true;
		$this->fdcModel 	= "EnergyUnitDataFdcValue";
		$this->idColumn 	= config("constants.euId");
		$this->phaseColumn 	= config("constants.euFlowPhase");
		
		$this->valueModel 	= "EnergyUnitDataValue";
		$this->theorModel 	= "EnergyUnitDataTheor";
		
		$this->keyColumns 	= [$this->idColumn,$this->phaseColumn,config("constants.eventType")];
	}
	

	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
	public function getObjectIds($dataSet,$postData,$properties){
		$isContainCTV	= $this->containField($properties,"CTV");
		$objectIds 		= $dataSet->map(function ($item, $key) use($isContainCTV) {
			$keyFields	=  ["DT_RowId"						=> $item->DT_RowId,
							"EU_FLOW_PHASE"					=> $item->EU_FLOW_PHASE,
							"EU_ID"							=> $item->X_EU_ID,
							"X_EU_ID"						=> $item->X_EU_ID,
							"EU_CONFIG_EVENT_TYPE"			=> $item->EU_CONFIG_EVENT_TYPE,
							"OCCUR_DATE"					=> $item->OCCUR_DATE
								
			];
			if ($isContainCTV) $keyFields["CTV"] = $item->CTV;
			return $keyFields;
		});
		return $objectIds;
	}
	
	function getEuPhaseConfigQuery(){
		
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$eu_group_id 	= $postData['EnergyUnitGroup'];
    	$record_freq 	= $postData['CodeReadingFrequency'];
    	$phase_type 	= $postData['CodeFlowPhase'];
    	$event_type 	= $postData['CodeEventType'];
    	$alloc_type 	= array_key_exists('CodeAllocType', $postData)?$postData['CodeAllocType']:0;
    	$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
    	$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
    	 
    	$euHistory		= EnergyUnitHistory::getTableName();
    	$codeFlowPhase 	= CodeFlowPhase::getTableName();
    	$codeStatus 	= CodeStatus::getTableName();
    	$euPhaseConfig 	= EuPhaseConfigHistory::getTableName();
    	$euStatus	 	= EnergyUnitStatus::getTableName();
    	$codeEventType 	= CodeEventType::getTableName();
    	 
    	$query 			= $this->buildQuery("EnergyUnitHistory",$occur_date,$facility_id,$postData);
    	$euWheres 		= ["$euHistory.FACILITY_ID" => $facility_id];
    	if ($record_freq>0) $euWheres["$euHistory.DATA_FREQ"]= $record_freq;
     	if ($eu_group_id>0) $euWheres["$euHistory.EU_GROUP_ID"]= $eu_group_id;
//     	else $euWheres["$eu.EU_GROUP_ID"]= null;
    	$columns	= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns = [];
    	array_push($columns,"$dcTable.OCCUR_DATE",
							"$dcTable.RECORD_STATUS",
    						"$euHistory.name as $dcTable",
							"$euPhaseConfig.ID as DT_RowId",
	 						"$codeFlowPhase.name as PHASE_NAME",
	 						"$codeEventType.name as TYPE_NAME",
							"$euPhaseConfig.EVENT_TYPE as ".config("constants.eventType"),
							"$euHistory.OBJECT_ID as ".config("constants.euId"),
	   						"$euPhaseConfig.FLOW_PHASE as EU_FLOW_PHASE",
// 	   						"$euPhaseConfig.ORDERS as PHASE_ORDERS",
	  						"$codeStatus.NAME as STATUS_NAME",
							"$codeFlowPhase.CODE as PHASE_CODE",
							"$codeEventType.CODE as TYPE_CODE");
//      	\DB::enableQueryLog();
    	//$eupcColumns	= EuPhaseConfig::getInstance()->getTableColumns();
    	//$orderField 	= in_array('ORDERS', $eupcColumns)?"$euPhaseConfig.ORDERS as PHASE_ORDERS":"$euPhaseConfig.ID as PHASE_ORDERS";
		//$orderField 	= "$euPhaseConfig.ORDERS as PHASE_ORDERS";
    	//array_push($columns,$orderField);
    		 
    		
		$euWheres["s1.FDC_DISPLAY"] = 1;
		
		$query = $query->leftjoin($euStatus.($this->isOracle?'':' AS').' s1','s1.EU_ID','=',"$euHistory.OBJECT_ID")
					->join($codeStatus,'s1.EU_STATUS', '=', "$codeStatus.ID")
					->join($euPhaseConfig,function ($query) use ($euHistory,$euPhaseConfig,$phase_type,$event_type,$occur_date) {
											$query->on("$euPhaseConfig.EU_ID",'=',"$euHistory.OBJECT_ID");
											$query->where("$euPhaseConfig.ACTIVE",'=',1)
											->where ( function ($query) use($occur_date, $euPhaseConfig) {
													$query->whereNull ( "$euPhaseConfig.EFFECTIVE_DATE" )->orWhere ( "$euPhaseConfig.EFFECTIVE_DATE", '<=', $occur_date );
												} )
											->where ( function ($query) use($occur_date, $euPhaseConfig) {
													$query->whereNull ( "$euPhaseConfig.EXPIRE_DATE" )->orWhere( "$euPhaseConfig.EXPIRE_DATE", '>', $occur_date );
												} ) ;
											if ($phase_type>0) $query->where("$euPhaseConfig.FLOW_PHASE",'=',$phase_type) ;
											if ($event_type>0) $query->where("$euPhaseConfig.EVENT_TYPE",'=',$event_type) ;
											//TODO note chu y active co the se dung
	// 							    		$query->with('CodeFlowPhase');
	// 							    		$query->select('FLOW_PHASE as EU_FLOW_PHASE');
					}) 
					->join($codeFlowPhase,"$euPhaseConfig.FLOW_PHASE", '=', "$codeFlowPhase.ID")
					->join($codeEventType,"$euPhaseConfig.EVENT_TYPE", '=', "$codeEventType.ID")
					->where($euWheres)
					->whereRaw("s1.EFFECTIVE_DATE = (SELECT max(EFFECTIVE_DATE) FROM $euStatus s2 WHERE s1.EU_ID = s2.EU_ID and ".($this->isOracle?"TRUNC(EFFECTIVE_DATE)":"CAST(EFFECTIVE_DATE AS date)")."<='$occur_date')")
					->leftJoin($dcTable, function($join) use ($euHistory,$dcTable,$euPhaseConfig,$occur_date,$alloc_type,$planType,$forecastType){
												//TODO add table name 
												$join->on("$euHistory.OBJECT_ID", '=', "$dcTable.EU_ID")
													->on("$dcTable.FLOW_PHASE",'=',"$euPhaseConfig.FLOW_PHASE")
													->on("$dcTable.EVENT_TYPE",'=',"$euPhaseConfig.EVENT_TYPE")
													->where("$dcTable.OCCUR_DATE",'=',$occur_date);
											
												$energyUnitDataAlloc 		= EnergyUnitDataAlloc::getTableName();
												$energyUnitCompDataAlloc 	= EnergyUnitCompDataAlloc::getTableName();
												if (($alloc_type > 0 &&  ($dcTable == $energyUnitDataAlloc || $dcTable == $energyUnitCompDataAlloc))) 
													$join->where("$dcTable.ALLOC_TYPE",'=',$alloc_type);
												else if (($planType > 0 &&  ($dcTable == EnergyUnitDataPlan::getTableName() ))) 
													$join->where("$dcTable.PLAN_TYPE",'=',$planType);
												else if (($forecastType > 0 &&  ($dcTable == EnergyUnitDataForecast::getTableName() )))
													$join->where("$dcTable.FORECAST_TYPE",'=',$forecastType);
					})
					->select($columns) 
	    			->orderBy("$dcTable")
					->orderBy("$euPhaseConfig.ORDERS")
					->orderBy("$euPhaseConfig.ID")
//					->orderBy('EU_FLOW_PHASE')
//   		    			->take(3)
//   		    			->skip(0)
					;
//		\Log::info($query->toSql());
		$dataSet = $query->get();
//   		\Log::info(\DB::getQueryLog());
    	return ['dataSet'=>$dataSet];
    }
	
	public function enableMergeMissData($model,$sourceModel,$editedData,$entryIndex){
		return true;
	}
    
	
	protected function sortByModel($editedData) {
		ksort($editedData);
		if(array_key_exists("EnergyUnitDataValue", $editedData)&&array_key_exists("EnergyUnitDataTheor", $editedData)){
			$EnergyUnitDataTheor	= $editedData["EnergyUnitDataTheor"];
			unset($editedData["EnergyUnitDataTheor"]);
			$editedData["EnergyUnitDataTheor"]	= $EnergyUnitDataTheor;
		}
		return $editedData;
	}
    
    public function compareEntryKeys($item,$element){
    	$sameKey 	=$item&&$element&&
			    	array_key_exists("X_EU_ID", $item)&&
			    	array_key_exists("EU_FLOW_PHASE", $item)&&
			    	array_key_exists("EU_CONFIG_EVENT_TYPE", $item)&&
			    	array_key_exists("X_EU_ID", $element)&&
			    	array_key_exists("EU_FLOW_PHASE", $element)&&
			    	array_key_exists("EU_CONFIG_EVENT_TYPE", $element)&&
    				$item["X_EU_ID"] 				== $element["X_EU_ID"]&&
    				$item["EU_FLOW_PHASE"] 			== $element["EU_FLOW_PHASE"]&&
    				$item["EU_CONFIG_EVENT_TYPE"] 	== $element["EU_CONFIG_EVENT_TYPE"];
    	if ($sameKey) {
    		if (array_key_exists("OCCUR_DATE", $item)&&$item["OCCUR_DATE"]) {
    			$sameKey = array_key_exists("OCCUR_DATE", $element)&&$item["OCCUR_DATE"]==$element["OCCUR_DATE"];
    		}
    	}
    	return $sameKey;
    }
    
    protected function afterSave($resultRecords,$occur_date) {
//     	\DB::enableQueryLog();
    	foreach($resultRecords as $mdlName => $records ){
    		$mdl = "App\Models\\".$mdlName;
    		foreach($records as $record ){
     			$mdl::updateWithQuality($record,$occur_date);
    		}
    	}
//   		\Log::info(\DB::getQueryLog());
    }
    
    protected function getFlowPhase($newData) {
    	return $newData [config ( "constants.euFlowPhase" )];
    }
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	$obj_id			= $rowData[config("constants.euId")];
		$where			= ["EU_ID"	=> $obj_id];
		if(array_key_exists('EU_FLOW_PHASE', $rowData))
			$where['FLOW_PHASE'] = $rowData['EU_FLOW_PHASE'];
		if(array_key_exists('EU_CONFIG_EVENT_TYPE', $rowData))
			$where['EVENT_TYPE'] = $rowData['EU_CONFIG_EVENT_TYPE'];
		if(array_key_exists('ALLOC_TYPE', $rowData))
			$where['ALLOC_TYPE'] = $rowData['ALLOC_TYPE'];
		if(array_key_exists('PLAN_TYPE', $rowData))
			$where['PLAN_TYPE'] = $rowData['PLAN_TYPE'];
		if(array_key_exists('FORECAST_TYPE', $rowData))
			$where['FORECAST_TYPE'] = $rowData['FORECAST_TYPE'];
    	return $where;
    }
}
