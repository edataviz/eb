<?php

namespace App\Models;

class FlowHourly extends Flow
{
	public static function loadBy($sourceData){
		if ($sourceData!=null&&is_array($sourceData)) {
			$facility 			= $sourceData['Facility'];
			if ($facility) {
				
				$codeReadingFrequency=CodeReadingFrequency::getTableName();
				$flow=Flow::getTableName();
				$result=Flow::join($codeReadingFrequency,"$codeReadingFrequency.ID",'=',"$flow.RECORD_FREQUENCY")->where(["$codeReadingFrequency.CODE"=>"HR","$flow.FACILITY_ID"=>$facility->ID])->orderBy("$flow.NAME")->get(["$flow.NAME","$flow.ID"]);
				$all = collect([
					(object)["ID" => 0	,"NAME" => '(All)'],
				]);
				return $all->merge($result);
			}
		}
		return null;
	}
}
