<?php

namespace App\Models;
use App\Models\DynamicModel;

class EnergyUnitGroup extends DynamicModel
{
	protected $table = 'ENERGY_UNIT_GROUP';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function Facility()
	{
		return $this->belongsTo('App\Models\Facility', 'FACILITY_ID', 'ID');
	}
	
	public function EnergyUnit($fields=null){
		return $this->hasMany('App\Models\EnergyUnit', 'EU_GROUP_ID', 'ID');
	}
}
