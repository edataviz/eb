<?php

namespace App\Models;

class UserDataScope extends DynamicModel
{
	protected $table = 'USER_DATA_SCOPE';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user(){
		return $this->belongsTo('App\Models\User', 'USER_ID', 'ID');
	}
	
	/* public function LoProductionUnit(){
		return $this->belongsTo('App\Models\LoProductionUnit', 'PU_ID', 'ID');
	} */
}
