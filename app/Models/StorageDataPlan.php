<?php 
namespace App\Models; 
use App\Trail\PlanModel;

 class StorageDataPlan extends FeatureStorageModel 
{ 
	use PlanModel;
	
	protected $table 			= 'STORAGE_DATA_PLAN'; 
	protected $objectModel 		= 'Storage';
	public  static  $idField 	= 'STORAGE_ID';
	protected $dates 			= ['OCCUR_DATE','LAST_DATA_READ'];
	
	protected $fillable  		= ['OCCUR_DATE',
									'LAST_DATA_READ',
									'STORAGE_ID',
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
									'STORAGE_GRS_ENGY',
									'STORAGE_GRS_PWR',
									'PLAN_TYPE'];
	
 } 
