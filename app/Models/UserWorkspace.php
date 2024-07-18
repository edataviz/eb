<?php

namespace App\Models;
use App\Models\DynamicModel;

class UserWorkspace extends DynamicModel
{
	protected $table = 'USER_WORKSPACE';
	protected $primaryKey = 'ID';
	protected $dates = ['W_DATE_BEGIN','W_DATE_END'];
	protected $fillable  = ['USER_ID', 
							'USER_NAME', 
							'W_DATE_BEGIN', 
							'W_FACILITY_ID', 
							'W_DATE_END', 
							'W_FLOW_PHASE',
							'DATE_FORMAT',
							'TIME_FORMAT',
							'DECIMAL_MARK'
	];
	protected $autoFillableColumns = false;
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function areas()
	{
		return $this->hasMany('App\Models\LoArea', 'PRODUCTION_UNIT_ID', 'ID');
	}
}
