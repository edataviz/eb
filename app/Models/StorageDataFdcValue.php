<?php 
namespace App\Models; 

 class StorageDataFdcValue extends FeatureStorageModel 
{ 
	public  static $ignorePostData = true;
	protected $table = 'storage_data_fdc_value'; 
	
	protected $dates = ['OCCUR_DATE','LAST_DATA_READ','STATUS_DATE'];
	
	public  $fillable  = 	['OCCUR_DATE', 
							'LAST_DATA_READ', 
							'STORAGE_ID', 
							'OBS_API', 
							'OBS_TEMP', 
							'OBS_PRESS', 
							'BEGIN_LEVEL', 
							'END_LEVEL', 
							'BEGIN_VOL', 
							'END_VOL', 
							'BEGIN_LEVEL2', 
							'END_LEVEL2', 
							'BEGIN_VOL2', 
							'END_VOL2', 
							'SW', 
							'STORAGE_GRS_VOL', 
							'STORAGE_NET_VOL', 
							'STORAGE_DENSITY', 
							'STORAGE_GRS_MASS', 
							'STORAGE_NET_MASS', 
							'VOL_UOM', 
							'MASS_UOM', 
							'RECORD_STATUS', 
							'STATUS_BY', 
							'STATUS_DATE'];
	
} 
