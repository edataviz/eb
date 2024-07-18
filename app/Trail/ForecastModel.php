<?php

namespace App\Trail;

trait ForecastModel {
	
	public static $modelType 	= "FORECAST_TYPE";
	public static $theorModel 	= "CodeForecastType";
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$keyFields		= parent::getKeyColumns($newData,$occur_date,$postData);
		$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
		if ($forecastType>0) {
			$newData["FORECAST_TYPE"] 	= $forecastType;
			$keyFields["FORECAST_TYPE"] = $forecastType;
		}
		return $keyFields;
	}
}
