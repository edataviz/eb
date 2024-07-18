<?php

namespace App\Models;
use App\Models\EuPhaseConfig;

class EnergyUnit extends EbBussinessModel
{

	protected $table = 'ENERGY_UNIT';
	public  static  $idField = 'ID';
	
	protected static $fieldACTIVE = null;
	protected static $fieldORDER = null;
	
	public function EuPhaseConfig()
	{
		return $this->hasMany('App\Models\EuPhaseConfig', 'EU_ID', $this->primaryKey);
	}
	
	public function CodeStatus()
	{
		return $this->belongsTo('App\Models\CodeStatus', 'STATUS', $this->primaryKey);
	}
	
	public function Facility(){
		return $this->belongsTo('App\Models\Facility', 'FACILITY_ID', 'ID');
	}
	
	public function EnergyUnitGroup(){
		return $this->belongsTo('App\Models\EnergyUnitGroup', 'EU_GROUP_ID', 'ID');
	}

	public function getKeyAttributes($mdlName,$column){
		$alloc_type 	= array_key_exists('CodeAllocType', $postData)?$postData['CodeAllocType']:0;
		$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
		$forecastType 	= array_key_exists('CodeForecastType', $postData)?$postData['CodeForecastType']:0;
		
		$attributes		= ["EU_ID"				=>	$this->ID,
		// 				"RECORD_FREQUENCY"		=>	$this->RECORD_FREQUENCY,
		];
		return $attributes;
	}
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0) {
			$wheres = ['FACILITY_ID'=>$facility_id];
		}
		else $wheres = [];
// 		$field		= 'NAME as '.\Helper::getConstantTextOverDbDriver("text");
		$query = static ::where($wheres)->select('ID','NAME');
		
		if ($product_type>0) {
			$eu_phase_config = EuPhaseConfig::getTableName();
			$query->whereHas('EuPhaseConfig' , function ($query) use ($product_type){
				$query->where("FLOW_PHASE",$product_type);
			});
		}
		$entries = $query->orderBy('NAME')->get();
		return $entries;
	}
	
	public function CodeFlowPhase(){
		$EuPhaseConfig 	 = EuPhaseConfig::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		$eu_id 			= $this->ID;
		$result 		=  CodeFlowPhase::whereHas("EuPhaseConfig",
							function ($query) use($EuPhaseConfig,$eu_id) {
								$query->where("$EuPhaseConfig.EU_ID",$eu_id);
							})
							->where("$code_flow_phase.ACTIVE","=",1)
							->orderBy("ORDER")
							->get(["$code_flow_phase.ID", "$code_flow_phase.NAME"] );
		return $result;
	}
}
