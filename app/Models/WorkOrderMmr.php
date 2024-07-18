<?php 
namespace App\Models; 
 
 class WorkOrderMmr extends EbBussinessModel 
{ 
	protected $table = 'WORK_ORDER_MMR'; 
	protected $dates = ['SCHEDULE_DATE','START_DATE','COMPLETE_DATE'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'SCHEDULE_DATE', 
							'START_DATE', 
							'COMPLETE_DATE', 
							'WO_ACTION', 
							'RIG_NO', 
							'UNIT_COST', 
							'UNIT_NO', 
							'UOM', 
							'COST', 
							'COMMENT', 
							'MMR_ID'];
} 
