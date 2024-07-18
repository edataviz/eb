<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class Personnel extends EbBussinessModel 
{ 
	protected $table = 'PERSONNEL'; 
	
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['NAME', 'BA_ID', 'FACILITY_ID', 'TYPE', 'TITLE', 'OCCUR_DATE', 'START_SHIFT', 'END_SHIFT', 'WORK_HOURS'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		$newData['OCCUR_DATE'] = $occur_date;
		if (!array_key_exists('FACILITY_ID', $newData)||!$newData['FACILITY_ID']) {
			$newData['FACILITY_ID'] = $postData['Facility'];
		}
		return [
				'ID' => $newData['ID'],
		];
	}
} 
