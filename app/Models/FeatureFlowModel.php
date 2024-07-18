<?php

namespace App\Models;

class FeatureFlowModel extends EbBussinessModel
{
	public  static  $idField = 'FLOW_ID';
	public  static  $typeName = 'FLOW';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'Flow';
	protected $excludeColumns = ['FLOW_ID','OCCUR_DATE'];
	protected $disableUpdateAudit = false;
	protected $autoFillableColumns 	= true;
	protected $dates = ['LAST_DATA_READ','OCCUR_DATE','STATUS_DATE'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		if (array_key_exists(config("constants.flowId"), $newData)) {
			$newData[static::$idField] = $newData[config("constants.flowId")];
			unset($newData[config("constants.flowId")]);
		}
		if (array_key_exists(static::$dateField, $newData)&&$newData[static::$dateField]) {
			$occur_date = $newData[static::$dateField];
		}
		else $newData[static::$dateField] = $occur_date;
		return [static::$idField => $newData[static::$idField],
				static::$dateField=>$occur_date];
	}
	
	public static function getObjects() {
		return Flow::where("ID",">",0)->orderBy("NAME")->get();
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
		//return "select $field from ". static::$table . ' where ' . static::$idField . "=$objectId and " . static::$dateField . "='$occurDate'";
		return "select $select from ". static::getTableName() . " where ".static::$idField."=$objectId and $dateCondition";
	}
}
