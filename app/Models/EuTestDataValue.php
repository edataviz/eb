<?php 
namespace App\Models; 
use App\Models\EuTestDataStdValue;
use App\Models\FeatureEuTestModel;
use App\Models\QltyData;

 class EuTestDataValue extends FeatureEuTestModel 
{ 
	protected $table = 'EU_TEST_DATA_VALUE'; 
	
	protected $fillable  = ['EU_ID', 
							'BEGIN_TIME', 
							'END_TIME', 
							'TEST_HRS', 
							'EFFECTIVE_DATE', 
							'TEST_METHOD', 
							'TEST_DEVICE_ID', 
							'TEST_USAGE', 
							'EVENT_TYPE', 
							'REFERENCE_ID', 
							'EU_TEST_TOTAL_LIQ_VOL', 
							'EU_TEST_LIQ_HC_VOL', 
							'EU_TEST_WTR_VOL', 
							'EU_TEST_TOTAL_GAS_VOL', 
							'EU_TEST_GAS_HC_VOL', 
							'EU_TEST_GAS_LIFT_VOL', 
							'EU_TEST_GAS_LIFT_ENGY', 
							'GOR', 
							'WATER_CUT', 
							'EU_TEST_TOTAL_LIQ_MASS', 
							'EU_TEST_LIQ_HC_MASS', 
							'EU_WTR_MASS', 
							'EU_TEST_TOTAL_GAS_MASS', 
							'EU_TEST_GAS_HC_MASS', 
							'EU_TEST_SEPARATOR_TEMP', 
							'EU_TEST_SEPARATOR_PRESS', 
							'EU_TEST_PUMP_RATE', 
							'EU_TEST_INJECT_RATE', 
							'EU_TEST_ENGY_QTY', 
							'EU_TEST_POWR_QTY', 
							'EU_TEST_LIQ_1_VOL', 
							'EU_TEST_LIQ_2_VOL', 
							'EU_TEST_LIQ_3_VOL', 
							'EU_TEST_LIQ_1_MASS', 
							'EU_TEST_LIQ_2_MASS', 
							'EU_TEST_LIQ_3_MASS',
							'EU_TEST_GAS_LIFT_MASS'
	];
	
	public function  getFdcValues($attributes){
		$originAttrCase = \Helper::setGetterUpperCase();
		$fdcValues = EuTestDataStdValue::where($attributes)->first();
		\Helper::setGetterCase($originAttrCase);
		return $fdcValues;
	}
	
	public function updateValuesFromSourceEntry($object_id, $occur_date, $sourceEntry, $rat) {
		if($rat != 1){
			$fields = [
				'EU_TEST_TOTAL_LIQ_VOL', 
				'EU_TEST_WTR_VOL', 
				'EU_TEST_TOTAL_GAS_VOL', 
				'EU_TEST_GAS_HC_VOL', 
				'EU_TEST_GAS_LIFT_VOL', 
				'EU_TEST_GAS_LIFT_ENGY', 
				'EU_TEST_CONDENSATE_VOL', 
				'EU_TEST_CONDENSATE_MASS', 
				'EU_TEST_TOTAL_LIQ_MASS', 
				'EU_TEST_LIQ_HC_MASS', 
				'EU_WTR_MASS', 
				'EU_TEST_TOTAL_GAS_MASS', 
				'EU_TEST_GAS_HC_MASS', 
				'EU_TEST_ENGY_QTY', 
				'EU_TEST_POWR_QTY', 
				'EU_TEST_LIQ_1_VOL', 
				'EU_TEST_LIQ_2_VOL', 
				'EU_TEST_LIQ_3_VOL', 
				'EU_TEST_LIQ_1_MASS', 
				'EU_TEST_LIQ_2_MASS', 
				'EU_TEST_LIQ_3_MASS', 
				'EU_TEST_GAS_LIFT_MASS'
			];
			foreach($fields as $field)
				$this->$field	=	$sourceEntry->$field*$rat;
		}

		$quality	= QltyData::getQualityOil($object_id,"ENERGY_UNIT",$occur_date);
		$_r = $quality&&is_numeric($quality->OIL_F)?$rat*$quality->OIL_F:$rat;
		$this->EU_TEST_LIQ_HC_VOL= $sourceEntry->EU_TEST_LIQ_HC_VOL*$_r;
		$_add = $quality&&is_numeric($quality->GAS_R)?$sourceEntry->EU_TEST_LIQ_HC_VOL*$quality->GAS_R:0;
		$this->EU_TEST_GAS_HC_VOL += $_add;
		
		if($quality&&isset($quality->ENGY_RATE)&&is_numeric($quality->ENGY_RATE)){
			$this->EU_TEST_GAS_LIFT_ENGY	= 	$this->EU_TEST_GAS_LIFT_VOL*$quality->ENGY_RATE;
			$this->EU_TEST_ENGY_QTY			= 	$this->EU_TEST_GAS_HC_VOL*$quality->ENGY_RATE;
		}
		
		$this->save();
	}
} 
