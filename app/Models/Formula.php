<?php

namespace App\Models;

use App\Models\DynamicModel;

class Formula extends DynamicModel
{
    protected $table = 'FORMULA';
    public $timestamps = false;
	public $primaryKey  = 'ID';
	protected $dates 		= ['BEGIN_DATE','END_DATE'];
	
	protected $fillable  = [    
						    'NAME',
						    'GROUP_ID',
						    'OBJECT_TYPE',
						    'OBJECT_ID',
						    'OBJECT_NAME',
						    'TABLE_NAME',
						    'VALUE_COLUMN',
						    'OBJ_ID_COLUMN',
						    'DATE_COLUMN',
						    'FLOW_PHASE',
						    'ALLOC_TYPE',
						    'FORMULA',
						    'BEGIN_DATE',
						    'END_DATE',
						    'COMMENT',
						    'ORDER'
					    ];
  
    public function FoVar($fields=null)
    {
    	if ($fields!=null&&is_array($fields)) {
    		return $this->hasMany('App\Models\FoVar', 'FORMULA_ID', 'ID')->select($fields);
    	}
    	return $this->hasMany('App\Models\FoVar', 'FORMULA_ID', 'ID')
    	->select(['FO_VAR.*',\DB::raw("case when (STATIC_VALUE like '%-%-%' or STATIC_VALUE = '@OCCUR_DATE') then 1 else 0 end as IS_DATE")])
    	->orderBy('ORDER');
    }
}
