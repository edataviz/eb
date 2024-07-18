<?php

namespace App\Http\Controllers;
use App\Models\CodeDeferGroupType;
use App\Models\Deferment;
use App\Models\DefermentDetail;
use App\Models\DefermentGroup;
use App\Models\DefermentGroupEu;
use App\Models\EnergyUnit;
use App\Models\WorkOrder;
use App\Models\WorkOrderMmr;
use App\Models\CodeDeferTheorMethod;

use Illuminate\Http\Request;

class DefermentController extends CodeController {
	
	protected $enableAutoCalculateOverrideValue = false;
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	'DEFER_GROUP_TYPE'	=>	[	'column'		=>'DEFER_TARGET',
																	'model'			=>'DefermentGroup'],
										'DEFER_TARGET'		=>	[	'column'		=>'DEFERMENT_GROUP_SUB1',
																	'model'			=>'DefermentGroupSub1',
																	'ForeignColumn'	=> "DEFERMENT_GROUP_ID"
										],
										'DEFERMENT_GROUP_SUB1'=>[	'column'		=>'DEFERMENT_GROUP_SUB2',
																	'model'			=>'DefermentGroupSub2',
																	'ForeignColumn'	=> "DEFERMENT_GROUP_SUB1"
										],
				
										'OBJECT_TYPE'		=>	[	'column'	=>'OBJECT_ID',
																	'model'		=>''],
										'CODE1'				=>	[	'column'	=>'CODE2',
																	'model'		=>'CodeDeferCode2'],
										'CODE2'				=>	[	'column'	=>'CODE3',
																	'model'		=>'CodeDeferCode3'],
										'DEFER_REASON'		=>	[	'column'	=>'DEFER_REASON2',
																	'model'		=>'CodeDeferReason2'],
										'THEOR_METHOD'		=>	[	'column'	=>'THEOR_METHOD_TYPE',
																	'model'		=>'CodeDeferTheorMethod'],
									];
		
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return $mdlName=="Deferment";
	}
	
	public function getFirstProperty($dcTable){
		if ($dcTable=="DEFERMENT" || $dcTable=="MIS_MEASUREMENT") 	
			return ['data'	=>$dcTable,
					'title'	=>'',
					'width'	=>config('constants.systemName')!='Santos'?120:70];
		if ($dcTable=="WORK_ORDER" || $dcTable=="WORK_ORDER_MMR") return  	['data'=>$dcTable,'title'=>'','width'=>50];
		return null;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		\Helper::setGetterUpperCase();
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	$dataSet		= [];
    	$extraDataSet	= [];
    	if ($mdlName=="Deferment") {
	    	$date_end 	= $postData['date_end'];
    		$date_end 	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
	    	$codeDeferGroupType 	= CodeDeferGroupType::getTableName();
	    	$codeDeferGroupTypeId 	= $postData['CodeDeferGroupType'];
	    	
		    $where = ["$dcTable.FACILITY_ID" => $facility_id];
		    if ($codeDeferGroupTypeId>0) $where["$dcTable.DEFER_GROUP_TYPE"]= $codeDeferGroupTypeId ;
		    
		    $columns	= $this->extractRespondColumns($dcTable,$properties);
		    if (!$columns) $columns = [];
		    array_push(	$columns,
    					"$dcTable.ID as $dcTable",
				 		"$codeDeferGroupType.CODE as DEFER_GROUP_CODE",
				 		"$dcTable.ID as DT_RowId",
				 		"$dcTable.ID",
		    			"$dcTable.ID as DEFERMENT_ID"
		    );
		    
	    	$dataSet = $mdl::join($codeDeferGroupType,function ($query) use ($dcTable,$codeDeferGroupType,$facility_id) {
					    		$query->on("$dcTable.DEFER_GROUP_TYPE",'=',"$codeDeferGroupType.ID");
					    		if ($facility_id>0) {
					    			$query->where(function ($query2) use($codeDeferGroupType,$facility_id){
							                $query2->where("$codeDeferGroupType.FACILITY_ID",'=',$facility_id)
							                      ->orWhereNull("$codeDeferGroupType.FACILITY_ID");
							            });
					    			}
				    		})
							 ->where($where)
							 ->whereDate("$dcTable.BEGIN_TIME", '>=', $occur_date)
							 ->whereDate("$dcTable.BEGIN_TIME", '<=', $date_end)
							 ->select($columns)
// 							 ->take(1)
// 					    	->orderBy($dcTable)
 							->get();
		    
	    	$bunde = ['FACILITY_ID' => $facility_id];
	    	$extraDataSet 	= $this->getExtraDataSet($dataSet, $bunde);
    	}
    	else if ($mdlName=="MisMeasurement") {
	    	$object_type 	= $postData['IntObjectType'];
	    	$date_end 	= $postData['date_end'];
    		$date_end 	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
	    	$dataSet 	= null;
	    	$codeDeferGroupType = CodeDeferGroupType::getTableName();
		    
			if($object_type)
				$where 	= ["$dcTable.FACILITY_ID" => $facility_id, 'OBJECT_TYPE' => $object_type];
			else
		    	$where = ["$dcTable.FACILITY_ID" => $facility_id];
			$dataSet = $mdl::where($where)
								    	->whereDate("$dcTable.BEGIN_TIME", '>=', $occur_date)
								    	->whereDate("$dcTable.BEGIN_TIME", '<=', $date_end)
								    	->select(
	 							    			"$dcTable.ID as $dcTable",
								    			"$dcTable.ID as DT_RowId",
								    			"$dcTable.ID as MMR_ID",
								    			"$dcTable.*"
								    			)
	// 					    			->orderBy($dcTable)
	 									->get();
		    
	    	$bunde = ['FACILITY_ID' => $facility_id];
	    	$extraDataSet 	= $this->getExtraDataSet($dataSet, $bunde);
    	}
    	else if ($mdlName=="DefermentDetail"){
    		$id				= $postData["id"];
    		$dataSet 		= $this->getDefermentDetails($id);
    	}
    	else if ($mdlName=="WorkOrder"){
    		$id				= $postData["id"];
    		$dataSet 		= $this->getWorkOrder($id);
    	}
    	else if ($mdlName=="WorkOrderMmr"){
    		$id				= $postData["id"];
    		$dataSet 		= $this->getWorkOrderMmr($id);
    	}
    	 
    	return ['dataSet'		=>$dataSet,
     			'extraDataSet'	=>$extraDataSet
    	];
    }
    
    
    public function putExtraBundle(&$bunde,$sourceColumn,$entry){
    	if ($sourceColumn=='DEFER_GROUP_TYPE') {
    		$bunde['DEFER_GROUP_CODE'] = $entry->DEFER_GROUP_CODE;
    	}
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	$data		 = [];
    	$field		= 'NAME as '.\Helper::getConstantTextOverDbDriver("text");
    	switch ($sourceColumn) {
    		case 'CODE1':
    		case 'CODE2':
    		case 'DEFER_TARGET':
    		case 'DEFER_REASON':
    		case 'DEFERMENT_GROUP_SUB1':
    			$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\\$targetModel";
		    	$parentId		= array_key_exists('ForeignColumn', $this->extraDataSetColumns[$sourceColumn])?
		    						$this->extraDataSetColumns[$sourceColumn]["ForeignColumn"]:
		    						'PARENT_ID';
		    	$data = $targetEloquent::where($parentId,'=',$sourceColumnValue)
		    							->select(
									    			"ID","NAME",
		 							    			"ID as value",
									    			$field
									    			)
	    								->orderBy('ORDER')
									    ->orderBy('NAME')
									    ->get();
    			break;
    			
    		case 'DEFER_GROUP_TYPE':
    			if ($bunde&&array_key_exists('DEFER_GROUP_CODE', $bunde)) {
	    			$defer_group_code = $bunde['DEFER_GROUP_CODE'];
	    			if ($defer_group_code=='WELL') {
	    				$facility_id = $bunde['FACILITY_ID'];
	    				$data = EnergyUnit::where(['FACILITY_ID'=>$facility_id,
	    									'FDC_DISPLAY'=>1
	    							])
	    							->select("ID","NAME","ID as value",$field)
	    							->orderBy('NAME')
	    							->get();
	    			}
	    			else{
	    				$data = DefermentGroup::where(['DEFER_GROUP_TYPE'=>$sourceColumnValue])
							    				->select("ID","NAME","ID as value",$field)
							    				->orderBy('NAME')
							    				->get();
	    			}
    			}
    			break;
    		case 'OBJECT_TYPE':
				if ($sourceColumnValue) {
					$obj_code = \App\Models\IntObjectType::find($sourceColumnValue)->CODE;
					$facility_id = $bunde['FACILITY_ID'];
					$mdl    = \Helper::getModelName ($obj_code);
					$data = $mdl::where(['FACILITY_ID'=>$facility_id
								])
								->select("ID","NAME","ID as value",$field)
    							->orderBy('ORDER')
								->orderBy('NAME')
								->get();
				}
    			break;
	    	case 'THEOR_METHOD':
	    		if ($sourceColumnValue) {
	    			$columns = ["ID","NAME","ID as value",$field];
	    			$data 		=  CodeDeferTheorMethod::loadTheorBy($sourceColumnValue,$columns);
	    		}
	    		break;
    	}
    	return $data;
    }
    
    
    public function loadsrc(Request $request){
    	//     	sleep(2);
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$bunde = [];
    	$dataSet = [];
    	if ($sourceColumn=='DEFER_GROUP_TYPE') {
    		$codeColumn = 'DEFER_GROUP_CODE';
    		$entry = CodeDeferGroupType::select("CODE as $codeColumn")->find($sourceColumnValue);
	    	$bunde[$codeColumn] = $entry->$codeColumn;
	    	
	    	$facility_id = $postData['Facility'];
	    	$bunde['FACILITY_ID'] = $facility_id;
    	}
    	else if ($sourceColumn=='OBJECT_TYPE') {
	    	$facility_id = $postData['Facility'];
	    	$bunde['FACILITY_ID'] = $facility_id;
    	}
    	$targetExists = true;
    	$loopIndex = 0;
    	while($loopIndex<5){
    		$loopIndex++;
    		if (!array_key_exists($sourceColumn, $this->extraDataSetColumns)) break;
	    	$extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
	    	$targetColumn = $extraDataSetColumn['column'];
	    	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
	    	$dataSet[$targetColumn] = [	'data'			=>	$data,
								    	'ofId'			=>	$sourceColumnValue,
								    	'sourceColumn'	=>	$sourceColumn
								    	];
	    	
	    	$sourceColumn = $targetColumn;
	    	$sourceColumnValue = $data&&$data->count()>0?$data[0]->ID:null;
	    	if (!$sourceColumnValue) break;
    	}
    	
    	return response()->json(['dataSet'=>$dataSet,
    							'postData'=>$postData]);
    }
    
    public function loadDetailData($id){
    	$defermentDetail 		= DefermentDetail::getTableName();
    	$properties 			= $this->getOriginProperties($defermentDetail);
    	$dataSet 				= $this->getDefermentDetails($id);
    	return ['properties'	=>$properties,
				 'dataSet'		=>$dataSet];
    }
    
    public function getWorkOrder($id){
    	$workOrder 			= WorkOrder::getTableName();
    	$dataSet 			= WorkOrder::where("DEFERMENT_ID",$id)
								    	->select(
	 							    			"ID as $workOrder",
								    			"ID as DT_RowId",
								    			"$workOrder.*"
								    			)
								    	->get();
    	return $dataSet;
    }
    
    public function getWorkOrderMmr($id){
    	$workOrderMmr		= WorkOrderMmr::getTableName();
    	$dataSet 			= WorkOrderMmr::where("MMR_ID",$id)
								    	->select(
	 							    			"ID as $workOrderMmr",
								    			"ID as DT_RowId",
								    			"$workOrderMmr.*"
								    			)
								    	->get();
    	return $dataSet;
    }
    
    public function getDefermentDetails($id){
    	$defermentDetail 	= DefermentDetail::getTableName();
    	$defermentGroup 	= DefermentGroup::getTableName();
    	$defermentGroupEu 	= DefermentGroupEu::getTableName();
    	$energyUnit 		= EnergyUnit::getTableName();
    	$deferment 			= Deferment::getTableName();
    	//\DB::enableQueryLog();
		if(config('constants.systemName')=='Mazarine')
			$dataSet		= Deferment::join($defermentGroupEu, "$deferment.DEFER_TARGET", '=', "$defermentGroupEu.DEFERMENT_GROUP_ID")
    						->join($energyUnit, "$defermentGroupEu.EU_ID", '=', "$energyUnit.ID")
					    	->leftJoin($defermentDetail, function($join) use ($defermentDetail,$deferment,$energyUnit){
					    		$join->on("$defermentDetail.DEFERMENT_ID", '=', "$deferment.ID")
					    		->on("$defermentDetail.EU_ID",'=',"$energyUnit.ID");
					    	})
					    	->where("$deferment.ID",'=',$id)
					    	->select(
					    			"$energyUnit.ID as ID",
					    			"$energyUnit.ID as EU_ID",
					    			"$energyUnit.NAME as NAME",
					    			"$energyUnit.ID as DT_RowId",
					    			"$deferment.DEFER_GROUP_TYPE",
					    			"$defermentDetail.THEOR_OIL_PERDAY", "$defermentDetail.THEOR_OIL_MASS_PERDAY", "$defermentDetail.THEOR_GAS_PERDAY", "$defermentDetail.THEOR_WATER_PERDAY", "$defermentDetail.CALC_DEFER_OIL_VOL", "$defermentDetail.CALC_DEFER_OIL_MASS", "$defermentDetail.CALC_DEFER_GAS_VOL", "$defermentDetail.CALC_DEFER_WATER_VOL", "$defermentDetail.OVR_DEFER_OIL_VOL", "$defermentDetail.OVR_DEFER_OIL_MASS", "$defermentDetail.OVR_DEFER_GAS_VOL", "$defermentDetail.OVR_DEFER_WATER_VOL", "$defermentDetail.COMMENT", "$defermentDetail.DEFER_SMPP_RATIO", "$defermentDetail.DEFER_WELL_RATIO", "$defermentDetail.SHARE_DEFER_GAS_VOL", "$defermentDetail.SHARE_DEFER_OIL_MASS", "$defermentDetail.SHARE_DEFER_OIL_VOL", "$defermentDetail.SHARE_DEFER_WTR_VOL", "$defermentDetail.SMPP_GAS_PERDAY", "$defermentDetail.SMPP_OIL_MASS_PERDAY", "$defermentDetail.SMPP_OIL_PERDAY", "$defermentDetail.SMPP_WTR_PERDAY"
					    			)
					    	->get();
		else
			$dataSet		= Deferment::join($defermentGroupEu, "$deferment.DEFERMENT_GROUP_SUB2", '=', "$defermentGroupEu.DEFERMENT_GROUP_SUB2_ID")
    						->join($energyUnit, "$defermentGroupEu.EU_ID", '=', "$energyUnit.ID")
					    	->leftJoin($defermentDetail, function($join) use ($defermentDetail,$deferment,$energyUnit){
					    		$join->on("$defermentDetail.DEFERMENT_ID", '=', "$deferment.ID")
					    		->on("$defermentDetail.EU_ID",'=',"$energyUnit.ID");
					    	})
					    	->where("$deferment.ID",'=',$id)
					    	->select(
					    			"$energyUnit.ID as ID",
					    			"$energyUnit.ID as EU_ID",
					    			"$energyUnit.NAME as NAME",
					    			"$energyUnit.ID as DT_RowId",
					    			"$deferment.DEFER_GROUP_TYPE",
					    			"$defermentDetail.THEOR_OIL_PERDAY", "$defermentDetail.THEOR_OIL_MASS_PERDAY", "$defermentDetail.THEOR_GAS_PERDAY", "$defermentDetail.THEOR_WATER_PERDAY", "$defermentDetail.CALC_DEFER_OIL_VOL", "$defermentDetail.CALC_DEFER_OIL_MASS", "$defermentDetail.CALC_DEFER_GAS_VOL", "$defermentDetail.CALC_DEFER_WATER_VOL", "$defermentDetail.OVR_DEFER_OIL_VOL", "$defermentDetail.OVR_DEFER_OIL_MASS", "$defermentDetail.OVR_DEFER_GAS_VOL", "$defermentDetail.OVR_DEFER_WATER_VOL", "$defermentDetail.COMMENT", "$defermentDetail.DEFER_SMPP_RATIO", "$defermentDetail.DEFER_WELL_RATIO", "$defermentDetail.SHARE_DEFER_GAS_VOL", "$defermentDetail.SHARE_DEFER_OIL_MASS", "$defermentDetail.SHARE_DEFER_OIL_VOL", "$defermentDetail.SHARE_DEFER_WTR_VOL", "$defermentDetail.SMPP_GAS_PERDAY", "$defermentDetail.SMPP_OIL_MASS_PERDAY", "$defermentDetail.SMPP_OIL_PERDAY", "$defermentDetail.SMPP_WTR_PERDAY"
					    			)
					    	->get();
		//\Log::info(\DB::getQueryLog());
    	return $dataSet;
    }
    
    
    
    public function editSaving(Request $request){
    	$postData = $request->all();
    	$id = $postData['id'];
	    $mdlData = $postData['editedData']['DefermentDetail'];
	    $columns = ['DEFERMENT_ID'=>$id];
	    foreach($mdlData as $key => $newData ){
	    	$columns['EU_ID'] = $newData['DT_RowId'];
	    	$newData['EU_ID'] = $newData['DT_RowId'];
	    	$newData['DEFERMENT_ID'] = $id;
	    	$returnRecord = DefermentDetail::updateOrCreate($columns, $newData);
	    	if($returnRecord) $returnRecord->afterSaving($postData);
	    }
	    
	    if ($this->enableAutoCalculateOverrideValue) {
	    	$dataSet = $this->getDefermentDetails($id);
	    	$totalOfOvrDeferOilVol = $dataSet->sum('OVR_DEFER_OIL_VOL');
	    	$totalOfOvrDeferOilMass = $dataSet->sum('OVR_DEFER_OIL_MASS');
	    	$totalOfOvrDeferGasVol = $dataSet->sum('OVR_DEFER_GAS_VOL');
	    	$totalOfOvrDeferWaterVol = $dataSet->sum('OVR_DEFER_WATER_VOL');
	    	
	    	Deferment::where('ID', $id)
	    			->update(array('OVR_DEFER_OIL_VOL' => $totalOfOvrDeferOilVol,
	    					'OVR_DEFER_OIL_MASS' => $totalOfOvrDeferOilMass,
	    					'OVR_DEFER_GAS_VOL' => $totalOfOvrDeferGasVol,
	    					'OVR_DEFER_WATER_VOL' => $totalOfOvrDeferWaterVol
	    			));
	    }
    	
    	$postData["tabTable"]	= "DefermentDetail";
    	$results				= $this->loadTableData($postData);
    	return response()->json($results);
    }
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['DEFER_TARGET'			=>	$rowData["DEFER_TARGET"],
    			'DEFER_GROUP_TYPE'		=>	$rowData["DEFER_GROUP_TYPE"],
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
    	$row = EnergyUnit::where(['ID'=>$rowData['DEFER_TARGET']])
				    	 ->select('NAME')
				    	 ->first();
    	$obj_name		= $row?$row->NAME:"";
    	return $obj_name;
    }
}
