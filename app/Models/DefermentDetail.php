<?php 
namespace App\Models; 
use Carbon\Carbon;

 class DefermentDetail extends EbBussinessModel 
{ 
	protected $table = 'DEFERMENT_DETAIL'; 
// 	protected $dates = ['END_TIME','BEGIN_TIME'];
	protected $fillable  = ['DEFERMENT_ID',
							 'EU_ID',
							 'THEOR_OIL_PERDAY',
							 'THEOR_OIL_MASS_PERDAY',
							 'THEOR_GAS_PERDAY',
							 'THEOR_WATER_PERDAY',
							 'CALC_DEFER_OIL_VOL',
							 'CALC_DEFER_OIL_MASS',
							 'CALC_DEFER_GAS_VOL',
							 'CALC_DEFER_WATER_VOL',
							 'OVR_DEFER_OIL_VOL',
							 'OVR_DEFER_OIL_MASS',
							 'OVR_DEFER_GAS_VOL',
							 'OVR_DEFER_WATER_VOL',
							 'COMMENT'];
	
	

	public function afterSaving($postData) {
		$shouldSave 	= false;
		$deferment		= Deferment::find($this->DEFERMENT_ID);
		if ($deferment) {
			$codeTheorMethod= $deferment->CodeDeferTheorMethod;
			if($codeTheorMethod){
				$method		= $codeTheorMethod->CODE;
				if ($method=="THEOR_REFERENCE") {
// 					$facilityId						= array_key_exists('Facility',  $postData)?$postData["Facility"]:0;
// 					$flowId							= array_key_exists('FlowId',  $postData)?$postData["FlowId"]:0;
					$beginTime						= $deferment->BEGIN_TIME;
					if (is_string($beginTime))$beginTime = Carbon::parse($beginTime)->startOfDay();
					$this->SMPP_OIL_PERDAY			= $this->calculateSmppPerday($beginTime,$deferment,"THEOR_OIL_PERDAY");
					$this->SMPP_OIL_MASS_PERDAY		= $this->calculateSmppPerday($beginTime,$deferment,"THEOR_OIL_MASS_PERDAY");
					$this->SMPP_GAS_PERDAY			= $this->calculateSmppPerday($beginTime,$deferment,"THEOR_GAS_PERDAY");
					$this->SMPP_WTR_PERDAY			= $this->calculateSmppPerday($beginTime,$deferment,"THEOR_WATER_PERDAY");
						
					$actualAllocated				= $deferment->getTheorEntry($this->EU_ID,"EnergyUnitDataAlloc");
					
					$this->CALC_DEFER_OIL_VOL  		= $this->SMPP_OIL_PERDAY 		- $actualAllocated->EU_TEST_LIQ_HC_VOL;
					$this->CALC_DEFER_OIL_MASS 		= $this->SMPP_OIL_MASS_PERDAY 	- $actualAllocated->EU_TEST_LIQ_HC_MASS;
					$this->CALC_DEFER_GAS_VOL  		= $this->SMPP_GAS_PERDAY 		- $actualAllocated->EU_TEST_GAS_HC_VOL;
					$this->CALC_DEFER_WATER_VOL		= $this->SMPP_WATER_PERDAY 		- $actualAllocated->EU_TEST_WTR_VOL;

					$deferWellRatio					= $this->DEFER_WELL_RATIO;
					$deferWellRatio					= $deferWellRatio?$deferWellRatio:1;
					$this->SHARE_DEFER_OIL_VOL  	= $deferWellRatio*$this->CALC_DEFER_OIL_VOL;
					$this->SHARE_DEFER_OIL_MASS 	= $deferWellRatio*$this->CALC_DEFER_OIL_MASS;
					$this->SHARE_DEFER_GAS_VOL  	= $deferWellRatio*$this->CALC_DEFER_GAS_VOL;
					$this->SHARE_DEFER_WTR_VOL		= $deferWellRatio*$this->CALC_DEFER_WATER_VOL;
					
					$shouldSave 					= true;
				}
			}
		}          
		
		else 
			return null;
		if ($shouldSave) $this->save();
		return $this;
	}
	
	public function calculateSmppPerday($occurDate,$deferment,$field){
		$deferSmppRatio	= $this->calculateDeferSmppRatio($occurDate,$deferment,$field);
		return $deferSmppRatio*$this->$field;
	}
	
	public function calculateDeferSmppRatio($occurDate,$deferment,$field){
		$deferSmppRatio				= $this->DEFER_SMPP_RATIO;
		if ($deferSmppRatio==null) {
			$deferSmppRatio			= 1;
			
			$flowPhase = 0;
			if($field == 'THEOR_OIL_PERDAY' || $field == 'THEOR_OIL_MASS_PERDAY')
				$flowPhase = 1;
			else if($field == 'THEOR_GAS_PERDAY')
				$flowPhase = 2;
			else if($field == 'THEOR_WATER_PERDAY')
				$flowPhase = 3;
				
			$volOrMass = ($field=='THEOR_OIL_MASS_PERDAY'?'MASS':'VOL');
			$facilityId = 0;
			$deferSmpp				= FlowDataForecast::getSMPP($occurDate,$flowPhase,$volOrMass,$facilityId);
			if ($deferSmpp) {
				$sum				= $deferment->$field;
				$deferSmppRatio		= $sum?$deferSmpp/$sum:1;
			}
		}
		return $deferSmppRatio;
	}
	
} 
