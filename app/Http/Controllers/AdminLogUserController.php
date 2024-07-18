<?php

namespace App\Http\Controllers;

class AdminLogUserController extends CodeController
{
    public function getFirstProperty($dcTable){
        return  null;
    }

    public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties){
        $mdlName                = $postData[config("constants.tabTable")];
        $mdl                    = "App\Models\\$mdlName";
        if($mdlName == "LogUser") {
        	$date_end 	= \Helper::parseDate($postData['date_end']);
            $username   = $postData['User'];
            $where = ($username!='0') ? ['USERNAME' => $username] : [];

            $select = ["ID", "USERNAME","LOGIN_TIME","LOGOUT_TIME","IP"];
            $dataSet = $mdl::where($where)
                ->whereDate('LOGIN_TIME', '>=', $occur_date)
                ->whereDate('LOGIN_TIME', '<=', $date_end)
                ->select($select)
                ->orderBy('LOGIN_TIME','DESC')
                ->get();
        }
        return ['dataSet' => $dataSet];
    }
}