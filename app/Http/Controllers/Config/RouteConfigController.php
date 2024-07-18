<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\CodeController;
use App\Models\EnergyUnit;
use App\Models\Flow;
use App\Models\Tank;
use App\Models\Equipment;
use App\Models\DcPointEu;
use App\Models\DcPointFlow;
use App\Models\DcPointTank;
use App\Models\DcPointEquipment;
use App\Models\IntObjectType;

class RouteConfigController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [
				'POINT_TYPE'		=>	[	'column'	=>'NAME',
											'model'		=>'IntObjectType'],
		];
	
	}
	
	
	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	\Helper::setGetterUpperCase();
    	$dcPointFlow= DcPointFlow::getTableName();
    	 
    	switch ($dcTable){
    		case $dcPointFlow:
		    	$dcPointId 	= array_key_exists('DcPoint', $postData)?$postData['DcPoint']:0;
		    	$energyUnit = EnergyUnit::getTableName();
		    	$flow 		= Flow::getTableName();
		    	$tank 		= Tank::getTableName();
		    	$equipment 	= Equipment::getTableName();
		    	$dcPointEu 	= DcPointEu::getTableName();
		    	$dcPointTank= DcPointTank::getTableName();
		    	$dcPointEquipment= DcPointEquipment::getTableName();
		    	 
		    	$euQuery	= DcPointEu::where("$dcPointEu.POINT_ID","=",$dcPointId)
		//     							->join($energyUnit,"$energyUnit.ID",'=',"$dcPointEu.EU_ID")
		    							->select(["$dcPointEu.EU_ID as NAME","$dcPointEu.ID",\DB::raw("'ENERGY_UNIT' as POINT_TYPE")]);
		    	$flowQuery	= DcPointFlow::where("$dcPointFlow.POINT_ID","=",$dcPointId)
		//     							->join($flow,"$flow.ID",'=',"$dcPointFlow.FLOW_ID")
		    							->select(["$dcPointFlow.FLOW_ID as NAME","$dcPointFlow.ID",\DB::raw("'FLOW' as POINT_TYPE")]);
		    	$tankQuery	= DcPointTank::where("$dcPointTank.POINT_ID","=",$dcPointId)
		//     							->join($tank,"$tank.ID",'=',"$dcPointTank.TANK_ID")
		    							->select(["$dcPointTank.TANK_ID as NAME","$dcPointTank.ID",\DB::raw("'TANK' as POINT_TYPE")]);
		    	$eqQuery	= DcPointEquipment::where("$dcPointEquipment.POINT_ID","=",$dcPointId)
		// 						    	->join($equipment,"$equipment.ID",'=',"$dcPointEquipment.EQUIPMENT_ID")
								    	->select(["$dcPointEquipment.EQUIPMENT_ID as NAME","$dcPointEquipment.ID",\DB::raw("'EQUIPMENT' as POINT_TYPE")]);
		    	
		    	$dataSet	= $euQuery->union($flowQuery)->union($tankQuery)->union($eqQuery)->orderBy("POINT_TYPE")->orderBy("NAME")->get();
		    	$extraDataSet = $this->getExtraDataSet($dataSet);
		    	return ['dataSet' 		=>$dataSet,
		    			'extraDataSet'	=>$extraDataSet
		    	];
    		default:
    			$mdl 		= \Helper::getModelName($dcTable);
    			$dataSet 	= $mdl::loadBy($postData);
    			return ['dataSet' 		=>$dataSet];
    	}
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	$data		 = [];
    	\Helper::setGetterUpperCase();
    	switch ($sourceColumn) {
    		case 'POINT_TYPE':
    			$intObjectType = IntObjectType::where("CODE",'=',$sourceColumnValue)->first();
    			if ($intObjectType) {
    				$options = ['Facility'=>0];
	    			$data = $intObjectType->ObjectName($options);
    			}
    			break;
    	}
    	return $data;
    }
    
    public function loadMultiTableData($resultTransaction,$postData,$editedData) {
    	switch ($this->getWorkingTable($postData)){
    		case DcPointFlow::getTableName():
		    	$editedData = ["DcPointFlow" => []];
    			return parent::loadMultiTableData($resultTransaction, $postData, $editedData);
    	}
    	return parent::loadMultiTableData($resultTransaction, $postData, $editedData);
    }
}
