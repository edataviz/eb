<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class EnergyUnitCoEntDataAlloc extends DynamicModel 
{ 
	protected $table = 'ENERGY_UNIT_CO_ENT_DATA_ALLOC'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'OCCUR_DATE',
		'EU_ID',
		'EVENT_TYPE',
		'ALLOC_TYPE',
		'FLOW_PHASE',
		'ACTIVE_HRS',
		'COST_INT_CTR_ID',
		'BA_ID',
		'EU_DATA_GRS_VOL',
		'EU_DATA_NET_VOL',
		'EU_DATA_GRS_MASS',
		'EU_DATA_GRS_ENGY',
		'EU_DATA_GRS_PWR'
	];
} 
