<?php

namespace App\Models;


class EnergyUnitDataAlloc extends FeatureEuAllocModel
{
	protected $table 	= 'ENERGY_UNIT_DATA_ALLOC';
	protected $fillable = ['OCCUR_DATE',
							'EU_ID',
							'EVENT_TYPE',
							'ALLOC_TYPE',
							'FLOW_PHASE',
							'ACTIVE_HRS',
							'EU_DATA_GRS_VOL',
							'EU_DATA_NET_VOL',
							'EU_DATA_GRS_MASS',
							'EU_DATA_GRS_ENGY',
							'EU_DATA_GRS_PWR',
							'STATUS_BY',
							'STATUS_DATE',
							'RECORD_STATUS',
							'EU_DATA_NET_MASS'
	];
	
	public static function getValueQuery($params){
		$allocType = $params[count($params)-2];
		return parent::getValueQuery($params) . " and ALLOC_TYPE=$allocType";
	}
	
	public static function getVolMassValues($eu_id,$beginTime,$theorMethodType) {
		$theorEntries			= null;
		if ($eu_id&&$eu_id>0&&$beginTime) {
			$codeFlowPhase		= CodeFlowPhase::getTableName();
			$mainTable			= static::getTableName();
			$entries 			= static::join($codeFlowPhase,"$mainTable.FLOW_PHASE",'=',"$codeFlowPhase.ID")
										->where(["$mainTable.ALLOC_TYPE"	=> 1,
												"$mainTable.EVENT_TYPE"		=> 1,
												"$mainTable.EU_ID"			=> $eu_id])
										->whereDate("$mainTable.OCCUR_DATE"	,"=" ,$beginTime)
										->select("$codeFlowPhase.CODE as CODE",
												"$mainTable.EU_DATA_NET_VOL as GRS_VOL",
												"$mainTable.EU_DATA_NET_MASS as GRS_MASS")
										->get();
			$theorEntries 		= $entries->keyBy(config('database.default')==='oracle'?'code':"CODE");
		}
		return $theorEntries;
	}
}
