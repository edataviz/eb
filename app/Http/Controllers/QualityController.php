<?php

namespace App\Http\Controllers;
use App\Models\CodeQltySrcType;
use Carbon\Carbon;
use App\Models\PdVoyage;
use App\Models\QltyDataDetail;
use App\Models\QltyProductElementType;
use App\Models\QltyData;
use App\Models\PdVoyageDetail;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class QualityController extends CodeController {

	public function __construct() {
		parent::__construct();
		$this->fdcModel = "QualityData";
		$this->idColumn = config("constants.qualityId");
		$this->keyColumns = [$this->idColumn,$this->phaseColumn];
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return false;
	}
	
	public function getFirstProperty($dcTable){
		if ($dcTable==QltyData::getTableName()) {
			return  ['data'=>$dcTable,'title'=>'','width'=>50];
		}
		return null;
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$mdlName 		= $postData[config("constants.tabTable")];
    	if($mdlName=="QltyDataDetail") 
    		return $this->edit($postData,$properties["properties"]);
//     		return ['dataSet'	=> $this->edit($postData,$properties["properties"])];
    		
    	$mdl 			= "App\Models\\$mdlName";
    	$src_type_id 	= $postData['CodeQltySrcType'];
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	$filterBy 		= $postData['cboFilterBy'];
    	
    	$extraDataSet = [];
    	$dataSet = null;
    	$codeQltySrcType = CodeQltySrcType::getTableName();
//     	$qltData = $mdl::getTableName();
    	$uoms = $properties['uoms'];
	    $sourceTypekey = array_search('CodeQltySrcType', array_column($uoms, 'id'));
	    $sourceTypes = $uoms[$sourceTypekey]['data'];
	    $objectType = null;
	    
	    $columns	= $this->extractRespondColumns($dcTable,$properties);
	    if (!$columns) $columns = [];
	    array_push($columns,"$dcTable.ID as $dcTable",
    						"$dcTable.ID",
			    			"$dcTable.ID as DT_RowId");
	    
    	$src_type_ids = $src_type_id==0?$sourceTypes->pluck('ID')->toArray():[$src_type_id];
	    $isSplitQuery = config('database.default')==='oracle'||config('database.default')==='sqlsrv';
	    if ($isSplitQuery) 
	    	$oQueries 	= [];
	    else
	    	$query 		= null;
	    // 	    \DB::enableQueryLog();
	    foreach($src_type_ids as $srcTypeId ){
	    	$where = ['SRC_TYPE' => $srcTypeId];
	    	switch ($srcTypeId) {
	    		case 1:						
	    		case 2:						
	    		case 3:						
	    		case 4:
	    			$objectType = $sourceTypes->find($srcTypeId);
			    	$objectType = $objectType->CODE;
		    		$cquery = $mdl::join($objectType,function ($query) use ($objectType,$facility_id,$dcTable) {
								    							$query->on("$objectType.ID",'=',"$dcTable.SRC_ID")
								    							->where("$objectType.FACILITY_ID",'=',$facility_id) ;
									})
							    	->where($where)
							    	->whereDate("$dcTable.$filterBy", '>=', $occur_date)
							    	->whereDate("$dcTable.$filterBy", '<=', $date_end)
							    	->select($columns)
					    			->orderBy($dcTable);
	    			if ($isSplitQuery) 
	    				$oQueries[] = $cquery;
	    			else 
	    				$query = $query==null?$cquery:$query->union($cquery);
	    			break;
		    	case 5:
		    		$objectType = $sourceTypes->find($srcTypeId);
		    		$objectType = $objectType->CODE;
		    		$storage = Storage::getTableName();
		    		$pdVoyageDetail = PdVoyageDetail::getTableName();
		    		$pdVoyage = PdVoyage::getTableName();
		    		
		    		$cquery = $mdl::join($pdVoyageDetail, "$dcTable.SRC_ID", '=', "$pdVoyageDetail.ID")
									->join($pdVoyage, "$pdVoyageDetail.VOYAGE_ID", '=', "$pdVoyage.ID")
									->join($storage,function ($query) use ($storage,$facility_id,$pdVoyage) {
											    			$query->on("$storage.ID",'=',"$pdVoyage.STORAGE_ID")
											    			->where("$storage.FACILITY_ID",'=',$facility_id) ;
						    		})
									->where($where)
						    		->whereDate("$dcTable.$filterBy", '>=', $occur_date)
							    	->whereDate("$dcTable.$filterBy", '<=', $date_end)
							    	->select($columns)
				    				->orderBy($dcTable);
    				if ($isSplitQuery)
    					$oQueries[] = $cquery;
    				else
    					$query = $query==null?$cquery:$query->union($cquery);
					break;
	    		case 6:
	    			$objectType = $sourceTypes->find($srcTypeId);
	    			$objectType = $objectType->CODE;
		    		$cquery = $mdl::where($where)
						    		->whereDate("$dcTable.$filterBy", '>=', $occur_date)
							    	->whereDate("$dcTable.$filterBy", '<=', $date_end)
							    	->select($columns)
				    				->orderBy($dcTable);
					if ($isSplitQuery)
						$oQueries[] = $cquery;
					else
						$query = $query==null?$cquery:$query->union($cquery);
					break;
	    	}
    	}
	    
    	if ($isSplitQuery){
	    	if (count($oQueries)>0) {
				foreach($oQueries as $key => $oQuery ){
					if($dataSet) {
// 						$dataSet	= $dataSet->merge($oQuery->get());
						$gData		= $oQuery->get();
						if ($gData) {
							foreach ($gData as $item){
								$dataSet->add($item);
							}
						}
					}
					else $dataSet 			= $oQuery->get();
				}
	    	}
    	}
    	else{
	    	if ($query!=null) $dataSet= $query->get();
    	}
    		
//     	\Log::info(\DB::getQueryLog());
    	$sourceColumn = 'SRC_TYPE';
    	if ($dataSet&&$dataSet->count()>0) {
    		if ($src_type_id>0) {
    			$srcTypeData = $this->getExtraDatasetBy($objectType,$facility_id);
    			if ($srcTypeData) {
    				$extraDataSet[$sourceColumn]= [];
					$extraDataSet[$sourceColumn][$src_type_id] = $srcTypeData;
    			}
    		}
    		else{
//     			\DB::enableQueryLog();
				$bySrcTypes = $dataSet->groupBy('SRC_ID');
// 				\Log::info(\DB::getQueryLog());
				if ($bySrcTypes) {
    				$extraDataSet[$sourceColumn]= [];
					foreach($bySrcTypes as $key => $srcType ){
						$srcTypeID = $srcType[0]->SRC_TYPE;
						$table = $sourceTypes->find($srcTypeID);
						$table = $table->CODE;
						$srcTypeData = $this->getExtraDatasetBy($table,$facility_id);
						if ($srcTypeData) {
    						$extraDataSet[$sourceColumn][$srcTypeID] = $srcTypeData;
						}
					}
				}
    		}
    	}
    	
    	return ['dataSet'=>$dataSet,
     			'extraDataSet'=>$extraDataSet
    	];
    }
    
    public function getExtraDatasetBy($objectType,$facility_id){
    	return CodeQltySrcType::loadObjectsByCode($objectType,$facility_id);
    }
    
    public function loadsrc(Request $request){
    	//     	sleep(2);
    	$postData = $request->all();
    	$facility_id = $postData['Facility'];
    	$name = $postData['name'];
    	$srcTypeData = [];
    	if ($name=='SRC_TYPE'||$name=='OBJECT_TYPE') {
 	    	$src_type_id = $postData['value'];
	    	$objectType = $postData['srcType'];
	    	$key = $name=='SRC_TYPE'?'SRC_ID':'OBJECT_ID';
	    	\Helper::setGetterUpperCase();
    		$srcTypeData[$key] = [	'data'			=>	$this->getExtraDatasetBy($objectType,$facility_id),
    									'ofId'			=>	$src_type_id,
    									'sourceColumn'	=>	$name
    		];
    	}
    	return response()->json(['dataSet'=>$srcTypeData,
    							'postData'=>$postData]);
    }
    
    public function edit($postData,$properties){
//     	$postData 				= $request->all();
    	$isOracle 				= config('database.default')==='oracle';
    	$id 					= $postData['id'];
    	$dcTable 				= QltyData::getTableName();
    	$qltyDataDetail 		= QltyDataDetail::getTableName();
    	$qltyProductElementType = QltyProductElementType::getTableName();
//     	$properties 			= $this->getOriginProperties($qltyDataDetail);
        $order                  = $isOracle ? 'order' : 'ORDER';
    	$dataSet 				= QltyProductElementType::join($dcTable,function ($query) use ($dcTable,$id,$qltyProductElementType) {
										    		$query->on("$dcTable.SAMPLE_TYPE",'=',"$qltyProductElementType.SAMPLE_TYPE")
										    				->where("$dcTable.ID",'=',$id) ;
									    	})
									    	->leftJoin($qltyDataDetail, function($join) use ($qltyDataDetail,$id,$qltyProductElementType){
									    				$join->on("$qltyDataDetail.ELEMENT_TYPE", '=', "$qltyProductElementType.ID")
									    					->where("$qltyDataDetail.QLTY_DATA_ID",'=',$id);
									    	})
									    	->where("$qltyProductElementType.ACTIVE","=",1)
								    		->select(
 								    				"$qltyProductElementType.ID as ID",
								    				"$qltyProductElementType.ID as DT_RowId",
								    				"$qltyProductElementType.ORDER".($isOracle?'_':''),
								    				"$qltyProductElementType.NAME",
								    				"$qltyProductElementType.SAMPLE_TYPE",
								    				"$qltyProductElementType.DEFAULT_UOM",
								    				/* "$qltyDataDetail.ELEMENT_TYPE",
								    				"$qltyDataDetail.VALUE",
								    				"$qltyDataDetail.UOM",
								    				"$qltyDataDetail.GAMMA_C7",
								    				"$qltyDataDetail.MOLE_FACTION",
								    				"$qltyDataDetail.MASS_FRACTION",
								    				"$qltyDataDetail.NORMALIZATION", */
								    				"$qltyDataDetail.*"
								    				)
 						    				->orderBy("$qltyProductElementType.$order", 'ASC')
 						    				//->orderBy("$qltyProductElementType.NAME")
						    				->get();
    	foreach ($dataSet as $value){
            if (empty($value->UOM)) $value->UOM = $value->DEFAULT_UOM;
        }
    	$datasetGroups = $dataSet->groupBy(function ($item, $key) {
									    $group	= "none";
									    if ($item->SAMPLE_TYPE!=2&&$item['DEFAULT_UOM']	!='Mole fraction') {
									    	$group	= 'NONE_MOLE_FACTION';
									    }
									    elseif ($item['DEFAULT_UOM']	=='Mole fraction') $group	= 'MOLE_FACTION';
// 									    return $item['DEFAULT_UOM']	!='Mole fraction'?'NONE_MOLE_FACTION':'MOLE_FACTION';
									    return $group;
									});
    	//none left, mole right
	    $results = ["dataSet"	=> $dataSet];
	    if ($datasetGroups->has('NONE_MOLE_FACTION')) {
	    	$gasElementColumns = ['MOLE_FACTION','MASS_FRACTION'];
		    $noneMole = $properties->groupBy(function ($item, $key) use ($gasElementColumns) {
						    if ($item instanceof Model) {
							    return in_array($item->name, $gasElementColumns)?'MOLE_FACTION':'NONE_MOLE_FACTION';
						    }
						    return "NONE";
		    });
		    $results['NONE_MOLE_FACTION'] = ['properties'	=>$noneMole['NONE_MOLE_FACTION'],
		    								'dataSet'		=>$datasetGroups['NONE_MOLE_FACTION']];
	    }
	    
	    if ($datasetGroups->has('MOLE_FACTION')) {
	    	$oilElementColumns = ['VALUE','UOM'];
	    	$noneMole = $properties->groupBy(function ($item, $key) use ($oilElementColumns) {
		    				if ($item instanceof Model) {
							    return in_array($item->name, $oilElementColumns)?'NONE_MOLE_FACTION':'MOLE_FACTION';
		    				}	
		    				return "NONE";
	    	});
    	
    		$results['MOLE_FACTION'] = ['properties'	=>$noneMole['MOLE_FACTION'],
	    								'dataSet'		=>$datasetGroups['MOLE_FACTION']];
	    }

// 	    return response()->json($results);
    	return $results;
    }
    
    public function editSaving(Request $request){
    	$postData 		= $request->all();
    	$id 			= $postData['id'];
    	
    	$qltyDataEntry 	= QltyData::find($id);
		//\Log::info($postData);
    	if ($qltyDataEntry) {
    		$productType = $qltyDataEntry->SAMPLE_TYPE;
    		switch ($productType){
    			case 1://oil
   					$attributes = ['QLTY_DATA_ID'=>$id];
//    					$values = ['QLTY_DATA_ID'=>$id];
   					$oils = array_key_exists('oil', $postData)?$postData['oil']:null;
   					if ($oils&&count($oils)>0) {
	    				foreach($oils as $oil ){
	    					/* if (array_key_exists("ID", $oil)) {
	    						unset($oil["ID"]);
	    					} */
	    					$attributes['ELEMENT_TYPE'] = $oil['DT_RowId'];
   							$oil['QLTY_DATA_ID'] = $id;
   							$oil['ELEMENT_TYPE'] = $oil['DT_RowId'];
   							QltyDataDetail::updateOrCreate($attributes,$oil);
	    				};
   					}
   					$gases = array_key_exists("gas", $postData)?$postData['gas']:null;
   					if ($gases&&count($gases)>0) {
	    				foreach($gases as $gas ){
	    					/* if (array_key_exists("ID", $gas)) {
	    						unset($gas["ID"]);
	    					} */
	    					$attributes['ELEMENT_TYPE'] = $gas['DT_RowId'];
	    					$gas['QLTY_DATA_ID'] = $id;
	    					$gas['ELEMENT_TYPE'] = $gas['DT_RowId'];
		    				QltyDataDetail::updateOrCreate($attributes,$gas);
	    				};
   					}
    				break;
    			case 2://gas
    				$constantElementTypes = QltyProductElementType::where("SAMPLE_TYPE",'=', $productType)->select("MOL_WEIGHT","CODE","ID")->get();
    				
    				$gases = array_key_exists("gas", $postData)?$postData['gas']:null;
    				if ($gases) {
	   					$attributes = ['QLTY_DATA_ID'=>$id];
	   					$qltDetails = [];
	    				foreach($constantElementTypes as $constantElementType ){
	// 	   					$entries[$eid] = [];
		    				$attributes['ELEMENT_TYPE'] = $constantElementType->ID;
		    				$aqltyDataDetail = QltyDataDetail::firstOrNew($attributes);
		    				$aqltyDataDetail->fill($attributes);
		    				$qltDetails[$constantElementType->ID] = $aqltyDataDetail;
	    				}
	    				
	    				if ($gases&&count($gases)>0) {
	    					foreach($gases as $gas ){
	    						$qltd = $qltDetails[$gas['DT_RowId']];
	    						$qltd->fill($gas);
	    					};
	    					
	    					$totalMole = 0;
	    					foreach($constantElementTypes as $constantElementType ){
	    						$qltd = $qltDetails[$constantElementType->ID];
	    						$qltd->{'calculated'} = $qltd->MOLE_FACTION*$constantElementType->MOL_WEIGHT;
	    						$totalMole+=$qltd->MOLE_FACTION*$constantElementType->MOL_WEIGHT;
	    					};
	    					
							foreach($qltDetails as $qltd ){
								if ($totalMole!=0)
									$qltd->MASS_FRACTION = $qltd->calculated/$totalMole;
								else
									$qltd->MASS_FRACTION = 0;
								unset($qltd->calculated);
								$qltd->save();
							};
// 	    					else response()->json('total = 0');
	    				}
// 	    				else response()->json('no change data detected');
    				}
//     				else response()->json('empty data');
					break;
				default:
   					$attributes = ['QLTY_DATA_ID'=>$id];
   					$items = array_key_exists('oil', $postData)?$postData['oil']:null;
   					if ($items&&count($items)>0) {
	    				foreach($items as $item ){
	    					$attributes['ELEMENT_TYPE'] = $item['DT_RowId'];
   							$item['QLTY_DATA_ID'] = $id;
   							$item['ELEMENT_TYPE'] = $item['DT_RowId'];
   							QltyDataDetail::updateOrCreate($attributes,$item);
	    				};
   					}
   					$items = array_key_exists('gas', $postData)?$postData['gas']:null;
   					if ($items&&count($items)>0) {
	    				foreach($items as $item ){
	    					$attributes['ELEMENT_TYPE'] = $item['DT_RowId'];
   							$item['QLTY_DATA_ID'] = $id;
   							$item['ELEMENT_TYPE'] = $item['DT_RowId'];
   							QltyDataDetail::updateOrCreate($attributes,$item);
	    				};
   					}
    		}
    	}
    	$postData["tabTable"]	= "QltyDataDetail";
    	return response()->json($this->loadTableData($postData));
//     	return response()->json('Edit Successfullly');
    }
    
    
    public function getHistoryConditions($dcTable,$rowData,$row_id){
    	return ['SRC_TYPE'		=>	$rowData["SRC_TYPE"],
    			'SRC_ID'		=>	$rowData["SRC_ID"],
    	];
    }
    
    public function getHistoryData($mdl, $field,$rowData,$where, $limit){
    	$row_id			= $rowData['ID'];
    	if ($row_id<=0) return [];
    	 
    	$occur_date		= $rowData['EFFECTIVE_DATE'];
    	$history 		= $mdl::where($where)
						    	->whereDate('EFFECTIVE_DATE', '<', $occur_date)
						    	->whereNotNull($field)
						    	->orderBy('EFFECTIVE_DATE','desc')
						    	->skip(0)->take($limit)
						    	->select('EFFECTIVE_DATE as OCCUR_DATE',
						    			"$field as VALUE"
						    			)
				    			->get();
    	return $history;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$obj_table = null;
    	if($rowData["SRC_TYPE"]==1)
    		$obj_table="Flow";
    	else if($rowData["SRC_TYPE"]==2)
	    	$obj_table="EnergyUnit";
	    else if($rowData["SRC_TYPE"]==3)
    		$obj_table="Tank";
    	else if($rowData["SRC_TYPE"]==4)
    		$obj_table="Equipment";
    	else if($rowData["SRC_TYPE"]==5)
    		$obj_table="PdCargo";
    	else if($rowData["SRC_TYPE"]==6)
    		$obj_table="Reservoir";
    	
    	$mdl = $obj_table?'App\Models\\' . $obj_table:null;
    	if ($mdl) {
	    	$row = $mdl::where(['ID'=>$rowData['SRC_ID']])
						    	->select('NAME')
						    	->first();
	    	$obj_name		= $row?$row->NAME:"";
	    	return $obj_name;
    	}
    	return '';
    }
}
