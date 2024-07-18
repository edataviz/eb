<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PersonnelSumDay extends EbBussinessModel 
{ 
	protected $table = 'PERSONNEL_SUM_DAY';
	public $primaryKey  = 'ID';
	protected $fillable  = ['FACILITY_ID', 'OCCUR_DATE', 'TYPE', 'TITLE', 'NUMBER', 'NOTE'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		$newData['OCCUR_DATE'] = $occur_date;
		if (!array_key_exists('FACILITY_ID', $newData)||!$newData['FACILITY_ID']) {
			$newData['FACILITY_ID'] = $postData['Facility'];
		}
		
		return [
				'FACILITY_ID' => $newData['FACILITY_ID'],
				'OCCUR_DATE' => $occur_date,
				'TYPE' => $newData['TYPE'],
				'TITLE' => $newData['TITLE'],
		];
	}
} 
