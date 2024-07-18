<?php

namespace App\Models;
use App\Models\UomModel;
use App\Trail\ObjectNameLoad;

class Facility extends UomModel
{
 	use ObjectNameLoad;
	
	protected $table = 'FACILITY';
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function Area(){
		return $this->belongsTo('App\Models\LoArea', 'AREA_ID', 'ID');
	}
	
	public function Tank($fields=null){
		return $this->hasMany('App\Models\Tank', 'FACILITY_ID', 'ID')->where('FDC_DISPLAY', 1);
	}
	
	public function EnergyUnitGroup($fields=null){
		return $this->hasMany('App\Models\EnergyUnitGroup', 'FACILITY_ID', 'ID');
	}
	
	public function EnergyUnit($fields=null){
		return $this->hasMany('App\Models\EnergyUnit', 'FACILITY_ID', 'ID')->orderBy('NAME');
	}

    public function FlowHourly($fields=null){
        $codeReadingFrequency=CodeReadingFrequency::getTableName();
        $flow=Flow::getTableName();
		$facility = $this->ID;
        $result=Flow::join($codeReadingFrequency,"$codeReadingFrequency.ID",'=',"$flow.RECORD_FREQUENCY")->where(["$codeReadingFrequency.CODE"=>"HR"]);
		if($facility > 0)
			$result = $result->where(["$flow.FACILITY_ID"=>$facility]);
		$result = $result->orderBy("$flow.NAME")->get(["$flow.NAME","$flow.ID"]);
		$all = collect([
			(object)["ID" => 0	,"NAME" => '(All)'],
		]);
        return $all->merge($result);
    }

	public function Storage($fields=null){
		return $this->hasMany('App\Models\Storage', 'FACILITY_ID', 'ID');
	}
	
	public function CodeDeferGroupType($fields=null){
		//return $this->hasMany('App\Models\CodeDeferGroupType', 'FACILITY_ID', 'ID')->orderBy('ORDER')->orderBy('NAME');
		return CodeDeferGroupType::where(function ($query) use ($fields) {
				$query->where('FACILITY_ID','=',$this->ID)
				  ->orWhereNull('FACILITY_ID');
			})
			->where('ACTIVE','=',1)->orderBy('ORDER')->orderBy('NAME')->get();
	}
	
	/* public function CodeDeferGroupType($option=null){
		if($this instanceof Facility){
			$facility_id 	= $this->ID;
		}
		elseif($option!=null&&is_array($option)&& array_key_exists('Facility', $option)) {
			$facility 		= $option['Facility'];
			$facility_id 	= $facility['id'];
		}
		else{
			$facility_id 	= 0;
		}
		$collections 		= CodeDeferGroupType::whereHas('ManyDefermentGroup', function ($query) use ($facility_id) {
									$query->where('FACILITY_ID', '=', $facility_id);
								})->get();
		return $collections;
	} */
	
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0)$wheres = ['ID'=>$facility_id];
		else $wheres = [];
	
		if ($product_type>0) {
// 			$wheres['PHASE_ID'] = $product_type;
		}
		$entries = static ::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}
	
	public function PlotViewConfig(){
		$result	= PlotViewConfig::where("CONFIG",'like',"%#$this->ID:%")->get();
		return $result;
	}
}