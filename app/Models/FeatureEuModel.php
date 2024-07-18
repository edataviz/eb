<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\EuPhaseConfig;

class FeatureEuModel extends EbBussinessModel{
	
// 	public  static  $unguarded = true;
	public  static  $idField = 'EU_ID';
	public  static  $typeName = 'ENERGY_UNIT';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'EnergyUnit';
	protected $excludeColumns = ['EU_ID','OCCUR_DATE','FLOW_PHASE'];
	protected $disableUpdateAudit 	= false;
	protected $autoFillableColumns 	= true;
// 	protected $dates = ['CREATED_DATE','OCCUR_DATE','STATUS_DATE'];
	

	public static function getValueQuery($params){
		$field = $params[0];
		$objectId = $params[1];
		$eventType = $params[2];
		$flowPhase = $params[3];
		$occurDate = $params[count($params)-1];

		$select = "$field as value";
		$dateCondition = "OCCUR_DATE='$occurDate'";
		if(strpos($occurDate, ',') !== false){
			$ds = explode(',', $occurDate);
			$dateCondition = "(OCCUR_DATE>='{$ds[0]}' and OCCUR_DATE<='{$ds[1]}')";
			$select = "OCCUR_DATE, $field as value";
		}
		return "select $select from ". static::getTableName() . " where ".static::$idField."=$objectId and EVENT_TYPE=$eventType and FLOW_PHASE=$flowPhase and $dateCondition";
	}

	public static function isAllowFormula($formula){
		return ($formula->FLOW_PHASE>0 && $formula->EVENT_TYPE>0);
	}
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (array_key_exists(config("constants.euId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.euId")];
			unset($newData[config("constants.euId")]);
		}
		if (array_key_exists(static::$dateField, $newData)&&$newData[static::$dateField]) {
			$occur_date = $newData[static::$dateField];
		}
		else $newData[static::$dateField] = $occur_date;
		
		$newData[config("constants.flowPhase")] = $newData[config("constants.euFlowPhase")];
		
		$keyFields = [static::$idField 					=> $newData[static::$idField],
						config("constants.flowPhase") 	=> $newData[config("constants.euFlowPhase")],
						static::$dateField				=> $occur_date];
		
		if (array_key_exists(config("constants.eventType"), $newData)) {
			$newData['EVENT_TYPE'] 		= $newData[config("constants.eventType")];
			$keyFields['EVENT_TYPE'] 	= $newData[config("constants.eventType")];
		}
		elseif (array_key_exists("CodeEventType", $postData)){
			if ($postData["CodeEventType"]>0) {
				$newData['EVENT_TYPE'] 		= $postData["CodeEventType"];
				$keyFields['EVENT_TYPE'] 	= $postData["CodeEventType"];
			}
		}
		
		return $keyFields;
	}
	
	public static function findManyWithConfig($updatedIds)
	{
		$tableName = static ::getTableName();
		$euPhaseConfig = EuPhaseConfig::getTableName();
		$result = static::join($euPhaseConfig, function ($query) use ($tableName,$euPhaseConfig) {
													$query->on("$euPhaseConfig.EU_ID",'=', "$tableName.EU_ID")
															->on("$tableName.EVENT_TYPE",'=',"$euPhaseConfig.EVENT_TYPE")
															->on("$euPhaseConfig.FLOW_PHASE",'=', "$tableName.FLOW_PHASE");
											})
						->whereIn("$tableName.ID",$updatedIds)
						->select(
								"$euPhaseConfig.ID as ".config("constants.euPhaseConfigId"),
								"$tableName.*")
						->get();
		return $result;
	}
	
	public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase,$event_type) {
		$newData = [static::$idField=>$object_id];
		$newData[config("constants.euFlowPhase")] = $flow_phase;
		$newData[config("constants.eventType")] = $event_type;
		$attributes = static::getKeyColumns($newData,$occur_date,[]);
		$values = array_merge($values,$newData);
		return parent::updateOrCreate($attributes,$values);;
	}
	
	public static function findWith($object_id,$occur_date,$flow_phase,$event_type) {
		$newData = [static::$idField=>$object_id];
		$newData[config("constants.euFlowPhase")] = $flow_phase;
		$newData[config("constants.eventType")] = $event_type;
		$attributes = static::getKeyColumns($newData,$occur_date,[]);
		return parent::where($attributes)->first();
	}
	
	public static function getObjects() {
		return EnergyUnit::where("ID",">",0)->orderBy("NAME")->get();
	}
	
	public static function getVolMassValues($eu_id,$beginTime,$theorMethodType) {
		$theorEntries			= null;
		if ($theorMethodType&&$theorMethodType>0&&$eu_id&&$eu_id>0) {
			$codeFlowPhase		= CodeFlowPhase::getTableName();
			$mainTable			= static::getTableName();
			$type				= static::$modelType;
			$entries 			= static::join($codeFlowPhase,"$mainTable.FLOW_PHASE",'=',"$codeFlowPhase.ID")
										->where(["$mainTable.$type"			=> $theorMethodType,
												"$mainTable.EU_ID"			=> $eu_id])
										->whereDate("$mainTable.OCCUR_DATE"	,"=" ,$beginTime)
										->select("$codeFlowPhase.CODE as CODE",
												"$mainTable.EU_DATA_GRS_VOL as GRS_VOL",
												"$mainTable.EU_DATA_GRS_MASS as GRS_MASS")
										->get();
			$theorEntries 		= $entries->keyBy(config('database.default')==='oracle'?'code':"CODE");
		}
		return $theorEntries;
	}
	
	public static function loadTheorEntries($columns) {
		$theorModel = "App\Models\\".static ::$theorModel;
		$data = $theorModel::where("ACTIVE","=",1)->orderBy("ORDER")->orderBy('NAME');
		if ($columns) $data = $data->select($columns);
		$data = $data->get();
		return $data;
	}
	
	public function getVAttribute($value)
	{
		$value = $value?$value:0;
		return $value;
	}
}
