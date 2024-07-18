<?php

namespace App\Http\Controllers;
use App\Models\EnergyUnit;
use Carbon\Carbon;

class EuTestController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving = true;
		$this->fdcModel = "EuTestDataFdcValue";
		$this->idColumn = 'ID';
// 		$this->phaseColumn = config("constants.euFlowPhase");
		
		$this->valueModel = "EuTestDataStdValue";
		$this->theorModel = "EuTestDataValue";
		$this->keyColumns = [$this->idColumn,'EU_ID','EFFECTIVE_DATE'];
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}
	
	public function getObjectIds($dataSet,$postData,$properties){
		$objectIds = $dataSet->map(function ($item, $key) {
			$odate = $item->EFFECTIVE_DATE;
			$odate	= $odate instanceof Carbon ?$odate->toDateString():$odate;
			return ["DT_RowId"			=> $item->DT_RowId,
 					"ID"				=> $item->ID,
					"EU_ID"				=> $item->EU_ID,
					"EFFECTIVE_DATE"	=> $odate,
					"attributes"		=> ["EU_ID"				=> $item->EU_ID,
											"EFFECTIVE_DATE"	=> $odate,]
			];
		});
			return $objectIds;
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
	public function initAutoElement($element){
		$autoElement = parent::initAutoElement($element);
		if (array_key_exists("EFFECTIVE_DATE", $autoElement)&&!$autoElement["EFFECTIVE_DATE"]&&
			array_key_exists("BEGIN_TIME", $element)&&$element["BEGIN_TIME"]) {
				if (strlen($element["BEGIN_TIME"])>10) $autoElement["EFFECTIVE_DATE"] = substr($element["BEGIN_TIME"],0,10);
				else  $autoElement["EFFECTIVE_DATE"] = $element["BEGIN_TIME"];
					
		}
		return $autoElement;
	}
	
	public function enableMergeMissData($model,$sourceModel,$editedData,$entryIndex){
		return true;
	}
	
	public function compareEntryKeys($item,$element){
		if ($item&&$element) {
			if (array_key_exists("attributes", $item)&&
				array_key_exists("attributes", $element)){
				return 	
	    			$item["attributes"]["EU_ID"] 			== $element["attributes"]["EU_ID"]&&
					$item["attributes"]["EFFECTIVE_DATE"] 	== $element["attributes"]["EFFECTIVE_DATE"];
			}
			else{
				return
					array_key_exists("EFFECTIVE_DATE", $item)&&
					array_key_exists("EU_ID", $element)&&
					array_key_exists("EFFECTIVE_DATE", $element)&&
	    			$item["EU_ID"] 				== $element["EU_ID"]&&
					$item["EFFECTIVE_DATE"] 	== $element["EFFECTIVE_DATE"]&&
					$this->noNewValue($item);
			}
		}
		return false;
	}
	
	public function noNewValue($item){
		$keysCount = 2;
		if (array_key_exists("DT_RowId", $item)) $keysCount++;
		if (array_key_exists("ID", $item)) $keysCount++;
		return 	count($item) > $keysCount;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$object_id 	= $postData['EnergyUnit'];
    	$date_end 	= $postData['date_end'];
    	$date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
    	$euWheres	= [];
    	if ($object_id>0)
    		$euWheres = ['EU_ID' => $object_id];
    	
    	if ($object_id>0) 
    		$query	= $mdl::where($euWheres);
    	else
    		$query	= $mdl::whereHas('EnergyUnit', function ($query) use($facility_id) {
					    $query->where('FACILITY_ID', '=', $facility_id);
					});
    	
    	$columns	= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns = [];
    	array_push($columns,
    			"ID as DT_RowId",
    			"ID",
				"RECORD_STATUS",
    			config("constants.euIdColumn"));
    	
//     	\DB::enableQueryLog();
    	$dataSet = $query->whereBetween('EFFECTIVE_DATE', [$occur_date,$date_end])
				    	->select($columns) 
  		    			->orderBy('EFFECTIVE_DATE')
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
  	/* protected function preSave(&$editedData, &$affectedIds, $postData) {
    	if ($editedData) {
    		if (array_key_exists ($this->fdcModel, $editedData )) {
    			$this->preSaveModel ( $editedData, $affectedIds, $this->valueModel,$this->fdcModel);
    		}
    		if (array_key_exists ($this->valueModel, $editedData )) {
    			$this->preSaveModel( $editedData, $affectedIds, $this->theorModel,$this->valueModel);
    		}
    	}
    } */
    
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['EU_ID'			=>	$rowData["EU_ID"],
    	];
    }
    
    public function getHistoryData($mdl, $field,$rowData,$where, $limit){
    	$row_id			= $rowData['ID'];
    	if ($row_id<=0) return [];
    	 
    	$occur_date		= $rowData['BEGIN_TIME'];
    	$history 		= $mdl::where($where)
    	->whereDate('BEGIN_TIME', '<', $occur_date)
    	->whereNotNull($field)
    	->orderBy('BEGIN_TIME','desc')
    	->skip(0)->take($limit)
    	->select('BEGIN_TIME as OCCUR_DATE',
    			"$field as VALUE"
    			)
    			->get();
    			return $history;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$row = EnergyUnit::where(['ID'=>$rowData['EU_ID']])
					    	->select('NAME')
					    	->first();
    	$obj_name		= $row?$row->NAME:"";
    	return $obj_name;
    }
    
}
