<?php
namespace App\Trail;
use App\Models\QltyData;

trait QltyDataConstrain
{
    public static function updateWithQuality($record,$occur_date) {
    	$object_id = $record->EU_ID;
    	$object_type_code = parent ::$typeName;
    	$flow_phase = $record->FLOW_PHASE;
    	$qr=QltyData::getQualityRow($object_id,$object_type_code,$occur_date);
    	if($qr&&is_numeric($qr->ENGY_RATE) && ($flow_phase==2 || $flow_phase==21))
    	{
    		$instance = parent::find($record->ID);
    		if ($instance&&is_numeric($qr->EU_DATA_GRS_VOL)) {
    			$instance->EU_DATA_GRS_VOL	= $qr->ENGY_RATE*$instance->EU_DATA_GRS_VOL;
    			$instance->save();
    			return $instance;
    		}
    	}
    	return false;
    }
}
