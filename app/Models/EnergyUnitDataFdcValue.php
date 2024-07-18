<?php

namespace App\Models;
use App\Models\FeatureEuModel;

class EnergyUnitDataFdcValue extends FeatureEuModel
{
	protected $table = 'ENERGY_UNIT_DATA_FDC_VALUE';
	
	protected $fillable  = ['OCCUR_DATE', 
							'EU_ID', 
							'EU_STATUS', 
							'EVENT_TYPE', 
							'FLOW_PHASE', 
							'COMP_LAB_ID', 
							'ACTIVE_HRS', 
							'DAYS_LAST_READ', 
							'OBS_API', 
							'OBS_TEMP', 
							'OBS_PRESS', 
							'PRESS_UOM', 
							'TEMP_UOM', 
							'EU_DATA_GRS_VOL', 
							'EU_DATA_GRS_VOL1', 
							'EU_DATA_GRS_VOL2', 
							'EU_DATA_NET_VOL', 
							'CHOKE_SETTING', 
							'GAS_LIFT_VOL', 
							'GAS_LIFT_PRESSURE', 
							'GAS_LIFT_TEMP', 
							'EU_DATA_GRS_MASS', 
							'DENS', 
							'DENS_UOM', 
							'EU_DATA_GRS_ENGY', 
							'EU_DATA_GRS_PWR', 
							'EU_DATA_AVG_WHT', 
							'EU_DATA_AVG_WHP', 
							'EU_DATA_AVG_BHT', 
							'EU_DATA_AVG_BHP', 
							'EU_DATA_AVG_FTP', 
							'EU_DATA_AVG_SITP', 
							'EU_DATA_AVG_THP', 
							'EU_DATA_AVG_PCP', 
							'EU_DATA_AVG_SCP', 
							'GAS_LIFT_DS_TEMP', 
							'GAS_LIFT_US_TEMP', 
							'METER_RUN_TEMP', 
							'METER_RUN_TEMP1', 
							'METER_RUN_TEMP2', 
							'GOR', 
							'SW', 
							'EU_VOL_UOM', 
							'EU_MASS_UOM', 
							'EU_ENGY_UOM', 
							'EU_POWR_UOM', 
							'CTV', 
							'PUMP_RATE', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS',
							'EU_DATA_NET_MASS',
							/* 'ANNULUS_PRESS',
							'SALINITY',
							'PH_WATER', */
	];
	
}
