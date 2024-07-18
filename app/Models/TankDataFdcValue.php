<?php 
namespace App\Models; 
use App\Models\FeatureTankModel; 
use App\Trail\LinkingTankModel;

class TankDataFdcValue extends FeatureTankModel 
{ 
	use LinkingTankModel;
	
	protected $table = 'TANK_DATA_FDC_VALUE'; 
	protected $dates = ['OCCUR_DATE','LAST_DATA_READ'];
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE',
							 'LAST_DATA_READ',
							 'TANK_ID',
							 'TANK_LOCATION',
							 'OBS_API',
							 'OBS_TEMP',
							 'OBS_PRESS',
							 'BEGIN_LEVEL',
							 'END_LEVEL',
							 'BEGIN_VOL',
							 'END_VOL',
							 'BEGIN_LEVEL2',
							 'END_LEVEL2',
							 'BEGIN_VOL2',
							 'END_VOL2',
							 'SW',
							 'TANK_GRS_VOL',
							 'TANK_NET_VOL',
							 'TANK_WTR_VOL',
							 'TANK_DENSITY',
							 'TANK_GRS_MASS',
							 'TANK_NET_MASS',
							 'VOL_UOM',
							 'MASS_UOM',
							 'CTV',
							 'TEMP_UOM',
							 'PRESS_UOM',
							 'STATUS_BY',
							 'STATUS_DATE',
							 'RECORD_STATUS'];
	
	
	public static function getCalculateFields() {
		return  [
				config("constants.extraFields") 	=>	["END_VOL"=>"BEGIN_VOL","END_LEVEL"=>"BEGIN_LEVEL"],
				config("constants.keyField") 		=>	'TANK_ID'
		];
	}
	
	public static function updateDependenceFields($object_id,$values){
		if (array_key_exists("BEGIN_LEVEL", $values)) {
			$tank_level=$values["BEGIN_LEVEL"];
			if($tank_level!="") $values["BEGIN_VOL"] = \FormulaHelpers::calculateTankVolume($object_id,$tank_level);
		}
		else{
			$rec = static :: where('OCCUR_DATE','=',$values ["OCCUR_DATE"])
										->where(static::$idField,'=',$values ["TANK_ID"])
										->select(["BEGIN_LEVEL","END_LEVEL"])
										->first();
			$tank_level=$rec["BEGIN_LEVEL"];
			if($tank_level!="") $values["BEGIN_VOL"] = \FormulaHelpers::calculateTankVolume($object_id,$tank_level);
		}
		
		if (array_key_exists("END_LEVEL", $values)) {
			$tank_level=$values["END_LEVEL"];
			if($tank_level!="") $values["END_VOL"] = \FormulaHelpers::calculateTankVolume($object_id,$tank_level);
		}
		else{
			if(!isset($rec)){
				$rec = static :: where('OCCUR_DATE','=',$values ["OCCUR_DATE"])
											->where(static::$idField,'=',$values ["TANK_ID"])
											->select("END_LEVEL")
											->first();
			}
			$tank_level=$rec["END_LEVEL"];
			if($tank_level!="") $values["END_VOL"] = \FormulaHelpers::calculateTankVolume($object_id,$tank_level);
		}
		return $values;
	}
} 
