<?php

namespace App\Models;
use App\Models\FeatureFlowModel;
use App\Trail\ForecastModel;
use Carbon\Carbon;

class FlowDataForecastSubday extends FeatureFlowModel
{
    use ForecastModel;
    protected $table 		= 'FLOW_DATA_FORECAST_SUBDAY';
    protected $fillable  	= 	['FLOW_ID',
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
        'RECORD_STATUS',
        'FORECAST_TYPE'];

    public static function getSMPP($occurDate,$flowPhase=0,$field='',$facilityId=0){
// 		SELECT getSMPP_Dana(1,'2017-06-01') FROM DUAL;
        $deferSmpp = null;
        if (config('database.default')==='oracle'&&$occurDate&&$occurDate!='') {
            $dateString = $occurDate instanceof Carbon?$occurDate->toDateString():$occurDate;
            $deferSmpp	= \DB::table("DUAL")->select(\DB::raw("getSMPP_Dana($facilityId,'$dateString',$flowPhase,'$field') as smpp"))->first();
            $deferSmpp	= $deferSmpp?$deferSmpp->smpp:null;
        }
        return $deferSmpp;
    }
}
