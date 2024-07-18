<?php

namespace App\Models;

class DefermentLevel extends DynamicModel
{
    protected $primaryKey = 'COLUMN_NAME';
    public static function getLevel(){
	    $fields = CfgFieldProps::where(["TABLE_NAME"  => "DEFERMENT"])
                ->whereIn("COLUMN_NAME",["DEFER_GROUP_TYPE","DEFER_TARGET","DEFERMENT_GROUP_SUB1","DEFERMENT_GROUP_SUB2"])
                ->select("COLUMN_NAME as CODE","LABEL as NAME")
                ->orderBy("FIELD_ORDER")
                ->get();
		return  $fields;
	}
}
