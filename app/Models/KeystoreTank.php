<?php 
namespace App\Models; 
 

 class KeystoreTank extends EbBussinessModel { 
	protected $table = 'KEYSTORE_TANK'; 
	
	public static function getEntries($facility_id=null,$product_type = 0){
		return  static::select('ID','NAME')->get();
	}
} 
