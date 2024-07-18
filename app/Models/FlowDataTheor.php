<?php

namespace App\Models;
use App\Models\FeatureFlowModel;
use App\Models\CfgFieldProps;

class FlowDataTheor extends FeatureFlowModel
{
	protected $table 		= 'FLOW_DATA_THEOR';
	protected $primaryKey 	= 'ID';
	protected $fillable  	= ['FLOW_ID',
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
								 'RECORD_STATUS'];
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
	
		if(array_key_exists("FLOW_ID",$attributes)
				&&array_key_exists("OCCUR_DATE",$attributes)){
			
			$object_id 	= $attributes["FLOW_ID"];
			$occur_date = $attributes["OCCUR_DATE"];
			$fields 	= CfgFieldProps::getNonCTVConfigFields(FlowDataFdcValue::getTableName());
			$theoFields = CfgFieldProps::getConfigFields( FlowDataTheor::getTableName());
			$fieldArray =array_column($fields->toArray(),'COLUMN_NAME');
			$theoFieldArray =array_column($theoFields->toArray(),'COLUMN_NAME');
			$fdcValues 	= FlowDataFdcValue::where(array('FLOW_ID'	=> $object_id))
											->whereDate('OCCUR_DATE','=',$occur_date)
											->select($fieldArray)
											->first();
			
			if ($fdcValues) {
				foreach ($theoFieldArray as $field){
					if (!array_key_exists($field, $values)) {
						$values[$field]= $fdcValues->$field;
					}
				}
			}
		}
		return $values;
	}
}
