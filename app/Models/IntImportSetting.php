<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class IntImportSetting extends DynamicModel 
{ 
	protected $table = 'INT_IMPORT_SETTING'; 
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
		'ID',
		'NAME',
		'TAB',
		'TABLE',
		'COLS_MAPPING',
		'COL_TAG',
		'COL_TIME',
		'COL_VALUE',
		'ROW_START',
		'ROW_FINISH',
		'COL_OBJECT_ID',
		'COL_DATE',
		'AUTO_FORMULA'
	];
	
} 
