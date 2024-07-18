<?php

namespace App\Models;
use App\Models\FeatureFlowModel;
use App\Models\FlowDataFdcValue;

class FlowDataValueSubday extends FeatureFlowModel
{
    protected $table = 'FLOW_DATA_VALUE_SUBDAY';
    protected $primaryKey = 'ID';
    protected $fillable  = ['FLOW_ID',
        'OCCUR_DATE',
        'ACTIVE_HRS',
        'RECORD_FREQUENCY',
        'DISP',
        'FL_DATA_GRS_VOL',
        'FL_DATA_NET_VOL',
        'FL_DATA_SW_PCT',
        'FL_DATA_GRS_WTR_VOL',
        'FL_DATA_GRS_MASS',
        'FL_DATA_NET_MASS',
        'FL_DATA_GRS_WTR_MASS',
        'FL_DATA_GRS_ENGY',
        'FL_DATA_GRS_PWR',
        'FL_DATA_DENS',
        'STATUS_BY',
        'STATUS_DATE',
        'RECORD_STATUS' ];

    public static function getKeyColumns(&$newData,$occur_date,$postData)
    {
        $cls = parent::getKeyColumns($newData,$occur_date,$postData);
        if (array_key_exists(config("constants.flFlowPhase"), $newData)) {
            $cls[config("constants.flowPhase")] = $newData[config("constants.flFlowPhase")];
        }
        return $cls;
    }

    public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){

        if(array_key_exists(config("constants.flowPhase"), $attributes)
            &&array_key_exists(config("constants.flowIdColumn"),$attributes)
            &&array_key_exists("OCCUR_DATE",$attributes))//OIL or GAS
        {
            $fields = ["FL_DATA_GRS_VOL","FL_DATA_NET_VOL",
                config("constants.keyField") 	=>	'FLOW_ID'];
            static::updateValues($attributes,$values,'FLOW',$fields);
        }
        if(array_key_exists(config("constants.flowPhase"), $attributes)) unset($attributes[config("constants.flowPhase")]);
        return $values;
    }

    public static function  getFdcValues($attributes){
        if(array_key_exists(config("constants.flowPhase"), $attributes)) unset($attributes[config("constants.flowPhase")]);
        $fdcValues = FlowDataFdcValue::where($attributes)->first();
        return $fdcValues;
    }

    public function StorageDataValue(){
        return $this->hasMany('App\Models\StorageDataValue',"OCCUR_DATE", "OCCUR_DATE");
    }

}
