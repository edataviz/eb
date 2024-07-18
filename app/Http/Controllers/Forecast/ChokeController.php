<?php
namespace App\Http\Controllers\Forecast;

use App\Http\Controllers\CodeController;
use App\Models\ConstraintDiagram; 
use Illuminate\Http\Request;

class ChokeController extends CodeController {
    
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$dataSet = ConstraintDiagram::orderBy("NAME")->get();
     	return ['dataSet'=>$dataSet];
    }
    
    public function filter(Request $request){
    	$postData 		= $request->all();
    	$filterGroups	= \Helper::getCommonGroupFilter();
    	if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
    	return view ( 'choke.editfilter',['filters'			=> $filterGroups,
						    			'prefix'			=> "secondary_",
						    			"currentData"		=> $postData
						    	]);
    }
    
    public function summaryData($constraints,$beginDate=null,$endDate=null,$postData=null){
    	$summaryData	= [];
    	$sumField		= /* config('database.default')==='oracle'?'v': */"V";
    	if (array_key_exists('CONFIG', $constraints)&&count($constraints['CONFIG'])>0){
    		$categories	= [];
    		$groups 	= [];
    		$minY 		= 1000000000;
    		$minYs		= [];
    		$series		= [];
    		$originAttrCase = \Helper::setGetterUpperCase();
    		foreach($constraints['CONFIG'] as $key => $constraint ){
    			$rquery				= null;
    			$factor				= $constraint['FACTOR'];
    			$factor				= $factor&&$factor!=''?$factor:0;
    			$category			= $constraint['NAME'];
    			$group				= trim($constraint['GROUP']);
    			$groups[]			= $group;
    	
    			$categories[] 		= $category;
    			$serie				= [];
    			if (!array_key_exists('OBJECTS', $constraint)) continue;
    			foreach($constraint['OBJECTS'] as $index => $object ){
    				$tableName		= $object["ObjectDataSource"];
					if (strpos($tableName, 'V_') === 0){
	    				$datefield		= null;
	    				$objectIdField	= "ID";
	    				$modelName		= null;
					}
    				else {
    					$modelName		= \Helper::getModelName($tableName);
	    				$datefield		= property_exists($modelName,"dateField")?$modelName::$dateField:null;
	    				$objectIdField	= $modelName::$idField;
    				}
    				$objectId		= $object["ObjectName"];
    				$operation		= $object["cboOperation"];
    				$operationValue	= $object["txtConstant"];
    				$queryField		= $object["ObjectTypeProperty"];
    				$queryField		= $operation&&$operation!=''&&$operationValue&&$operationValue!=""&&$operationValue!=0?
    				"$queryField$operation$operationValue":
    				$queryField;
    				
    				$where			= [];
    				$flowPhase		= array_key_exists('CodeFlowPhase', $object)&&\Schema::hasColumn($tableName, "FLOW_PHASE")?$object['CodeFlowPhase']:0;
    				if ($flowPhase>0) $where["FLOW_PHASE" ] 	= $flowPhase;
    				
    				$allocType		= array_key_exists('CodeAllocType', $object)&&\Schema::hasColumn($tableName, "ALLOC_TYPE")?$object['CodeAllocType']:0;
    				if ($allocType>0) $where["ALLOC_TYPE" ] 	= $allocType;
    				
    				$planType		= array_key_exists('CodePlanType', $object)&&\Schema::hasColumn($tableName, "PLAN_TYPE")?$object['CodePlanType']:0;
    				if ($planType>0) $where["PLAN_TYPE" ] 	= $planType;
    				
    				$forecastType	= array_key_exists('CodeForecastType', $object)&&\Schema::hasColumn($tableName, "FORECAST_TYPE")?$object['CodeForecastType']:0;
    				if ($forecastType>0) $where["FORECAST_TYPE" ] 	= $forecastType;
    				
//      			$query			= $modelName?$modelName::where($objectIdField,$objectId):\DB::table($tableName)->where($objectIdField,$objectId);
     				$query			= $modelName?$modelName::buildLoadQuery($objectId,$object):\DB::table($tableName)->where($objectIdField,$objectId);
     				if (!$query) continue;
     				if (count($where)>0) $query->where($where);
    				
     				if($datefield){
     					$query->whereDate("$datefield", '>=', $beginDate)
    						->whereDate("$datefield", '<=', $endDate);
     				}
    				$query->select(\DB::raw("$queryField as $sumField"));
    				if ($index==0) $rquery = $query;
    				else 	$rquery->union($query);
    			}
    			$value								= 0;
    			if ($rquery) {
    				$dataSet						= $rquery->get();
    				$value							= $dataSet->sum($sumField);
    				$ycaptionValue					= $value*$factor;
    				if(array_key_exists($group, $minYs))
    					$minYs[$group]+=$ycaptionValue;
    				else
    					$minYs[$group] = $ycaptionValue;
//     				$minY							= ($minYs[$group] < $minY && $minYs[$group]>0)?$minYs[$group]:$minY;
    				$constraint['VALUE']			= $value;
    				$constraint['YCAPTION']			= $ycaptionValue;
    				$constraints['CONFIG'][$key]	= $constraint;
    			}
    			if(!array_key_exists($group,$series)) $series[$group] = [];
    			$series[$group][$category] 			= [
    					"name"	=> $category,
    					"data"	=> [$ycaptionValue],
    					"color"	=> "#".$constraint['COLOR'],
    			];
    		}
    		
    		foreach($minYs as $gr => $min ){
    			$minY	= $min < $minY && $min>0?$min:$minY;
    		}
    		\Helper::setGetterCase($originAttrCase);
    			
    		$groups		= array_unique($groups);
    		$groups		= array_values($groups);
    	
    		$title 						= array_key_exists("NAME",$constraints)?$constraints["NAME"]:"UNTITLE";
    		$ycaption 					= array_key_exists("YCAPTION",$constraints)?$constraints["YCAPTION"]:"";
    		if(!$ycaption) 	$ycaption	= "Oil Limit";
    		if(!$title) 	$ycaption	= "Limit Diagram";
    		$bgcolor					= "";
    		$summaryData["diagram"] = ["bgcolor"		=> $bgcolor,
    				"title"			=> $title,
    				"groups"		=> $groups,
    				"categories"	=> $categories,
    				"series"		=> $series,
    				"ycaption"		=> $ycaption,
    				"minY"			=> $minY==1000000000?0:$minY,
    		];
    	}
    	$summaryData["constraints"] 	= $constraints;
    	return $summaryData;
    }
    
    public function summary(Request $request){
    	$postData 		= $request->all();
    	$beginDate 		= \Helper::parseDate($postData['date_begin']);
    	$endDate 		= \Helper::parseDate($postData['date_end']);
    	if(isset($postData['constraints']))
	    	$constraints	= $postData['constraints'];
    	else{
    		$constraintId	= $postData['constraintId'];
    		$constraints	= $this->loadDiagramConfig($constraintId,$postData);
    	}
    	$summaryData	= $this->summaryData($constraints,$beginDate,$endDate,$postData);
    	
    	return response()->json($summaryData);
    }
    
    public function loadDiagramConfig($constraintId,$postData){
    	$originAttrCase = \Helper::setGetterUpperCase();
    	$constraints	= ConstraintDiagram::find($constraintId);
    	if ($constraints) {
    		$constraints->CONFIG 	= json_decode($constraints->CONFIG,true);
    		$constraints			= $constraints->toArray();
    	}
    	\Helper::setGetterCase($originAttrCase);
    	return $constraints;
    }
}
