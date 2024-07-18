<?php
namespace App\Http\Controllers;

use App\Models\Logistic;

class LogisticController extends CodeController
{
    public function getFirstProperty($dcTable){
        return  ['data'=>'ID','title'=>'','width'=>50];
    }

    public function getDataSet($postData,$safetyTable,$facility_id,$occur_date,$properties){

        $mdlName = $postData[config("constants.tabTable")];
        $mdl = "App\Models\\$mdlName";

        $logistic = Logistic::getTableName();
        $where = ["$logistic.FACILITY_ID"=>$facility_id];

        $begin_date = $occur_date->copy()->addHours(1)->format('Y-m-d H:i:s');
        $end_date = $occur_date->copy()->addHours(24)->format('Y-m-d H:i:s');

        $dataSet = $mdl::where($where)
            ->where('ARRIVE_DATE', '>=', $begin_date)
            ->where('ARRIVE_DATE', '<=', $end_date)
            ->select("ID", "ARRIVE_DATE", "ARRIVE_FROM", "DEPART_DATE", "DEPART_TO","MASTER_NAME","VESSEL_NAME", "POB_NUMBER", "COMMENTS","ID as DT_RowId")
            ->orderBy('ID', 'ASC')
            ->get();
        return ['dataSet'=>$dataSet];
    }
}