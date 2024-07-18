<?php 
namespace App\Models; 
 
 class WorkOrder extends EbBussinessModel 
{ 
	protected $table = 'WORK_ORDER'; 
	protected $dates = ['SCHEDULE_DATE','START_DATE','COMPLETE_DATE'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'SCHEDULE_DATE', 
							'START_DATE', 
							'COMPLETE_DATE', 
							'RIG_NO', 
							'UNIT_COST', 
							'UNIT_NO', 
							'UOM', 
							'COST', 
							'COMMENT', 
							'DEFERMENT_ID'];
} 
