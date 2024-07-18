<?php 
namespace App\Models; 

 class StorageDisplayChart extends EbBussinessModel 
{ 
	protected $table 		= 'STORAGE_DISPLAY_CHART';
// 	protected $dates 		= ['FROM_DATE','MID_DATE','TO_DATE','CREATE_DATE'];

	protected $fillable  	= ['TITLE',
							 'CONFIG',
							 'FROM_DATE',
							 'MID_DATE',
							 'TO_DATE',
							 'STORAGE_ID',
							 'CREATE_BY',
							 'CREATE_DATE'];
	
	public function getConfigAttribute($value){
		return json_decode($value,true);
	}
}  
