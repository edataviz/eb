<?php

namespace App\Models;
use App\Models\DynamicModel;

class LoArea extends DynamicModel
{
	protected $table = 'LO_AREA';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function ProductionUnit(){
		return $this->belongsTo('App\Models\LoProductionUnit', 'PRODUCTION_UNIT_ID', 'ID');
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function Facility($option=null){
		if ($option) {
			$userDataScope			= UserDataScope::where("USER_ID",auth()->user()->ID)->first();
			if ($userDataScope){
				$DATA_SCOPE_FACILITY	= $userDataScope->FACILITY_ID;
				if($DATA_SCOPE_FACILITY&&$DATA_SCOPE_FACILITY!=""&&$DATA_SCOPE_FACILITY!="0"&&$DATA_SCOPE_FACILITY!=0){
					$facilityIds		= explode(",", str_replace('*','',$DATA_SCOPE_FACILITY));
					return Facility::whereIn('ID',$facilityIds)->where("AREA_ID","=",$this->ID)->orderBy("ORDERS")->orderBy("NAME")->get();
				}
			}
		}
		return $this->hasMany('App\Models\Facility', 'AREA_ID', 'ID')->orderBy("ORDERS")->orderBy("NAME");
	}
	
	public static function getEntries($facility_id=null,$product_type = 0){

		$entries = static ::select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}

    public function CodeDeferGroupType($fields=null){
        $facility = Facility::where("AREA_ID","=",$this->ID)->select('ID')->orderBy("ORDERS")->orderBy("NAME")->get();
        $facility_id = $facility[0]->ID;
        $defer_group = CodeDeferGroupType::where(function ($query) use ($fields, $facility_id) {
            $query->where('FACILITY_ID','=',$facility_id)
                ->orWhereNull('FACILITY_ID');
        })->where('ACTIVE','=',1)->orderBy('ORDER')->orderBy('NAME')->get();
        return $defer_group;
    }
}
