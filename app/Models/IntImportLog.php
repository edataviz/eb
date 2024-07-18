<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class IntImportLog extends DynamicModel 
{ 
	protected $table = 'INT_IMPORT_LOG'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'FILE_NAME',
			'FILE_SIZE',
			'BEGIN_TIME',
			'END_TIME',
			'USER_NAME',
			'TAGS_READ',
			'TAGS_LOADED',
			'TAGS_REJECTED',
			'TAGS_OVERRIDE',
			'NOTE'
	];
} 
