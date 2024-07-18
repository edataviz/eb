<?php

namespace App\Models;


class FeatureEuAllocModel extends FeatureEuModel{

	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$keyFields		= parent::getKeyColumns($newData,$occur_date,$postData);
		$allocType 		= array_key_exists('CodeAllocType', $postData)?$postData['CodeAllocType']:0;
		if ($allocType>0) {
			$newData["ALLOC_TYPE"] 		= $allocType;
			$keyFields["ALLOC_TYPE"] 	= $allocType;
		}
		return $keyFields;
	}	
	
}
