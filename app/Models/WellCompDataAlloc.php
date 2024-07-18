<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class WellCompDataAlloc extends DynamicModel 
{ 
	protected $table = 'WELL_COMP_DATA_ALLOC'; 
	protected $primaryKey = 'ID';
	protected $fillable  = [
			'ID',
			'NAME',
			'OCCUR_DATE',
			'COMP_ID',
			'EVENT_TYPE',
			'FLOW_PHASE',
			'ACTIVE_HRS',
			'GRS_VOL',
			'NET_VOL',
			'GRS_MASS',
			'GRS_PWR',
			'GRS_ENGY'
	];
} 
