<?php 
namespace App\Models; 

 class FlowCompDataAlloc extends FeatureFlowModel 
{ 
	protected $table = 'FLOW_COMP_DATA_ALLOC'; 
	protected $fillable  = [
		'ID',
		'FLOW_ID',
		'OCCUR_DATE',
		'ACTIVE_HRS',
		'RECORD_FREQUENCY',
		'COMPOSITION',
		'DISP',
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
