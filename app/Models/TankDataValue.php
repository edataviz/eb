<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 
use App\Models\TankDataFdcValue;
use App\Trail\LinkingTankModel;

 class TankDataValue extends FeatureTankModel 
{ 
	use LinkingTankModel;
	
	protected $table 		= 'TANK_DATA_VALUE';
	protected $primaryKey 	= 'ID';
	protected $dates 		= ['OCCUR_DATE'];
	protected $fillable  = ['TANK_ID',
							'OCCUR_DATE',
							'TANK_LOCATION',
							'BEGIN_LEVEL',
							'END_LEVEL',
							'BEGIN_VOL',
							'END_VOL',
							'BEGIN_LEVEL2',
							'END_LEVEL2',
							'BEGIN_VOL2',
							'END_VOL2',
							'AVAIL_SHIPPING_VOL',
							'SW',
							'TANK_GRS_VOL',
							'TANK_NET_VOL',
							'TANK_WTR_VOL',
							'TANK_CORR_VOL',
							'TANK_DENSITY',
							'TANK_GRS_MASS',
							'TANK_NET_MASS',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS'];
	
	protected static $enableCheckCondition = true;
	
	public static function  getFdcValues($attributes){
		if(array_key_exists(config("constants.flowPhase"), $attributes)) unset($attributes[config("constants.flowPhase")]);
		$fdcValues = TankDataFdcValue::where($attributes)->first();
		return $fdcValues;
	}
	
	public static function getCalculateFields() {
		return  [
					config("constants.mainFields") 		=>	["TANK_GRS_VOL","TANK_NET_VOL","STORAGE_GRS_VOL","STORAGE_NET_VOL"],
					config("constants.extraFields") 	=>	["END_VOL"=>"BEGIN_VOL"],
					config("constants.keyField") 		=>	'TANK_ID'
			];;
	}
} 
