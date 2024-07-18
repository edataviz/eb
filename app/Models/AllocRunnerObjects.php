<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class AllocRunnerObjects extends DynamicModel 
{ 
	protected $table = 'alloc_runner_objects'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'RUNNER_ID',
			'OBJECT_TYPE',
			'OBJECT_ID',
			'DIRECTION',
			'FIXED',
			'MINUS'
	];
} 
