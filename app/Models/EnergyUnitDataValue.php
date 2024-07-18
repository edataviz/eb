<?php

namespace App\Models;
use App\Models\EnergyUnitDataFdcValue;
use App\Models\FeatureEuModel;
use App\Trail\QltyDataConstrain;

class EnergyUnitDataValue extends FeatureEuModel
{
	use QltyDataConstrain;
	protected $table = 'ENERGY_UNIT_DATA_VALUE';
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE', 
							'EU_ID', 
							'EU_STATUS', 
							'FLOW_PHASE', 
							'EVENT_TYPE', 
							'ACTIVE_HRS', 
							'EU_DATA_GRS_VOL', 
							'EU_DATA_NET_VOL', 
							'EU_DATA_GRS_MASS', 
							'EU_DATA_GRS_ENGY', 
							'EU_DATA_GRS_PWR', 
							'GOR', 
							'SW', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS',
							'EU_DATA_NET_MASS'
	];
	
	protected static $enableCheckCondition = true;
	
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){

		if(array_key_exists(config("constants.flowPhase"), $attributes)
				&&array_key_exists(config("constants.euIdColumn"),$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
		{
			$fields = ["EU_DATA_GRS_VOL","EU_DATA_NET_VOL",
						config("constants.keyField") 	=>	'EU_ID'];
			static::updateValues($attributes,$values,'ENERGY_UNIT',$fields);
		}
		return $values; 
	}
	
	public static function  getFdcValues($attributes){
		$fdcValues = EnergyUnitDataFdcValue::where($attributes)->first();
		return $fdcValues;
	}
}
