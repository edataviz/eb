<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class StrappingTableData extends DynamicModel 
{ 
	protected $table = 'STRAPPING_TABLE_DATA'; 
	
	public function Tank()
	{
		return $this->belongsTo('App\Models\Tank', 'STRAPPING_TABLE_ID', 'STRAPPING_TABLE_ID');
	}
} 
