<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Models\IntObjectType;
use App\Trail\RelationDynamicModel;

class FlowDataTrend extends EbBussinessModel
{
	protected $table = 'FLOW_DATA_TREND';
	protected $primaryKey = 'ID';
}
