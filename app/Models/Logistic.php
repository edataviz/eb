<?php

namespace App\Models;
use App\Models\DynamicModel;

class Logistic extends EbBussinessModel
{
    protected $table = 'LOGISTIC';
    protected $fillable  = ['FACILITY_ID', 'CREATED_DATE', 'VESSEL_NAME', 'MASTER_NAME', 'ARRIVE_DATE', 'ARRIVE_FROM', 'DEPART_DATE', 'DEPART_TO', 'POB_NUMBER', 'COMMENTS'];

    public static function getKeyColumns(&$newData,$occur_date,$postData)
    {
        $newData['FACILITY_ID']  = $postData['Facility'];
        $newData['CREATED_DATE'] = $newData['ARRIVE_DATE'];
//        $newData['CREATED_DATE'] = $occur_date;

        return [
            'FACILITY_ID'  => $postData['Facility'],
            'CREATED_DATE' => $newData['ARRIVE_DATE'],
//            'CREATED_DATE' => $occur_date,
        ];
    }
}