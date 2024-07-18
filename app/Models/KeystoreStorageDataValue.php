<?php 
namespace App\Models; 

 class KeystoreStorageDataValue extends FeatureKeystore { 
	protected $table = 'KEYSTORE_STORAGE_DATA_VALUE';
	protected static $objectModelName = "KeystoreStorage";
	public static $foreignKeystore = "KEYSTORE_STORAGE_ID";
	public static $idField = "KEYSTORE_STORAGE_ID";
	protected $dates = ['OCCUR_DATE'];
	public static  $relationStatusField = 'KEYSTORE_STORAGE_ID';
	
	protected $fillable  = ['KEYSTORE_STORAGE_ID', 'OCCUR_DATE', 'BEGIN_VOL', 'END_VOL', 'FILLED_VOL', 'INJECTED_VOL', 'CONSUMED_VOL'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (!array_key_exists("OCCUR_DATE",$newData)|| !$newData["OCCUR_DATE"]||$newData["OCCUR_DATE"]==''){
			$newData["OCCUR_DATE"] 		= $occur_date;
		}
		return ["KEYSTORE_STORAGE_ID" 	=> $newData["KEYSTORE_STORAGE_ID"],
				"OCCUR_DATE" 			=> $newData["OCCUR_DATE"],
		];
	}
 
	public function getKeystoreCondition(){
		return ["KEYSTORE_STORAGE_ID"	=> $this->KEYSTORE_STORAGE_ID];
	}
	
	public function getKeystoreStorageId(){
		return null;
	}
} 
