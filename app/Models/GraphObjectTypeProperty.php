<?php
namespace App\Models;

class GraphObjectTypeProperty extends ObjectTypeProperty{

    public static function buildQuery($model) {
        $dates 			= $model::getDateFields();
        return GraphCfgFieldProps::where("USE_GRAPH",1)->where("INPUT_TYPE",2)->whereNull("CONFIG_ID")
            ->whereNotIn("COLUMN_NAME",$dates);
    }
}