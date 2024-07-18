<?php

namespace App\Models;
use App\Models\FeatureFlowModel;
use App\Trail\PlanModel;

class FlowDataPlanSubday extends FeatureFlowModel {

    use PlanModel;

    protected $table 		= 'FLOW_DATA_PLAN_SUBDAY';
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
        'PLAN_TYPE'];

}
