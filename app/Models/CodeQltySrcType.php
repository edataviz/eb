<?php

namespace App\Models;
use App\Models\DynamicModel;

class CodeQltySrcType extends DynamicModel
{
	protected $table = 'CODE_QLTY_SRC_TYPE';
	
	/* public function getReferenceTable($code){
		if($code=="PARCEL") return CodeQltySrcType::getTableName();
		return $code;
	} */
	public static function loadObjectsByCode($objectType,$facility_id){
		$codeQltySrcType		= static ::where("CODE","=",$objectType)->first();
    	if ($codeQltySrcType) return $codeQltySrcType->loadObjects($objectType,$facility_id);
    	return [];
	}
	
	
	public function ExtensionQltySrcObject($option=[]){
		$objectType 	= $this->CODE;
    	$facility 		= is_array($option)&&array_key_exists('Facility',  $option)?$option['Facility']:null;
    	$facility_id 	= is_array($facility)&&array_key_exists('id',  $facility)?$facility['id']:null;
    	return $this->loadObjects($objectType,$facility_id);
	}
	
	public function loadObjects($objectType,$facility_id){
		$srcTypeData 	= null;
		$isOracle		= config('database.default')==='oracle';
		$field			= $isOracle?'NAME as "text"':'NAME as text';
		if($objectType=="PARCEL"){
			$storage 		= Storage::getTableName();
			$pdVoyageDetail = PdVoyageDetail::getTableName();
			$pdVoyage 		= PdVoyage::getTableName();
			$field			= $isOracle?"$pdVoyage.NAME as ".'"text"':"$pdVoyage.NAME as text";
			$srcTypeData 	= PdVoyage::join($storage,function ($query) use ($storage,$facility_id,$pdVoyage) {
				$query->on("$pdVoyage.STORAGE_ID",'=',"$storage.ID");
				if($facility_id) $query->where("$storage.FACILITY_ID",'=',$facility_id) ;
			})
			->join($pdVoyageDetail,"$pdVoyage.ID", '=', "$pdVoyageDetail.VOYAGE_ID")
			->select(
					"$pdVoyageDetail.ID",
					"$pdVoyageDetail.ID as CODE",
					"$pdVoyage.NAME as NAME",
					"$pdVoyageDetail.ID as value",
					$field,
					"$pdVoyageDetail.PARCEL_NO as PARCEL_NO"
					)
					->orderBy("$pdVoyage.ID")
					->orderBy("$pdVoyageDetail.PARCEL_NO")
					->get();
		}
		else if($objectType=="RESERVOIR"){
			$mdl			= \Helper::getModelName($objectType);
			$srcTypeData 	= $mdl::get(['ID','CODE','NAME','ID as value',$field]);
		}
		else if($objectType=="FACILITY"){
			$fa = auth()->user()->getScopeFacility();
			if($fa){
				$srcTypeData = Facility::whereIn('ID', $fa)->get(['ID','CODE','NAME','ID as value',$field]);
			}
		}
		else if ($objectType){
			$mdl			= \Helper::getModelName($objectType);
			if($facility_id) 
				$query	= $mdl::where("FACILITY_ID",$facility_id);
			else
				$query	= $mdl::where([]);
			$selects		= ['ID','CODE','NAME','ID as value',$field];
			if (method_exists($mdl,"loadCustomObjectName")) {
				$mdl::loadCustomObjectName($query,$selects);
			}
			$srcTypeData 	= $query->get($selects);
		}
		return $srcTypeData;
	}
}
