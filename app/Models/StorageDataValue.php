<?php 
namespace App\Models; 

 class StorageDataValue extends FeatureStorageModel 
{ 
	public  static $ignorePostData = true;
	protected $table = 'STORAGE_DATA_VALUE'; 
	protected $dates = ['LAST_DATA_READ'];
	
 } 
