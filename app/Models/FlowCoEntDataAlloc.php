<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class FlowCoEntDataAlloc extends DynamicModel 
{ 
	protected $table = 'FLOW_CO_ENT_DATA_ALLOC'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
		'ID',
		'FLOW_ID',
		'OCCUR_DATE',
		'ACTIVE_HRS',
		'RECORD_FREQUENCY',
		'DISP',
		'COST_INT_CTR_ID',
		'BA_ID',
		'FL_DATA_GRS_VOL',
		'FL_DATA_NET_VOL',
		'FL_DATA_SW_PCT',
		'FL_DATA_GRS_WTR_VOL',
		'FL_DATA_GRS_MASS',
		'FL_DATA_NET_MASS',
		'FL_DATA_GRS_WTR_MASS',
		'FL_DATA_GRS_ENGY',
		'FL_DATA_GRS_PWR',
		'FL_DATA_DENS'			
	];
} 
