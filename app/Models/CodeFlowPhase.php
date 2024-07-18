<?php

namespace App\Models;
use App\Models\DynamicModel;

class CodeFlowPhase extends DynamicModel{
	
	protected $table = 'CODE_FLOW_PHASE';
	
	public function EuPhaseConfig(){
		return $this->hasOne('App\Models\EuPhaseConfig','FLOW_PHASE','ID');
	}
	
	public static function loadBy($sourceData){
		if ($sourceData!=null&&is_array($sourceData)) {
// 			$EuPhaseConfig 	 = EuPhaseConfig::getTableName();
// 			$code_flow_phase = CodeFlowPhase::getTableName();
			$objectName 	= $sourceData['ObjectName'];
			if (method_exists($objectName,'CodeFlowPhase')) {
				return $objectName->CodeFlowPhase();
			}
// 			$eu_id 			= $objectName->ID;

			/* return static::whereHas("EuPhaseConfig",
									function ($query) use($EuPhaseConfig,$eu_id) {
										$query->where("$EuPhaseConfig.EU_ID",$eu_id);
						})
						->get(["$code_flow_phase.ID", "$code_flow_phase.NAME"] ); */
		}
		return static::where("ACTIVE","=",1)
						->orderBy("ORDER")
						->get(["ID", "NAME"] );
	}
	
	public static function loadIndependence($sourceData = null){
		return static::where("ACTIVE","=",1)
						->orderBy("ORDER")
						->get(["ID", "NAME"] );
	}
}
