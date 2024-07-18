<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Forecast\ChokeController;
use App\Http\Controllers\ProductDeliveryController;
use App\Models\DynamicModel;
use App\Models\IntObjectType;
use App\Models\PlotViewConfig;
use App\Models\StorageDisplayChart;
use Illuminate\Http\Request;

class StorageDisplayController extends ChokeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		$dataSet = StorageDisplayChart::orderBy("TITLE")->get();
		return ['dataSet'=>$dataSet];
	}
	
	public function filter(Request $request){
		$postData 		= $request->all();
		$filterGroups	= ProductDeliveryController::storagedisplayFilter();
		return view ( 'front.cargoadmin.editfilter',['filters'			=> $filterGroups,
				'prefix'			=> "secondary_",
				"currentData"		=> $postData
		]);
	}
	
	public function loadDiagramConfig($constraintId,$postData){
		$originAttrCase = \Helper::setGetterUpperCase();
		$constraints	= StorageDisplayChart::find($constraintId);
		if ($constraints) {
			$config					= $constraints->CONFIG;
			$constraints->CONFIG 	= $config&&is_string($config)?json_decode($config,true):$config;
			$constraints			= $constraints->toArray();
		}
		\Helper::setGetterCase($originAttrCase);
		return $constraints;
	}
	
    public function summaryData($constraints,$beginDate=null,$endDate=null,$postData=null){
	// 		$midleDate		= $postData["date_mid"];
//     	$midleDate 		= \Helper::parseDate($midleDate);
		$summaryData	= [];
		$isOracle		= config('database.default')==='oracle';
		$sumField		= /* $isOracle?'v': */"V";
		$selectDateFn	= $isOracle?'TRUNC(':"DATE(";
		$selectDateFn	= config('database.default')==='sqlsrv'?'CONVERT(DATE,':$selectDateFn;
		
		$subName		= \Helper::getSubNameSelectQuery("sub");
		$Configuration	= auth()->user()->getConfiguration();
		$dateFormat		= $Configuration["time"]["DATE_FORMAT_UTC"];
		if (count($constraints['CONFIG'])>0){
			$categories	= [];
			$minY 		= null;
			$maxY 		= null;
			$summaryLine= [];
			$series		= [];
			$plotLines	= [];
			$groups 	= [];

			$originAttrCase = \Helper::setGetterUpperCase();
			foreach($constraints['CONFIG'] as $key => $constraint ){
				$rquery				= null;
				$rLineQuery			= null;
				$group				= array_key_exists("GROUP", $constraint)?$constraint['GROUP']:"MAIN";
				$isAdditional		= array_key_exists("isAdditional", $constraint)?$constraint['isAdditional']:false;
				$groups[]			= $group;
				$chartType			= $constraint['CHART_TYPE'];
				$color				= $constraint['COLOR'];
				$negative			= $constraint['NEGATIVE'];
				$minus				= $negative==1?"-":"";
				$objects			= array_key_exists("OBJECTS", $constraint)?$constraint['OBJECTS']:null;
				$plotViewConfigId	= $constraint['PlotViewConfig'];
				$plotViewConfig		= PlotViewConfig::find($plotViewConfigId);
// 				if (!$plotViewConfig) continue;
				if (!$objects||count($objects)<=0) {
					$objects		= $plotViewConfig?$plotViewConfig->parseViewConfig():[];
					$category		= $plotViewConfig?$plotViewConfig->NAME:"category";
				}
				else{
					$category		=  array_key_exists("viewName", $constraint)?$constraint['viewName']	:"category";
				}
				if (!$objects||!is_array($objects)||count($objects)<=0) continue;
						
				$beginDate			= array_key_exists("FROM_DATE", $constraint)?$constraint['FROM_DATE']	:$beginDate;
				$endDate			= array_key_exists("TO_DATE", $constraint)	?$constraint['TO_DATE']		:$endDate;
				$categories[] 		= $category;
				$serie				= [];
				$lineSeries			= [];
				$objectNames		= [];
				foreach($objects as $index => $object ){
					$objectId		= $object["ObjectName"];
					$queryField		= $object["ObjectTypeProperty"];
					$objectType		= $object["IntObjectType"];
					$flowPhase		= array_key_exists("CodeFlowPhase", $object)?$object["CodeFlowPhase"]:0;
					$calculation	= array_key_exists("Calculation", $object)?$object["Calculation"]:
									 (array_key_exists("cboOperation", $object)&&array_key_exists("txtConstant", $object)
									 		&&$object["cboOperation"]!=""&&$object["txtConstant"]!=""?
									 			$object["cboOperation"].$object["txtConstant"]:"");
					$tableView		= $object["ObjectDataSource"];
					$tableName		= $tableView;
					$extraSelect	= \DB::raw("-1 as E");
					if (strpos($tableView, "V_")===0) {
// 						$modelName		= \DB::table($tableName);
						$modelName		= new DynamicModel;
						$modelName->setTable($tableView);
						$datefield		= "OCCUR_DATE";
						$objectIdField	= $objectType."_ID";
						$extraSelect	= \Helper::getExtraSelects($tableView,$objectType,$objectId,$extraSelect);
					}
					else{
						$modelName		= \Helper::getModelName($tableName);
						$datefield		= property_exists($modelName, "dateField")?$modelName::$dateField:null;
						$objectIdField	= $modelName::getObjectTypeCode($objectType);
						$objectTypeModel= \Helper::getModelName($objectType);
						$objectName 	= $objectTypeModel::find($objectId);
						if ($objectName) {
							$objectName 	= $objectName->NAME;
// 							$objectNames[]	= "$objectName($tableName.$queryField)$calculation";
							$objectNames[]	= $objectName;
						}
					}
					
					$is_eutest		= false;
					$is_defer		= false;
					if(substr($tableView, 0, strlen("EU_TEST")) === "EU_TEST"){
						$is_eutest	= true;
					}
					else if(substr($tableView, 0, strlen("DEFERMENT")) === "DEFERMENT"){
						$is_defer	= true;
					}
					
					$where			= [$objectIdField	=> $objectId];
					if(method_exists($modelName,"addExtraQueryCondition")) $modelName::addExtraQueryCondition($where,$object,$objectType);
					
					if ($objectType=="ENERGY_UNIT" && !$is_eutest && !$is_defer && $flowPhase>0)  $where['FLOW_PHASE']	= $flowPhase;
					
					$lineQuery		= null;
					$query			= null;
					if ($datefield) {
						if (is_string($modelName))
							$query			= $modelName::where($objectIdField,$objectId);
						else
							$query			= $modelName->where($objectIdField,$objectId);
						
						$query				= $query->whereDate("$datefield", '>=', $beginDate)
													->whereDate("$datefield", '<=', $endDate)
													->select(\DB::raw("$minus($queryField$calculation) as $sumField"),
															\DB::raw("$selectDateFn$datefield) as D"),
															$extraSelect
// 															"$datefield as D"
															)
													->take(300);
						if ($rquery&&$query) $rquery->union($query);
						else if($query)	$rquery = $query;
					}
					else{
						if (is_string($modelName))
							$lineQuery = $modelName::where($objectIdField,$objectId)->select(\DB::raw("$minus($queryField$calculation) as $sumField"))
													->take(1);
						else 
							$lineQuery = $modelName->where($objectIdField,$objectId)->select(\DB::raw("$minus($queryField$calculation) as $sumField"))
													->take(1);
						if ($rLineQuery&&$lineQuery) $rLineQuery->union($lineQuery);
						else if ($lineQuery) $rLineQuery = $lineQuery;
					}
				}
				$value						= 0;
				if ($rquery) {
					$dataSet	= \DB::table( \DB::raw("({$rquery->toSql()}) $subName") )
										->mergeBindings($rquery->getQuery()) // you need to get underlying Query Builder
										->groupBy("D","E")
										->orderBy("D")
										->selectRaw("sum($sumField) as $sumField, D, E")
										->get();
					$series[]       = [
							"type"  		=> $chartType,
							"name"  		=> $category,
							"color" 		=> "#$color",
							"data"  		=> $dataSet,
							"extraTooltip"  => $objectNames,
							"isAdditional"  => $isAdditional,
							"group"  		=> $group,
					];

					foreach($dataSet as $index => $data ){
						$dataValue		= $data->V;
 						$fieldD			=  $data->D;
						if (array_key_exists($fieldD, $summaryLine))
							$summaryLine[$fieldD]->V	+= $dataValue;
						else
							$summaryLine[$fieldD]	= (object) array('D' => $fieldD,'V' => $dataValue);
						$compareValue = $summaryLine[$fieldD]->V;
						if ((!$minY||$minY>$dataValue)&&$dataValue) $minY = $dataValue;
						if ((!$maxY||$maxY<$dataValue)&&$dataValue) $maxY = $dataValue;
						
						if ((!$minY||$minY>$compareValue)&&$compareValue) $minY = $compareValue;
						if ((!$maxY||$maxY<$compareValue)&&$compareValue) $maxY = $compareValue;
					}
				}
				if ($rLineQuery) {
// 					$binding = $rLineQuery instanceof  Illuminate\Database\Eloquent\Builder ? $rLineQuery->getQuery():$rLineQuery;
					$binding = $rLineQuery->getQuery();
					$rDataSet	= \DB::table( \DB::raw("({$rLineQuery->toSql()}) $subName") )
										->mergeBindings($binding) // you need to get underlying Query Builder
										->selectRaw("sum($sumField) as $sumField")
										->first();
					if($rDataSet){
						$rValue			= $rDataSet->$sumField;
						$plotLines[] 	= [
										"label"		=> ["text"	=> $category],
										"color"		=> "#$color",
// 										"dashStyle"	=> 'shortdash',
										"value"		=> $rValue,
									];
						if ((!$minY||$minY>$rValue)&&$rValue) $minY 	= $rValue;
						if ((!$maxY||$maxY<$rValue)&&$rValue) $maxY		= $rValue;
					}
				}
			}
			\Helper::setGetterCase($originAttrCase);
			$groups		= array_unique($groups);
			$groups		= array_values($groups);
			
			ksort($series);
			$this->modifySerieAdditional($series,$maxY,$minY);
			$summaryLine		= $this->sumarySeries($series,$maxY,$minY,$groups);
			$summarySeriesData 	= array_values($summaryLine);
			$series[] 	= [
					"type"	=> "line",
					"name"	=> 'Storage Display',
					"color"	=> "#de3ee6",
					"data"	=> $summarySeriesData,
			];
				
			$title 					= $constraints["TITLE"];
			$bgcolor				= "";
			$ycaption				= "";
			$summaryData["diagram"] = ["bgcolor"		=> $bgcolor,
										"title"			=> $title,
										"categories"	=> $categories,
										"series"		=> $series,
										"plotLines"		=> $plotLines,
										"ycaption"		=> $ycaption,
										"minY"			=> $minY,
										"maxY"			=> $maxY,
			];
		}
		$summaryData["constraints"] 	= $constraints;
		return $summaryData;
	}
	
	public function diagram(Request $request){
		$postData 			= $request->all();
		$id					= array_key_exists("id", $postData)?$postData["id"]:0;
		if ($id>0) {
			$constraints	= $this->loadDiagramConfig($id,$postData);
			$summaryData	= $this->summaryData($constraints);
			return view ( 'datavisualization.storage_diagram_alone',['summaryData'=>$summaryData]);
		}
		return response()->json("not available!");
	}
	
	public function modifySerieAdditional(&$series,&$maxY,&$minY){
		foreach($series as $serieIndex => $serie ){
			$isAdditional	= array_key_exists("isAdditional", $serie)?$serie['isAdditional']:false;
			if($isAdditional===true||$isAdditional==="true"){
				$preValue		= 0;
				foreach($serie["data"] as $index => $data ){
					$otherValue				= $this->sumOtherValue($series,$serie,$serieIndex,$data);
					$dataValue				= $preValue+$data->V+$otherValue;
					$data->V				= $dataValue;
					$preValue				= $dataValue;
					$serie["data"][$index]	= $data;
					
					if ((!$minY||$minY>$dataValue)&&$dataValue) $minY = $dataValue;
					if ((!$maxY||$maxY<$dataValue)&&$dataValue) $maxY = $dataValue;
				}
				$series[$serieIndex]		= $serie;
			}
		}
	}
	
	
	public function sumOtherValue($series,$mainSerie,$mainSerieIndex,$mainData){
		$otherValue			= 0;
		foreach($series as $serieIndex => $serie ){
			if ($mainSerieIndex!=$serieIndex&& array_key_exists("group", $serie) && $serie['group']==$mainSerie['group']) {
				$sValue		= 0;
				foreach($serie["data"] as $index => $data ){
					if($data->D&&$mainData->D==$data->D){
						$sValue		= $data->V;
						break;
					}
				}
				$otherValue+=$sValue;
			}
		}
		return $otherValue;
	}
	
	public function sumarySeries($series,&$maxY,&$minY,$groups){
		$summaryLine= [];
		$groups;
		foreach($series as $serieIndex => $serie ){
			foreach($serie["data"] as $index => $data ){
				$dataValue		= $data->V;
				$fieldD			=  $data->D;
				if (array_key_exists($fieldD, $summaryLine))
					$summaryLine[$fieldD]->V	+= $dataValue;
					else
						$summaryLine[$fieldD]	= (object) array('D' => $fieldD,'V' => $dataValue);
						$compareValue = $summaryLine[$fieldD]->V;
	
						if ((!$minY||$minY>$compareValue)&&$compareValue) $minY = $compareValue;
						if ((!$maxY||$maxY<$compareValue)&&$compareValue) $maxY = $compareValue;
			}
		}
	
		return 	$summaryLine;
	
	}
	
	public function sumarySeriesWithGroup($series,&$maxY,&$minY,$groups){
		$summaryGroups	= [];
		foreach($groups as $groupIndex => $group ){
			$summaryGroups[$group]	= [];
		}
		
		$groupFinish= [];
		foreach($series as $serieIndex => $serie ){
			$group	= $serie["group"];
// 			if (in_array($group, $groupFinish)) continue;
			
			$isAdditional	= array_key_exists("isAdditional", $serie)?$serie['isAdditional']:false;
			if($isAdditional===true||$isAdditional==="true"){
				$summaryGroups[$group]	= [];
				$groupFinish[$group] 	= $serieIndex;
			}
			$dataSet	= $serie["data"];
			foreach($dataSet as $index => $data ){
				$dataValue		= $data->V;
				$fieldD			=  $data->D;
				if (array_key_exists($fieldD, $summaryGroups[$group])){
					if ( array_key_exists($group, $groupFinish)&&$groupFinish[$group]==$serieIndex) {
						$summaryGroups[$group][$fieldD]->V	= $dataValue;
					}
					else
						$summaryGroups[$group][$fieldD]->V	+= $dataValue;
				}
				else{
					$summaryGroups[$group][$fieldD]	= (object) array('D' => $fieldD,'V' => $dataValue);
				}
			}
		}
		
		$summaryLine= [];
		foreach($summaryGroups as $group => $summary ){
			foreach($summary as $fieldD => $dataObject ){
				$dataValue	= $dataObject->V;
				if (array_key_exists($fieldD, $summaryLine)){
					$summaryLine[$fieldD]->V	+= $dataValue;
				}
				else{
					$summaryLine[$fieldD]	= (object) array('D' => $fieldD,'V' => $dataValue);
				}
				$compareValue = $summaryLine[$fieldD]->V;
				if ((!$minY||$minY>$compareValue)&&$compareValue) $minY = $compareValue;
				if ((!$maxY||$maxY<$compareValue)&&$compareValue) $maxY = $compareValue;
			}
		}
		
		ksort($summaryLine);
		return 	$summaryLine;
		
	}
}
