<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class StorageDataAlloc extends FeatureStorageModel 
{ 
	protected $table = 'STORAGE_DATA_ALLOC'; 
	protected $dates = ['LAST_DATA_READ'];
	public  static $ignorePostData = true;
	protected $primaryKey = 'ID';
	protected $fillable  = [
			'ID',
			'OCCUR_DATE',
			'LAST_DATA_READ',
			'STORAGE_ID',
			'BEGIN_LEVEL',
			'END_LEVEL',
			'BEGIN_VOL',
			'END_VOL',
			'SW',
			'STORAGE_GRS_VOL',
			'STORAGE_NET_VOL',
			'STORAGE_DENSITY',
			'STORAGE_GRS_MASS',
			'STORAGE_NET_MASS',
			'RECORD_STATUS',
			'STATUS_BY',
			'STATUS_DATE'
	];
 } 
