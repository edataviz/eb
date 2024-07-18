<?php
namespace App\Models;

class EuTestDataScssv extends EnergyUnitStatusAndEuTestDataScssv
{
    protected $table                = 'EU_TEST_DATA_SCSSV';
    public  static  $dateField  	= 'BEGIN_TIME';

    public static function deleteWithConfig($mdlData) {
        $valuesIds = array_values($mdlData);
        static::whereIn('ID', $valuesIds)->delete();
    }
}