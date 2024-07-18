<?php

namespace App\Http\Controllers;
use App\Models\EquipmentHistory;


class EquipmentController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Equipment','width'=>220];
	}
	
	public function getObjectIds($dataSet,$postData,$properties){
		$objectIds = $dataSet->map(function ($item, $key) {
			return ["DT_RowId"			=> $item->DT_RowId,
					"EQUIPMENT_ID"			=> $item->EQUIPMENT_ID,
					"X_EQUIPMENT_ID"			=> $item->EQUIPMENT_ID,
    				"OCCUR_DATE"		=> $item->OCCUR_DATE
			];
		});
			return $objectIds;
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$equip_group = $postData['EquipmentGroup'];
    	$equip_type = $postData['CodeEquipmentType'];
    	
    	$equipmentHistory 	= EquipmentHistory::getTableName();
    	
    	$where = ["$equipmentHistory.FACILITY_ID"=>$facility_id, 'FDC_DISPLAY' => 1];
    	if ($equip_group>0) $where["$equipmentHistory.EQUIPMENT_GROUP"] = $equip_group;
    	if ($equip_type>0) $where["$equipmentHistory.EQUIPMENT_TYPE"] = $equip_type;
    	//      	\DB::enableQueryLog();
    	$query 	= $this->buildQuery("EquipmentHistory",$occur_date,$facility_id,$postData);
    	$dataSet = $query->where($where)
    					->leftJoin($dcTable, function($join) use ($equipmentHistory,$dcTable,$occur_date){
				    		$join->on("$equipmentHistory.OBJECT_ID", '=', "$dcTable.EQUIPMENT_ID");
				    		$join->where("$dcTable.OCCUR_DATE",'=',$occur_date);
				    	})
				    	->select(
				    			"$equipmentHistory.OBJECT_ID as DT_RowId",
				    			"$equipmentHistory.NAME as $dcTable",
				    			"$equipmentHistory.FUEL_TYPE",
				    			"$equipmentHistory.GHG_REL_TYPE",
// 				    			"$equipment.NAME as FL_NAME",
				    			"$dcTable.*",
				    			"$equipmentHistory.OBJECT_ID as EQUIPMENT_ID"
// 				    			"$equipment.FUEL_TYPE as EQP_FUEL_CONS_TYPE",
// 				    			"$equipment.GHG_REL_TYPE as EQP_GHG_REL_TYPE"
				    			)
// 		    			->exclude(["$dcTable.EQP_FUEL_CONS_TYPE"])
 		    			->orderBy("$dcTable")
		    			->get();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
}
