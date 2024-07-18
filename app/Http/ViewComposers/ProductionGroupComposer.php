<?php

namespace App\Http\ViewComposers;

use App\Models\LoProductionUnit;
use App\Models\LoArea;
use App\Models\Facility;
use App\Models\UserDataScope;
use App\Repositories\UserRepository as UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Role;

class ProductionGroupComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $user;
    protected $prefix;
    protected $currentData;
    protected $facility;
    protected $scopeFacilities;
    protected $workspace ;
    
    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users){
        $this->user 			= auth()->user();
        $this->prefix 			= "";
        $this->currentData 		= null;
        $this->scopeFacilities 	= [];
        $this->facility			= 0;
    }

    public static function getInstance($currentData){
    	$user 					= auth()->user();
    	$role 					= new Role;
    	$instance 				= new self(new UserRepository($user,$role));
    	$instance->user 		= $user;
    	$instance->currentData 	= $currentData;
    	return $instance;
    }
    
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view){
    	$fgs 				= $view->filters;
    	$this->prefix		= isset($view->prefix)?$view->prefix:"";
    	$this->currentData	= isset($view->currentData)?$view->currentData:null;
    	$this->workspace 	= $this->user->workspace();
    	$this->checkTaskParams($view,$fgs);
    	$filterGroups		= $this->composeData($fgs);
    	$view->with('filterGroups', $filterGroups);
    	$view->with('facility', $this->facility);
    	$view->with('scopeFacilities', $this->scopeFacilities);
    	$view->with('prefix', $this->prefix);
    }
    
    public function checkTaskParams($view,$fgs){
    	$view->with('shouldUseLastFilter', true);
    	$openTaskId			= \Request::query('open_task_id');
    	if ($openTaskId) {
    		$tmWokflowTask 	= \App\Models\TmWokflowTask::find($openTaskId);
    		if ($tmWokflowTask) {
    			$task_config= $tmWokflowTask->task_config;
    			$taskConfig = json_decode ( $task_config , true );
    			if ($taskConfig&&is_array($taskConfig)) {
    				$view->with('shouldUseLastFilter', false);
    				$this->updateFilterByTaskConfig($taskConfig,"Facility","facility");
    				if (array_key_exists("facility", $taskConfig)) {
    					$this->currentData["LoArea"] 			= LoArea::whereHas('Facility', function ($query) {$query->where('ID', '=', $this->currentData["Facility"]);})->first()->ID;
    					$this->currentData["LoProductionUnit"] 	= LoProductionUnit::whereHas('LoArea',function ($query) {$query->where('ID', '=', $this->currentData["LoArea"]);})->first()->ID;
    				};
    				$this->updateFilterByTaskConfig($taskConfig,"EnergyUnitGroup","eugroup_id");
    				$this->updateFilterByTaskConfig($taskConfig,"CodeFlowPhase","phase_type");
    				$this->updateFilterByTaskConfig($taskConfig,"CodeReadingFrequency","freq");
    				$this->updateFilterByTaskConfig($taskConfig,"CodeEventType","event_type");
    				$this->updateFilterByTaskConfig($taskConfig,"CodeAllocType","alloc_type");
    				$this->updateFilterByTaskConfig($taskConfig,"CodePlanType","plan_type");
    				$this->updateFilterByTaskConfig($taskConfig,"CodeForecastType","forecast_type");
    				$this->updateFilterByTaskConfig($taskConfig,"CodeProductType","product_type");
    				$this->updateFilterByTaskConfig($taskConfig,"EnergyUnit","eu_id");
    				
    				if (array_key_exists("type", $taskConfig)) {
    					if ($taskConfig["type"]=="date") {
		    				if (array_key_exists("from", $taskConfig)) {
		    					$this->currentData["beginDate"]	= Carbon::parse($taskConfig["from"]);
		    				};
		    				if (array_key_exists("to", $taskConfig)) {
		    					$this->currentData["endDate"]	= Carbon::parse($taskConfig["to"]);
		    				};
    					}
    					elseif ($taskConfig["type"]=="day0") {
    						$today = Carbon::now();
		    				$this->currentData["beginDate"]	= $today;
		    				$this->currentData["endDate"]	= $today;
    					}
    					elseif ($taskConfig["type"]=="month0") {
    						$today = Carbon::now()->startOfMonth();
    						$this->currentData["beginDate"]	= $today;
    						$this->currentData["endDate"]	= $today;
    					}
    					elseif ($taskConfig["type"]=="day") {
    						$today = Carbon::now()->subDay();
		    				$this->currentData["beginDate"]	= $today;
		    				$this->currentData["endDate"]	= $today;
    					}
    					elseif ($taskConfig["type"]=="month") {
    						$today = Carbon::now()->startOfMonth()->subMonth();
    						$this->currentData["beginDate"]	= $today;
    						$this->currentData["endDate"]	= $today;
    					}
    				};
    				
    				/*
    				if(task_config['type']=='date'){
    					task_config['from']=$('#txt_from').val();
    					task_config['to']=$('#txt_to').val(); */
    			}
    		}
    	}
    }
    
    public function updateFilterByTaskConfig($taskConfig,$filterName,$param){
    	if (!$this->currentData) $this->currentData = [];
    	if (array_key_exists($param, $taskConfig)) {
    		$this->currentData[$filterName] = $taskConfig[$param];
    	};
    }
    
    public function composeData($fgs){
    	$filterGroups 		= array('enableButton'	=> isset($fgs['enableButton'])?$fgs['enableButton']:true);
    	$workspace 			= $this->workspace;
    	if (array_key_exists('dateFilterGroup', $fgs)) {
    		$dateFilterGroup = $this->initDateFilterGroup($workspace,$fgs['dateFilterGroup']);
    		$filterGroups['dateFilterGroup'] = $dateFilterGroup;
    	}
    	 
    	if (array_key_exists('productionFilterGroup', $fgs)) {
    		$productionFilterGroup = $this->initProductionFilterGroup($workspace,$fgs['productionFilterGroup'],$fgs);
    		$filterGroups['productionFilterGroup'] = $productionFilterGroup;
    	}
    	 
    	$fres = array_key_exists('frequenceFilterGroup', $fgs)?$fgs['frequenceFilterGroup']:array();
    	$frequenceFilterGroup = $this->initFrequenceFilterGroup($fres,$filterGroups);
    	$filterGroups['frequenceFilterGroup'] = $frequenceFilterGroup;
    	return $filterGroups;
    }
    	
    public function initDateFilterGroup($workspace,$extra=null){
    	if ($extra==null) return null;
        if ($this->currentData) {
            $beginDate = array_key_exists('beginDate', $this->currentData)?$this->currentData["beginDate"]:null;
            $endDate = array_key_exists('endDate', $this->currentData)?$this->currentData["endDate"]:null;
            $endDate = $endDate?Carbon::parse($endDate):null;
            $beginDate = $beginDate?Carbon::parse($beginDate):null;
        }
    	else if ($workspace) {
			$new_view = request()->attributes->get('ui_view');
			$old_view = request()->cookies->get('ui_view');
			if($new_view == $old_view){
				$beginDate = $workspace->W_DATE_BEGIN;
				$endDate = $workspace->W_DATE_END;
			}
			else {
				$beginDate 	= Carbon::yesterday();
				$endDate 	= $beginDate;
			}
    	}
    	else{
	    	$beginDate 	= Carbon::yesterday();
	    	$endDate 	= $beginDate;
    	}
		
		//$beginDate 	= Carbon::yesterday();
		//$endDate 	= $beginDate;

    	foreach($extra as $id => $item ){
            $checkItem = $item['id'];
            $checkItem = $checkItem&&strpos($checkItem, 'date_begin') !== false?'date_begin':$checkItem;
            $checkItem = $checkItem&&strpos($checkItem, 'date_end') !== false?'date_end':$checkItem;
    		switch ($checkItem) {
    			case 'date_begin':
    				$item['value'] = $beginDate;
    				break;
                case 'date_end':
    				$item['value'] = $endDate;
    				break;
    			default:
    				$item['value'] = Carbon::now();
    				break;
    		}
    		$extra[$id] = $item;
    	};
    	return $extra;
    }
    
    
    public function initProductionFilterGroup($workspace,$extras=null,$fgs)
    {
    	$enableShowAll = isset($fgs["enableShowAll"]) ? $fgs["enableShowAll"] : false;
    	if ($workspace) {
	    	$pid = $workspace->PRODUCTION_UNIT_ID;
	    	$aid = $workspace->AREA_ID;
	    	$fid = $workspace->W_FACILITY_ID;
    	}
    	else{
    		$pid = 0;
    		$aid = 0;
    		$fid = 0;
    	}
    	
    	if ($this->currentData) {
    		$pid = array_key_exists('LoProductionUnit', $this->currentData)?$this->currentData["LoProductionUnit"]:$pid;
    		$aid = array_key_exists('LoArea', $this->currentData)?$this->currentData["LoArea"]:$aid;
    		$fid = array_key_exists('Facility', $this->currentData)?$this->currentData["Facility"]:$fid;
    	}
    	
    	$currentFacility = $fid&&$fid>0?Facility::find($fid):null;
    	$userDataScope	= UserDataScope::where("USER_ID",$this->user->ID)->first();
    	if ($userDataScope && !$enableShowAll) {
    		$DATA_SCOPE_PU			=$userDataScope->PU_ID;
			$DATA_SCOPE_AREA		=$userDataScope->AREA_ID;
			$DATA_SCOPE_FACILITY	=$userDataScope->FACILITY_ID;
    	}
    	else {
    		$DATA_SCOPE_PU			=null;
    		$DATA_SCOPE_AREA		=null;
    		$DATA_SCOPE_FACILITY	=null;
    	}
    	
    	if($DATA_SCOPE_FACILITY&&$DATA_SCOPE_FACILITY!=""&&$DATA_SCOPE_FACILITY!="0"&&$DATA_SCOPE_FACILITY!=0){
    		$facilityIdStrings		= $DATA_SCOPE_FACILITY;
    		$scopeFacilities		= explode(",", $facilityIdStrings);
	    	$this->scopeFacilities 	= $scopeFacilities;
    		$facilityIdStrings		= str_replace("*", "", $facilityIdStrings);
    		$facilityIds			= explode(",", $facilityIdStrings);
    		$areas 					= LoArea::whereHas('Facility', function ($query) use($facilityIds) {
							    			$query->whereIn('ID',  $facilityIds);
							    		})->orderBy("ORDER")->orderBy("NAME")->get();
							    		
    		$currentFacility 	= in_array($fid, $facilityIds)?$currentFacility:null;
    		if ($currentFacility) {
	    		$found = $areas->search(function ($item, $key) use($currentFacility){
	    			return $item->ID == $currentFacility->AREA_ID;
	    		});
    			$currentArea 	= $found?$areas->get($found):$areas->first();
    		}
    		else
    			$currentArea 	= $areas->first();
    		
    		$facilities 		= Facility::whereIn('ID',$facilityIds)->where("AREA_ID","=",$currentArea->ID)->orderBy("ORDER")->orderBy("NAME")->get();
    		$currentFacility 	= $currentFacility?$currentFacility:$facilities->first();
    		$field 				= config('database.default')==='oracle'?'id':'ID';
    		$areaIds			= $areas->pluck($field);
    		
    		$productionUnits	= LoProductionUnit::whereHas('LoArea', function ($query) use($areaIds) {
						    			$query->whereIn('ID',  $areaIds);
						    		})->get();
    		$found 				= $productionUnits->search(function ($item, $key) use($currentArea){
    			return $item->ID == $currentArea->PRODUCTION_UNIT_ID;
    		});
    		$currentProductUnit = $found?$productionUnits->get($found):$productionUnits->first();
    		if ($currentProductUnit) {
	    		$areas			= $areas->filter(function ($item, $key) use($currentProductUnit){
	    			return $item->PRODUCTION_UNIT_ID == $currentProductUnit->ID;
	    		});
    		}
    	}
    	else if($DATA_SCOPE_AREA&&$DATA_SCOPE_AREA>0){
    		$areas 				= LoArea::where('ID',$DATA_SCOPE_AREA)->orderBy("ORDER")->orderBy("NAME")->get();
    		$currentArea 		= $areas->first();
    		$productionUnits	= LoProductionUnit::where("ID",$currentArea->PRODUCTION_UNIT_ID)->orderBy("ORDER")->orderBy("NAME")->get();
//     		$currentProductUnit = $productionUnits->first();
    		$currentProductUnit = $productionUnits->where('ID', $currentArea->PRODUCTION_UNIT_ID)->first();
    		if($currentArea) 
    			$facilities 	= $currentArea->Facility()->getResults();
    		else
    			$facilities 	=	null;
    		$currentFacility 	= ProductionGroupComposer::getCurrentSelect($facilities,$fid);
    	}
    	else {
	    	if($DATA_SCOPE_PU&&$DATA_SCOPE_PU>0)
	    		$productionUnits = LoProductionUnit::where('ID',$DATA_SCOPE_PU)->orderBy("ORDER")->orderBy("NAME")->get();
	    	else 
	    		$productionUnits = LoProductionUnit::orderBy("ORDER")->orderBy("NAME")->get();
	
	    	$currentProductUnit = ProductionGroupComposer::getCurrentSelect($productionUnits,$pid);
	    	
	    	if($currentProductUnit) 
	    		$areas = $currentProductUnit->LoArea()->getResults();
		    else  
		    	$areas 	=	null;
	    			
	    	$currentArea = ProductionGroupComposer::getCurrentSelect($areas,$aid);
	    	
	    	if($currentArea) 
	    		$facilities = $currentArea->Facility()->getResults();
	    	else  
	    		$facilities 	=	null;
	    	
	    	$currentFacility = ProductionGroupComposer::getCurrentSelect($facilities,$fid);
    	}
    	
    	$loProductionOption			= $this->getFilterArray('LoProductionUnit',$productionUnits,$currentProductUnit);
    	if(!array_key_exists('extra', $fgs)) $loProductionOption['extra']= ["limit"	=> "LoArea"];
    	$loAreaOption				= $this->getFilterArray('LoArea',$areas,$currentArea);
    	if(!array_key_exists('extra', $fgs)) $loAreaOption['extra']		= ["limit"	=> "Facility"];
	    $productionFilterGroup =['LoProductionUnit'	=>	$loProductionOption,
					    		'LoArea'			=>	$loAreaOption,
					    		'Facility'			=>	$this->getFilterArray('Facility',$facilities,$currentFacility)
    							];
	    
	    if ($currentFacility&&$currentFacility instanceof Facility) {
	    	$this->facility = $currentFacility->ID;
	    }
	    $currentObject = $currentFacility;
	    foreach($extras as $source => $model ){
	    	$option = $this->getExtraOptions($productionFilterGroup,$model,$source);
	    	if ($option&&array_key_exists($source, $option)&&array_key_exists("object", $option[$source])) 
	    		$currentObject = $option[$source]["object"];
	    	$rs = ProductionGroupComposer::initExtraDependence($productionFilterGroup,$model,$currentObject,$option);
	    	$eCollection 	= $rs['collection'];
	    	$modelName 		= $rs['model'];
	    	$eId			= $this->currentData&&isset($this->currentData[$modelName])?$this->currentData[$modelName]:null;
	    	$extraFilter 	= is_numeric($eId)&&$eId<=0?null:ProductionGroupComposer::getCurrentSelect ( $eCollection,$eId );
	    	$productionFilterGroup [$modelName] = $this->getFilterArray ($modelName, $eCollection, $extraFilter,$model );
	    }
// 	    \Helper::setGetterCase($originAttrCase);
	    return $productionFilterGroup;
    }
    
    public function loadEntryBy($model, $entryId,$sourceModel) {
    	$modelName	= \Helper::getModelName($sourceModel);
    	$table	= $modelName::getTableName(); 
    	return $modelName::whereHas($model , function ($query) use($entryId,$table) {
    						$query->where("$table.ID",$entryId );
    				})
			    	->get();
    }
    
	public function getExtraOptions($productionFilterGroup, $model, $source = null) {
		if (is_string ( $source )) {
			$extraSource = $productionFilterGroup [$source];
			// $currentId = $extraSource['currentId'];
			$entry = ProductionGroupComposer::getCurrentSelect($extraSource ['collection']);
			if ($entry) {
				$eModel = $entry->CODE;
				$tableName = strtolower ( $eModel );
				$mdlName = \Helper::camelize ( $tableName, '_' );
	// 			$mdl = 'App\Models\\' . $mdlName;
	// 			$eCollection = $mdl::getEntries ( $currentFacility->ID );
				return [
						$source =>[	'name'		=>$mdlName,
									'id'		=>$entry->ID,
									'object'	=>$entry
						]
				];
			}
		}
		return null;
	}
    
    
    public function initFrequenceFilterGroup($extras=null,$filterGroups = null)
    {
    	$frequenceFilterGroup =[];
    	foreach($extras as $model ){
    		if ($filterGroups) {
    			$filterGroups["frequenceFilterGroup"] = $frequenceFilterGroup;
    		}
    		if (is_array($model)) {
    			$collection 		= $this->getFrequenceCollection($model,$filterGroups);
    			$modelName			= $model['name'];
	    		$eId				= $this->currentData&&isset($this->currentData[$modelName])?$this->currentData[$modelName]:null;
    			$unit 				= ProductionGroupComposer::getCurrentSelect($collection,$eId,$modelName);
//     			$unit = $collection!=null&&$collection->count()>0?$collection->first():null;
    			$frequenceFilterGroup[$modelName] = $this->getFilterArray($modelName,$collection,$unit,$model);
    		}
    		else{
    			$eId				= $this->currentData&&isset($this->currentData[$model])?$this->currentData[$model]:null;
    			$unit 				= ["ID"	=> $eId];
    			$frequenceFilterGroup[$model] = $this->getFilterArray($model,null,$unit);
    		}
    	}
    	return $frequenceFilterGroup;
    }
       
    public function getFrequenceCollection($options=null,$filterGroups = null){
    	$collection 			= null;
    	if ($filterGroups) {
    		$mdl		= $options['name'];
	    	$mdl 		= 'App\Models\\' . $mdl;
    		if (array_key_exists('source', $options)&&array_key_exists('getMethod', $options)) {
	    		$source 	= $options['source'];
	    		$getMethod = array_key_exists('getMethod', $options)?$options['getMethod']:'getAll';
	    		$sourceData	= [];
	    		foreach($source as $filter => $fields ){
	    			foreach($fields as $field ){
	    				if ($filter=="dateFilterGroup") {
		    				$sourceData[$field] = $filterGroups[$filter][$field]['value'];
	    				}
	    				else $sourceData[$field] = $filterGroups[$filter][$field]['current'];
	    			}
	    		}
	    		$collection = $mdl::$getMethod($sourceData);
    		}
    		else if (array_key_exists('getMethod', $options)) {
	    		$getMethod 	= $options['getMethod'];
	    		$collection = $mdl::$getMethod($options);
    		}
    		else if(class_exists($mdl))
    			$collection = $mdl::getAll();
    		else if (array_key_exists('collection', $options))
                $collection = new Collection($options['collection']);
    	}
    	return $collection;
    }
    
    public static function getCurrentSelect($collection,$id=null,$modelName=null){
//     	if ($modelName&&method_exists("getCurrentSelect",$modelName)&&$id&&is_string($id)) return $modelName::getCurrentSelect($collection,$id);
		$unit = null;
    	if ($collection&& $collection instanceof Collection) {
    		$primaryKey = 'ID';
    		$secondaryKey	= null;
    		if ($collection->count()>0) {
    			$unit 	= $collection->first();
    			if($unit){
	    			if (property_exists($unit,'getKeyName'))  
	    				$primaryKey	= $unit->getKeyName();
	    			else if(method_exists($unit, "getKeyName"))
	    				$primaryKey	= $unit->getKeyName();
    				if(method_exists($unit, "getSecondaryKeyName"))
    					$secondaryKey	= $unit->getSecondaryKeyName();
    			}
    		}
    		if ($primaryKey) {
	    		$units 			= $collection->keyBy($primaryKey);
	    		if($id) $unit 	= $units->get($id);
	    		
	    		if (!$unit&&$secondaryKey) {
	    			$units 		= $collection->where($secondaryKey,$id);
	    			$unit 		= $units?$units->first():null;
	    		}
	    		if (!$unit) {
	    			$unit = $collection->first();
	    		}
	    		return $unit;
    		}
    	}
    	return null;
    }
    
    public function getFilterArray($id,$collection=null,$currentUnit=null,$option=null){
    	$fullId					= "$this->prefix".$id;
    	$filters				= \Helper::getFilterArray($fullId,$collection,$currentUnit,$option);
		$filters['modelName'] 	= $id;
    	return $filters;
    }
    
    public static function initExtraDependence($productionFilterGroup, $model, $currentUnit,$option = null) {
    	$modelName = $model;
		$currentId = null;
		$eCollection = [];
    	if (is_string ( $model )) {
    		if (method_exists($currentUnit,$model)) {
				$entry = $currentUnit->$model($option);
				if ($entry) {
					if ($entry instanceof Collection || is_array($entry)) $eCollection = $entry;
					else if ($entry instanceof Relation)   $eCollection = $entry->getResults();
				}
				$currentId = isset($option[$model]['id'])?$option[$model]['id']:null;
    		}
    	} else {
			$modelName 		= $model ['name'];
			if (array_key_exists('independent', $model)&&$model ['independent']) {
				$mdl = 'App\Models\\' . $modelName;
				$getMethod = array_key_exists('getMethod', $model)?$model['getMethod']:'getAll';
				$eCollection = $mdl::$getMethod();
			}
			else if(!is_array($currentUnit)&&method_exists($currentUnit,$modelName)){
				$entry = $currentUnit->$modelName($option);
				if ($entry) {
					if ($entry instanceof Collection || is_array($entry)) $eCollection = $entry;
					else if ($entry instanceof Relation)   $eCollection = $entry->getResults();
				}
			}
			else if (is_string($modelName)&& method_exists("App\Models\\$modelName","loadIndependence")) {
				$mdlN = "App\Models\\$modelName";
				$eCollection 	= $mdlN::loadIndependence();
			}
		}
		return ['collection' 	=> $eCollection,
				'model' 		=> $modelName,
				'currentId'		=> $currentId
		];
	}
    
}