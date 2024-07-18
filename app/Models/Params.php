<?php 
namespace App\Models; 
 

 class Params extends EbBussinessModel 
{ 
	protected $table = 'params'; 
	protected $primaryKey = 'key';
	
	public static function getLicenseKey(){
// 		$originAttrCase = \Helper::setGetterUpperCase();
		$licenseKey = Params::where(\Helper::correctColumn("key"),"=","license_key")
							->select(\Helper::correctColumn("value"))
							->first();
		return $licenseKey?$licenseKey->value:null;
	}
	
	public static function saveLicenseKey($key){
		$originAttrCase = \Helper::setGetterUpperCase();
		$license 		= Params::where(\Helper::correctColumn("key"),"=","license_key")->first();
		if($license){
			Params::where(\Helper::correctColumn("key"),"=","license_key")->update([\Helper::correctColumn("value") => $key]);
		}
		else{
			Params::insert([\Helper::correctColumn("key")			=> "license_key",
							\Helper::correctColumn("value")			=> $key,
							"NUMBER_VALUE" 	=> 1,
							"X1" 			=> 0,
					]);
		}
	}
} 
