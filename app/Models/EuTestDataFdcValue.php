<?php 
namespace App\Models; 
use App\Models\FeatureEuTestModel; 

 class EuTestDataFdcValue extends FeatureEuTestModel 
{ 
	protected $table = 'EU_TEST_DATA_FDC_VALUE'; 
	protected $primaryKey = 'ID';
// 	protected $dates = ['EFFECTIVE_DATE','BEGIN_TIME','END_TIME'];
	
	public $fillable  = ['EU_ID',
						'BEGIN_TIME',
						'END_TIME',
						'TEST_HRS',
						'EFFECTIVE_DATE',
						'TEST_METHOD',
						'TEST_DEVICE_ID',
						'TEST_USAGE',
						'EVENT_TYPE',
						'REFERENCE_ID',
						'OBS_TEMP',
						'OBS_PRESS',
						'OBS_API',
						'CHOKE_SETTING',
						'EU_TEST_TOTAL_LIQ_VOL',
						'EU_TEST_LIQ_HC_VOL',
						'EU_TEST_WTR_VOL',
						'EU_TEST_TOTAL_GAS_VOL',
						'EU_TEST_GAS_HC_VOL',
						'EU_TEST_GAS_LIFT_VOL',
						'EU_TEST_GAS_LIFT_ENGY',
						'EU_TEST_CHP',
						'EU_TEST_WHT',
						'GASLIFT_TEMP',
						'GASLIFT_PRESS',
						'EU_TEST_THP',
						'EU_TEST_ANNU_TEMP',
						'SAND_RATE',
						'SANILITY',
						'COMMENT',
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
						'EU_DATA_AVG_FTP',
						'EU_DATA_AVG_SITP',
						'CTV',
						'TEMP_UOM',
						'PRESS_UOM',
						'EU_TEST_GAS_LIFT_MASS'
	];
	public function afterSaving($postData) {
		
	}
	
} 
