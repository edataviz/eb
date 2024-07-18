<?php

namespace App\Http\Controllers;
use App\Models\EnergyUnit;
use Carbon\Carbon;

class EuScssvController extends CodeController {
    public function getFirstProperty($dcTable){
        return  ['data'=>'ID','title'=>'','width'=>50];
    }

    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

        $mdlName = $postData[config("constants.tabTable")];
        $mdl = "App\Models\\$mdlName";

        $object_id 	= $postData['EnergyUnit'];
        $date_end 	= $postData['date_end'];
        $date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
        $date_to	= $date_end->modify('+1 day')->modify('-1 seconds');

        $euWheres =  ($object_id > 0) ? ['EU_ID' => $object_id] : [];

        $columns	= $this->extractRespondColumns($dcTable,$properties);
        if (!$columns) $columns = [];
        array_push($columns,
            "$dcTable.ID as DT_RowId",
            "$dcTable.ID",
            "$dcTable.".config("constants.euIdColumn"));

        $date_time = ($mdlName == "EnergyUnitStatus") ? "$dcTable.EFFECTIVE_DATE" : "$dcTable.BEGIN_TIME";

        if ($object_id == 0) {
            $energyUnit 		= EnergyUnit::getTableName();
            $query				= $mdl::join($energyUnit,"$energyUnit.ID",'=',"$dcTable.EU_ID")
                ->where("$energyUnit.FACILITY_ID",'=',$facility_id);
        }
        else{
            $query		= $mdl::where($euWheres);
        }
        $dataSet = $query->whereBetween($date_time, [$occur_date,$date_to])
            ->select($columns)
            ->orderBy($date_time)
            ->get();

        return ['dataSet'=>$dataSet];
    }
}