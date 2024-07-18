<?php

namespace App\Http\Controllers;
use App\Models\AdvChart;
use App\Models\CfgFieldProps;
use App\Models\CodeFlowPhase;
use App\Models\EuPhaseConfig;
use App\Models\Facility;
use App\Models\Formula;
use App\Models\LoArea;
use App\Models\LoProductionUnit;
use App\Models\UomConversion;
use App\Models\UserWorkspace;
use Illuminate\Http\Request;
use App\Http\ViewComposers\ProductionGroupComposer;
use Carbon\Carbon;
use DB;
use App\Models\DynamicModel;

class graphController extends EBController {
	
	protected $defaultNumber = 1000;
	
	
	public function _index() {
		$filterGroups	= \Helper::getGraphFilter();
		return view ( 'front.graph',['filters'			=> $filterGroups,
									"defaultNumber"		=> $this->defaultNumber
				
		]);
	}
	
	private function getDataSource($code){
		$result = null;
	
		$datasource = config("constants.tab");
		$result = $datasource[$code];
		
		return $result;
	}
	
	public function loadVizObjects(Request $request){
		$data = $request->all ();	
		
		$tmp = $this->loadObjectname($data);
		
		$tab = $this->getTab($data);
	
		return response ()->json ( ['result' => $tmp, 'tab'=>$tab] );
	}
	
	private function getTab($data){
		$object_type = $data['object_type'];
		
		$obj_types=explode("/",$object_type);
		
		$table_name=$obj_types[1];
		if($table_name == 'TANK')
			$table_name = 'STORAGE';
		
		return $this->getDataSource($table_name);
	 }
	
	private function loadObjectname($data){
		
		$facility_id = $data['facility_id'];
		$product_type = $data['product_type'];
		$date_begin = $data['date_begin'];
		$date_end = $data['date_end'];
		$object_type = $data['object_type'];
		
		if($date_begin && $facility_id){
			$this->saveWorkSpaceInfo($date_begin, $date_end, $facility_id);
		}
		
		$obj_types=explode("/",$object_type);
		
		$table_name=$obj_types[0];
		$model = \Helper::getModelName($table_name);
		$tmp = [];
		//\DB::enableQueryLog ();
		switch ($table_name){
			case "TANK":
			case "STORAGE":
				$tmp = $model::where(['FACILITY_ID'=>$facility_id])
				->where ( function ($q) use ($product_type) {
					if ($product_type != 0) {
						$q->where ( [
								'PRODUCT' => $product_type
						] );
					}
				})->get(['ID', 'NAME']);
				break;
					
			case "FLOW":
				$tmp = $model::where(['FACILITY_ID'=>$facility_id])
				->where ( function ($q) use ($product_type) {
					if ($product_type != 0) {
						$q->where ( [
								'PHASE_ID' => $product_type
						] );
					}
				})->get(['ID', 'NAME']);
				break;
					
			case "ENERGY_UNIT":
				$tableName = $model::getTableName ();
				$euPhaseConfig = EuPhaseConfig::getTableName ();
				$tmp = DB::table($tableName.' AS a')
				->where(['FACILITY_ID'=>$facility_id])
				->whereNotExists(function($query) use ($euPhaseConfig, $product_type){
					$query->select(DB::raw('A.ID'))
					->from($euPhaseConfig.' AS b')
					->whereRaw('b.EU_ID = a.ID')
					->where(['b.FLOW_PHASE'=>$product_type]);
				})->get(['ID', 'NAME']);
				break;
		}
		//\Log::info ( \DB::getQueryLog () );
		
		return $tmp;
	}
	
	public function getProperty(Request $request){
		$data = $request->all ();	
		$result = array ();
		$model = 'App\\Models\\' . $data['table'];
		$tableName = $model::getTableName ();
		
		$tmp  = CfgFieldProps::where(['USE_FDC'=>1, 'TABLE_NAME'=>$tableName])->get(['COLUMN_NAME AS CODE', 'LABEL AS NAME']);
		
		if(count($tmp) > 0){
			foreach ($tmp as $t){
				if($t->NAME == '' || is_null($t->NAME)){
					$t->NAME = $t->CODE;
				}
				array_push($result, $t);
			}
		}
		
		return response ()->json ( $result );
	}
	
	public function saveWorkSpaceInfo($date_begin, $date_end, $facility_id)
	{
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
		
		if(!$user_name) return;
		
		$date_begin = Carbon::createFromFormat('m/d/Y',$date_begin)->format('Y-m-d');
		$date_end = Carbon::createFromFormat('m/d/Y',$date_end)->format('Y-m-d');		
		
		$condition = array (
				'USER_NAME' => $user_name
		);
		
		$obj ['W_DATE_BEGIN'] = $date_begin;
		$obj ['W_DATE_END'] = $date_end;
		$obj ['W_FACILITY_ID'] = $facility_id;
		
		//\DB::enableQueryLog ();
		UserWorkspace::updateOrCreate ( $condition, $obj );
		//\Log::info ( \DB::getQueryLog () );		
	}
	
	private function getWorkSpaceInfo(){
	
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
	
		$user_workspace = UserWorkspace::getTableName();
		$facility = Facility::getTableName();
		$lo_area = LoArea::getTableName();
		$lo_production_unit = LoProductionUnit::getTableName();
	
		$workspace = DB::table($user_workspace.' AS a')
		->join($facility.' AS b', 'a.W_FACILITY_ID', '=', 'b.ID')
		->join($lo_area.' AS c', 'b.AREA_ID', '=', 'c.ID')
		->join($lo_production_unit.' AS d', 'c.PRODUCTION_UNIT_ID', '=', 'd.ID')
		->where(['a.USER_NAME'=>$user_name])
		->select('a.*',DB::raw('DATE_FORMAT(a.W_DATE_BEGIN, "%m/%d/%Y") as DATE_BEGIN'), DB::raw('DATE_FORMAT(a.W_DATE_END, "%m/%d/%Y") as DATE_END'),
				'b.AREA_ID', 'c.PRODUCTION_UNIT_ID' )
				->first();
	
		return $workspace;
	}
	
	public function loadEUPhase(Request $request){
		$data = $request->all ();		
		$eu_id=$data['eu_id'];
		
		$euPhaseConfig = EuPhaseConfig::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		
		//\DB::enableQueryLog ();
		$tmp = EuPhaseConfig::join($code_flow_phase, "$euPhaseConfig.FLOW_PHASE", '=', "$code_flow_phase.ID")
		->where(["$euPhaseConfig.EU_ID"=>$eu_id])
		->get(["$code_flow_phase.ID", "$code_flow_phase.NAME"] );
		//\Log::info ( \DB::getQueryLog () );
		return response ()->json ( ['result' => $tmp] );
	}
	
	public function loadChart(Request $request){
// 		$options 	= $request->only('title','minvalue', 'maxvalue','date_begin','date_end','input',"bgcolor");
		$options 	= $request->all();
		$title		= isset($options['title']		)?$options['title']:"";
		$minvalue	= isset($options['minvalue']	)?$options['minvalue']:"";
		$maxvalue	= isset($options['maxvalue']	)?$options['maxvalue']:"";
		$date_begin	= isset($options['date_begin']	)?$options['date_begin']:"";
		$date_end	= isset($options['date_end']	)?$options['date_end']:"";
		$input		= isset($options['input']		)?$options['input']:"";
		$bgcolor	= isset($options["bgcolor"]		)?$options['bgcolor']:"";
		$chart_id	= isset($options["chart_id"]	)?$options['chart_id']:0;
		$nolegend	= isset($options["nolegend"])	;
		
		if($chart_id>0){
			$rc		= AdvChart::find($chart_id);
			if ($rc) {
				$title		= $rc->TITLE;
				$minvalue	= $rc->MIN_VALUE;
				$maxvalue	= $rc->MAX_VALUE;
				$input		= $rc->CONFIG;
			}
		}
		
		$isrange	=(is_numeric($minvalue) && $maxvalue>$minvalue);
		$date_begin = \Helper::parseDate($date_begin);
    	$date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
		
    	$configs	= explode("\r\n",$input);
		$ss=explode(",",$configs[0]);
		$interval	= 5000;
		$timebase	= 5*60000;
    	$lastDate	= null;
		if (count($configs)>1) {
			try {
				$oConfigs = json_decode(str_replace("\\","",$configs[1]));
				if (isset($oConfigs->SampleInterval)) 	$interval	= $oConfigs->SampleInterval;
				if (isset($oConfigs->Timebase))	 		$timebase	= $oConfigs->Timebase;
				if (isset($oConfigs->LastDate))	 		$lastDate	= $oConfigs->LastDate;
				if ($lastDate ){
                    $lastDate = trim($lastDate);
                    if(strpos($lastDate," ")===false) $lastDate.=" 00:00";
                }
			}
			catch (\Exception $e){
				\Log::info($e->getMessage());
				\Log::info($e->getTraceAsString());
			}
		}
		$seriesIndex=0;
		$maxV=0;
		$minV=PHP_INT_MAX;
		$strData = "";
		$pies="";
        $defermentPies= [];
        $pieSize = 100;
		$stackedColumnOption="";
		$realtimeTagsArray = "";
		
		$ya=array();
		$no_yaxis_config=true;
		
		$originAttrCase 	= \Helper::setGetterUpperCase();
		$user 				= auth()->user();
		$facilityIds 		= $user->getScopeFacility();
        $colors             = \Helper::$colors;
        $color              = $colors[0];
        foreach($ss as $sIndex => $s)
		{
			$tmp = [];
			$yaIndex=-1;
			$phase_type = -1;
			$eventType = -1;
			$s = str_replace("undefined", "", $s);
			$xs=explode(":",$s);
			$tag = '';
			$is_eutest=false;
			$is_deferment=false;
			$is_cummulative = false;
			
			if(count($xs)<6) continue;
			$obj_type = $xs[0];
			$object_value = $xs[1];				
			$data_table = $xs[2];
			$chart_type = $xs[4];
			$chart_name = $xs[5];
			$chart_color = $xs[count($xs)-1];
			if(!(substr($chart_color,0,1)=="#" && strlen($chart_color)>1)) $chart_color="";
			if(strpos($object_value, "(*)")!==false){
				$object_value = str_replace("(*)", "", $object_value);
				$is_cummulative = true;
			}
			
			if($obj_type=="TAG"){
				$tag = $object_value;
				$realtimeTagsArray .= ($realtimeTagsArray?",":"")."{tag: '$tag', index: $seriesIndex}";
			}
			else {
				$types=explode("~",$xs[3]);
				$vfield=$types[0];
				
				$datefield="OCCUR_DATE";
				$obj_type_id_field  = null;
				
				if($obj_type=="TANK") 			$obj_type_id_field="TANK_ID";
				else if($obj_type=="STORAGE") 	$obj_type_id_field="STORAGE_ID";
				else if($obj_type=="EQUIPMENT") $obj_type_id_field="EQUIPMENT_ID";
				else if($obj_type=="FLOW")		$obj_type_id_field="FLOW_ID";
				else if($obj_type=="EU_TEST") 	$obj_type_id_field="EU_ID";
				else if($obj_type=="KEYSTORE")	$obj_type_id_field="KEYSTORE";
				else if($obj_type=="DEFERMENT") $obj_type_id_field="DEFER_GROUP_TYPE";
                else if($obj_type=="EQUIPMENT")	$obj_type_id_field="EQUIPMENT_ID";				
				else if($obj_type=="ENERGY_UNIT"){
					$obj_type_id_field="EU_ID";
					$chart_type=$xs[5];
					$chart_name=$xs[6];
					$vfield=$xs[3];
					$types=explode("~",$xs[4]);
					$phase_type=$types[0];
					$eventType	= $types[count($types)-1];
				}
					
			
				if (!$obj_type_id_field || !$vfield || $vfield == "null") continue;
				
				if(count($types)>5){
					$ypos	=$types[4];
					$ytext	=$types[5];
				}
				else {
					$ytext	= "";
					$ypos	= "";
				}
				
				if($ytext=="") $ypos="";
				if($ypos!="" && $ytext!=""){
					$yatext=$ypos."^^^".$ytext;
					for($i=0;$i<count($ya);$i++)
						if($ya[$i]==$ytext){
							$yaIndex=$i;
							break;
					}
					if($yaIndex<0){
						array_push($ya,$yatext);
						$yaIndex=count($ya)-1;
					}
					$no_yaxis_config=false;
				}
				

				$model = \Helper::getModelName($data_table);
				
				if(substr($data_table, 0, 7) == "EU_TEST"){
					$is_eutest=true;
					$datefield="EFFECTIVE_DATE";
				}
				else if($obj_type=="DEFERMENT"){
					$is_deferment=true;
					$datefield="BEGIN_TIME";
					$obj_type_id_field="DEFER_TARGET";
				}
				
				$hasPhase = true;
				if (strpos($data_table, "V_")===0) {
					$modelName		= new DynamicModel;
					$modelName->setTable($data_table);
					$model 			= $modelName;
					$fillable		= $model->getTableColumns();
					$hasPhase		= in_array('FLOW_PHASE', $fillable);
				}				
						
				if ($facilityIds) {
					if (count($facilityIds)>0 && is_numeric($object_value)) {
						$mdlName 	= \Helper::getModelName($obj_type);
						if (method_exists($mdlName, "isInFacilities") && !$mdlName::isInFacilities($facilityIds,$object_value))
							break;
					}
				}
				//\DB::enableQueryLog ();
				if ($obj_type_id_field=="KEYSTORE") {
					if (isset($model::$foreignKeystore)&&$model::$foreignKeystore) {
						$obj_type_id_field	=	$model::$foreignKeystore;
					}
					else continue;
				}
				if (is_string($model)) {
					$tquery = $model::where([$obj_type_id_field=>$object_value]);
				}
				else{
					$tquery = $model->where([$obj_type_id_field=>$object_value]);
				}
							
				$wheres	= [];
				
				if ($obj_type == "ENERGY_UNIT" && !$is_eutest && !$is_deferment&&$hasPhase&&$phase_type&&$phase_type>0) {
					$wheres["FLOW_PHASE"]	= $phase_type;
				}
				
				if ($eventType&&$obj_type == "ENERGY_UNIT" && !$is_eutest && !$is_deferment&&$eventType>0) {
					$wheres["EVENT_TYPE"]	= $eventType;
				}
				
				if(substr($data_table, -4) === 'PLAN' && count($types)>2 &&$types[2]>0){
					$wheres["PLAN_TYPE"]	= $types[2];
				}
				else if(substr($data_table, -8) === 'FORECAST' && count($types)>3&&$types[3]>0){
					$wheres["FORECAST_TYPE"]	= $types[3];
				}
				else if($obj_type == "ENERGY_UNIT" && substr($data_table, -5) === 'ALLOC' && count($types)>1 &&$types[1]>0){
					$wheres["ALLOC_TYPE"]	= $types[1];
				}
				else if($data_table=='KEYSTORE_INJECTION_POINT_DAY' && count($types)>8){
					if($vfield=='MIN_QTY_DAY' || $vfield=='MAX_QTY_DAY' || $vfield=='RECOMMEND_QTY_DAY'){
						$model = 'App\\Models\KeystoreInjPointChemical';
						$V = null;
						$V = $model::where(['INJECTION_POINT_ID'=>$object_value,'KEYSTORE_ID'=>$types[8]])->select($vfield)->first()->$vfield;
						if($V>0){
							$tmp = [];
							$d1 = date ( "Y-m-d", strtotime ( $date_begin ) );
							$d2 = strtotime ( $date_end );
							while ( strtotime ( $d1 ) <= $d2 ) {
								$tmp[] = ['V' => $V, $datefield => $d1];
								$d1 = date ( "Y-m-d", strtotime ( "+1 day", strtotime ( $d1 ) ) );
							}
						}
						$wheres = null;
					}
					else
						$wheres["KEYSTORE_ID"]	= $types[8];
				}
				
				if($wheres!==null){
					if(count($wheres)>0) $tquery->where($wheres);
					$tquery->whereDate ( $datefield, '>=',  $date_begin ) 
					->whereDate ( $datefield, '<=',  $date_end )	
					->orderBy ( $datefield )
					->take(5000);

					if($chart_type!="pie" && !$is_deferment) $tmp =  $tquery->select([$vfield.' AS V', "$datefield"])->get();
				}
			}
			
			//\Log::info ( \DB::getQueryLog () );
			if($chart_type=="pie"){
                if($is_deferment){
                    $level = $object_value;
                    $facilityId = isset($options['facility']	)?$options['facility']:0;
                    $where = [];
                    if($facilityId) $where["FACILITY_ID"] = $facilityId;
                    $results = $model::where($where)
                        ->whereDate( $datefield, '>=',  $date_begin )
                        ->whereDate( $datefield, '<=',  $date_end )
                        ->select(\DB::raw("sum($vfield) AS V"),$level)
                        ->groupBy($level)
						->orderBy('V', 'desc')
						->get();
                    foreach ($results as $result ){
                        $mdlName = $model::getSourceModel($level);
                        if($mdlName){
                            $eloquent       = "\App\Models\\$mdlName";
                            $component      = $eloquent::find($result->$level);
                            if (!$component) continue;
                            $result->$level = $component->NAME;

                            if ($result&&$result->V) {
                                $vl = $result->V;
                                if (is_numeric($vl)) {
                                    $color = count($colors)>0?array_shift($colors):$color;
                                    $defermentPies[$sIndex][] = ["name" => $result->$level,
                                        "y" => $vl ,
                                        'color'=>$color];
                                }
                            }
                        }
                    }
                }
                else if(isset($model)){
                    $result = $model::where([$obj_type_id_field=>$object_value])
                                        ->where ( function ($q) use ($obj_type, $is_eutest, $phase_type,$is_deferment) {
                                            if ($obj_type == "ENERGY_UNIT" && !$is_eutest && !$is_deferment) {
                                                $q->where (['FLOW_PHASE' => $phase_type]);
                                            }
                                        })
                                        ->whereDate ( $datefield, '=',  $date_begin )
                                        ->select($vfield.' AS V')
                                        ->first();
                    if ($result&&$result->V) {
                        $vl = $result->V;
                        if(is_numeric($vl)){
                            if($chart_color&&$chart_color!=""){
                                list($r, $g, $b) = sscanf($chart_color, "#%02x%02x%02x");
                                $rgba=",color:'rgba($r, $g, $b,0.9)'";
                            }
                            else {
                                $color = count($colors)>0?array_shift($colors):$color;
                                $rgba=",color:'$color'";
                            }
                            $field = $chart_name;
                            $pies.=($pies?",":"")."{name:'$field',y:$vl$rgba}";
                        }
                    }
                }
			}
			else {
				if($chart_type=="stacked"){
					if($stackedColumnOption=="")
						$stackedColumnOption="        column: {
            stacking: 'normal',
            dataLabels: {
                enabled: false,
                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
            }
        },";
					$chart_type="column";
				}
				else if ($chart_type=="point"){
					$lineWidth = 0;
					$chart_type="line";
				}
				$i=0;
				$strData.= $strData!=""?",{":"{";
				$strData .= "type: '$chart_type',\n";
				if(isset($lineWidth)) $strData .= "lineWidth: $lineWidth,\n";
				$strData .= "name: '".preg_replace('/\s+/', '_@', $chart_name)."',\n";
				if($tag !== ''){
					$strData .= "realtimeTag: '$tag',\n";
					$strData .= "index: $seriesIndex,\n";
				}
				$strData .= ($chart_color?"color: '$chart_color',\n":"");
				$strData .= $yaIndex>-1?"yAxis: $yaIndex,":"";
				
				//$strData .= "name: '".$chart_name."',";
				$strData .= "data: [";
				$value = 0;
				foreach ($tmp as $row)
				{
					if($row->V == "") $row ->V=0;
					if($row->V>$maxV)$maxV=$row->V;
					if($row->V<$minV)$minV=$row->V;
					if($i>0){
						$strData .= ",\r\n";
					}
					$dateTime 		= $row->$datefield;
					if (is_string($dateTime)) {
						try {
							$dateTime	= Carbon::parse($dateTime);
						} catch (Exception $e) {
							\Log::info ( $e->getMessage() );
						}
					}
					if ($dateTime instanceof  Carbon) {
						$dateTimeText 	= sprintf("%d,%d,%d,%d,%d,%d", $dateTime->year,$dateTime->month-1,$dateTime->day, $dateTime->hour,$dateTime->minute,$dateTime->second);
						$value = ($is_cummulative?$value+$row->V:$row->V);
						$strData .= "[Date.UTC(".$dateTimeText."), ".$value."]";
						$i++;
					}
				}
				$strData .="]}\r\n";
				$seriesIndex++;
			}
		}
        $dCount = count($defermentPies);
		if($dCount){
			$pieIndex = 1;
			$pieMargin = 20;
			$size = 180; //320/$dCount;
			foreach ($defermentPies as $sIndex => $defermentPie){
				$pieData = ['type' =>'pie',
					'center' =>[''.(($pieIndex)/$dCount*100 - (50/$dCount) )."%"],
					'size'  => $size,
					'data' => $defermentPie];
					$strData.= ($strData!=""?",":"").json_encode($pieData,JSON_NUMERIC_CHECK );
				$pieIndex++;
			}
		}
        if($pies)
            $strData.= ($strData!=""?",":"")."{type:'pie',size: 200,data:[$pies],center: [".($pieIndex*$pieMargin*2).", null],}";

        $yaxis="";
		$min1 = 0;
		$max1 = 0;
		$min2 = 0;
		$max2 = 0;
		$yaxisArray=[];
		if($no_yaxis_config){
			$min1=($minV<0?$minV:0);
			$div=5;
			if($isrange){
				$min1=$minvalue;
				$max1=$maxvalue;
			}
			else{
				$x=ceil($maxV);
				$xs=strval($x);
				$xl=strlen($xs)-1;
				$n=(int)$xs[0];
				$t=pow(10,$xl);
				$x=ceil(2*$maxV/$t)/2;
				$max1=$x*$t;
				if($max1/$div*($div-1)>$maxV){
					$max1 = $max1/$div*($div-1);
					$div -= 1;
				}
			}
			$tickInterval1=($max1-($min1>0?$min1:0))/$div;

			$tickInterval2=0;
			$min2=$min1;
			$max2 = $max1;
			/*
			$x = $this->convertUOM($tickInterval1,'kL','m3');
			if(is_numeric($x)){
				$tickInterval2=$x;
				if($isrange)
					$min2 = $this->convertUOM($min1,'kL','m3');
			}
			if($tickInterval2>0){
				$max2=($min2<0?0:$min2)+$tickInterval2*$div;
			}
			*/
		}
		else{
			if(count($ya)>0){
				$ci=0;
				foreach($ya as $yat){
					$ys=explode("^^^",$yat);
					$ci++;
					$yaxisItem = [
									"labels"	=> [
													"format"	=> '{value}',
													"style"		=> ['color'	=>"Highcharts.getOptions().colors[$ci]"],
													],
									"title"		=> [
													"text"	=> "$ys[1]",
													"style"		=> ['color'	=>"Highcharts.getOptions().colors[$ci]"],
									],
							
					];
					if($ys[0]=="R") $yaxisItem["opposite"] = true;
					$yaxisArray[] = $yaxisItem;
					$ci++;
				}
			}
		}
		
		\Helper::setGetterCase($originAttrCase);
		
		return view('front.graph_loadchart', [
				'min1'		=>$min1,
				'max1'		=>$max1,
				'min2'		=>$min2,
				'max2'		=>$max2,
				"nolegend"	=> false,//$nolegend,
				'title'		=>($title != "null")?$title:"",
				'series'	=>$strData,
				'ya'		=>$ya,
				'no_yaxis_config'		=>$no_yaxis_config,
				"yaxis"		=> $yaxisArray,
				"realtimeTagsArray" => $realtimeTagsArray,
				"sampleInterval" 	=> $interval,
				"timebase" 			=> $timebase,
				"lastDate" 			=> $lastDate,
				"defaultNumber"		=> $this->defaultNumber,
				'stackedColumnOption'=>$stackedColumnOption
				
		]);
	}
	
	private function convertUOM($value,$from_uom,$to_uom){
		if(is_numeric($value)){
			$uom = UomConversion::where(['CODE'=>$from_uom, 'TO_CODE'=>$to_uom])->select('MULTIPLY_BY', 'PLUS_TO')->first();
			
			$result = $uom->MULTIPLY_BY*$value + $uom->PLUS_TO;
			
			return $result;
		}
		return false;
	}
	
	public function getListCharts(){
		$formula 		= Formula::where(['GROUP_ID'=>8])
							->orderBy('ID')
							->get(['ID', 'NAME']);
		return response ()->json ( ['adv_chart' => $this->getChart(), 'formula'=>$formula] ); 
	}
	
	public function deleteChart(Request $request){
		$data = $request->all();
		
		AdvChart::where(['ID'=>$data['ID']])->delete();
		
		return response ()->json ( ['adv_chart' => $this->getChart()] );
	}
	
	private function getChart(){
		$originAttrCase 	= \Helper::setGetterUpperCase();
		$adv_chart 			= AdvChart::orderBy('TITLE')->get();
		\Helper::setGetterCase($originAttrCase);
		return $adv_chart;
	}
	
	public function getChartById($id){
		$originAttrCase 	= \Helper::setGetterUpperCase();
		$adv_chart 			= AdvChart::find($id);
		\Helper::setGetterCase($originAttrCase);
		return response ()->json ($adv_chart); 
	}
	
	public function saveChart(Request $request){
	    //\Log::info($request);
		$data = $request->all();
		$facilityId=isset($data['facility_id'])?$data['facility_id']:null;
		$id = $data['id'];		
		$title = addslashes($data["title"]);
		$group = addslashes($data["group"]);
		$config = ($data["config"]);
		$minvalue = null;//$data["minvalue"];
		$maxvalue = null;//$data["maxvalue"];
		if(!is_numeric($minvalue)) $minvalue = null;
		if(!is_numeric($maxvalue)) $maxvalue = null;
		
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
		
		
		$adv_chart = AdvChart::find($id);
		
		if($adv_chart){
			AdvChart::where(['ID'=>$id])->update(['TITLE'=>$title, 'CONFIG'=>$config, 'MAX_VALUE'=>$maxvalue, 'MIN_VALUE'=>$minvalue]);
		}else{
			$now = Carbon::now('Europe/London');
			$time = date('Y-m-d H:i:s', strtotime($now));
			$adv_chart = AdvChart::UpdateOrCreate(['ID'=>$id],['TITLE'=>$title, 'CONFIG'=>$config, 'GROUP_ID'=>$group, 'MAX_VALUE'=>$maxvalue, 'MIN_VALUE'=>$minvalue, 'CREATE_BY'=>$user_name, 'CREATE_DATE'=>$time,'FACILITY_ID'=>$facilityId]);
			$id = $adv_chart->ID;
		}
		
		return response ()->json ( "ok:$id" );
	}

	public function filter(Request $request){
		$postData 		= $request->all();
		$filterGroups	= \Helper::getGraphFilter();
		if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
		return view ( 'graph.editfilter',['filters'			=> $filterGroups,
				'prefix'			=> "secondary_",
				"currentData"		=> $postData
		]);
	}
	
	public function objects(Request $request){
		$postData 		= $request->all();
		$dataStores		= $postData["dataStores"];
		$filterGroups	= \Helper::getGraphFilter();
		if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
		foreach ($dataStores as $key => $dataStore){
			$filterGroupComposer	= ProductionGroupComposer::getInstance($dataStore);
			$filterGroupData		= $filterGroupComposer->composeData($filterGroups);
			$frequenceFilterGroup	= $filterGroupData['frequenceFilterGroup'] ;
			$dataStore["objects"] 	= $frequenceFilterGroup["ObjectName"]["collection"];
			$dataStores[$key] 		= $dataStore;
		}
		
		return response ()->json ($dataStores);
	}
}