<?php

namespace App\Http\Controllers;

use App\Models\CfgConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\ViewComposers\ProductionGroupComposer;
use App\Models\BaAddress;
use App\Models\CfgFieldProps;
use App\Models\CodeAllocType;
use App\Models\CodeBoolean;
use App\Models\CodeCommentStatus;
use App\Models\CodeEnvStatus;
use App\Models\CodeDeferCategory;
use App\Models\CodeDeferCode1;
use App\Models\CodeDeferGroupType;
use App\Models\CodeDeferReason;
use App\Models\CodeDeferPlan;
use App\Models\CodeDeferStatus;
use App\Models\CodeEqpFuelConsType;
use App\Models\CodeEqpGhgRelType;
use App\Models\CodeEqpOfflineReason;
use App\Models\CodeEventType;
use App\Models\QltyProductElementType;
use App\Models\CodeFlowPhase;
use App\Models\CodePersonnelTitle;
use App\Models\CodePersonnelType;
use App\Models\CodePressUom;
use App\Models\CodeProductType;
use App\Models\CodeSampleType;
use App\Models\CodeQltySrcType;
use App\Models\CodeReadingFrequency;
use App\Models\CodeSafetySeverity;
use App\Models\CodeStatus;
use App\Models\CodeTestingMethod;
use App\Models\CodeTestingUsage;
use App\Models\CodeTicketType;
use App\Models\CodeVolUom;
use App\Models\Environmental;
use App\Models\CustomizeDateCollection;
use App\Models\EbFunctions;
use App\Models\Facility;
use App\Models\IntSystem;
use App\Models\PdTransitCarrier;
use App\Models\Personnel;
use App\Models\StandardUom;
use App\Models\Tank;	
use App\Models\TmTask;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use App\Models\IntObjectType;

class CodeController extends EBController {
	 
	protected $fdcModel;
	protected $idColumn;
	protected $phaseColumn;
	protected $valueModel ;
	protected $keyColumns ;
	protected $theorModel ;
	protected $isApplyFormulaAfterSaving;
	protected $extraDataSetColumns;
	protected $detailModel;
	protected $contraintEloquents;
    protected $editFilterName;
    protected $editFilterPrefix;


	public function __construct() {
		parent::__construct();
		$this->isApplyFormulaAfterSaving 	= false;
        $this->editFilterName = 'partials.editfilter';
        $this->editFilterPrefix = '';
	}
	
	public function getCodes(Request $request){
		$options 	= $request->all();
		$bunde 		= array_key_exists('extra', $options)?$options['extra']:null;
		$type 		= $options['type'];
		
		if ($type=='date_end'||$type=='date_begin') {
			$unit = new CustomizeDateCollection($type,$options['value']);
		}
		else{
			$fId		= $options['value'];
			if ($fId<=0&&is_array($bunde)&&array_key_exists("secondary", $bunde)) {
				$type	= $bunde['secondary'];
				$fId	= $bunde[$type]["id"];
			}
			$mdl 		= 'App\Models\\'.$type;
			$unit 		= $mdl::find($fId);
		}
		$originUnit 	= $unit;
		$results 		= [];
		$currentUnits 	= [$type	=> $unit];
		$dependences	= $options['dependences'];
		foreach($dependences as $model ){
			$modelName 	= $model;
			$elementId	= $modelName;
			$currentId 	= null;
			$sourceUnit = $unit;
			$isAdd		= true;
			if (is_array($model)) {
				if (array_key_exists("source", $model)) {
					$currentSourceName = $model["source"];
					$sourceUnit = array_key_exists($currentSourceName, $currentUnits)?$currentUnits[$currentSourceName]:$originUnit;
					if (is_array($sourceUnit)&&array_key_exists("ID", $sourceUnit)&&$sourceUnit["ID"]<=0) {
						foreach($dependences as $dependency ){
							if(is_array($dependency)&&array_key_exists("name", $dependency)&&$currentSourceName==$dependency["name"]
									&&array_key_exists("source", $dependency)){
								$sSource = $dependency["source"];
								if(array_key_exists($sSource, $currentUnits)){
									$sourceUnit = $currentUnits[$sSource];
									break;
								}
							}
						}
					}
				}
				else $sourceUnit = $originUnit;
				$modelName  = $model["name"];
				$isAdd = !array_key_exists("independent", $model)||!$model["independent"];
				
				if (array_key_exists("elementId", $model)) $elementId	= $model['elementId'];
			}
			
			if ($sourceUnit!=null) {
				$rs = ProductionGroupComposer::initExtraDependence($results,$model,$sourceUnit,$bunde);
				$eCollection 	= $rs['collection'];
				$modelName 		= $rs['model'];
				$currentId 		= $rs['currentId'];
			}
			else if (is_string($model)&& method_exists("App\Models\\$model","loadIndependence")) {
				$mdlN = "App\Models\\$model";
				$eCollection 	= $mdlN::loadIndependence();
			}
			else  break;
			$cUnit				= null;
			if (is_string($modelName) && array_key_exists($modelName,  config("constants.subProductFilterMapping"))&&
				array_key_exists('default',  config("constants.subProductFilterMapping")[$modelName])) {
				$cUnit			= config("constants.subProductFilterMapping")[$modelName]['default'];
				if ($eCollection) {
					$eCollection->prepend($cUnit);
				}
			}
			$unit = $cUnit?$cUnit:ProductionGroupComposer::getCurrentSelect ( $eCollection,$currentId );
			$currentUnits[$modelName]	= $unit;
			$mdlName 		= 'App\Models\\'.$modelName;
			$aOption 		= $mdlName::getOptionDefault($modelName,$unit);
			$elementId		= is_string($elementId)?$elementId:$modelName;
			$filterArray 	= \Helper::getFilterArray ( $elementId, $eCollection, $unit,$aOption );
			if ($isAdd) $results [] = $filterArray;
		}
		
		return response($results, 200)->header('Content-Type', 'application/json');
    }
    
    public function load(Request $request){
    	$postData 		= $request->all();
    	$results		= $this->loadTableData($postData);
    	return response()->json($results);
    }
    
    public function loadTableData($postData){
    	$dcTable 		= $this->getWorkingTable($postData);
    	$facility_id 	= array_key_exists('Facility',  $postData)?$postData['Facility']:null;
    	$occur_date 	= null;
    	if (array_key_exists('date_begin',  $postData)){
    		$occur_date = $postData['date_begin'];
    		$occur_date = \Helper::parseDate($occur_date);
    	}

    	$results 		= $this->getProperties($dcTable,$facility_id,$occur_date,$postData);
    	$originAttrCase = \Helper::setGetterUpperCase();
    	$data 			= $this->getDataSet($postData,$dcTable,$facility_id,$occur_date,$results);
    	\Helper::setGetterCase($originAttrCase);
    	 
    	$secondaryData 	= $this->getSecondaryData($postData,$dcTable,$facility_id,$occur_date,$results);
    	$results['secondaryData'] = $secondaryData;
    	$results['postData'] = $postData;
    	if ($data&&is_array($data)) {
    		$results 	= array_merge($results, $data);
    		$dataSet	= array_key_exists('dataSet',  $results)?$results['dataSet']:null;
    		$mdlName 	= array_key_exists(config("constants.tabTable"), $postData)?$postData[config("constants.tabTable")]:null;
    		$properties	= $results['properties'];
    		
    		if ($dataSet&&$dataSet instanceof Collection && $dataSet->count()>0) {
    			$rQueryList			= [];
    			$enableBatchRun		= $this->enableBatchRun($dataSet,$mdlName,$postData);
    			$dataSet->each(function ($item, $key) use ($enableBatchRun,$properties,&$rQueryList,$mdlName,$occur_date,$postData){
    				if ($item&&$item instanceof Model) {
	    				if (($item->DT_RowId===null||$item->DT_RowId==="")) {
	    					if (($item->DT_ROWID!==null&&$item->DT_ROWID!=="")) {
	    						$item->DT_RowId	= $item->DT_ROWID;
	    					}
	    					else
	    						$item->DT_RowId	= substr( md5(rand()), 0, 10);
	    				}
	    				if ($properties) {
	    					$properties->each(function ($property, $key) use ($item,&$rQueryList,$mdlName,$occur_date,$postData) {
	    						if ($property&&$property instanceof CfgFieldProps) {
									$range = $property->shouldLoadLastValueOf($item);
	    							if ($range) {
		    							$column		= isset($property->DATA)?$property->DATA:$property->data;//$property->data;
										$_rs = explode('%', $range);
										$take_config = (count($_rs)>1?$_rs[1]:'1');
					    				$query		= $item->getLastValueOfColumn($mdlName,$column,$occur_date,$postData,true,$take_config);
			    						if ($query) {
			    							if (!array_key_exists($column, $rQueryList)) $rQueryList[$column] = [];
			    							$rQueryList[$column][]	= $query;
			    						}
	    							}
	    						}
	    					});
	    				}
    				}
    			});
    			if ($enableBatchRun&&$mdlName) {
    				$objectIds				= $this->getObjectIds($dataSet,$postData,$properties);
    				$results["objectIds"]	= [$mdlName	=> $objectIds];
    			}
    			
    			$this->updatePropertiesWithLastValue($properties,$rQueryList);
    		}
    	}
    	return $results;
    }
    
    public function extractRespondColumns($dcTable,$oProperties){
    	$columns = [];
    	$properties	= $oProperties['properties'];
    	if ($properties &&count($properties)>0) {
    		$mdl 		= \Helper::getModelName($dcTable);
    		$fillable	= $mdl::getInstance()->getTableColumns();
    		foreach($properties as $property){
    			if ($property instanceof CfgFieldProps) {
					$data = isset($property->DATA)?$property->DATA:$property->data;
					$column = "$dcTable.".$data;
					if (count($fillable)>0){
						if (config('database.default')==='oracle') $column = strtolower($column);
						$column = in_array($data, $fillable)?$column:null;
					}
					if ($column) $columns[] = $column;
    			}
    		};
    		
    		$pColumns	= ["ALLOC_TYPE","PLAN_TYPE","FORECAST_TYPE"];
    		foreach($pColumns as $pColumn){
    			$column = "$dcTable.$pColumn";
    			if (config('database.default')==='oracle') $column = strtolower($column);
    			if (in_array($pColumn, $fillable)) $columns[] = $column;
    		};
    	}
    	$columns = array_unique($columns);
    	return $columns;
    }
    
    public function getObjectIds($dataSet,$postData,$properties){
    	$objectIds = $dataSet->map(function ($item, $key) {
    		return ["DT_RowId"			=> $item->DT_RowId,
    				"ID"				=> $item->ID,
    		];
    	});
    	return $objectIds;
    }

    public function containField($properties,$field){
    	return ($properties instanceof Collection) && $properties->contains(function ($key, $value) use ($field){
    		return ($value instanceof Model )&&$value->data == $field;
    	});
    }
    
    public function updatePropertiesWithLastValue($properties,$rQueryList){
    	$lastValues	= [];
		foreach($rQueryList as $column => $rQuerys){
			if (config('database.default')==='oracle') {
				$values = new Collection;
				foreach($rQuerys as $query){
					$values->prepend($query->first());
				};
				if ($values&&$values->count()>0) {
					$values	= $values->keyBy('dt_rowid');
					$lastValues[$column]	= $values;
				}
			}
			else{
				$rQuery	= null;
				foreach($rQuerys as $query){
					$rQuery	= $rQuery&&$query?$rQuery->union($query):$query;
				};
				if ($rQuery) {
					$values	= $rQuery->get();
					if ($values) {
						if(is_array($values)){
							$x = (object) array();
							foreach($values as $value){
								$x->{$value->DT_RowId}=(object) array($column => $value->$column);
							}
							$values = $x;
						}
						else 
							$values	= $values->keyBy('DT_RowId');
						$lastValues[$column]	= $values;
					}
				}
			}
		};
		
		foreach($lastValues as $column => $values){
			$key = $properties->search(function ($item, $key) use ($column) {
				return $item&&$item instanceof Model && $item->data == $column;
			});
			if ($key&&$key>=0) {
				$property	= $properties->get($key);
				if ($property) $property->LAST_VALUES = $values;
			}
		};
    }
    
    public function enableBatchRun($dataSet,$mdlName,$postData){
        return false;
    }
    
    public function loadDetail(Request $request){
    	$postData 				= $request->all();
    	$id 					= $postData['id'];
    	$tab					= isset($postData['tab'])?$postData['tab']:$this->detailModel;
    	$detailModel			= "App\Models\\$tab";
    	$detailTable	 		= $detailModel::getTableName();
    	$results 				= $this->getProperties($detailTable);
    	$originAttrCase 		= \Helper::setGetterUpperCase();
    	$dataSet 				= $this->getDetailData($id,$postData,$results['properties']);
    	$results['dataSet'] 	= $dataSet;
    	\Helper::setGetterCase($originAttrCase);
    	return response()->json([$this->detailModel => $results]);
    }
    
    public function getDetailData($id,$postData,$properties){
    	return [];
    }
    
    public function history(Request $request){
    	$postData 				= $request->all();
    	$dcTable 				= $this->getWorkingTable($postData); 
     	$field 					= $postData['field'];
     	$rowData 				= $postData['rowData'];
     	$filters 				= $postData['filters'];
     	$mdlName 				= $postData[config("constants.tabTable")];
		$mdl 					= "App\Models\\$mdlName";
     	$limit					= array_key_exists('limit',  $postData)?$postData['limit']:10;
     	$limit					= ($limit>=1 && $limit<=100)?$limit:10;
     	
     	$history				= $this->getHistory($mdl,$field,$rowData,$limit,$filters);
     	
        $results['history'] 	= $history;
        $results['$limit'] 		= $limit;
        $results['postData'] 	= $postData;
        /* return view ('partials.history',['history'	=>$history,
						        		'limit'		=>$limit,
						        		'postData'	=>$postData,
        ]); */
        
    	return response()->json($results);
    }
    
	public function getHistory($mdl,$field,$rowData,$limit,$filters){
		$dcTable		= $mdl::getTableName();
		$obj_name		= $this->getFieldTitle($dcTable,$field,$rowData);
		
		$row_id			= array_key_exists("ID", $rowData)?$rowData['ID']:null;
		$fieldName		= $this->getFieldLabel($field,$dcTable);
		
		$where			= $this->getHistoryConditions($dcTable,$rowData,$row_id);
		
		if ($where) {
			$originAttrCase = \Helper::setGetterUpperCase();
			$this->checkCondition($where,'ALLOC_TYPE',"CodeAllocType",$rowData,$filters);
			$this->checkCondition($where,'PLAN_TYPE',"CodePlanType",$rowData,$filters);
			$this->checkCondition($where,'FORECAST_TYPE',"CodeForecastType",$rowData,$filters);
			$history		= $this->getHistoryData($mdl, $field,$rowData,$where, $limit);
			\Helper::setGetterCase($originAttrCase);
			
		}
		else $history = [];
		
		return ['name'		=> $fieldName,
				'dataSet'	=> $history,
				'fieldName'	=> $fieldName
		];
	}
	
	public function checkCondition(&$where,$column,$filterName,$rowData,$filters){
		if (array_key_exists($column, $rowData)) {
			if ($rowData[$column]) {
				$where[$column]	= $rowData[$column];
			}
			else if (array_key_exists($filterName, $filters)&&$filters[$filterName]) {
				$where[$column]	= $filters[$filterName];
			}
		}
	}
	
	public function getHistoryData($mdl, $field,$rowData,$where, $limit){
// 		$row_id			= array_key_exists("ID", $rowData)?$rowData['ID']:-1;
// 		$occur_date		= $row_id>0?$rowData['OCCUR_DATE']:Carbon::now();
		
		$occur_date		= array_key_exists("OCCUR_DATE", $rowData)?$rowData['OCCUR_DATE']:null;
		if ($occur_date&&count_chars($occur_date)>5) 
			$occur_date 		= \Helper::parseDate($rowData['OCCUR_DATE']);
		else 
			$occur_date = Carbon::now();
		
		$history 		= $mdl::where($where)
							->whereDate('OCCUR_DATE', '<', $occur_date)
							->whereNotNull($field)
							->orderBy('OCCUR_DATE','desc')
							->skip(0)->take($limit)
							->select('OCCUR_DATE',
									"$field as VALUE")
							->get();
		return $history;
	}
    
	public function getHistoryConditions($table,$rowData,$row_id){
		return null;
	}
	
    public function getWorkingTable($postData){
    	if (array_key_exists(config("constants.tabTable"), $postData)) {
	    	$mdlName = $postData[config("constants.tabTable")];
	    	$mdl = "App\Models\\$mdlName";
	    	return $mdl::getTableName();
    	}
    	return null;
    }
    
    public function getSecondaryData($postData,$dcTable,$facility_id,$occur_date,$results){
    	return null;
    }
    
    protected function getFieldLabel($field, $table) {
    	$row =  CfgFieldProps::where('TABLE_NAME', '=', $table)
    	->where('USE_FDC', '=', 1)
    	->where('COLUMN_NAME', '=', $field)
        ->whereNull("CONFIG_ID")
        ->select('LABEL')
    	->first();
    	return $row?($row->LABEL?$row->LABEL:$field):$field;
    }
    
    public function getFieldTitle($dcTable,$field,$rowData){
    	$obj_name		= $rowData[$dcTable];
    	return $obj_name;
    }
    
    
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
    	
    	$properties = $this->getOriginProperties($dcTable);
        $configProperties = $this->getConfigProperties($dcTable,$facility_id,$occur_date,$postData);
        $cpValues = array_values($configProperties);
        if(count($configProperties)>0) $properties = array_shift($cpValues);
    	if($properties)	{
    		$lang			= session()->get('locale', "en");
    		$properties->each(function ($item, $key) use ($lang) {
    			if ($item&&$item instanceof Model) {
    				if ($item->title&&Lang::has("front/site.{$item->title}", $lang)) {
	    				$item->title	= trans("front/site.$item->title");
    				}
    			}
    		});
    	}
    	$firstProperty = $this->getFirstProperty($dcTable);
    	if ($firstProperty) {
	    	$properties->prepend($firstProperty);
    	}
    	
    	$locked = $this->isLocked($dcTable,$occur_date,$facility_id);
    	$uoms = $this->getUoms($properties,$facility_id,$dcTable,$locked,$postData);
    	
    	$results = ['properties'	=>$properties,
	    			'uoms'			=>$uoms,
                    'configProperties'=>$configProperties,
	    			'locked'		=>$locked,
    				'tableLock'		=>\Helper::checkLockedTable($dcTable,$occur_date,$facility_id),
	    			'rights'		=> is_array(session('statut'))?array_values(session('statut')):[]];
    	return $results;
    }
    
    public function isLocked($dcTable,$occur_date,$facility_id){
    	$locked = false;
    	$user = auth()->user();
    	if ($occur_date&&$facility_id){
	    	$locked = 	$user->hasRight('DATA_READONLY')||
	    				\Helper::checkLockedTable($dcTable,$occur_date,$facility_id)
	    				/* ||(\Helper::checkApproveTable($dcTable,$occur_date,$facility_id)&&
	    						!$user->hasRight('ADMIN_APPROVE'))||
	    				(\Helper::checkValidateTable($dcTable,$occur_date,$facility_id)&&
	    						!$user->hasRight('ADMIN_APPROVE')&&
	    						!$user->hasRight('ADMIN_VALIDATE')) */;
    	}
    	if (!$locked) {
    		$request 		= request();
    		$parameters 	= $request->route()->parameters();
    		$rightCode		= isset($parameters['rightCode'])?$parameters['rightCode']:"";
    		$locked 		= $user->checkReadOnly($rightCode,$facility_id);
//     		$locked 		= !$user->hasWritableRight($rightCode);
    	}
    	return $locked;
    }
    
    
    public function getOriginProperties($dcTable,$configId=null){
    	$where	= ['TABLE_NAME' => $dcTable,
    			    'USE_FDC' => 1,
                    'CONFIG_ID' => $configId,
    	];
    	$properties	= CfgFieldProps::getOriginProperties($where);
    	return $properties;
    }

    public function getConfigProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
        $cfgConfigs = [];
        if($facility_id){
            $cfgConfigConditions = ['FACILITY_ID' => $facility_id, 'TABLE_NAME' => $dcTable];
            $cQuery = CfgConfig::where($cfgConfigConditions);
            if ($occur_date){
                $cQuery->where ( function ($query1) use($occur_date) {
                    $query1->whereNull ( "EFFECTIVE_DATE" )->orWhereDate ( "EFFECTIVE_DATE", '<=', $occur_date );
                })->where ( function ($query2) use($occur_date) {
                    $query2->whereNull ( "END_DATE" )->orWhereDate( "END_DATE", '>=', $occur_date );
                });
            }
            if ($postData) {
                $endDate = array_key_exists('date_end',  $postData)?$postData['date_end']:null;
                $endDate = $endDate?\Helper::parseDate($endDate):null;
                if ($endDate){
                    $c2Query = CfgConfig::where($cfgConfigConditions);
                    $c2Query->where ( function ($query1) use($endDate) {
                        $query1->whereNull ( "EFFECTIVE_DATE" )->orWhereDate ( "EFFECTIVE_DATE", '<=', $endDate );
                    })->where ( function ($query2) use($endDate) {
                        $query2->whereNull ( "END_DATE" )->orWhereDate( "END_DATE", '>=', $endDate );
                    });
                    $cQuery->union($c2Query);
                }
            }
            $cfgConfigs = $cQuery->orderBy("EFFECTIVE_DATE","desc")->get();
        }

        $properties = [];
        foreach($cfgConfigs as $cfgConfig){
            $configId = $cfgConfig?$cfgConfig->ID:null;
            if($configId) {
                $effectiveDate = $cfgConfig->EFFECTIVE_DATE;
                $endDate = $cfgConfig->END_DATE;
                $dateKey = ($effectiveDate?$effectiveDate->toDateString():0)."_".($endDate?$endDate->toDateString():0);
                $properties[$dateKey] = $this->getOriginProperties($dcTable,$configId);
            }
        }
        return $properties;
    }
    
    public function getFirstProperty($dcTable){
    	return  ['data'=>$dcTable,'title'=>'Object name','width'=>230];
    }
    
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date,$properties) {
		return [];
	}
	
	public function getExtraDataSet($dataSet, $bunde = []){
		$extraDataSet = [];
		if ($dataSet
			&&$dataSet->count()>0
			&&$this->extraDataSetColumns
			&&is_array($this->extraDataSetColumns)
			&&count($this->extraDataSetColumns)>0) {
				
			foreach($this->extraDataSetColumns as $column => $extraDataSetColumn){
				$extraDataSet[$column] = $this->getExtraEntriesBy($column,$extraDataSetColumn,$dataSet,$bunde);
			}
		}
		return $extraDataSet;
	}
	
	public function getModelName($mdlName,$postData) {
		return $mdlName;
	}
	
	public function save(Request $request){
		//     	sleep(2);
		//     	return response()->json('[]');
// 				throw new \Exception("not Save");
		$postData 	= $request->all();
		$result 	= $this->doSave($postData,true);
		if (is_string($result)&&$result) 
			return response($result, 400);
		else 
			return response()->json($result);
	}

	public function doSaveRecalculating(){
		
	}
	
    public function doSave($postData,$returnData = false){
    	if (!array_key_exists('editedData', $postData)&&!array_key_exists('deleteData', $postData))
    		return ['Saving completed without any change !'];
    	$editedData = array_key_exists('editedData', $postData)?$postData['editedData']:false;
    	$deleteData = array_key_exists('deleteData', $postData)?$postData['deleteData']:false;
    	 
     	$facility_id = null;
     	if (array_key_exists('Facility',  $postData)){
     		$facility_id = $postData['Facility'];
     	}
     	
     	$occur_date = null;
     	if (array_key_exists('date_begin',  $postData)){
     		$occur_date = $postData['date_begin'];
 			$occur_date 	= \Helper::parseDate($occur_date);
     	}
     	
     	$affectedIds = [];
     	$this->preSave($editedData,$affectedIds,$postData);
//      	throw new \Exception("not Save");
     	try
     	{
     		$resultTransaction = \DB::transaction(function () use ($postData,$editedData,$affectedIds,
													     		 $occur_date,$facility_id){
     			$this->deleteData($postData);
//      			throw new \Exception("test exception");
     			$objectIds	= array_key_exists('objectIds',  $postData)?$postData['objectIds']:[];
     			$objectIds	= $objectIds?$objectIds:[];
     			if(!$editedData&&count($objectIds)<=0) return [];
     			
     			$lockeds					= [];
     			$ids 						= [];
     			$resultRecords 				= [];
     			$shouldRefreshAttributes	= [];
     			
     			//      			\DB::enableQueryLog();
     			if ($editedData) {
     				$editedData = $this->sortByModel($editedData);
     				$affectColumns = [];
	     			foreach($editedData as $mdlName => $mdlData ){
	     				if (!is_array($mdlData)) continue;
	     				$modelName = $this->getModelName($mdlName,$postData);
	 		     		$mdl = "App\Models\\".$modelName;
			     		if ($mdl::$ignorePostData) {
			     			unset($editedData[$mdlName]);
			     			continue;
			     		}
			     		$ids[$mdlName] = [];
			     		$resultRecords[$mdlName] = [];
			     		$tableName = $mdl::getTableName();
			     		$locked = \Helper::checkLockedTable($tableName,$occur_date,$facility_id);
			     		if ($locked) {
			     			$lockeds[$mdlName] = "Data of $modelName with facility $facility_id was locked to {$locked->LOCK_DATE} ";
			     			unset($editedData[$mdlName]);
			     			continue;
			     		}
			     		
			     		$objectWithformulas = [];
			     		$ignoredFormulas 	= [];
			     		
			     		foreach($mdlData as $key => $newData ){
			     			$columns 			= $mdl::getKeyColumns($newData,$occur_date,$postData);
			     			if (!$columns||count($columns)<=0) continue;
			     			$originNewData		= $mdlData[$key];
			     			$returnRecord 		= $mdl::updateOrCreateWithCalculating($columns, $newData);
			     			if ($returnRecord) {
		 		     			$mdlData[$key] 		= $newData;
			     				$affectRecord 	= $returnRecord->updateDependRecords($occur_date,$originNewData,$postData);
			     				$returnRecord->updateAudit($columns,$newData,$postData);
				     			$ids[$mdlName][] = $returnRecord['ID'];
				     			$resultRecords[$mdlName][] = $returnRecord;
				     			if ($affectRecord) {
				     				$ids[$mdlName][] = $affectRecord['ID'];
				     				$resultRecords[$mdlName][] = $affectRecord;
				     			}
			     				//formula in formula table
				     			if ($this->isApplyFormulaAfterSaving) {
					     			$columns 							= array_keys($newData);
					     			$uColumns 							= $mdl::getKeyColumns($newData,$occur_date,$postData);
					     			$uColumns 							= array_keys($uColumns);
					     			$columns 							= array_diff($columns, $uColumns);
					     			list($aFormulas,$aIgnoredFormulas) 	= $this->getAffectedObjects($mdlName,$columns,$newData,$occur_date);
					     			$objectWithformulas 				= array_merge($objectWithformulas,$aFormulas);
					     			$ignoredFormulas 					= array_merge($ignoredFormulas,$aIgnoredFormulas);
				     			} 
			     			}
			     			else{
			     				unset($mdlData[$key]);
			     			}
			     		}
			     		
			     		$editedData[$mdlName] = $mdlData;
			     		
			     		if ($this->isApplyFormulaAfterSaving && count($objectWithformulas)>0) {
			     			//get affected object with id
			     			$objectWithformulas = array_unique($objectWithformulas);
			     			//remove formula where column has input value
			     			/* if(count($ignoredFormulas)>0){
				     			foreach($objectWithformulas as $fIndex => $formula){
				     				foreach($ignoredFormulas as $iFormula)
				     					if ($formula->ID==$iFormula->ID) unset($objectWithformulas[$fIndex]);
				     			}
			     			} */
			     			 
			     			usort($objectWithformulas, function ($a, $b) {
			     				$order		= config('database.default')==='oracle'?"ORDER_":"ORDER";
			     				if (is_null($a->$order)) return -1;
			     				if (is_null($b->$order)) return 1;
			     				return ($a->$order - $b->$order);
			     			});
			     		
		     				//apply Formula in formula table
		     				$applieds = \FormulaHelpers::applyAffectedFormula($objectWithformulas,$occur_date);
		     				if ($applieds&&count($applieds)) {
		     					foreach($applieds as $apply ){
		     						$mdlNameA = "{$apply->modelName}";
		     						if (!array_key_exists($mdlNameA, $ids)) {
		     							$ids[$mdlNameA] = [];
		     						}
		     						$ids[$mdlNameA][] = $apply->ID;
		     						$ids[$mdlNameA]  = array_unique($ids[$mdlNameA]);
		     						$resultRecords[$mdlNameA][] = $apply;
// 		     						$resultRecords[$mdlNameA]  = array_unique($resultRecords[$mdlNameA]);
		     					}
		     				}
			     		}
			     		
			     		//formula in field config
	     				if (is_array($mdlData)){
	     					$cls  = \FormulaHelpers::doFormula($modelName,'ID',$ids[$mdlName]);
	     					if (is_array($cls)&&count($cls)>0) {
	     						$affectColumns[$mdlName] = $cls;
	     						$shouldRefreshAttributes[$mdlName] = true;
	     					}
	     				}
	     			}
	     			
			     	foreach($resultRecords as $mdlName => $records ){
			     		$shouldRefresh	= 	array_key_exists($mdlName, $shouldRefreshAttributes)&&$shouldRefreshAttributes[$mdlName]?
			     							$shouldRefreshAttributes[$mdlName]:false;
			     		foreach($records as $key => $returnRecord ){
			     			if ($shouldRefresh) {
			     				$returnRecord	= $returnRecord->refresh();
			     				$records[$key]	= $returnRecord;
			     			}
							if($returnRecord){
								$returnRecord->afterSaving($postData);
								$shouldRefreshAttributes[$mdlName] = false;
							}
			     		}
			     	}
			     	
			     	$this->afterSave($resultRecords,$occur_date);
			     	
			     	foreach($resultRecords as $mdlName => $records ){
				     	if ($this->enableReformular($mdlName,$records)&&array_key_exists($mdlName, $ids)) {
				     		$cls  = \FormulaHelpers::doFormula($modelName,'ID',$ids[$mdlName]);
				     		if (is_array($cls)&&count($cls)>0) {
				     			$returnRecord	= $returnRecord->refresh();
				     			$records[$key]	= $returnRecord;
				     		}
				     	}
			     	}
     			}
		     	
		     	$resultTransaction = [];
		     	if (count($lockeds)>0) {
			     	$resultTransaction['lockeds'] = $lockeds;
		     	}
	     		$resultTransaction['ids']	= $ids;
// 	     		throw new \Exception("rollback save");
 		     	return $resultTransaction;
	      	});
     	}
     	catch (\Exception $e){
      		\Log::info("\n------------------------------------------------------------------------------------------\nException wher run transation\n ");
      		if (!$e) $e = new \Exception("error when save data");
      		\Log::info($e->getMessage());
      		\Log::info($e->getTraceAsString());
      		if ($e instanceof \Illuminate\Database\QueryException && $e->getCode() == 22001)
      			$errorText = "You've inputted too long value. Please try with shorter one !";
      		else
      			$errorText = $e->getMessage();
 			return $errorText;
// 			throw $e;
     	}
     	
     	if ($editedData&&$returnData){
     		$tableData						= $this->loadMultiTableData($resultTransaction,$postData,$editedData);
     		$tableData["resultTransaction"]	= $resultTransaction;
     		return $tableData;
     	}
     	else if ($deleteData&&$returnData) {
     		$tableData						= $this->loadMultiTableData($resultTransaction,$postData,$deleteData);
     		$tableData["resultTransaction"]	= $resultTransaction;
     		return $tableData;
     	}
     	else if (($editedData ||$deleteData) && !$returnData){
     		return $resultTransaction;
     	}
     	else
     		return "empty post data";
    }
    
    protected function enableReformular($mdlName,$records) {
    	return false;
    }
    
    
    protected function sortByModel($editedData) {
     	ksort($editedData);
    	return $editedData;
    }
    
	protected function buildQuery($model, $occur_date, $facility_id, $postData) {
		$mdl 	= (strpos($model, 'App\Models')=== false)?'App\Models\\' . $model:$model;
		$table 	= $mdl::getTableName ();
		$query 	= $mdl::where ( function ($query1) use($occur_date, $table) {
			$query1->whereNull ( "$table.EFFECTIVE_DATE" )->orWhereDate ( "$table.EFFECTIVE_DATE", '<=', $occur_date );
		} )->where ( function ($query2) use($occur_date, $table) {
			$query2->whereNull ( "$table.EXPIRE_DATE" )->orWhereDate( "$table.EXPIRE_DATE", '>', $occur_date );
		} );
		return $query;
	}
    
    public function loadMultiTableData($resultTransaction,$postData,$editedData) {
    	$dataSets		= [];
	    foreach($editedData as $mdlName => $mdlData ){
	     	$postData[config("constants.tabTable")]	= $mdlName;
		   	$dataSets[]	= $this->loadTableData($postData);
	    }
     	$results 		= ['postData'		=> $postData,
     						"dataSets"		=> $dataSets
     	];
    	return $results;
    }
    
    protected function loadUpdatedData($resultTransaction,$postData,$editedData) {
     	$updatedData = [];
     	if (array_key_exists('ids', $resultTransaction)) {
	     	foreach($resultTransaction['ids'] as $mdlName => $updatedIds ){
		     	$modelName = $this->getModelName($mdlName,$postData);
	     		$mdl = "App\Models\\".$modelName;
	     		$updatedData[$mdlName] = $mdl::findManyWithConfig($updatedIds);
	     	}
     	}
     	return ['updatedData'		=> $updatedData];
    }
    
    protected function deleteData($postData) {
    	if (array_key_exists ('deleteData', $postData )) {
    		$deleteData = $postData['deleteData'];
    		foreach($deleteData as $mdlName => $mdlData ){
    			$mdl = "App\Models\\".$mdlName;
    			$mdl::deleteWithConfig($mdlData);
    		}
    	}
    }
    
    
	protected function preSave(&$editedData, &$affectedIds, $postData) {
		return;
		if ($editedData) {
			if ($this->fdcModel&&array_key_exists ($this->fdcModel, $editedData )) {
				$this->preSaveModel ( $editedData, $affectedIds, $this->valueModel,$this->fdcModel);
// 				$this->preSaveModel ( $editedData, $affectedIds, $this->theorModel,$this->fdcModel);
				
				$new_value 	= $editedData[$this->fdcModel];
				unset($editedData[$this->fdcModel]);
				$editedData = [$this->fdcModel => $new_value] + $editedData;
			}
			
			if ($this->valueModel&&array_key_exists ($this->valueModel, $editedData )) {
				$this->preSaveModel ( $editedData, $affectedIds, $this->theorModel,$this->valueModel);
				$new_value 	= $editedData[$this->valueModel];
				unset($editedData[$this->valueModel]);
				$editedData = [$this->valueModel => $new_value] + $editedData;
			}
		}
	}
	
    protected function afterSave($resultRecords,$occur_date) {
	}
	
	protected function getAffectedObjects($mdlName,$columns,$newData,$occur_date){
		$mdl = "App\Models\\".$mdlName;
		$idField = $mdl::$idField;
		$objectId = is_array($newData)? $newData [$idField]:$newData;
		if(!$objectId) return [];
		
		$flowPhase = $this->getFlowPhase($newData);
		return \FormulaHelpers::getAffects( $mdlName, $columns, $objectId,$occur_date,$flowPhase);
	}
	
	protected function getFlowPhase($newData) {
		return false;
	}
	
    
    public function getUoms($properties = null,$facility_id,$dcTable=null,$locked = false,$postData = null)
    {
    	$uoms = [];
    	$model = null;
    	$withs = [];
    	$i = 0;
    	$selectData = false;
    	$rs = [];
    	 
    	foreach($properties as $property ){
    		$columnName = is_array($property)&&array_key_exists('data', $property)?$property['data']:$property->data;
    		switch ($columnName){
    			case 'PRESS_UOM' :
    				$withs[] = 'CodePressUom';
    				$uoms[] = ['id'=>'CodePressUom','targets'=>$i,'COLUMN_NAME'=>'PRESS_UOM'];
    				break;
    			case 'TEMP_UOM' :
    				$withs[] = 'CodeTempUom';
    				$uoms[] = ['id'=>'CodeTempUom','targets'=>$i,'COLUMN_NAME'=>'TEMP_UOM'];
    				break;
    			case 'FL_POWR_UOM' :
    			case 'EU_POWR_UOM' :
    				$withs[] = 'CodePowerUom';
    				$uoms[] = ['id'=>'CodePowerUom','targets'=>$i,'COLUMN_NAME'=>'FL_POWR_UOM'];
    				break;
    			case 'FL_ENGY_UOM' :
    			case 'EU_ENGY_UOM' :
	    			$withs[] = 'CodeEnergyUom';
	    			$uoms[] = ['id'=>'CodeEnergyUom','targets'=>$i,'COLUMN_NAME'=>'FL_ENGY_UOM'];
	    			break;
	    		case 'FL_MASS_UOM' :
	    		case 'EU_MASS_UOM' :
		    		$withs[] = 'CodeMassUom';
		    		$uoms[] = ['id'=>'CodeMassUom','targets'=>$i,'COLUMN_NAME'=>'FL_MASS_UOM'];
		    		break;
		    	case 'MMR_QTY_UOM' :
		    	case 'VOL_UOM' :
		    	case 'FL_VOL_UOM' :
		    	case 'EU_VOL_UOM' :
	    			$withs[] = 'CodeVolUom';
	    			$uoms[] = ['id'=>'CodeVolUom','targets'=>$i,'COLUMN_NAME'=>'FL_VOL_UOM'];
	    			break;
    			case 'EU_STATUS' :
    				$selectData = ['id'=>'EuStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
				/*
    				$selectData['data'] = collect([
													(object)['ID' =>	-1	,'NAME' => '(Auto)'    ],
													(object)['ID' =>	1	,'NAME' => 'Online'    ],
													(object)['ID' =>	0	,'NAME' => 'Offline'   ],
												]);
				*/
    				$selectData['data'] = CodeStatus::loadActive();
    				$rs[] = $selectData;
    				break;
    			case 'CHOKE_UOM' :
	    			$selectData = ['id'=>'choke_uom','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = collect([
											(object)['ID' => 1, 'NAME' => '%'],
											(object)['ID' => 2, 'NAME' => '/64'],
										]);
	    			$rs[] = $selectData;
	    			break;
    			case 'ALLOC_TYPE' :
	    				$selectData = ['id'=>'CodeAllocType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = CodeAllocType::loadActive();
	    				$rs[] = $selectData;
	    				break;
    			case 'ATTRIBUTE' :
	    				$selectData = ['id'=>'PdCodeContractAttribute','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = \App\Models\PdCodeContractAttribute::loadActive();
	    				$rs[] = $selectData;
	    				break;
    			case 'TEST_METHOD' :
    					$selectData = ['id'=>'CodeTestingMethod','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeTestingMethod::loadActive();
    					$rs[] = $selectData;
    					break;
    			case 'TEST_USAGE' :
    					$selectData = ['id'=>'CodeTestingUsage','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeTestingUsage::loadActive();
    					$rs[] = $selectData;
    					break;
	    		case 'EVENT_TYPE' :
		    			$selectData = ['id'=>'CodeEventType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    			$selectData['data'] = CodeEventType::loadActive();
		    			$rs[] = $selectData;
		    			break;
	    		case 'ELEMENT_TYPE' :
		    			$selectData = ['id'=>'QltyProductElementType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    			$selectData['data'] = QltyProductElementType::loadActive();
		    			$rs[] = $selectData;
		    			break;
    			case 'SRC_TYPE' :
	    				$selectData = ['id'=>'CodeQltySrcType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = CodeQltySrcType::loadActive();
	    				$rs[] = $selectData;
	    				break;
				case 'PRODUCT_TYPE' :
		    		$selectData = ['id'=>'CodeProductType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = CodeProductType::loadActive();
		    		$rs[] = $selectData;
		    		break;
		    	case 'SAMPLE_TYPE' :
		    		$selectData = ['id'=>'CodeSampleType','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = CodeSampleType::loadActive();
		    		$rs[] = $selectData;
		    		break;
	    		case 'DEFER_REASON' :
	    			$selectData = ['id'=>'CodeDeferReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeDeferReason::loadActive();
	    			$rs[] = $selectData;
		    		break;
	    		case 'PLANNED' :
	    			$selectData = ['id'=>'CodeDeferPlan','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeDeferPlan::loadActive();
	    			$rs[] = $selectData;
	    			break;
	    		case 'DEFER_STATUS' :
	    			$selectData = ['id'=>'CodeDeferStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeDeferStatus::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'CODE1' :
    				$selectData = ['id'=>'CodeDeferCode1','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeDeferCode1::loadActive();
    				$rs[] = $selectData;
    				break;
    			case 'DEFER_CATEGORY' :
    				$selectData = ['id'=>'CodeDeferCategory','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeDeferCategory::loadActive();
    				$rs[] = $selectData;
    				break;
    			case 'DEFER_GROUP_TYPE' :
    				$selectData = ['id'=>'CodeDeferGroupType','targets'=>$i,'COLUMN_NAME'=>$columnName]; 
    				$selectData['data'] = CodeDeferGroupType::loadActive($facility_id);
    				$rs[] = $selectData;
    				break;
    			case 'THEOR_METHOD' :
	    			$selectData = ['id'=>'CodeDeferTheorMethod','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeDeferTheorMethod::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'TICKET_TYPE' :
	    			$selectData = ['id'=>'CodeTicketType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeTicketType::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			/* case 'TARGET_TANK' :
    				$selectData = ['id'=>'Tank','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = Tank::where('FACILITY_ID', $facility_id)->get();
    				$rs[] = $selectData;
    				break; */
    			case 'CARRIER_ID' :
    			case 'PD_TRANSIT_CARRIER_ID' :
    			case 'CONNECTING_CARRIER' :
    				if ($dcTable==\App\Models\PdCargoNomination::getTableName()&&!$locked) break;
    				$selectData = ['id'=>'PdTransitCarrier','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				if ($dcTable==\App\Models\RunTicketFdcValue::getTableName()
    						||$dcTable==\App\Models\RunTicketValue::getTableName()) 
    					$selectData['data'] = PdTransitCarrier::where('TRANSIT_TYPE',1)->get();
    				else
    					$selectData['data'] = PdTransitCarrier::all();
    				$rs[] = $selectData;
    				break;										
    			case 'BA_ID' :
    			case 'MMR_APPROVED_ID' :
				case 'MMR_ORIGINATED_ID' :
				case 'INSPECTOR_NAME' :
    				if ($dcTable!=Personnel::getTableName()) {
	    				$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    				$selectData['data'] = BaAddress::all();
	    				$rs[] = $selectData;
    				}
    				break;
    			case 'SEVERITY_ID' :
    				$selectData = ['id'=>'CodeSafetySeverity','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeSafetySeverity::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
    			case 'OFFLINE_REASON_CODE' :
    				$selectData = ['id'=>'CodeEqpOfflineReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeEqpOfflineReason::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
	    		case 'EQP_FUEL_CONS_TYPE' :
	    			$selectData = ['id'=>'CodeEqpFuelConsType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeEqpFuelConsType::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
	    			$rs[] = $selectData;
	    			break;
    			case 'EQP_GHG_REL_TYPE' :
    				$selectData = ['id'=>'CodeEqpGhgRelType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeEqpGhgRelType::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
    				break;
    			case 'EQP_GHG_UOM' :
				case 'EQP_CONS_UOM' :
				case 'TCV_UOM' :
				case 'TOV_UOM' :					
    				$selectData = ['id'=>'CodeVolUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeVolUom::loadActive();
    				$rs[] = $selectData;
    				break;
    			case 'TYPE' :
    				$selectData = ['id'=>'CodePersonnelType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodePersonnelType::loadActive();
    				$rs[] = $selectData;
    				break;
		    	case 'TITLE' :
		    		$selectData = ['id'=>'CodePersonnelTitle','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = CodePersonnelTitle::loadActive();
		    		$rs[] = $selectData;
		    		break;
	    		case 'SYSTEM_ID' :
	    			$selectData = ['id'=>'IntSystem','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = IntSystem::all();
	    			$rs[] = $selectData;
	    			break;
				case 'INJECTION_POINT_ID' :
					$selectData = ['id'=>'KeystoreInjectionPoint','targets'=>$i,'COLUMN_NAME'=>$columnName];
					$selectData['data'] = \App\Models\KeystoreInjectionPoint::all();
					$rs[] = $selectData;
					break;
				case 'FREQUENCY' :
    				$selectData = ['id'=>'CodeReadingFrequency','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeReadingFrequency::loadActive();
    				$rs[] = $selectData;
    				break;
    			case 'ALLOW_OVERRIDE' :
    				$selectData = ['id'=>'CodeBoolean','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = CodeBoolean::loadActive();
    				$rs[] = $selectData;
    				break;
	    		case 'FLOW_PHASE' :
    			case 'PHASE_TYPE' :
	    			$selectData = ['id'=>'CodeFlowPhase','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = CodeFlowPhase::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'REQUEST_UOM' 		:
    			case 'NOMINATION_UOM' 	:
    			case 'REQUEST_QTY_UOM' 	:
    			case 'SCHEDULE_UOM' 	:
    			case 'ATTRIBUTE_UOM' 	:
    			case 'LOAD_UOM' 		:
    			case 'QTY_UOM' 			:
    			case 'ITEM_UOM' 		:
    				$selectData = ['id'=>'PdCodeMeasUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeMeasUom::loadActive();
    				$rs[] = $selectData;
    				break;
	    		case 'PRIORITY' :
	    			$selectData = ['id'=>'PdCodeCargoPriority','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeCargoPriority::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'QUANTITY_TYPE' :
    				$selectData = ['id'=>'PdCodeCargoQtyType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeCargoQtyType::loadActive();
    				$rs[] = $selectData;
    				break;
	    		case 'LIFTING_ACCT' :
	    		case 'LIFTING_ACCOUNT' :
	    			$selectData = ['id'=>'PdLiftingAccount','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdLiftingAccount::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CONTRACT_ID' :
    				$selectData = ['id'=>'PdContract','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdContract::all();
    				$rs[] = $selectData;
    				break;
	    		case 'STORAGE_ID' :
	    			$selectData = ['id'=>'Storage','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\Storage::where('FACILITY_ID', $facility_id)->get();
	    			$rs[] = $selectData;
	    			break;
    			case 'REQUEST_TOLERANCE' :
    				$selectData = ['id'=>'PdCodeQtyAdj','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeQtyAdj::loadActive();
    				$rs[] = $selectData;
    				break;
    			case 'ADJUSTABLE_TIME' :
    			case 'NOMINATION_ADJ_TIME' :
    				$selectData = ['id'=>'PdCodeTimeAdj','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeTimeAdj::loadActive();
    				$rs[] = $selectData;
    				break;
		    	case 'INCOTERM' :
		    		$selectData = ['id'=>'PdCodeIncoterm','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = \App\Models\PdCodeIncoterm::loadActive();
		    		$rs[] = $selectData;
		    		break;
	    		case 'TRANSIT_TYPE' :
	    			$selectData = ['id'=>'PdCodeTransitType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeTransitType::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			/* case 'ACTIVITY_NAME' :
    				$selectData = ['id'=>'ID','targets'=>$i,'COLUMN_NAME'=>'NAME'];
    				$sql = "";
    				$sql .= " SELECT ID, NAME FROM pd_code_load_activity a where exists (select 1 from PD_CARGO_LOAD b join TERMINAL_TIMESHEET_DATA  c ON ( b.ID = c.PARENT_ID AND c.IS_LOAD = 1 ) where c.ACTIVITY_ID = a.ID)";
					$sql .= " union all";
					$sql .= " SELECT ID, NAME FROM pd_code_load_activity a where exists (select 1 from PD_CARGO_UNLOAD b join TERMINAL_TIMESHEET_DATA  c ON ( b.ID = c.PARENT_ID AND c.IS_LOAD = 1 ) where c.ACTIVITY_ID = a.ID)"; 

					$tmp = \DB::select($sql);					
    				$selectData['data'] = $tmp;
    				$rs[] = $selectData;
    				break; */
    			case 'CARGO_ID' :
    				$selectData = ['id'=>'PdCargo','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCargo::all();
    				$rs[] = $selectData;
    				break;
	    		case 'BERTH_ID' :
	    			$selectData = ['id'=>'PdBerth','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdBerth::all();
	    			$rs[] = $selectData;
	    			break;
				case 'COMPARTMENT_ID' :
					$voyageId = $postData['id'];
					$sql = "select a.ID, a.NAME from PD_COMPARTMENT a, PD_VOYAGE b where a.CARRIER_ID=b.CARRIER_ID and b.ID=$voyageId";
					//$selectData = ['id'=>'PdCompartment','targets'=>$i,'COLUMN_NAME'=>$columnName];
					//$selectData['data'] = \App\Models\PdCompartment::join('PD_VOYAGE')->where('CARRIER_ID', $postData['CARRIER_ID'])->get();
					$rs[] = \DB::select($sql);
					break;
				case 'CARGO_STATUS' :
    				$selectData = ['id'=>'PdCodeCargoStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeCargoStatus::loadActive();
    				$rs[] = $selectData;
    				break;
	    		case 'CONTRACT_TYPE' :
	    		case 'CONTACT_TYPE' :
	    			$selectData = ['id'=>'PdCodeContractType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeContractType::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'CONTRACT_PERIOD' :
    				$selectData = ['id'=>'PdCodeContractPeriod','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeContractPeriod::loadActive();
    				$rs[] = $selectData;
    				break;
	    		case 'CONTRACT_EXPENDITURE' :
	    			$selectData = ['id'=>'PdContractExpenditure','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdContractExpenditure::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'CONTRACT_TEMPLATE' :
    				$selectData = ['id'=>'PdContractTemplate','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdContractTemplate::all();
    				$rs[] = $selectData;
    				break;
	    		case 'DEMURRAGE_EBO' :
	    			$selectData = ['id'=>'PdCodeDemurrageEbo','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeDemurrageEbo::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'SURVEYOR_BA_ID' :
    				$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\BaAddress::where('SOURCE',15)->get();
    				$rs[] = $selectData;
    				break;
	    		case 'WITNESS_BA_ID1' :
				case 'WITNESS_BA_ID2' :
	    			$selectData = ['id'=>'BaAddress','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\BaAddress::where('SOURCE',4)->get();
	    			$rs[] = $selectData;
	    			break;
    			case 'ACTIVITY_ID' :
				case 'ACTIVITY_NAME' :
					$selectData = ['id'=>'PdCodeLoadActivity','targets'=>$i,'COLUMN_NAME'=>$columnName];
					$selectData['data'] = \App\Models\PdCodeLoadActivity::loadActive();
					$rs[] = $selectData;
					break;
				case 'VOYAGE_ID' :
		    		$selectData = ['id'=>'PdVoyage','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = \App\Models\PdVoyage::all();
		    		$rs[] = $selectData;
		    		break;
	    		case 'DEPART_PORT' :
	    		case 'NEXT_DESTINATION_PORT' :
    			case 'PORT_ID' :
    			case 'ULLAGE_PORT' :
    			case 'ORIGIN_PORT' :
    			case 'DESTINATION_PORT' :
	    			$selectData = ['id'=>'PdPort','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdPort::all();
	    			$rs[] = $selectData;
	    			break;
    			case 'FLOW_ID' :
    				if ($dcTable==\App\Models\RunTicketFdcValue::getTableName()
    						||$dcTable==\App\Models\RunTicketValue::getTableName())  break;
    						
    			    if ($dcTable == \App\Models\EmissionIndirectDataValue::getTableName()){
                        $codeReadingFrequencyId     = $postData['CodeReadingFrequency']; // RECORD_FREQUENCY
                        $codeFlowPhaseId            = $postData['CodeFlowPhase']; // PHASE_ID
                        $selectData                 = ['id'=>'Flow','targets'=>$i,'COLUMN_NAME'=>$columnName];
                        $where                      = ["FACILITY_ID" => $facility_id];
                        if ($codeReadingFrequencyId>0) $where["RECORD_FREQUENCY"] = $codeReadingFrequencyId;
                        if ($codeFlowPhaseId>0) $where["PHASE_ID"] = $codeFlowPhaseId;
                        $selectData['data'] = \App\Models\Flow::where($where)->get();
                        $rs[] = $selectData;
                    }
					else if ($dcTable == \App\Models\FlowDataValueSubday::getTableName()){
                        $codeReadingFrequencyId     = $postData['CodeReadingFrequency']; // RECORD_FREQUENCY
                        $codeFlowPhaseId            = $postData['CodeFlowPhase']; // PHASE_ID
                        $selectData                 = ['id'=>'Flow','targets'=>$i,'COLUMN_NAME'=>$columnName];
                        $where                      = ["FACILITY_ID" => $facility_id];
                        if ($codeReadingFrequencyId>0) $where["RECORD_FREQUENCY"] = $codeReadingFrequencyId;
                        if ($codeFlowPhaseId>0) $where["PHASE_ID"] = $codeFlowPhaseId;
                        $selectData['data'] = \App\Models\Flow::where($where)->get();
                        $rs[] = $selectData;
                    }
					else {
                        $selectData = ['id'=>'Flow','targets'=>$i,'COLUMN_NAME'=>$columnName];
                        $selectData['data'] = \App\Models\Flow::where("FACILITY_ID",'=',$facility_id)->get();
                        $rs[] = $selectData;
                    }
    				break;
	    		case 'MEASURED_ITEM' :
	    			$selectData = ['id'=>'PdCodeMeasItem','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeMeasItem::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'FORMULA_ID' :
					if($dcTable==\App\Models\IntTagMapping::getTableName()){
						$selectData = ['id'=>'Formula','targets'=>$i,'COLUMN_NAME'=>$columnName];
						$selectData['data'] = \App\Models\Formula::select('ID','NAME')->get();
						$rs[] = $selectData;
					}
					else{
						$selectData = ['id'=>'Formula','targets'=>$i,'COLUMN_NAME'=>$columnName];
						$selectData['data'] = \App\Models\Formula::where("GROUP_ID",'=',7)->get();
						$rs[] = $selectData;
					}
    				break;
	    		case 'PROGRAM_TYPE' :
	    			$selectData = ['id'=>'PdCodeProgramType','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeProgramType::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'RUN_FREQUENCY' :
    				$selectData = ['id'=>'PdCodeRunFrequency','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\PdCodeRunFrequency::loadActive();
    				$rs[] = $selectData;
    				break;
	    		case 'ADJUST_CODE' :
	    			$selectData = ['id'=>'PdCodeLiftAcctAdj','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\PdCodeLiftAcctAdj::loadActive();
	    			$rs[] = $selectData;
	    			break;
	    		case 'OBJECT_TYPE' :
	    		    if ($dcTable == \App\Models\EmissionEventDataValue::getTableName()){
                        $selectData = ['id'=>'CodeQltySrcType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                        $selectData['data'] = \App\Models\CodeQltySrcType::loadActive();
                        $rs[] = $selectData;
                    }else{
                        $selectData = ['id'=>'IntObjectType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                        $selectData['data'] = \App\Models\IntObjectType::loadActive();
                        $rs[] = $selectData;
	    		    }
	    			break;
	    		case 'MMR_STATUS' :
	    			$selectData = ['id'=>'CodeMmrStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrStatus::loadActive();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_CLASS' :
	    			$selectData = ['id'=>'CodeMmrClass','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrClass::loadActive();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_REASON' :
	    			$selectData = ['id'=>'CodeMmrReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrReason::loadActive();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_ROOT_CAUSE' :
	    			$selectData = ['id'=>'CodeMmrRootCause','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrRootCause::loadActive();
	    			$rs[] = $selectData;
	    			break;
	    		case 'MMR_CALC_METHOD_FORMULA':
	    			$selectData = ['id'=>'CodeMmrCalcMethod','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrCalcMethod::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'WO_ACTION' :
	    			$selectData = ['id'=>'CodeMmrWOAction','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\CodeMmrWOAction::loadActive();
	    			$rs[] = $selectData;
	    			break;
    			case 'COMPOSITION' :
    				$selectData = ['id'=>'QltyProductElementType','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$selectData['data'] = \App\Models\QltyProductElementType::where(["SAMPLE_TYPE"	=> 2,"ACTIVE"	=> 1])->orderBy("ORDER")->get();
    				$rs[] = $selectData;
    				break;
    			case 'task_group' :
    			case 'TASK_GROUP' :
    				$selectData = ['id'=>'EbFunctions','targets'=>$i,'COLUMN_NAME'=>$columnName];
		    		$selectData['data'] = \App\Models\EbFunctions::loadByCode();
		    		$rs[] = $selectData;
		    		break;
	    		case 'task_code' :
	    		case 'TASK_CODE' :
	    			$selectData = ['id'=>'EbFunctions','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = \App\Models\EbFunctions::loadActiveFunction();
	    			$rs[] = $selectData;
	    			break;
	    		case 'runby' :
	    		case 'RUNBY' :
	    			$selectData = ['id'=>'runby','targets'=>$i,'COLUMN_NAME'=>$columnName];
	    			$selectData['data'] = collect([
											(object)['ID' =>	TmTask::RUN_BY_SYSTEM	,'NAME' => 'System'  ],
											(object)['ID' =>	TmTask::RUN_BY_USER		,'NAME' => 'User'    ],
										]);
	    			$rs[] = $selectData;
	    			break;
    			case 'status' :
    			case 'STATUS' :
    				if ($dcTable==\App\Models\TmTask::getTableName()) {
	    				$selectData = ['id'=>'status','targets'=>$i,'COLUMN_NAME'=>$columnName];
			    		$selectData['data'] = \App\Models\TmTask::loadStatus();
	    				$rs[] = $selectData;
    				}
    				elseif ($dcTable==\App\Models\Environmental::getTableName()) {
    					$selectData = ['id'=>'CodeEnvStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeEnvStatus::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    					$rs[] = $selectData;
    				}
    				else{
    					$selectData = ['id'=>'CodeCommentStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeCommentStatus::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    					$rs[] = $selectData;
    				}
    				break;
    			case 'ENV_STATUS' :
    				if ($dcTable==\App\Models\TmTask::getTableName()) {
	    				$selectData = ['id'=>'status','targets'=>$i,'COLUMN_NAME'=>$columnName];
			    		$selectData['data'] = \App\Models\TmTask::loadStatus();
	    				$rs[] = $selectData;
    				}
    				else{
    					$selectData = ['id'=>'CodeCommentStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
    					$selectData['data'] = CodeCommentStatus::where('ACTIVE',1)->orderBy('ORDER')->orderBy('ID')->get();
    					$rs[] = $selectData;
    				}
    				break;
	    		case 'COMMENT_CATEGORY' :
    				$selectData 		= ['id'=>'CodeCommentCategory','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$where				= ['ACTIVE'	=> 1,'PARENT_ID'	=> $postData['CodeCommentType']];
    				$selectData['data'] = \App\Models\CodeCommentCategory::where($where)
    										->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
	    			break;
	    		case 'ENV_CATEGORY' :
    				$selectData 		= ['id'=>'CodeEnvCategory','targets'=>$i,'COLUMN_NAME'=>$columnName];
    				$where				= ['ACTIVE'	=> 1,'PARENT_ID'	=> $postData['CodeEnvType']];
    				$selectData['data'] = \App\Models\CodeEnvCategory::where($where)
    										->orderBy('ORDER')->orderBy('ID')->get();
    				$rs[] = $selectData;
	    			break;
                // combustion
                case 'EQP_CONS_UOM' :
                    $selectData = ['id'=>'CodeGhgUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeGhgUom::all();
                    $rs[] = $selectData;
                    break;

                case 'EQP_CONS_FUEL_TYPE' :
                    $selectData = ['id'=>'CodeEqpFuelConsType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeEqpFuelConsType::all();
                    $rs[] = $selectData;
                    break;

                case 'EQUIPMENT_ID' :
                    $equipmentGroupId = $postData['EquipmentGroup'];
                    $equipmentTypeId = $postData['CodeEquipmentType'];
                    $selectData = ['id'=>'Equipment','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\Equipment::loadByEquipment($equipmentGroupId, $equipmentTypeId);
                    $rs[] = $selectData;
                    break;
                case 'EQUIP_STATUS' :
                	$selectData = ['id'=>'CodeEquipStatus','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeEquipStatus::loadActive();
                    $rs[] = $selectData;
                	break;
                case 'FACILITY_ID' :
                    $selectData = ['id'=>'Facility','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\Facility::select("ID","CODE","NAME")->get();
                    $rs[] = $selectData;
                    break;

                // combustion select detail
                case 'EMISSION_GAS' :
                    $selectData = ['id'=>'CodeEqpGhgRelType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeEqpGhgRelType::all();
                    $rs[] = $selectData;
                    break;

                case 'GRS_MASS_UOM' :
                    $selectData = ['id'=>'CodeMassUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeMassUom::all();
                    $rs[] = $selectData;
                    break;

                case 'GRS_MASS_CO2E_UOM' :
                    $selectData = ['id'=>'CodeMassUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeMassUom::all();
                    $rs[] = $selectData;
                    break;
                case 'PROTOCOL' :
                    $selectData = ['id'=>'CodeProtocol','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeProtocol::all();
                    $rs[] = $selectData;
                    break;

                case 'GRS_ENGY_UOM' :
                    $selectData = ['id'=>'CodeGhgUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeGhgUom::all();
                    $rs[] = $selectData;
                    break;

                case 'GRS_PWR_UOM' :
                    $selectData = ['id'=>'CodeGhgUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeGhgUom::all();
                    $rs[] = $selectData;
                    break;

                // Events
                case 'EMISSION_VENT_ID' :
                    $selectData = ['id'=>'EmissionVent','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\EmissionVent::all();
                    $rs[] = $selectData;
                    break;

                case 'VENT_REASON' :
                    $selectData = ['id'=>'CodeDeferReason','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeDeferReason::all();
                    $rs[] = $selectData;
                    break;

                case 'GRS_VENT_UOM' :
                    $selectData = ['id'=>'CodeGhgUom','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeGhgUom::all();
                    $rs[] = $selectData;
                    break;

                case 'RECORD_FREQUENCY' :
                    $selectData = ['id'=>'CodeReadingFrequency','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeReadingFrequency::all();
                    $rs[] = $selectData;
                    break;

                // Source Emission
                case 'SOURCE_CATEGORY_ID' :
                    $selectData = ['id'=>'CodeSourceCategory','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeSourceCategory::all();
                    $rs[] = $selectData;
                    break;

                case 'SOURCE_TYPE_ID' :
                    $selectData = ['id'=>'CodeEpaSourceType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeEpaSourceType::all();
                    $rs[] = $selectData;
                    break;

                case 'SECTOR_ID' :
                    $selectData = ['id'=>'CodeSector','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeSector::all();
                    $rs[] = $selectData;
                    break;

                // Child Source Emission
                case 'GHG_PROTOCOL' :
                    $selectData = ['id'=>'CodeProtocol','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeProtocol::select("ID","CODE as NAME")->get();
                    $rs[] = $selectData;
                    break;

                case 'CALC_SECTION_ID' :
                    $selectData = ['id'=>'CodeCalcSection','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeCalcSection::all();
                    $rs[] = $selectData;
                    break;

//                case 'EMISSION_FORMULA_ID' :
//                    $selectData = ['id'=>'EmissionFormula','targets'=>$i,'COLUMN_NAME'=>$columnName];
//                    $selectData['data'] = \App\Models\EmissionFormula::all();
//                    $rs[] = $selectData;
//                    break;

//                case 'EMISSION_FACTOR_TABLE_ID' :
//                    $selectData = ['id'=>'EmissionFactorTable','targets'=>$i,'COLUMN_NAME'=>$columnName];
//                    $selectData['data'] = \App\Models\EmissionFactorTable::all();
//                    $rs[] = $selectData;
//                    break;

                case 'EMISSION_FACTOR_ID' :
                    $selectData = ['id'=>'EmissionFactor','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\EmissionFactor::all();
                    $rs[] = $selectData;
                    break;

                case 'TEST_TYPE' :
                    $selectData = ['id'=>'CodeEuScssvTestType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeEuScssvTestType::all();
                    $rs[] = $selectData;
                    break;
                case 'EU_TEST_NEXT_TYPE' :
                    $selectData = ['id'=>'CodeEuScssvTestType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $selectData['data'] = \App\Models\CodeEuScssvTestType::all();
                    $rs[] = $selectData;
                    break;
                case 'EU_ID' :
                    $selectData = ['id'=>'EnergyUnit','targets'=>$i,'COLUMN_NAME'=>$columnName];
                    $eWhere=[];
                    if ($facility_id>0) {
                    	$eWhere['FACILITY_ID'] = $facility_id ;
                    }
                    $selectData['data'] = \App\Models\EnergyUnit::where($eWhere)->select('ID','NAME')->orderBy('NAME','ASC')->get();
                    $rs[] = $selectData;
                    break;
                case 'POINT_TYPE' :
                   	$selectData = ['id'=>'IntObjectType','targets'=>$i,'COLUMN_NAME'=>$columnName];
                   	$selectData['data'] = IntObjectType::loadBasic();
                   	$rs[] = $selectData;
                   	break;
    		}
    		$i++;
    	}
    	
    	if (count($withs)>0) {
    		$model = StandardUom::with($withs)->where('ID', $facility_id)->first();
	    	if ($model==null) {
		    	$model = Facility::with($withs)->where('ID', $facility_id)->first();
	    	}
    	}
//     	\DB::enableQueryLog();
    	if ($model!=null) {
	    	foreach($uoms as $key => $uom ){
	    		$uom['data'] = $model->{$uom['id']};
	    		$uoms[$key] = $uom;
	    		$rs[] = $uom;
	    	}
    	}
    	return $rs;
//     	\Log::info(\DB::getQueryLog());
    }
    
    public function getUomType($uom_type = null,$facility_id)
    {
    	if ($uom_type==null) {
    		$uom_type = StandardUom::where('facility_id', $facility_id)->select('UOM_TYPE')->first();
    		if ($uom_type==null) {
	    		$uom_type = Facility::where('facility_id', $facility_id)->select('UOM_TYPE')->first();
    		}
    	}
    	return $uom_type;
    }
    
    
    public function preSaveModel(&$editedData,&$affectedIds,$model,$sourceModel) {
    	if ($model) {
	    	$fdcModel = $sourceModel;
	    	if (array_key_exists($fdcModel, $editedData)) {
	    		$idColumn = $this->idColumn;
// 	    		$phaseColumn = $this->phaseColumn;
	    
	    		if (!array_key_exists($model, $editedData)){
	    			$editedData[$model] = [];
	    		}
	    		foreach ($editedData[$fdcModel] as $element) {
	    			$entryIndex = $this->getExistPostEntryIndex($editedData,$model,$element,$idColumn);
	    			if ($entryIndex<0) {
	    				$autoElement = $this->initAutoElement($element);
	    				$editedData[$model][] =  $autoElement;
	    			}
	    			else {
	    				if($this->enableMergeMissData($model,$sourceModel,$editedData,$entryIndex)){
	    					//merge
	    					$mergeEntry		= $this->mergeEntry($editedData[$model][$entryIndex],$element);
	    					$editedData[$model][$entryIndex] = $mergeEntry;
	    				}
	    			}
	    			if ($idColumn&&array_key_exists($idColumn, $element)) $affectedIds[]=$element[$idColumn];
	    		}
	    	}
    	}
    }

    public function enableMergeMissData($model,$sourceModel,$editedData,$entryIndex){
    	return false;
    }
    
    public function initAutoElement($element){
    	$autoElement = array_intersect_key($element, array_flip($this->keyColumns));
    	$autoElement['auto'] = true;
    	if(array_key_exists("attributes", $element)) $autoElement['attributes'] = $element['attributes'];
    	return $autoElement;
    }
    
    public function mergeEntry($mergeEntry,$element){
    	if ($element&&count($element)>0) {
    		foreach ($element as $column => $value){
    			if($column!="ID"&&$column!="id"
    					&&(!array_key_exists($column, $mergeEntry)||in_array($column, $this->keyColumns))) $mergeEntry[$column]= $value;
    		}
    	}
    	return $mergeEntry;
    }
    
    public function getExistPostEntryIndex(&$editedData,$model,$element,$idColumn){
    	$ctvAvailable	= \Helper::checkCTVavailabe($element);
    	$entryIndex		= -1;
    	foreach($editedData[$model] as $key 	=> $item ){
    		if ($this->compareEntryKeys($item,$element)) {
    			if($ctvAvailable)
    				unset($editedData[$model][$key]);
    			else
    				$entryIndex	= $key;
    			break;
    		}
    	}
    	return $entryIndex;
    }
    
    public function getExtraEntriesBy($sourceColumn,$extraDataSetColumn,$dataSet,$bunde=[]){
    	$extraDataSet = [];
    	$subDataSets = $dataSet->groupBy($sourceColumn);
    	if ($subDataSets&&count($subDataSets)>0) {
    		foreach($subDataSets as $key => $subData ){
    			$entry = $subData[0];
    			$sourceColumnValue = $entry->$sourceColumn;
    			$this->putExtraBundle($bunde,$sourceColumn,$entry);
    			$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
    			if ($data) {
    				$extraDataSet[$sourceColumnValue] = $data;
    			}
    		}
//     		$extraDataSet=count($extraDataSet)>0?$extraDataSet:null;
    	}
    	return $extraDataSet;
    }
    
    public function putExtraBundle(&$bunde,$sourceColumn,$entry){
    }
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	return null;
    }
    
    public function loadsrc(Request $request){
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$dataSet = [];
    
    	$targets	= array_key_exists("target", $postData)?$postData["target"]:null;
    	$targets	= $targets&&is_array($targets)&&count($targets)>0?$targets:
    					($this->extraDataSetColumns&&array_key_exists($sourceColumn, $this->extraDataSetColumns)?
    							[$this->extraDataSetColumns[$sourceColumn]]:null);
    	if ($targets) {
		    foreach($targets as $extraDataSetColumn ){
			   	$targetColumn = is_array($extraDataSetColumn)?$extraDataSetColumn['column']:$extraDataSetColumn;
			   	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$postData);
			   	$dataSet[$targetColumn] = [	'data'			=>	$data,
			   			'ofId'			=>	$sourceColumnValue,
			   			'sourceColumn'	=>	$sourceColumn
			   	];
		    }
    	}
    	return response()->json(['dataSet'=>$dataSet,
    			'postData'=>$postData]);
    }
    
    public function help($name){
		\Helper::setGetterUpperCase();
    	$help = EbFunctions::where("CODE",$name)->select("HELP")->first();
    	$help = $help?$help:"";
    	return response()->json($help);
    }
    
    public function getGroupFilter($postData){
    	$filterGroups = array('productionFilterGroup'	=> [],
    			'dateFilterGroup'			=> [],
    			'frequenceFilterGroup'		=> []
    	);
    	
    	return $filterGroups;
    }

    public function getDependentFilterData($postData){
        $facilityId = array_key_exists('Facility', $postData)?$postData["Facility"]:null;
        if ($facilityId){
            $facility = Facility::with(["Area","Area.ProductionUnit"])->where("ID",'=',$facilityId)->first();
            if ($facility){
                $loArea = $facility->Area;
                if ($loArea){
                    $loProductUnit = $loArea->ProductionUnit;
                    if ($loProductUnit){
                        return ['LoProductionUnit'			=> $loProductUnit->ID,
                                'LoArea'			    => $loArea->ID,
                        ];
                    }
                }
            }
        }
        return null;
    }

    public function filter(Request $request){
        $postData 		= $request->all();
    	$filterGroups	= $this->getGroupFilter($postData);
        $formatData     = $this->getDependentFilterData($postData);
        $currentData    = $formatData?array_merge($postData,$formatData):$postData;
    	return view ( $this->editFilterName,
    			['filters'			=> $filterGroups,
     			'prefix'			=> $this->editFilterPrefix,
    			"currentData"		=> $currentData
    	]);
    }
}
