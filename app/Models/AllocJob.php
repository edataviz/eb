<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class AllocJob extends DynamicModel 
{ 
	protected $table = 'alloc_job'; 
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $dates 	= ['BEGIN_DATE','END_DATE'];
	protected $fillable  = [
			'ID',
			'CODE',
			'NAME',
			'NETWORK_ID',
			'VALUE_TYPE',
			'LAST_RUN',
			'ALLOC_OIL',
			'ALLOC_GAS',
			'ALLOC_WATER',
			'ALLOC_COMP',
			'ALLOC_GASLIFT',
			'ALLOC_CONDENSATE',
			'DAY_BY_DAY'
	];
} 
