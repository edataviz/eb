<?php

namespace App\Models;

class EnergyUnitCompDataAlloc extends FeatureEuAllocModel
{
	protected $table = 'ENERGY_UNIT_COMP_DATA_ALLOC'; 
	protected $fillable  = [
							'OCCUR_DATE',
							'EU_ID',
							'EVENT_TYPE',
							'ALLOC_TYPE',
							'FLOW_PHASE',
							'COMPOSITION',
							'ACTIVE_HRS',
							'EU_DATA_GRS_VOL',
							'EU_DATA_NET_VOL',
							'EU_DATA_GRS_MASS',
							'EU_DATA_GRS_ENGY',
							'EU_DATA_GRS_PWR'
	];	
}
