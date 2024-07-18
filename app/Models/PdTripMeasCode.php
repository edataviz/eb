<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdTripMeasCode extends DynamicModel 
{ 
	protected $table = 'PD_TRIP_MEAS_CODE'; 

	public static function loadObjects($measureType, $carrierId){
		$isOracle = config('database.default')==='oracle';
		$nameField = $isOracle?'NAME as "text"':'NAME as text';
		$rs = null;
		if($measureType == 1 && $carrierId > 0){
			//Vessel Tank
			$sql = "SELECT ID, NAME, ID as value, $nameField FROM PD_COMPARTMENT where CARRIER_ID=$carrierId";
			$rs = \DB::select($sql);
		}
		else if($measureType == 2 || $measureType == 4){
			$sql = "SELECT ID, NAME, ID as value, $nameField FROM PD_SHORE_TANK";
			$rs = \DB::select($sql);
		}
		else if($measureType == 3){
			$sql = "SELECT ID, NAME, ID as value, $nameField FROM PD_SHIPPING_METER";
			$rs = \DB::select($sql);
		}
		return $rs;
	}
} 
