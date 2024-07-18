<?php 
namespace App\Models; 

class CodeDeferGroupType extends DynamicModel {
 	
	protected $table = 'CODE_DEFER_GROUP_TYPE';
	
	public function ManyDefermentGroup($fields=null){
		return $this->hasMany('App\Models\DefermentGroup', 'DEFER_GROUP_TYPE', 'ID');
	}
	
	
	public function DefermentGroup($option=null){
		if ($this->CODE == 'WELL') {
			return EnergyUnit::where(['FDC_DISPLAY'=>1])
								->select("ID","NAME")
								->orderBy('NAME')
								->get();
		}
		else{
			return DefermentGroup::where(['DEFER_GROUP_TYPE'=>$this->ID])
			    				->select("ID","NAME")
			    				->orderBy('NAME')
			    				->get();
		}
	}
	
	public static function loadActive($facility_id = null){
		return static :: where(function ($query) use ($facility_id) {
				$query->where('FACILITY_ID','=',$facility_id)
				  ->orWhereNull('FACILITY_ID');
			})->where('ACTIVE','=',1)->orderBy('ORDER')->orderBy('NAME')->get();
	}
} 
