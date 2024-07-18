<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureTankModel extends EbBussinessModel
{
	public  static  $enableLevelCalculating = false;
	public  static  $idField = 'TANK_ID';
	public  static  $typeName = 'TANK';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'Tank';
	protected $excludeColumns = ['TANK_ID','OCCUR_DATE'];
	protected $disableUpdateAudit = false;
	protected $autoFillableColumns 	= true;
	
	public function getStorageId(){
		return null;
	}
	
	public function Tank()
	{
		return $this->belongsTo('App\Models\Tank', 'TANK_ID', 'ID');
	}
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (array_key_exists(config("constants.tankId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.tankId")];
			unset($newData[config("constants.tankId")]);
		}
		if (array_key_exists(static::$dateField, $newData)&&$newData[static::$dateField]) {
			$occur_date = $newData[static::$dateField];
		}
		else $newData[static::$dateField] = $occur_date;
		
		$cls = [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
		if (array_key_exists(config("constants.tankFlowPhase"), $newData)) {
			$cls[config("constants.flowPhase")] = $newData[config("constants.tankFlowPhase")];
		}
		return $cls;
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
	
		if(array_key_exists(config("constants.flowPhase"), $attributes)
				&&array_key_exists(config("constants.tankIdColumn"),$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
		{
			$fields = static::getCalculateFields();
			if ($fields) {
				static::updateValues($attributes,$values,'TANK',$fields);
			}
				
		}
		if(array_key_exists(config("constants.flowPhase"), $attributes)) unset($attributes[config("constants.flowPhase")]);
		return $values;
	}
	
	
	public static function updateValues(array $attributes, array &$values = [], $type, $fields) {
		$object_id = $attributes [$fields [config ( "constants.keyField" )]];
		$values  = static :: updateDependenceFields($object_id,$values);
		$occur_date = $attributes ["OCCUR_DATE"];
		$extraFields	= [	"END_VOL"	=>"BEGIN_VOL",
							"END_LEVEL"	=>"BEGIN_LEVEL"
							];
		$sourceFields 	= array_keys($extraFields);
//		\DB::enableQueryLog();
		$yesterdayRecord = static :: where('OCCUR_DATE','=',$occur_date->copy()->subDay())			 
									->where(static::$idField,'=',$object_id)
									->select($sourceFields)
									->first();
/*
		$yesterdayRecord = static :: whereDate('OCCUR_DATE','<',$occur_date)
									->where(static::$idField,'=',$object_id)
									->whereNotNull("END_LEVEL")
									->select($sourceFields)
									->orderBy('OCCUR_DATE','desc')
									->first();
*/
//		\Log::info(\DB::getQueryLog());
//		\DB::disableQueryLog();
				 
		if ($yesterdayRecord) {
			foreach ( $extraFields as $sourceField => $targetField ) {
				//if($yesterdayRecord->$sourceField) 
					$values[$targetField] = $yesterdayRecord->$sourceField;
			}
		}

		if (array_key_exists(config("constants.mainFields"), $fields)) {
			$mainFields = $fields[config("constants.mainFields")];
			$mainFields[config ( "constants.keyField" )] = $fields [config ( "constants.keyField" )]; 
			parent::updateValues($attributes, $values, $type,$mainFields);
		}
	}
	
	public static function updateDependenceFields($object_id,$values){
		return $values;
	}
	
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0)$wheres = ['FACILITY_ID'=>$facility_id];
		else $wheres = [];
		if ($product_type>0) {
			$wheres['PRODUCT'] = $product_type;
		}
		$entries = static ::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}
	
	public static function getObjects() {
		return Tank::where("ID",">",0)->orderBy("NAME")->get();
	}

	public static function getValueQuery($params){
		$field = $params[0];
		$objectId = $params[1];
		$occurDate = $params[count($params)-1];

		$select = "$field as value";
		$dateCondition = "OCCUR_DATE='$occurDate'";
		if(strpos($occurDate, ',') !== false){
			$ds = explode(',', $occurDate);
			$dateCondition = "(OCCUR_DATE>='{$ds[0]}' and OCCUR_DATE<='{$ds[1]}')";
			$select = "OCCUR_DATE, $field as value";
		}
		return "select $select from ". static::getTableName() . " where ".static::$idField."=$objectId and $dateCondition";
	}
}
