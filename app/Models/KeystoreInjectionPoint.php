<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Trail\RelationDynamicModel;

 class KeystoreInjectionPoint extends DynamicModel { 
	
	use RelationDynamicModel;
	
	protected $table = 'keystore_injection_point'; 
	
	public static function loadBy($sourceData){
		return static::get(["ID", "NAME"] );
	}

	public static function getSourceModel($columnName){
		return "CodeInjectPoint";
	}
} 
