<?php
namespace App\Models;

class ObjectTypePropertyExportData extends ObjectTypeProperty
{
        /*$tmp  			= GraphCfgFieldProps::where('TABLE_NAME','=',$tableName)
            ->where(function ($query) {
                $query->where('USE_FDC','=',1)
                    ->orWhere('USE_DIAGRAM','=',1)
                    ->orWhere('USE_GRAPH','=',1);
            })
            ->whereNotIn("COLUMN_NAME",$dates)
            ->get(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'LABEL AS NAME']);*/

    public static function buildQuery($model) {
        $sql = GraphCfgFieldProps::whereNull("CONFIG_ID")->where(function ($query) {
            $query->where('USE_FDC','=',1)
                ->orWhere('USE_DIAGRAM','=',1)
                ->orWhere('USE_GRAPH','=',1);
        });
        //if ($model == "App\Models\KeystoreInjectionPointDay") $sql->where([["INPUT_TYPE",2],["DATA_METHOD",1]]);
        return $sql;
    }
}