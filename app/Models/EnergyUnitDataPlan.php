<?php

namespace App\Models;
use App\Models\FeatureEuModel;
use App\Trail\PlanModel;

class EnergyUnitDataPlan extends FeatureEuModel
{
	use PlanModel;
	
	protected $table 		= 'ENERGY_UNIT_DATA_PLAN';
	protected $fillable  	= ['OCCUR_DATE', 
								'EU_ID', 
								'EVENT_TYPE', 
								'PLAN_TYPE', 
								'FLOW_PHASE', 
								'ACTIVE_HRS', 
								'EU_DATA_GRS_VOL', 
								'EU_DATA_GRS_MASS', 
								'EU_DATA_GRS_ENGY', 
								'EU_DATA_GRS_PWR', 
								'RECORD_STATUS', 
								'STATUS_BY', 
								'STATUS_DATE',
								'EU_DATA_NET_MASS'
	];

	public static function getValueQuery($params){
		$planType = $params[count($params)-2];
		return parent::getValueQuery($params) . " and PLAN_TYPE=$planType";
	}
}
