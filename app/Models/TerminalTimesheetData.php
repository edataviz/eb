<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class TerminalTimesheetData extends EbBussinessModel 
{ 
	protected $table = 'TERMINAL_TIMESHEET_DATA';
	protected $dates = ['START_TIME','END_TIME'];
	protected $fillable  = ['CARGO_ID', 
							'TERMINAL_ID', 
							'PORT_ID', 
							'BERTH_ID', 
							'CARRIER_ID', 
							'VOYAGE_ID', 
							'ACTIVITY_ID', 
							'START_TIME', 
							'END_TIME', 
							'COMMENT', 
							'PARENT_ID', 
							'IS_LOAD'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData) {
// 		$newData['IS_LOAD']		= 1;
		return ['PARENT_ID'		=> $newData['PARENT_ID'],
				'ACTIVITY_ID'	=> $newData['ACTIVITY_ID']];
	}
} 
