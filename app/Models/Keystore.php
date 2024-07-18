<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class Keystore extends DynamicModel 
{ 
	protected $table = 'KEYSTORE';
	
	public static function loadBy($sourceData){
		return static::get(["ID", "NAME"] );
	}

	public static function getEntries($facility_id=null,$product_type = 0){
	
		$wheres = [];
		$query = static ::where($wheres)->select('ID','NAME');
		$entries = $query->get();
		return $entries;
	}
} 
