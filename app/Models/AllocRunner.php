<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class AllocRunner extends DynamicModel 
{ 
	protected $table = 'ALLOC_RUNNER';
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $dates 	= ['BEGIN_DATE','END_DATE'];
	protected $fillable  = [
			'ID',
			'CODE',
			'NAME',
			'JOB_ID',
			'ORDER',
			'FIFO',
			'ALLOC_TYPE',
			'THEOR_PHASE',
			'THEOR_VALUE_TYPE',
			'LAST_RUN',
			'BA_ID'
	];
} 
