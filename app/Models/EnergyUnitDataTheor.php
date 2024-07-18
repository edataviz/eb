<?php

namespace App\Models;
use App\Models\FeatureEuModel;
use App\Trail\QltyDataConstrain;

class EnergyUnitDataTheor extends FeatureEuModel{
	
	use QltyDataConstrain;
	
	protected $table = 'ENERGY_UNIT_DATA_THEOR';
	
	protected $primaryKey = 'ID';
	protected $fillable  = ['OCCUR_DATE',
							'EU_ID',
							'EVENT_TYPE',
							'FLOW_PHASE',
							'ACTIVE_HRS',
							'EU_DATA_GRS_VOL',
							'EU_DATA_NET_VOL',
							'EU_DATA_GRS_MASS',
							'EU_DATA_GRS_ENGY',
							'EU_DATA_GRS_PWR',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS',
							'EU_DATA_NET_MASS'
	];
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
	
		if(array_key_exists(config("constants.flowPhase"), $attributes)
				&&array_key_exists(config("constants.euIdColumn"),$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
		{
			$systemName = config("constants.systemName");
			if($systemName=='CP')
				$dataRow = EnergyUnitDataValue::where($attributes)->first();
			else
				$dataRow = EnergyUnitDataFdcValue::where($attributes)->first();
			$active_hrs=$dataRow["ACTIVE_HRS"];
			
			$flow_phase = $attributes[config("constants.flowPhase")];
			$object_id = $attributes[config("constants.euIdColumn")];
			$occur_date = $attributes['OCCUR_DATE'];
			$rowTest=static::getEUTest($object_id,$occur_date);
				
			$theoFields = CfgFieldProps::getConfigFields( static::getTableName());
			$theoFieldArray =array_column($theoFields->toArray(), 'COLUMN_NAME');
			
			$flowPhases = config("constants.flowPhases");
			
			if($rowTest && is_numeric($active_hrs))
			{
				$rat=$active_hrs/24;
				foreach($theoFieldArray as $field ){
					$_v = false;
					if($systemName=='CP'){
						if($flow_phase==$flowPhases['OIL'])
						{
							if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_LIQ_HC_VOL;
							if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_TEST_LIQ_HC_MASS;
						}
						else if($flow_phase==$flowPhases['GAS'])
						{
							if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_GAS_HC_VOL;
							if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_TEST_GAS_HC_MASS;
						}
						else if($flow_phase==$flowPhases['GASLIFT'])
						{
							if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_GAS_LIFT_VOL;
						}
						else if($flow_phase==$flowPhases['CONDENSATE'])
						{
							if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_CONDENSATE_VOL;
						}
						else if($flow_phase==$flowPhases['WATER'])
						{
							if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_WTR_VOL;
							if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_WTR_MASS;
						}
					}
					else
					{
						if($flow_phase==$flowPhases['OIL'])
						{
							if($field=='EU_DATA_GRS_VOL') $_v=$rat*$rowTest->EU_TEST_TOTAL_LIQ_VOL;
							if($field=='EU_DATA_NET_VOL') $_v=$rat*$rowTest->EU_TEST_LIQ_HC_VOL;
							if($field=='EU_DATA_GRS_MASS') $_v=$rat*$rowTest->EU_TEST_TOTAL_LIQ_MASS;
							if($field=='EU_DATA_NET_MASS') $_v=$rat*$rowTest->EU_TEST_LIQ_HC_MASS;
						}
						else if($flow_phase==$flowPhases['GAS'])
						{
							if($field=='EU_DATA_GRS_VOL' || $field=='EU_DATA_NET_VOL') $_v=$rat*$rowTest->EU_TEST_GAS_HC_VOL;
							if($field=='EU_DATA_GRS_MASS' || $field=='EU_DATA_NET_MASS') $_v=$rat*$rowTest->EU_TEST_GAS_HC_MASS;
							if($field=='EU_DATA_GRS_ENGY') $_v=$rat*$rowTest->EU_TEST_ENGY_QTY;
							if($field=='EU_DATA_GRS_PWR') $_v=$rat*$rowTest->EU_TEST_POWR_QTY;
						}
						else if($flow_phase==$flowPhases['GASLIFT'])
						{
							if($field=='EU_DATA_GRS_VOL' || $field=='EU_DATA_NET_VOL') $_v=$rat*$rowTest->EU_TEST_GAS_LIFT_VOL;
							if($field=='EU_DATA_GRS_MASS' || $field=='EU_DATA_NET_MASS') $_v=$rat*$rowTest->EU_TEST_GAS_LIFT_MASS;
						}
						else if($flow_phase==$flowPhases['CONDENSATE'])
						{
							if($field=='EU_DATA_GRS_VOL' || $field=='EU_DATA_NET_VOL') $_v=$rat*$rowTest->EU_TEST_CONDENSATE_VOL;
						}
						else if($flow_phase==$flowPhases['WATER'])
						{
							if($field=='EU_DATA_GRS_VOL' || $field=='EU_DATA_NET_VOL') $_v=$rat*$rowTest->EU_TEST_WTR_VOL;
							if($field=='EU_DATA_GRS_MASS' || $field=='EU_DATA_NET_MASS') $_v=$rat*$rowTest->EU_WTR_MASS;
						}
					}
					if($_v!==false)
						$values[$field] = $_v;
				}
			}
		}
		return $values; 
	}
}
