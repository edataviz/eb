<?php

namespace App\Http\Controllers;
use App\Models\EnergyUnit;
use App\Models\EnergyUnitGroup;
use Carbon\Carbon;

class EuStatusController extends CodeController {
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

        $mdlName = $postData[config("constants.tabTable")];
        $mdl = "App\Models\\$mdlName";

    	$object_id 	= $postData['EnergyUnit'];
		$group_id 	= $postData['EnergyUnitGroup'];
    	$date_end 	= $postData['date_end'];
    	$date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
        $date_to	= $date_end->modify('+1 day')->modify('-1 seconds');

    	$columns	= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns = [];
    	array_push($columns,
    			"$dcTable.ID as DT_RowId",
    			"$dcTable.ID",
    			"$dcTable.".config("constants.euIdColumn"));
    	
//     	\DB::enableQueryLog();
        $date_time = ($mdlName == "EnergyUnitStatus") ? "$dcTable.EFFECTIVE_DATE" : "$dcTable.BEGIN_TIME";
		$energyUnit = EnergyUnit::getTableName();
		$query = $mdl::join($energyUnit, function($join) use($dcTable, $energyUnit, $facility_id, $group_id, $object_id){
			$join->on("$energyUnit.ID", '=', "$dcTable.EU_ID");
			if($facility_id > 0) $join->where("$energyUnit.FACILITY_ID", '=', $facility_id);
			if($group_id > 0) $join->where("$energyUnit.EU_GROUP_ID", '=', $group_id);
			if($object_id > 0) $join->where("$energyUnit.ID", '=', $object_id);
		});
		$dataSet = $query->whereBetween($date_time, [$occur_date,$date_to])
			->select($columns)
			->orderBy($date_time)
			->get();
 		    			
    	return ['dataSet'=>$dataSet];
    }
}
