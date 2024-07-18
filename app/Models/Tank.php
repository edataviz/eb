<?php

namespace App\Models;
use App\Models\FeatureTankModel;

class Tank extends FeatureTankModel
{
	protected $table = 'TANK';
	public  static  $idField = 'ID';
	public  static  $dateField = null;

	protected static $fieldACTIVE = null;
	protected static $fieldORDER = null;
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function Facility()
	{
		return $this->belongsTo('App\Models\Facility', 'FACILITY_ID', 'ID');
	}
	
	public static function getEntries($facility_id=null,$product_type = 0){
		if ($facility_id&&$facility_id>0)$wheres = ['FACILITY_ID'=>$facility_id];
		else $wheres = [];
	
		if ($product_type>0) {
			$wheres['PRODUCT'] = $product_type;
		}
		$wheres['FDC_DISPLAY'] = 1;
		$entries = static ::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
		return $entries;
	}
	
	public static function loadBy($sourceData){
		if ($sourceData!=null&&is_array($sourceData)) {
			$facility 			= $sourceData['Facility'];
			if ($facility) {
				$facility_id 		= $facility->ID;
				if (array_key_exists('CodeProductType', $sourceData)) {
					$phaseType 		= $sourceData['CodeProductType'];
					$phaseTypeId 	= $phaseType->ID;
				}
				else if (array_key_exists('ExtensionPhaseType', $sourceData)) {
					$phaseType 		= $sourceData['ExtensionPhaseType'];
					$phaseTypeId 	= $phaseType->ID;
				}
				else $phaseTypeId 	= 0;
				return static ::getEntries($facility_id,$phaseTypeId);
			}
		}
		return null;
	}
	
	public static function loadCustomObjectName(&$query,&$selects){
		$selects[] = "PRODUCT as FLOW_PHASE";
	}
	
}
