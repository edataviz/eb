<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\IntObjectType;
use App\Trail\RelationDynamicModel;

class IntTagMapping extends EbBussinessModel
{
	use RelationDynamicModel;
	protected $table = 'INT_TAG_MAPPING';
	protected $primaryKey = 'ID';
	protected $fillable  = ['TAG_ID', 
							'SYSTEM_ID', 
							'FREQUENCY', 
							'ALLOW_OVERRIDE', 
							'OBJECT_TYPE', 
							'OBJECT_ID', 
							'BEGIN_DATE', 
							'END_DATE', 
							'EVENT_TYPE', 
							'FLOW_PHASE', 
							'TABLE_NAME', 
							'COLUMN_NAME'];
	
	public  static  $idField = 'ID';
	
	
	protected $casts = [
			'configs' => 'object',
			'CONFIGS' => 'object',
	];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData) {
		if (array_key_exists("OBJECT_ID", $newData)&&!$newData["OBJECT_ID"])$newData["OBJECT_ID"] = 1;
		return parent::getKeyColumns($newData,$occur_date,$postData);
	}
	
	public static function getSourceModel($columnName){
		return "IntObjectType";
	}
	
	public function setConfigsAttribute($value){
		$tacf = json_encode($value);
		$this->attributes['configs'] = $tacf;
	}
	
	public function getConfigsAttribute($value){
		return json_decode ( $value , true );
	}
	
	public static function getList($condition = [], $selectedValue = null, $all = false, $none = false){
		$originAttrCase = \Helper::setGetterUpperCase();
		$rs	= IntTagMapping::where($condition)
			->select('TAG_ID as value', 'TAG_ID as name')
			->orderBy('TAG_ID')
			->distinct()
			->get()->all();
		\Helper::setGetterCase($originAttrCase);
		return $rs;
	}	
}
