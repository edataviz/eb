<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContract extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT';
	protected $dates = ['BEGIN_DATE','END_DATE'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'EXTERNAL_CONTRACT_CODE', 
							'EXTERNAL_CONTRACT_NAME', 
							'BEGIN_DATE', 
							'END_DATE', 
							'CONTRACT_TYPE', 
							'CONTRACT_PERIOD', 
							'CONTRACT_TEMPLATE', 
							'CONTRACT_EXPENDITURE'];
	
	
	public static function getByDateRange($sourceData){
		$beginDate 		= $sourceData['date_begin'];
		$endDate 		= $sourceData['date_end'];
		
		$entries = static::whereDate('BEGIN_DATE','>=',$beginDate)
							->whereDate('BEGIN_DATE','<=',$endDate)
							->select('ID', 'NAME')
							->get();
		
		return $entries;
	}
} 
