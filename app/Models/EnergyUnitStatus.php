<?php 
namespace App\Models; 
 

class EnergyUnitStatus extends EnergyUnitStatusAndEuTestDataScssv{
 	
	protected $table = 'ENERGY_UNIT_STATUS'; 
// 	protected $dates = ['EFFECTIVE_DATE','STATUS_DATE'];
	protected $dates = [];

    public static function deleteWithConfig($mdlData) {
        $valuesIds = array_values($mdlData);
        static::whereIn('ID', $valuesIds)->delete();
    }
} 
