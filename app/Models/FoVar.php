<?php

namespace App\Models;

use App\Models\DynamicModel;

class FoVar extends DynamicModel
{
    protected $table = 'FO_VAR';
    public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [  
	    'ID',
	    'NAME',
	    'FORMULA_ID',
	    'OBJECT_TYPE',
	    'OBJECT_ID',
	    'OBJECT_NAME',
	    'STATIC_VALUE',
	    'TABLE_NAME',
	    'VALUE_COLUMN',
	    'OBJ_ID_COLUMN',
	    'DATE_COLUMN',
	    'FLOW_PHASE',
	    'EVENT_TYPE',
	    'ALLOC_TYPE',
	    'ORDER',
	    'COMMENT'
    ];
	
    public function Formula($fields=null)
    {
    	if ($fields!=null&&is_array($fields)) {
    		return $this->belongsTo('App\Models\Formula', 'FORMULA_ID', 'ID')->select($fields);
    	}
    	return $this->belongsTo('App\Models\Formula', 'FORMULA_ID', 'ID');
    }
}
