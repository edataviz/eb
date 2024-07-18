<?php

namespace App\Http\Controllers;


class AllocateForecastController extends AllocatePlanController{
	
	public function getModelName($mdlName,$postData) {
		$source_type 	= 	$postData['IntObjectTypeName'];
		$table			=	$source_type."_DATA_FORECAST";
		$tableName		= 	strtolower ( $table );
		$mdlName 		= 	\Helper::camelize ( $tableName, '_' );
		return $mdlName;
	}
	
	public function getQueryCondition($where,$postData){
		$forecastType	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
		if ($forecastType>0) $where["FORECAST_TYPE" ] 	= $forecastType;
		return $where;
	}
	
}
