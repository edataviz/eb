<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use Carbon\Carbon;
use App\Exceptions\DataInputException;

class FeatureEuTestModel extends EbBussinessModel
{
	public  static  $idField 		= 'EU_ID';
	public  static  $typeName 		= 'EUTEST';
	public  static  $dateField 		= 'EFFECTIVE_DATE';
	protected $dates				= ['EFFECTIVE_DATE'/* ,'END_TIME','BEGIN_TIME' */];
	protected $disableUpdateAudit 	= false;
	protected $objectModel 			= 'EnergyUnit';
	
	/* protected $objectModel = 'EuTest';
	protected $excludeColumns = ['EU_ID','OCCUR_DATE']; */ 
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$attributes = [];
		if ( !array_key_exists ( 'isAdding', $newData )) {
			 if (!array_key_exists ( 'auto', $newData )
					&& array_key_exists ( 'ID', $newData )
					&&$newData['ID']){
				$attributes['ID'] 	= $newData['ID'];
				return $attributes;
			}
			else if (array_key_exists ('attributes', $newData )) {
				$attributes = $newData['attributes'];
				return $attributes;
			}
		}
		
		if(static::$dateField){
			$attributes[static::$dateField] = array_key_exists ( static::$dateField, $newData )?$newData[static::$dateField]:$occur_date;
			if (!$attributes[static::$dateField]&&array_key_exists ( "BEGIN_TIME", $newData )) {
				if (strlen($newData["BEGIN_TIME"])>10) $attributes["EFFECTIVE_DATE"] = substr($newData["BEGIN_TIME"],0,10);
				else  $attributes["EFFECTIVE_DATE"] = $newData["BEGIN_TIME"];
				$newData[static::$dateField] = $attributes[static::$dateField];
			}
		}
		
		if 	(!array_key_exists ( 'EU_ID', $newData )||$newData['EU_ID']=="") {
			$newData['EU_ID'] = array_key_exists ( 'EnergyUnit', $postData )?$postData['EnergyUnit']:0;
		}
		
		if ($newData['EU_ID']>0) {
			$attributes['EU_ID'] = $newData['EU_ID'];
			return $attributes;
		}
		return null;
	}
	
	public function EnergyUnit($fields=null){
		return $this->belongsTo('App\Models\EnergyUnit', 'EU_ID', 'ID');
	}
	
	public function  getFdcValues($attributes){
		return null;
	}
	
	public function afterSaving($postData) {
		$occur_date = $this->EFFECTIVE_DATE;
		if(!$occur_date) return;
		$object_id	=$this->EU_ID;
		$attributes = [	'EFFECTIVE_DATE'	=>	$occur_date,
						'EU_ID'				=>	$object_id,
		];
		$sourceEntry = $this->getFdcValues($attributes);
		if ($sourceEntry) {
			$start_time = $sourceEntry->BEGIN_TIME;
			$end_time 	= $sourceEntry->END_TIME;
			$start_time = !$start_time	||$start_time 	instanceof Carbon ? $start_time	: Carbon::parse($start_time);
			$end_time 	= !$end_time 	||$end_time 	instanceof Carbon ? $end_time	: Carbon::parse($end_time);
			if ($start_time&&$end_time) {
				$hours = $start_time->diffInSeconds($end_time, false) / Carbon::SECONDS_PER_MINUTE / Carbon::MINUTES_PER_HOUR;
				if($hours<=0) throw new DataInputException ( "Wrong STD duration (less than or equal zero). Please check BEGIN_TIME and END_TIME" );
				$rat = 24/$hours;
			
				if ($this->isAuto) {
					$commonFields = array_intersect($sourceEntry->fillable, $this->fillable);
					$sourceFillable = [];
					foreach ($commonFields as $field){
						if($this->$field!=$sourceEntry->$field) $sourceFillable[$field]	= $sourceEntry->$field;
	// 					$this->$field	= 	$sourceEntry->$field;
					}
					$this->fill($sourceFillable);
					$this->updateValuesFromSourceEntry($object_id, $occur_date, $sourceEntry,$rat);
				}
			}
		}
	}
	
	public function updateValuesFromSourceEntry($object_id, $occur_date, $sourceEntry,$rat) {
	}
	
	public static function getObjects() {
		return EnergyUnit::where("ID",">",0)->orderBy("NAME")->get();
	}
	
	public static function deleteWithConfig($mdlData) {
		if($mdlData&&count($mdlData)>0){
			foreach ($mdlData as $entry){
				static::whereDate('EFFECTIVE_DATE','=' ,$entry['EFFECTIVE_DATE'])
						->where('EU_ID', $entry['EU_ID'])
						->delete();
			}
		}
	}
}
