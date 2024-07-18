<?php

namespace App\Models;

class DcPoint extends EbBussinessModel
{
	protected $table = 'DC_POINT';
	
	public static function loadBy($sourceData){
		if ($sourceData!=null&&is_array($sourceData)) {
			if (array_key_exists("DcRoute", $sourceData)) {
				$dcRoute 		= $sourceData['DcRoute'];
				$dcRouteId 		= $dcRoute instanceof DcRoute?$dcRoute->ID:$dcRoute;
				return static::where("ROUTE_ID",'=',$dcRouteId)->orderBy("NAME")->get(["ID", "NAME"]);
			}
			return []; 
		}
		return static::orderBy("NAME")->get(["ID", "NAME"]);
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_values($mdlData);
		DcPointFlow::whereIn('POINT_ID', $valuesIds)->delete();
		DcPointEu::whereIn('POINT_ID', $valuesIds)->delete();
		DcPointTank::whereIn('POINT_ID', $valuesIds)->delete();
		DcPointEquipment::whereIn('POINT_ID', $valuesIds)->delete();
		parent::deleteWithConfig($mdlData);
	}
}
