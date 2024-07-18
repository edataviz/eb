<?php 
namespace App\Models; 
use App\Models\FeatureTicketModel; 

 class RunTicketFdcValue extends FeatureTicketModel 
{ 
	protected $table = 'RUN_TICKET_FDC_VALUE';
// 	protected $dates = [/* 'OCCUR_DATE', */'REPORT_DATE'];
	protected $fillable  = ['NAME', 
							'TICKET_NO', 
							'LOADING_TIME', 
							'TICKET_TYPE', 
							'BA_ID', 
							'TANK_ID', 
							'OCCUR_DATE', 
							'LAST_DATA_READ', 
							'OBS_API', 
							'OBS_TEMP', 
							'OBS_PRESS', 
							'BEGIN_LEVEL', 
							'END_LEVEL', 
							'BEGIN_VOL', 
							'END_VOL', 
							'SW', 
							'TICKET_GRS_VOL',
							'TICKET_NET_VOL', 
							'TICKET_DENSITY', 
							'TICKET_GRS_MASS', 
							'TICKET_NET_MASS', 
							'TICKET_WTR_VOL', 
							'VOL_UOM', 
							'MASS_UOM', 
							'CTV', 
							'TEMP_UOM', 
							'PRESS_UOM', 
							'CARRIER_ID',
							'REPORT_DATE',
							'PHASE_TYPE',
							'FLOW_ID',
							'TARGET_TANK', 
	];
} 
