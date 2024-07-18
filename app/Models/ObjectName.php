<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Models\EnergyUnit; 

 class ObjectName extends DynamicModel { 
 	protected $primaryKey = 'ID2';
 	
	public static function loadBy($sourceData){
		if ($sourceData!=null&&is_array($sourceData)) {
			$facility 			= $sourceData['Facility'];
			$facility_id 		= $facility?$facility->ID:null;
			if (array_key_exists('CodeProductType', $sourceData)) {
				$phaseType 		= $sourceData['CodeProductType'];
				$phaseTypeId 	= $phaseType?$phaseType->ID:0;
			}
			else if (array_key_exists('ExtensionPhaseType', $sourceData)) {
				$phaseType 		= $sourceData['ExtensionPhaseType'];
				$phaseTypeId 	= $phaseType?$phaseType->ID:0;
			}
			else 
				$phaseTypeId 	= 0;
			
			$objectType 		= $sourceData['IntObjectType'];
			if ($objectType) {
				$mdlName 		= $objectType->CODE;
				$mdl 			= \Helper::getModelName ( $mdlName);
				$collections	= $mdl::getEntries($facility_id,$phaseTypeId);
				return $collections;
			}
		}
		return null;
	}
	
	public static function find($id){
		return EnergyUnit::find($id);
	}
} 
