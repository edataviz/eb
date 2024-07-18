<?php

namespace App\Models;
use App\Models\FeatureEuModel;
use App\Trail\ForecastModel;

class EnergyUnitDataForecast extends FeatureEuModel{
	
	use ForecastModel;
	protected $table = 'ENERGY_UNIT_DATA_FORECAST';
	protected $fillable  = ['OCCUR_DATE', 
							'EU_ID', 
							'EVENT_TYPE', 
							'ALLOC_TYPE', 
							'FLOW_PHASE', 
							'ACTIVE_HRS', 
							'EU_DATA_GRS_VOL', 
							'EU_DATA_GRS_MASS', 
							'EU_DATA_GRS_ENGY', 
							'EU_DATA_GRS_PWR', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS',
							'EU_DATA_NET_MASS',
							'FORECAST_TYPE', 
	];
}
