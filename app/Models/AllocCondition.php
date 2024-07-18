<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class AllocCondition extends DynamicModel 
{ 
	protected $table = 'ALLOC_CONDITION'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'NAME',
			'EXPRESSION',
			'RUNNER_FROM_ID',
			'RUNNER_TO_ID'
	];
} 
