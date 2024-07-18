<?php 
namespace App\Models; 

 class ConstraintDiagram extends EbBussinessModel 
{ 
	protected $table = 'CONSTRAINT_DIAGRAM'; 
	
	protected $fillable  = ['NAME', 'YCAPTION', 'CONFIG'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$attributes	= [];
		if ( array_key_exists ( 'ID', $newData )) {
			$attributes	["ID"]	= $newData['ID'];
		}
		if ( array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'Tank', $postData )) {
			$newData['TANK_ID'] = $postData['Tank'];
		}
		return $attributes;
	}
} 
