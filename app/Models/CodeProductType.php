<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Trail\ObjectNameLoad;

 class CodeProductType extends DynamicModel {
	use ObjectNameLoad;
	protected $table = 'CODE_PRODUCT_TYPE'; 
	
	
	public static function find($id){
		if ($id==0) {
			$instance = new CodeProductType;
			return $instance;
		}
		else  return static ::where('ID',$id)->first();
	}
	
	public function Tank($option=null){
		if ($option!=null&&is_array($option)) {
			if ( array_key_exists('Facility', $option)) {
				$facility 		= $option['Facility'];
				$facility_id 	= $facility['id'];
			}
			else $facility_id 	= 0;
				
			if ( array_key_exists('ExtensionPhaseType', $option)) {
				$phaseType 		= $option['ExtensionPhaseType'];
				$phaseTypeId 	= $phaseType['id'];
			}
			else if ( array_key_exists('CodeProductType', $option)) {
				$phaseType 		= $option['CodeProductType'];
				$phaseTypeId 	= $phaseType['id'];
			}
			else $phaseTypeId 	= 0;
			return Tank::getEntries($facility_id,$phaseTypeId);
		}
		return null;
	}
} 
