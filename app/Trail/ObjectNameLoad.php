<?php

namespace App\Trail;
use App\Models\Facility;

trait ObjectNameLoad {
	
	public function ObjectName($option=null){
		if ($option!=null&&is_array($option)) {
			if(array_key_exists('IntObjectType', $option)){
				$objectType 	= $option['IntObjectType'];
				if ($objectType['id']=="KEYSTORE" /*|| $objectType['id']=="DEFERMENT"*/) {
					if (array_key_exists('ObjectDataSource', $option)) {
						$objectDataSource 	= $option['ObjectDataSource'];
						$mdl 				= $objectDataSource['id'];
						$mdl 				= 'App\Models\\' . $mdl;
					}
					else return null;
				}
				else {
					$mdlName 		= $objectType['name'];
					$mdl 			= \Helper::getModelName ( $mdlName);
				}
			}
			else if ($this->CODE) {
				/* $mdl			= $this->CODE;
				$mdl 			= 'App\Models\\' . $mdl; */
				$mdl			= \Helper::getModelName($this->CODE);
			}
			
			if($this instanceof Facility){
				$facility_id 	= $this->ID;
			}
			elseif( array_key_exists('Facility', $option)) {
					$facility 		= $option['Facility'];
					$facility_id 	= $facility['id'];
			}
			else{
				$facility_id 	= 0;
			}
			
			if ( array_key_exists('ExtensionPhaseType', $option)) {
				$phaseType 		= $option['ExtensionPhaseType'];
				$phaseTypeId 	= $phaseType['id'];
			}
			else if ( array_key_exists('CodeProductType', $option)) {
				$phaseType 		= $option['CodeProductType'];
				$phaseTypeId 	= $phaseType['id'];
			}
			else $phaseTypeId 	= 0;
			
			if ($mdl&&method_exists($mdl, "getEntries")) {
				$collections		= $mdl::getEntries($facility_id,$phaseTypeId);
				if (array_key_exists('ObjectName', $option)
						&&is_array($option['ObjectName'])
						&&array_key_exists('default', $option['ObjectName'])){
					
					if ($collections instanceof \Illuminate\Support\Collection)
						$collections->prepend($option['ObjectName']['default']);
					else 
						array_unshift($collections, $option['ObjectName']['default']);
				}
				return $collections;
			}
		}
		return null;
	}
}
