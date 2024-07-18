<?php

namespace App\Models;

class Equipment extends EbBussinessModel
{
	protected $table = 'EQUIPMENT';
	public  static  $idField = 'ID';
	
	public static function getEntries($facility_id=null){
		$wheres = [];
		if ($facility_id>0) {
			$wheres = ['FACILITY_ID'=>$facility_id];
		}
		$entries = static ::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}

    public static function loadByEquipment($equipmentGroupId = 0,$equipmentTypeId = 0){
        $wheres = [];
        if( $equipmentGroupId > 0 ) $wheres['EQUIPMENT_GROUP'] = $equipmentGroupId;
        if( $equipmentTypeId > 0 ) $wheres['EQUIPMENT_TYPE'] = $equipmentTypeId;
        $entries = static :: where($wheres)->select('ID','NAME')->get();
        return $entries;
    }
}
