<?php
namespace App\Trail;

trait PlanModel {
	
	public static $modelType 	= "PLAN_TYPE";
	public static $theorModel 	= "CodePlanType";
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$keyFields		= parent::getKeyColumns($newData,$occur_date,$postData);
		$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
		if ($planType>0) {
			$newData["PLAN_TYPE"] 	= $planType;
			$keyFields["PLAN_TYPE"] = $planType;
		}
		return $keyFields;
	}
}
