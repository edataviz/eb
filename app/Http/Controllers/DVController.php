<?php

namespace App\Http\Controllers;

use App\Jobs\runAllocation;
use App\Models\AllocJob;
use App\Models\CfgFieldProps;
use App\Models\CodeFlowPhase;
use App\Models\CodeInjectPoint;
use App\Models\Dashboard;
use App\Models\Deferment;
use App\Models\EbFunctions;
use App\Models\EnergyUnit;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\Flow;
use App\Models\FoGroup;
use App\Models\IntConnection;
use App\Models\IntObjectType;
use App\Models\IntTagMapping;
use App\Models\IntTagSet;
use App\Models\Keystore;
use App\Models\KeystoreInjectionPoint;
use App\Models\KeystoreStorage;
use App\Models\KeystoreTank;
use App\Models\LoArea;
use App\Models\LoProductionUnit;
use App\Models\NetWork;
use App\Models\Params;
use App\Models\Storage;
use App\Models\Tank;
use App\Models\TmTask;
use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DVController extends CodeController {
	
	protected $interfaceController;
	protected $tagStore = [];
	protected $sqlStore = [];
	protected $valueStore = [];
	function getInterface(){
		if($this->interfaceController==null)
			$this->interfaceController = new InterfaceController();
		return $this->interfaceController;
	}
	
	public function _indexDiagram() {
		$codeFlowPhase = CodeFlowPhase::all ( [ 
				'ID',
				'NAME' 
		] );
		
		$loProductionUnit = LoProductionUnit::all ( [ 
				'ID',
				'NAME' 
		] );
		
		$loArea = !isset($loProductionUnit [0])?[]:LoArea::where ( [ 
				'PRODUCTION_UNIT_ID' => $loProductionUnit [0]->ID 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		$facility = !isset($loArea [0])?[]:Facility::where ( [ 
				'AREA_ID' => $loArea [0]->ID 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		$intObjectType = IntObjectType::where ( [ 
				'DISPLAY_TYPE' => 1 
		] )->get ( [ 
				'CODE',
				'NAME' 
		] );
		
		$tmp = ucwords ( $intObjectType [0]->NAME );
		
		$mode = 'App\\Models\\' . str_replace ( ' ', '', $tmp );
		
		$type = !isset($facility [0])?[]:$mode::where ( [ 
				'FACILITY_ID' => $facility [0]->ID 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		return view ( 'front.diagram', [ 
				'codeFlowPhase' => $codeFlowPhase,
				'loProductionUnit' => $loProductionUnit,
				'loArea' => $loArea,
				'facility' => $facility,
				'intObjectType' => $intObjectType,
				'type' => $type 
		] );
	}
	public function onChangeObj(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$mode = 'App\\Models\\' . str_replace ( " ", "", $data ['TABLE'] );
		
		$result = $mode::where ( [ 
				$data ['keysearch'] => $data ['value'] 
		] )->get ( [ 
				'ID',
				'NAME' 
		] );
		
		return response ()->json ( $result );
	}
	public function getdiagram(Request $request) {
        $network_type = $request->exists('diagramType') ? $request->diagramType : 1;
        $tmp = NetWork::getDataWithNetworkType($network_type);
		return response ()->json ( $tmp );
	}
	public function loaddiagram($id) {
		$tmp = NetWork::where ( [ 
				'ID' => $id 
		] )->select ( 'XML_CODE' )->first ();
		return response ( $tmp->XML_CODE );
	}
	public function showTaskLog(Request $request){
		$data 		= $request->all ();
		$task_id 	= $data['task_id'];
		$log = TmWorkflowTask::where(['ID'=>$task_id])->get(['LOG'])->first();
		return response ()->json ($log);
	}	

	public function loadNetworkModel(Request $request) {
		$postData       = $request->all();
		$diagram_id     = $postData['id'];
		$occur_date		= $postData["date"];
		$refreshInterval= $postData["refresh"];
		$tmp            = NetWork::where ( ['ID' => $diagram_id] )
								->select ( 'XML_CODE' )
								->first ();
		return view ( 'graph.networkmodel',['xml'				=> $tmp?$tmp->XML_CODE:null,
											'diagram_id'		=> $diagram_id,
											'occur_date'		=> $occur_date,
											'refreshInterval'	=> $refreshInterval,
		]);
		
	}
	
	public function deletediagram(Request $request) {
		NetWork::where ( [ 'ID' => $request->ID ] )->delete ();
		return $this->getdiagram($request);
	}
	
	public function savediagram(Request $request) {
		$data = $request->all ();
		$condition = array (
				'ID' => $data ['ID']
		);
		$obj ['NAME'] = $data ['NAME'];
		$obj ['XML_CODE'] = urldecode ( $data ['KEY'] );
		$obj ['NETWORK_TYPE'] = $data ['TYPE'];
		
		//\DB::enableQueryLog ();
        if (config('database.default')==='sqlsrv') $result = $data ['ID'] != 0 ? NetWork::updateOrCreate ( $condition, $obj ) : NetWork::firstOrCreate($obj);
        else $result = NetWork::updateOrCreate ( $condition, $obj );
		//\Log::info ( \DB::getQueryLog () );
		
		return response ()->json ( $result->ID );
	}
	public function getSurveillanceSetting(Request $request) {
		$data 			= $request->all ();
		$cfgFieldProps 	= array ();
		$tags 			= array ();
		$surs 			= array ();
		$objType_id		= - 100;
		$objectType 	= "";
		$tag_other 		= array ();
		$other 			= "";
		$strMessage 	= null;
		$tagNames		= array_key_exists("tagNames", $data)?$data["tagNames"]:[];
		
		if (isset ( $data ['OBJECT_TYPE'] )) {
			$objectType = $data ['OBJECT_TYPE'];
		}
		
		if (isset ( $data ['OBJECT_ID'] )) {
			$objType_id = $data ['OBJECT_ID'];
		}
		
		if (isset ( $data ['SUR'] )) {
			$surs = explode ( '@', $data ['SUR'] );
		}
		
		$originAttrCase = \Helper::setGetterUpperCase();
		$cfgFields = CfgFieldProps::where ( [ 
				'USE_DIAGRAM' => 1 
		] )->where ( 'TABLE_NAME', 'like', $objectType . '%' )
            ->whereNull("CONFIG_ID")
            ->orderBy ( 'TABLE_NAME', 'COLUMN_NAME' )
            ->get ( [
				'COLUMN_NAME',
				'TABLE_NAME',
				'LABEL' 
		] );
		
		if (count ( $cfgFields ) > 0) {
			foreach ( $cfgFields as $v ) {
				$value = $v->TABLE_NAME . "/" . $v->COLUMN_NAME;
				
				$checked = (in_array ( $value, $surs, TRUE ) ? "checked" : "");
				
				$v ['CHECK'] = $checked;
				
				array_push ( $cfgFieldProps, $v );
			}
		}
		
		$intConnection = IntConnection::all ( [ 
				'ID',
				'NAME' 
		] );
		
		$intTagMapping 	= IntTagMapping::getTableName ();
		$intObjectType 	= IntObjectType::getTableName ();
		$wheres			= [];
		if ($objType_id != - 100){
			$wheres["$intTagMapping.OBJECT_ID"] = $objType_id;
		}
		if ($objectType&&$objectType>0&&$objectType!=''){
			$wheres["$intObjectType.CODE"] 		= $objectType;
		}
		$vTags 			= 	IntTagMapping::join ( $intObjectType, "$intTagMapping.OBJECT_TYPE", '=', "$intObjectType.ID" )
							->where ($wheres )
							->distinct ()
							->orderBy ( "$intTagMapping.TAG_ID" )
							->get ( [
									"$intTagMapping.TAG_ID",
									"$intTagMapping.TAG_ID AS CHECK"
							]);
		if (count ( $vTags ) > 0) {
			foreach ( $vTags as $t ) {
				$tagId		= $t->TAG_ID;
				$checked 	= (in_array ( $tagId, $surs, TRUE ) ? "checked" : "");
				$t->CHECK 	= $checked;
				$t->NAME 	= array_key_exists($tagId, $tagNames)?$tagNames[$tagId]:$tagId;
				array_push ( $tags, $t );
			}
		}
		else{
			$names = IntObjectType::where ( [ 'CODE' => $objectType ] )->select ( 'NAME' )->first ();
			$sname = (count ( $names ) > 0 ? $names->NAME : "");
			$strMessage = '<br><br><br><br><center><font color="#88000">No tag configured for <b>' . $sname . '</b>.</font><br><br><input type="button" style="width:145px;" id="btnTagsMapping" value="Config Tag Mapping"></center>';
		}
		
		if ($objType_id == - 100) {
			$strMessage = '<br><br><br><br><center><font color="#880000">No tag displayed because object is not mapped.</font><br><br><input type="button" style="width:160px;" id="openSurveillanceSetting" value="Object Mapping"></center>';
		}
		\Helper::setGetterCase($originAttrCase);
		return response ()->json ( [ 
				'cfgFieldProps' => $cfgFieldProps,
				'intConnection' => $intConnection,
				'tags' => $tags,
				'strMessage' => $strMessage 
		] );
	}
	
	function checkExpression($expression){
		//$expression = "Sur: {round(TAG(TAG 1)+10.555,2)}\nAbc = {FLOW_DATA_VALUE.FL_DATA_GRS_VOL(FLOW_ID=123)+ENERGY_UNIT_DATA_VALUE.EU_DATA_NET_VOL(EU_ID in [1,2],FLOW_PHASE=2 or FLOW_PHASE=3,EVENT_TYPE=1)} uom",
		//$pattern = (\w+)(\.(\w+))*\(([^()]+)\) find all TAG(...) or Table.Attr(Conditions)
		$exps = [];
		if(!$expression)
			return [];
		preg_match_all('/(\w+)(\.(\w+))*\(([^()]+)\)/i', $expression, $es, PREG_SET_ORDER);
		foreach($es as $ei){
			$exp = $ei[0];
			if($exp){
				if(strlen($ei[3])){
					$exps[$exp] = null;
					if(!isset($this->sqlStore[$exp]))
						$this->sqlStore[$exp] = ['table' => $ei[1], 'attr' => $ei[3], 'cond' => $ei[4]];
				}
				else{
					if(strtoupper($ei[1])=='TAG'){
						$tag = $ei[4];
						$exps["TAG($tag)"] = null;
						if(!in_array($tag, $this->tagStore))
							$this->tagStore[] = $tag;
					}
				}
			}
		}
		return array_keys($exps);
	}
	
	function fillExpression($expression, $exps = []){
		if(!$expression) return;
		$vals = [
			';;'	=> '".chr(59)."',
			'{{'	=> '".chr(123)."',
			'}}'	=> '".chr(125)."',
			'{'		=> '".(',
			'}'		=> ')."',
			';'		=> '',
		];
		//\Log::info($exps);
		//\Log::info($this->valueStore);
		foreach($exps as $exp)
			$vals[$exp] = (isset($this->valueStore[$exp]) ?
				($this->valueStore[$exp] != null ? $this->valueStore[$exp] : 'null') :
			'null');
			
		//fill values
		$expression = str_ireplace(array_keys($vals), $vals, $expression);
		
		//check unallowed functions
		$tks = token_get_all('<?php "'.$expression.'";');
		foreach($tks as $tk){
			if(isset($tk[0]) && ($tk[0] == 319 || $tk[0]==308)){
				$tk_name = $tk[1];
				if(!in_array($tk_name, ['null', 'abs', 'sqrt', 'round', 'rand', 'floor', 'ceil', 'exp', 'log', 'pi', 'pow', 'is_numeric', 'is_nan', 'min', 'max', 'log', 'sin', 'cos', 'tan', 'asin', 'acos', 'atan']))
					return "!Not allowed: $tk_name";
			}
		}

		try{
			\Log::info("aa:".$expression);
			$ret = eval('return "'.$expression.'";');
			\Log::info("-->".$ret);
			return $ret;
		} catch( \Throwable $ex ){
			\Log::info("evalExpression ($expression) throw error: ".$ex->getMessage());
			return "Expression error!";
		} catch( \Exception $ex ){
			\Log::info("evalExpression ($expression) error: ".$ex->getMessage());
			return "Expression error!";
		}
	}

	public function replaceConditionDate($cond, $key, $carbonDate){
		return preg_replace_callback('/'.$key.'([\+|\-]\d+){0,1}/i',
			function ($matches) use ($carbonDate) {
				$days = 0;
				if(isset($matches[1]) && is_numeric($matches[1]))
					$days = $matches[1];
				return "'".$carbonDate->addDays($days)->format('Y-m-d')."'";
			},
			$cond
		);
	}
	
	public function evalExpression($expression, $date = false){
		if(!$date)
			$date = date('Y-m-d');
		$exps = $this->checkExpression($expression);
		$sql = "";
		\Log::info($this->sqlStore);
		foreach($this->sqlStore as $exp => $sqlInfo)
			if(!isset($this->valueStore[$exp])){
				$sqlInfo['cond'] = $this->replaceConditionDate($sqlInfo['cond'], '@DATE', Carbon::parse($date));
				$sqlInfo['cond'] = $this->replaceConditionDate($sqlInfo['cond'], '@NOW', Carbon::now());
				//if (!preg_match("/\bOCCUR_DATE\b/i", $sqlInfo['cond']))
				//	$sqlInfo['cond'] .= ($sqlInfo['cond']?',':'')."OCCUR_DATE='$date'";
				$sql .= ($sql?" union all ":"")."select ".$sqlInfo['attr']." as value, '".str_replace("'", "''", $exp)."' as exp from ".$sqlInfo['table']." where (".implode(') AND (', explode(',', $sqlInfo['cond'])).')';
				$this->valueStore[$exp] = null;
			}
		if($sql){
			\Log::info($sql);
			$data = \DB::select($sql);
			foreach($data as $row)
				$this->valueStore[$row->exp] = $row->value;
		}
		$tags = [];
		foreach($this->tagStore as $tag)
			if(!isset($this->valueStore["TAG($tag)"])){
				$tags[] = $tag;
				$this->valueStore["TAG($tag)"] = null;
			}
		if(count($tags)){
			//$data = $this->getIP21Data($tags);
			$data = $this->getInterface()->getExtData($tags);
			foreach($data as $tag => $tagData)
				$this->valueStore["TAG($tag)"] = $tagData['value'];
		}
		return $this->fillExpression($expression, $exps);
	}

	function evalExps($exps, $occur_date, $filters){
		$expData = [];
		if(!$exps) return [];
		if(count($exps) == 0) return [];
		
		foreach($exps as $cell_id => $exp)
			$expData[$cell_id] = [$exp, $this->checkExpression($exp)];
		
		try{
			$this->evalExpression(null, $occur_date);
			foreach($expData as $cell_id => $expInfo)
				$expData[$cell_id] = $this->fillExpression($expInfo[0], $expInfo[1]);
		}
		catch( \Throwable $ex ){
			\Log::info("evalExpression error: ".$ex->getMessage());
			return response ()->json (["data" => "Expression error!\n\n".$ex->getMessage()]);
		}
		catch( \Exception $ex ){
			\Log::info("evalExpression error: ".$ex->getMessage());
			return response ()->json (["data" => "Expression error!\n\n".$ex->getMessage()]);
		}
		return $expData;
	}
	
	public function getValueSurveillance(Request $request) {
		$data 		= $request->all ();
		$flow_phase = array_key_exists('flow_phase', $data)?$data ['flow_phase']:'';
		$vparam 	= array_key_exists('vparam', $data)?$data ['vparam']:'';
		$occur_date	= array_key_exists('occur_date', $data)?$data ['occur_date']:'';
		$occur_date = $occur_date&&$occur_date!=""?\Helper::parseDate($occur_date):Carbon::now()->format('Y-m-d');
		$ret 		= "";
// 		$date_begin = $occur_date->startOfDay();
// 		$date_end 	= $occur_date->endOfDay();
		
		if (!$vparam || !is_array($vparam) ||count($vparam)<=0)  return response ()->json ( "empty param" );
		
		$cells_data = [];
		$fieldConfigs = [];
		$cellConfigs = [];
		$conn_objs	= [];
		$exps = [];
		foreach ( $vparam as $v ) {
			$cell_id 				= $v ['ID'];
			$cellConfigs[$cell_id]	= [];
			$exp 					= array_key_exists('EXP', $v)?$v ['EXP']:'';
			if($exp){
				$exps[$cell_id] = $exp;
				continue;
			}

			$object_type			= array_key_exists('OBJECT_TYPE', $v)?$v ['OBJECT_TYPE']:'';
			$object_id				= array_key_exists('OBJECT_ID', $v)?$v ['OBJECT_ID']:'';
			$tagNames 				= array_key_exists('tagNames', $v)?$v ['tagNames']:[];
			$conn_id 				= array_key_exists('CONN_ID', $v)?$v ['CONN_ID']:-1;
			$su 					= array_key_exists('SU', $v)?$v ['SU']:'';

			if ($object_type == 'ENERGY_UNIT' && array_key_exists('SUR_PHASE_CONFIG', $v)) {
				$phase_config 		= $v ['SUR_PHASE_CONFIG'];
				if ($phase_config&&is_array($phase_config)) {
					foreach ( $phase_config as $phaseObject ) {
						$flowPhase 		= $phaseObject ["phaseId"];
						$eventType 		= $phaseObject ["eventType"];
						$phase1 		= explode ( "/", $phaseObject ["dataField"] );
						$table 			= $phase1 [0];
						$field 			= $phase1 [1];
						if (! $field) $field = "EU_DATA_GRS_VOL";
						if (! $table) $table = "ENERGY_UNIT_DATA_VALUE";
						$model 			= \Helper::getModelName ( $table );
	
						// 					\DB::enableQueryLog ();
						$tmp 			= 	$model::where ( [ 'EU_ID' => $object_id ] )
													->whereDate ( 'OCCUR_DATE', '=', $occur_date)
													->where([	'FLOW_PHASE'	=> $flowPhase,
																'EVENT_TYPE'	=> $eventType])
													->select ( [
															$field . ' AS FIELD_VALUE',
															'FLOW_PHASE',
															'EVENT_TYPE'])
													->first();
						// 					\Log::info ( \DB::getQueryLog () );
						if ($tmp) {
							$value 					= $tmp->FIELD_VALUE;
							$value 					= is_numeric ( $value )?number_format ( $value, 2 ):"--";
							$phaseObject["value"]	= $value;
							$cellConfigs[$cell_id]	["%SF"][] 	= $phaseObject;
								
							$item	= new EnergyUnit;
							$item->{$item::$idField}	= $object_id;
							$item->DT_RowId				= $object_id;
							if (!array_key_exists($table, $fieldConfigs)) $fieldConfigs[$table] = [	"fields"	=> [$field	=> []],
																									"model"		=> $model	];
							$fieldConfigs[$table]["fields"][$field][$cell_id] = $item;
						}
					}
				}
			}
			
			$field_tables = explode ( "@", $su );
			$label = "";
			$item	= null;
			foreach ( $field_tables as $field_table ) {
				if (strpos ( $field_table, "TAG:" ) !== FALSE) {
					if ($conn_id > 0) {
						$tagId			= substr($field_table, 4, strlen($field_table)-4);
						$tagNameLabel	= array_key_exists($tagId, $tagNames)?$tagNames[$tagId]:$tagId;
						$conn_objs ["$conn_id"] [] = 	["cellId"		=> $cell_id,
														"tag"			=> $tagId,
														"tagName"		=> $tagNameLabel
						];
					}
				} else {
					if ($field_table) {
						$f = explode ( "/", $field_table );
						if (count($f)<2) continue;
						$table = $f [0];
						$field = $f [1];
						$label = CfgFieldProps::where ( [ 
								'TABLE_NAME' => $table,
								'COLUMN_NAME' => $field 
						] )->select ( 'LABEL' )->first ();
						if (count ( $label ) > 0) {
							$xlabel = $label->LABEL;
						} else {
							$xlabel = "$table/$field";
						}
						
						$model = strtolower ( $table );
						$model = str_replace ( ' ', '', ucwords ( str_replace ( '_', ' ', $model ) ) );
						$model = 'App\\Models\\' . $model;
						
						$condition = array ();
						$item		= null;
						if ($object_type == 'FLOW') {
							$value = "--";
							$condition ['FLOW_ID'] = $object_id;
							$item	= new Flow;
							$item->{$item::$idField}	= $object_id;
							$item->DT_RowId				= $object_id;
						} else {
							if ($object_type == 'TANK') {
								$value = "--";
								$condition ['TANK_ID'] = $object_id;
							} else {
								if ($object_type == 'STORAGE') {
									$value = "--";
									$condition ['STORAGE_ID'] = $object_id;
								} else {
									if ($object_type == 'EQUIPMENT') {
										$value = "--";
										$condition ['EQUIPMENT_ID'] = $object_id;
									} else {
										if ($object_type == 'ENERGY_UNIT') {
											$value = "--";
											$condition ['EU_ID'] = $object_id;
											$condition ['FLOW_PHASE'] = $flow_phase;
											$item	= new EnergyUnit;
											$item->{$item::$idField}	= $object_id;
											$item->DT_RowId				= $object_id;
										} else {
											$value = "--";
										}
									}
								}
							}
						}
						
// 						\DB::enableQueryLog ();
						$values = $model::where ( $condition )->whereDate ( 'OCCUR_DATE', '=', $occur_date)->SELECT ( [ 
								$field . ' AS FIELD_VALUE' 
						] )->first ();
// 						\Log::info ( \DB::getQueryLog () );
						
						if (count ( $values ) > 0) {
							$value = $values->FIELD_VALUE;
						}
						$rawValue	= $value;
						$value = (is_numeric ( $value ) ? number_format ( $value, 2 ) : $value);
						$cells_data ["$cell_id"] ["$xlabel"] 	= $value;
						$cellConfigs[$cell_id]["$table/$field"] = ["table"=>$table,"field"=>$field,"value"	=> $rawValue, "OBJECT_ID"	=> $object_id];
						
						if (!array_key_exists($table, $fieldConfigs))
							$fieldConfigs[$table] = [	"fields"	=> [$field	=> []],
														"model"		=> $model,
							];
						$fieldConfigs[$table]["fields"][$field][$cell_id] = $item;
						
					}
				}
			}
		}
		
		foreach ( $cells_data as $cell_id => $cell_data ) {
			$ret .= ($ret == "" ? "" : "#") . "$cell_id^";
			foreach ( $cell_data as $data_label => $data_value ) {
				if ($data_label == "%SF") {
					$sv = "";
					foreach ( $data_value as $flow_phase => $phase_value ) {
						$sv .= ($sv == "" ? "" : "%SV") . $flow_phase . "%SV" . $phase_value;
					}
					$ret .= "%SF^$sv" . "#" . "$cell_id^";
				} else
					$ret .= "$data_label: $data_value\n";
			}
		}
		
		$properties 	= $this->getExtendProperties($fieldConfigs,$occur_date,$cellConfigs);
		$connectionData	= $this->loadConnectionData($conn_objs,$cellConfigs);

		\Log::info($exps);
		$expData = $this->evalExps($exps, $occur_date);
		
		\Log::info($expData);
		return response ()->json ([
									"data"				=> "ok$ret",
									"fieldConfigs"		=> 	$fieldConfigs,
									"cellConfigs"		=> 	$cellConfigs,
									'properties'		=>	$properties,
									'connectionData'	=>	$connectionData,
									'expressionData'	=>	$expData,
		] );
	}
	
	public function loadConnectionData($conn_objs,&$cellConfigs) {
		//\Log::info($conn_objs);
		//\Log::info($cellConfigs);
		$arrTags = [];
		foreach ( $conn_objs as $conn_id => $tags ) {
			foreach ( $tags as $index => $tag ) {
				$arrTags[] = $tag["tag"];
			}
		}
		$data = $this->getIP21Data($arrTags);
		foreach ( $conn_objs as $conn_id => $tags ) {
			foreach ( $tags as $index => $tag ) {
				$cell_id		= $tag["cellId"];
				$tagId			= $tag["tag"];
				$tagName		= $tag["tagName"];
				$cellConfigs[$cell_id][$tagId] 	= ["tag"		=> $tagId,
													"value"		=> (array_key_exists($tagId, $data)?($data[$tagId]['value'].' '.utf8_encode($data[$tagId]['unit'])):""),
													"tagName"	=> $tagName
				];
			}
		}
		return $conn_objs;
	}
	
	function getIP21Data($tags){
		$testMode = config('constants.simulateExternalDatasource',true);
		$cond = "";
		$data = [];
		$time = strtotime(date('Y-m-d H:i:s').' UTC') * 1000;//Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), 'UTC')->timestamp * 1000;
		foreach($tags as $tag) if($tag) {
			$cond .= ($cond?" or ":"")."NAME='$tag'";
			if($testMode) $data[$tag] = ["value" => rand(100,1000), "time" => $time, "unit" => "uom","tag" => $tag];//rand(10,100);
		}
		if($testMode) return $data;
		
		if(!$cond)
			return $data;
		$conn = IntConnection::select('SERVER','USER_NAME','PASSWORD')->first();

		$server = $conn['SERVER'];
		$username = $conn['USER_NAME'];
		$password = $conn['PASSWORD'];

		$connection = new \COM("ADODB.Connection");
		$connection_string = "Driver={AspenTech SQLPlus};HOST=$server;PORT=10014";
		$connection->Open("$connection_string;User ID=$username;Password=$password;");
		
		$sql = "select NAME,IP_INPUT_VALUE,IP_ENG_UNITS,IP_INPUT_TIME from IP_SAN_Anadef where $cond"; //IP_ENG_UNITS,IP_INPUT_TIME
		$result_set = $connection->Execute($sql);
		$i=0;
		while (!$result_set->EOF && $i<100) 
		{   
			$NAME=$result_set->fields[0]->value;
			$IP_INPUT_VALUE=round($result_set->fields[1]->value,3);
			$IP_ENG_UNITS=$result_set->fields[2]->value;
            $time = \DateTime::createFromFormat('d-M-y H:i:s.u', $result_set->fields[3]->value)->format('Y-m-d H:i:s').".000";
			$data[$NAME] = ["value" => $IP_INPUT_VALUE,
                "time" => $time,
                "unit" => mb_convert_encoding($IP_ENG_UNITS, 'UTF-8', 'UTF-8'),
                "tag" => $NAME];
			$result_set->MoveNext();
			$i++;
		}
		return $data;
	}
	
	function getIP21HistoricalData($tagsInfo, $startTime, $sampleInterval = false,$date_end = false){
		$testMode 	= config('constants.simulateExternalDatasource',true);
		$cond 		= "";
		$data 		= [];
		$now 		= Carbon::now('UTC');
        $time 		= $now->toDateTimeString();
		$lastTime	= !$sampleInterval?$startTime:$startTime.".00";
		
		//\Log::info("getIP21HistoricalData sampleInterval $sampleInterval startTime $startTime testMode $testMode");
		
		if ($sampleInterval) {
			$beginTime 			= $startTime.".00";
			$endTime 			= $date_end?$date_end:null;
			$endTimeCondition	= $endTime? " and IP_TREND_TIME <='$endTime'":"";
		}
		else {
			$beginTime 			= $startTime;
			$endTime 			= $date_end;
			$endTimeCondition	= " and IP_TREND_TIME <='$endTime'";
		}
		//\Log::info("getIP21HistoricalData count tagsInfo  ".count($tagsInfo));
		foreach($tagsInfo as $tagInfo){
			$tag		= $tagInfo->TAG_ID;
			//\Log::info("getIP21HistoricalData tag $tag ");
				
			$cond 		.= ($cond?" or ":"")."(NAME='$tag' and IP_TREND_TIME>'$lastTime' $endTimeCondition)";
			if($testMode) {
				if ($sampleInterval) {
					$endTimeCondition	= $endTime? " and LOGIN_TIME <='$endTime'":"";
					$roundDatetime = \Helper::getRoundDatetimeFunction("LOGIN_TIME",$sampleInterval,$testMode);
					$sql 	= "select top 100 AVG(t.ID) as IP_TREND_VALUE ,
								t.IP_TREND_TIME
								from (
								  select USERNAME, IP,ID,
									$roundDatetime as IP_TREND_TIME
									from log_user
									where LOGIN_TIME >= '$beginTime'
									$endTimeCondition
								  ) t
								group by t.IP_TREND_TIME
								order by t.IP_TREND_TIME";
					$dataSet = \DB::select($sql);
					//\Log::info("getIP21HistoricalData sql $sql ");
					foreach($dataSet as $kid => $row){
						$data[] = ["tag" => $tag, "value" => rand(10,100), "time" => $row->IP_TREND_TIME, "unit" => "uom $kid"];
					}
				}
				else{
					$data[] = ["tag" => $tag, "value" => rand(10,100), "time" => $time, "unit" => "uom"];
				}
				
			}
		}
		if($testMode) return $data;
		
		//\Log::info("getIP21HistoricalData cond $cond");
		
		if(!$cond) return $data;
		
		$conn = IntConnection::select('SERVER','USER_NAME','PASSWORD')->first();

		$server = $conn['SERVER'];
		$username = $conn['USER_NAME'];
		$password = $conn['PASSWORD'];

		$connection = new \COM("ADODB.Connection");
		$connection_string = "Driver={AspenTech SQLPlus};HOST=$server;PORT=10014";
		$connection->Open("$connection_string;User ID=$username;Password=$password;");
		
		//\Log::info($tm);
		
		if ($sampleInterval) {
			if ($sampleInterval<=30000) {
				$sql = "select NAME,IP_TREND_VALUE,IP_ENG_UNITS,IP_TREND_TIME from IP_SAN_AnaDef_1 where ($cond) and IP_TREND_TIME >='$beginTime' order by IP_TREND_TIME";
			}
			else{
				$roundDatetime = \Helper::getRoundDatetimeFunction("IP_TREND_TIME",$sampleInterval,$testMode);
				$sql 	= "select t.NAME,AVG(t.IP_TREND_VALUE) as IP_TREND_VALUE, t.IP_ENG_UNITS,t.IP_TREND_TIME2
							from (
								select NAME, ''||IP_ENG_UNITS as IP_ENG_UNITS,IP_TREND_VALUE,
								$roundDatetime as IP_TREND_TIME2
								from IP_SAN_AnaDef_1
								where ($cond) and IP_TREND_TIME >='$beginTime'
							) t
							group by t.NAME,t.IP_ENG_UNITS,t.IP_TREND_TIME2
							order by t.IP_TREND_TIME2";
			}
		}
		else{
			$sql = "select NAME,IP_TREND_VALUE,IP_ENG_UNITS,IP_TREND_TIME from IP_SAN_AnaDef_1 where ($cond) and IP_TREND_TIME >='$beginTime'$endTimeCondition order by IP_TREND_TIME";
		}
		
		//\Log::info($sql);
		$result_set = $connection->Execute($sql);
		$i=0;
		while (!$result_set->EOF && $i<10000) 
		{   
			$tagName=$result_set->fields[0]->value;
			$value=round($result_set->fields[1]->value,3);
			$unit=$result_set->fields[2]->value;
			if (!$sampleInterval||$sampleInterval<=30000) {
				$time = \DateTime::createFromFormat('d-M-y H:i:s.u', $result_set->fields[3]->value)->format('Y-m-d H:i:s').".000";
			}
			else {
				$time = $result_set->fields[3]->value;
			}
				
			$data[] = ["tag" => $tagName, "value" => $value, "time" => $time, "unit" => mb_convert_encoding($unit, 'UTF-8', 'UTF-8')];
			$result_set->MoveNext();
			$i++;
		}
		//\Log::info($data);
		return $data;
	}

	public function getExtendProperties($fieldConfigs,$occur_date,&$cellConfigs) {
		$properties 		= new Collection;
    	$rQueryList			= [];
    	$index				= 0;
    	$cfgFieldProps		= CfgFieldProps::getTableName();
		foreach ( $fieldConfigs as $table => $fields ) {
			$mdlName			= $fields["model"] ;
			foreach ( $fields["fields"] as $field => $fieldData) {
				$where	= [	"$cfgFieldProps.TABLE_NAME" 	=> $table,
							"$cfgFieldProps.COLUMN_NAME" 	=> $field,
				];
				$property	= CfgFieldProps::getOriginProperties($where,false)->first();
				if ($property&&$property instanceof CfgFieldProps) {
					foreach ( $fieldData as $cell_id => $item) {
						if ($item&&$property->shouldLoadLastValueOf($item)) {
							$column		= $property->data;
							$query		= $item->getLastValueOfColumn($mdlName,$column,$occur_date);
							if ($query) {
								if (!array_key_exists($column, $rQueryList)) $rQueryList[$column] = [];
								$rQueryList[$column][]	= $query;
							}
						}
						if (array_key_exists("$table/$field", $cellConfigs[$cell_id])) $cellConfigs[$cell_id]["$table/$field"]["index"] = $index;
					}
					$properties->push($property);
					$index++;
				}
			}
 			$fieldConfigs[$table] = $fields;
		}
		$this->updatePropertiesWithLastValue($properties,$rQueryList);
		return $properties;
	}
	
	public function uploadFile() {
		$files = Input::all ();
		$tmpFilePath = '/fileUpload/';
		$error = false;
		if (count ( $files ) > 0) {
			// foreach ($files as $file){
			$file = $files[1];
			$tmpFileName = $file->getClientOriginalName ();
			$v = explode ( '.', $tmpFileName );
			$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];
			$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
			if ($file) {
				$result = $tmpFilePath . $tmpFileName;
				$this->file = $result;
			} else {
				$error = true;
			}
			// }
			$data = ($error) ? [ 
					'error' => 'There was an error uploading your files' 
			] : [ 
					'files' => $result 
			];
		} else {
			$data = array (
					'success' => 'Form was submitted',
					'formData' => $_POST 
			);
		}
		return response ()->json ( $data );
	}
	
	public function uploadImg() {
		$files = Input::all ();
		$tmpFilePath = '/images/upload/';
		$error = false;
		if (count ( $files ) > 0) {
			// foreach ($files as $file){
			if(count ( $files ) == 1){
				$file = $files [0];
			}else{
				$file = $files [1];
			}
			$tmpFileName = $file->getClientOriginalName ();
			$v = explode ( '.', $tmpFileName );
			$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];
			$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
			if ($file) {
				$result = $tmpFilePath . $tmpFileName;
				$this->file = $result;
			} else {
				$error = true;
			}
			// }
			$data = ($error) ? [
					'error' => 'There was an error uploading your files'
			] : [
					'files' => $result
			];
		} else {
			$data = array (
					'success' => 'Form was submitted',
					'formData' => $_POST
			);
		}
		return response ()->json ( $data );
	}
	
	public function _indexTagsMapping() {
		return view ( 'front.tagsmapping' );
	}
	public function _indexWorkFlow() {
		$originAttrCase = \Helper::setGetterUpperCase();
		$ebfunctions = EbFunctions::loadForTaskGroup();
		$funcs = EbFunctions::where('USE_FOR', 'like', '%TASK_FUNC%')
			->get(['CODE', 'NAME', 'PARENT_CODE']);
		$result = [];
		foreach ($ebfunctions as $eb){
			$name = "";
			if(!is_null($eb->PARENT_CODE)){
				$name = "---";
			}
			
			$eb['FUNCTION_NAME'] = $name.$eb->NAME;
			$eb['FUNCTION_CODE'] = $eb->CODE;
			$eb['FUNCTION_URL'] = $eb->PATH;
			$arr = [];
			foreach ($funcs as $func) if($func->PARENT_CODE == $eb->CODE){
				$arr[]=[
					'FUNCTION_CODE' => $func->CODE,
					'FUNCTION_NAME' => $func->NAME,
				];
			}
			$eb['FUNCTIONS'] = $arr;
			array_push($result, $eb);
		}
		
		$users = array();
		$user = User::where(['ACTIVE'=>1])->get(['USERNAME', 'LAST_NAME', 'MIDDLE_NAME', 'FIRST_NAME']);		
		foreach ($user as $u) {
			$u['NAME'] = $u->LAST_NAME.' '.$u->MIDDLE_NAME.' '.$u->FIRST_NAME;
			
			array_push($users, $u);
		}
		
		$foGroup = FoGroup::all('ID', 'NAME');
		\Helper::setGetterCase($originAttrCase);
		
		return view ( 'front.workflow',array ('ebfunctions'=>$result, 'user'=>$users, 'foGroup'=>$foGroup) );
	}
	public function getListWorkFlow() {
		$result = TmWorkflow::loadActive();
		return response ()->json ( [ 
				'result' => $result 
		] );
	}
	public function getXMLCodeWF(Request $request) {
		$param = $request->all ();
		
		$diagram_id = isset ( $param ['ID'] ) ? $param ['ID'] : 0;
		$readonly = isset ( $param ['readonly'] ) ? $param ['readonly'] : 0;
		
		$originAttrCase = \Helper::setGetterUpperCase();
		$result = TmWorkflow::where ( [ 
				'ID' => $diagram_id 
		] )->select (  
				'DATA',
				'NAME',
				'INTRO',
				'ISRUN' 
		)->first();
		
		$xml = $result ['DATA'];
		if ($readonly) {
			$tmWorkflowTask = TmWorkflowTask::where ( [ 
					'WF_ID' => $diagram_id 
			] )->get ( [ 
					'ID',
					'TASK_CODE',
					'ISRUN',
					'RUNBY',
					'START_TIME',
					'FINISH_TIME',
					'USER'.($this->isReservedName?'_':''),
					'LOG' 
			] );
			
			$flowTask = array ();
			$current_username = auth()->user()->username;
			foreach ( $tmWorkflowTask as $tm ) {
				if ($tm->LOG == "") {
					$tm ['LOG'] = 0;
				} else {
					$tm ['LOG'] = 1;
				}
				$granted = 0;
				if (strpos(','.$tm ['USER'.($this->isReservedName?'_':'')].',', ','.$current_username.',') !== false) {
					$granted = 1;
				}				
				$xml = str_replace ( 'task_id="' . $tm->ID . '"', 'task_id="' . $tm->ID . '" granted="' . $granted . '" has_log="' . $tm->LOG . '" start_time="' . $tm->START_TIME . '" finish_time="' . $tm->FINISH_TIME . '" task_code="' . $tm->TASK_CODE . '" isrun="' . $tm->ISRUN . '" autorun="' . ($tm->RUNBY == 1 ? 1 : 0) . '"', $xml );
				$result ['DATA'] = $xml;
			}
		}
		\Helper::setGetterCase($originAttrCase);
		return response ()->json ( [ 
				'result' => $result 
		] );
	}
	
	function getUpdatedTaskString($task_config, &$arr_mapping){
		$task_ids = explode(',', $task_config);
		$tasks_config = null;
		foreach($task_ids as $task_id){
			$task_id = trim($task_id);
			if($task_id)
				$tasks_config .= (array_key_exists($task_id, $arr_mapping)?$arr_mapping[$task_id]:$task_id).",";
		}
		return $tasks_config;
	}

	public function workflowSave(Request $request) {
		$data = $request->all ();
		$isSaveAs = ($data ['ADD'] == 1);
		$wf_id = $isSaveAs?null:$data ['ID'];
		$isCreateNewWorkflow = false;
		$result	= "no change";
		//\Helper::setGetterUpperCase();
		DB::beginTransaction ();
		try {
			
			$objwf = [];
// 			$objwf ['ID'] = $wf_id;
			$objwf ['NAME'] = $data ['NAME'];
			$objwf ['INTRO'] = $data ['INTRO'];
			if($isSaveAs){
				$objwf ['ISRUN'] = '0';
			}
//			$objwf ['DATA'] = $data ['KEY'];
			$objwf ['STATUS'] = 1;
			
			if ($wf_id>0) {
				$condition = [
						'ID' => $wf_id 
				];
				$tmWorkflow = TmWorkflow::updateOrCreate ( $condition, $objwf );
			}
			else{
				$tmWorkflow = TmWorkflow::create ( $objwf );
				$wf_id = $tmWorkflow->ID;
				$isCreateNewWorkflow = true;
			}
			
			//=$dom_xml->xpath ( '//mxCell[@edge = 1]/parent::*' );
			$dom_xml = simplexml_load_string ( $data ['KEY'] );
			$cells = $dom_xml->xpath ( '//mxCell[@vertex=1]/parent::*' );
			$relations = $dom_xml->xpath ( '//mxCell[@edge = 1]' );
			//\Log::info($cells); return response ()->json ( $result );

			$task_ids = [];
			$cell_task_ids = [];
			$arr_mapping = [];
			foreach ( $cells as $cell ) {
				$objwf_task = [];
				
				$task_id = ($isSaveAs?null:(int)$cell ['task_id']);
					  
					

												

				$objwf_task ['wf_id'] = $wf_id;
				if (isset ( $cell ['isbegin'] )) {
					$objwf_task ['name'] = 'Begin';
					$objwf_task ['isbegin'] = 1;
				}
				if (isset ( $cell ['isend'] )) {
					$objwf_task ['name'] = 'End';
					$objwf_task ['isbegin'] = - 1;
				}
				if (isset ( $cell ['task_data'] )) {
					$param = json_decode ( "".$cell ['task_data'] );
					$cell_style = $cell->children () [0]->attributes () ['style'];
					
					if (strpos ( $cell_style, 'style_plus' ) !== false)
						$param->task_code = 'NODE_COMBINE';
					else if (strpos ( $cell_style, 'rhombus' ) !== false)
							$param->task_code = 'NODE_CONDITION';
						
					$objwf_task ['name'] = addslashes ( $param->name );
					$objwf_task ['runby'] = addslashes ( $param->runby );
					$objwf_task ['user'.($this->isReservedName?'_':'')] = addslashes ( $param->user );
					$objwf_task ['task_group'] = addslashes ( $param->task_group );
					$objwf_task ['task_code'] = addslashes ( $param->task_code );
				}
				
				if (isset ( $cell ['task_config'] )) 
					$objwf_task ['task_config'] = "".$cell['task_config'];

								 
				if ($task_id>0) {
					TmWorkflowTask::where(['id' => $task_id])->update($objwf_task);
					   
	   
																							 
				}
				else{
					$tmTask = TmWorkflowTask::create ($objwf_task );
					$task_id = $tmTask->id;
				}
				$task_ids [] = $task_id;
				$cell_id = "".$cell['id'];
				$cell['task_id'] = $task_id;
				$cell_task_ids[$cell_id] = $task_id;
			}
			$next_config = [];
			$prev_config = [];
			foreach($relations as $relation){
				$source = "{$relation['source']}";
				$target = "{$relation['target']}";
				$next_config[$source] = (isset($next_config[$source])?$next_config[$source]:"").$cell_task_ids[$target].',';
				$prev_config[$target] = (isset($prev_config[$target])?$prev_config[$target]:"").$cell_task_ids[$source].',';
			}
			foreach($cells as $cell){
				$cell_id = "".$cell['id'];
				$task_id = (int)$cell ['task_id'];
				TmWorkflowTask::where(['id'=>$task_id])->update([
					'next_task_config'=>isset($next_config[$cell_id])?$next_config[$cell_id]:null,
					'prev_task_config'=>isset($prev_config[$cell_id])?$prev_config[$cell_id]:null
							  
													 
												   
												  
				]);
			}

			if(!$isCreateNewWorkflow){
				TmWorkflowTask::where(['WF_ID'=>$wf_id])
							->whereNotIn('ID', $task_ids)
							->delete();
			}
			
			TmWorkflow::where(['ID'=>$wf_id])->update(['DATA' => $dom_xml->asXml()]);
			$result	= $wf_id;
			
 		} catch ( \Exception $e ) {
 			if (!$e) $e = new \Exception("Exception wher run save workflow no message");
			$result	= $e->getMessage();
 			\Log::info("\n------------------------------------------------------------------------------------------\nException wher run save workflow\n ");
 			\Log::info ($result);
 			\Log::info($e->getTraceAsString());
 			DB::rollback ();
		}
		 
		DB::commit ();
		
		return response ()->json ( $result );
	}
	
	private  function getIdTaskByWf($wfid){		
		return TmWorkflowTask::where(['WF_ID'=>$wfid])->get(['ID']);
	}
	
	public function loadConfigTask(Request $request) {
		$data = $request->all ();
		
		$id=isset($data['taskid'])?$data['taskid']:0;
		
		if($id == -1){
			$id = $this->getkeyConfig();
		}
		
		$flowTask = TmWorkflowTask::find($id);
		
		return response ()->json ( ['flowTask'=>$flowTask] );
	}
	
	private function getkeyConfig(){
		$data = Params::where(['KEY'=>'WF_TASK_ID'])->select('NUMBER_VALUE')->first();
		$key = ($data->NUMBER_VALUE == "")?0:$data->NUMBER_VALUE + 1;
		
		Params::where(['KEY'=>'WF_TASK_ID'])->update(['NUMBER_VALUE'=>$key]);
		
		return $key;
	}
	
	public function getKey(){
		$key = $this->getkeyConfig();
		
		return response ()->json ($key);
	}
	
	public function changeRunTask(Request $request) {
		$data = $request->all ();
		
		$parent_code = $data['PARENT_CODE'];
		
		if($parent_code != 'workflow-fun'){
			$ebfunctions = EbFunctions::where('USE_FOR', 'like', '%TASK_FUNC%')
			->where(['PARENT_CODE'=>$parent_code])
			->get(['CODE AS FUNCTION_CODE', 'NAME AS FUNCTION_NAME', 'PATH AS FUNCTION_URL']);
		}else{
			$ebfunctions = TmWorkflow::all(['ID AS FUNCTION_CODE', 'NAME AS FUNCTION_NAME']);
		}
		
		return response ()->json ( array ('ebfunctions'=>$ebfunctions) );
	}
	
	public function loadFormSetting(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		$result = array();
		$value = $data['value'];
		$task_id = isset($data['task_id'])?$data['task_id']:0;
		
		switch($value){
			case 'ALLOC_CHECK':
			case 'ALLOC_RUN':
				$network = Network::getTableName();
				$allocJob = AllocJob::getTableName();
					 
				$tm = [];
				$tm = DB::table($network)->where(['NETWORK_TYPE'=>1])->get(['ID', 'NAME']);
				
				$alloc_job = AllocJob::where(['NETWORK_ID'=>$tm[0]->ID])->get(['ID', 'NAME']);
				
				$result['network'] = $tm;
				$result['allocJob'] = $alloc_job;
				
				break;
			case 'VIS_REPORT':
				$reports = DB::table("RPT_REPORT")->where(['ACTIVE'=>1])->get(['ID as VALUE','FILE as ID', 'NAME']);
				$request->merge(['type' => "core.report_form"]);
				$result['reports'] = $reports;
/*
				return (new ReportController)->_index($request);
// 				$result = Facility::all(['ID', 'NAME']);
// 				break;
*/
			case 'FDC_EU':
				$models = ['Facility', 'EnergyUnitGroup', 'CodeReadingFrequency', 'CodeFlowPhase', 'CodeEventType', 'CodeAllocType', 'CodePlanType', 'CodeForecastType'];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}				
				break;
			case 'FDC_FLOW':
				$models = ['Facility', 'CodeReadingFrequency', 'CodeFlowPhase'];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}
				break;
			case 'FDC_EU_TEST':
				$models = ['Facility', 'EnergyUnit'];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}
				break;
			case 'FDC_STORAGE':
				$models = ['Facility', 'CodeProductType',];
				foreach ($models as $m){
					$tm = [];
					$model = 'App\\Models\\' .$m;
					$tm = $model::all(['ID', 'NAME']);
					$result[$m] = $tm;
				}
				break;
			case 'INT_IMPORT_DATA':
				$tm = [];
				$tm = IntConnection::all(['ID', 'NAME']);
				
				$intTagSet = IntTagSet::where(['CONNECTION_ID'=>$tm[0]->ID])->get(['ID', 'NAME']);
				
				$result['IntConnection'] = $tm;
				$result['IntTagSet'] = $intTagSet;
				
				break;
			default:
				$result = [];
		}
		
		//$task = TmWorkflowTask::where(['ID'=>$task_id])->get();
		//$result['task'] = $task;
		$result['value'] = $value;
		
		return response ()->json ( array ('result'=>$result) );
	}
	
	public function getEntity(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();		
		$value = $data['VALUE'];
		$key = $data['KEY'];
		$entity = $data['TABLE'];
		
		$model = 'App\\Models\\' .$entity;
		$result = $model::where([$key=>$value])->get(['ID','NAME']);
		
		return response ()->json ( array ('result'=>$result) );
	}
	
	public function workflowSaveTask(Request $request){
		$data = $request->all ();
		
		$type= isset($data['type'])?$data['type']:'';
		$tasks=isset($data['taskdata'])?$data['taskdata']:'';
		$wfid=$data['wfid'];
		$key=$data['key'];
		$objwf_task = [];
		if($tasks!=''){
			$tasks=json_decode($tasks);
			
			TmWorkflow::where(['ID'=>$wfid])->update(['DATA'=>$key]);
			
			$tmpTaskId	= 0;
			if(!empty($tasks->id)){
				$tmpTaskId	= (int)$tasks->id;
			}
			$objwf_task['wf_id'] = $wfid;
			$objwf_task['name'] = addslashes($tasks->name);
			$objwf_task['runby'] = addslashes($tasks->runby);
			$objwf_task['user'] = addslashes($tasks->user);
			$objwf_task['task_group'] = addslashes($tasks->task_group);
			$objwf_task['task_code'] = addslashes($tasks->task_code);
			
			if(isset($data['taskconfig'])){
				$objwf_task['task_config'] = $data['taskconfig'];
				
				if (isset($tasks->next_task_config)) $objwf_task['next_task_config'] = addslashes(str_replace('NaN,','',$tasks->next_task_config));
				if (isset($tasks->prev_task_config)) $objwf_task['prev_task_config'] = addslashes(str_replace('NaN,','',$tasks->prev_task_config));
				
				$conTask = ['id' => $tmpTaskId];
				
				if ($tmpTaskId>0) {
					$tmp = TmWorkflowTask::updateOrCreate($conTask,$objwf_task);
				}
				else{
					$tmp = TmWorkflowTask::create($objwf_task);
				}
				//\DB::enableQueryLog ();
				//\Log::info ( \DB::getQueryLog () );
				
			}
		}
		
		return response ()->json ( array ('result'=>$tmp) );
	}
	
	public function deleteWorkFlow(Request $request){
		$data = $request->all ();
		
		DB::beginTransaction ();
		try {		
			TmWorkflowTask::where(['WF_ID'=>$data['ID']])->delete();
			
			TmWorkflow::where(['ID'=>$data['ID']])->delete();		
		} catch ( \Exception $e ) {
			DB::rollback ();
		}
		
		DB::commit ();
		
		$result = $this->getTmWorkflow();
		
		return response ()->json ( [
				'result' => $result
		] );
	}
	
	public function stopWorkFlow(Request $request){
		$data 			= $request->all ();
		$tmWorkflowId	= $data['ID'];
		$this->stopWorkFlowId($tmWorkflowId,true);
		$result 		= $this->getTmWorkflow();
		return response ()->json ( [
				'result' => $result
		] );
	}
	
	public function stopWorkFlowId($tmWorkflowId,$shouldUpdateReference = false){
		TmWorkflow::where(['ID'=>$tmWorkflowId])->update(['ISRUN'=>'no']);
		if ($shouldUpdateReference) TmTask::updateReferenceFrom($tmWorkflowId);
	}
	
	public function runWorkFlowId($tmWorkflowId){
		TmWorkflow::where(['ID'=>$tmWorkflowId])->update(['ISRUN'=>'yes']);
		// 		\DB::enableQueryLog ();
		$tmWorkflowTask = TmWorkflowTask::where(['WF_ID'=>$tmWorkflowId, 'ISBEGIN'=>1])->first();
		// 		\Log::info ( \DB::getQueryLog () );
		if($tmWorkflowTask){
			TmWorkflowTask::where(['WF_ID'=>$tmWorkflowId])
							->where('ID', '<>', $tmWorkflowTask['id'])
							->update(['ISRUN'=>0]);
			$objRun = new WorkflowProcessController(null, $tmWorkflowTask);
			$objRun->runTask(null, $tmWorkflowTask);
			/* $job = (new runAllocation(null, $tmWorkflowTask));
			 $this->dispatch($job); */
		}
		else \Log::info ( "TmWorkflowTask id not found");
	}
	
	public function runWorkFlow(Request $request){
		$data 			= $request->all ();
		$tmWorkflowId	= $data['ID'];
		$this->runWorkFlowId($tmWorkflowId);
		$result 		= $this->getTmWorkflow();
		
		return response ()->json ( [
				'result' => $result
		] );
	}
	
	private function getTmWorkflow(){
		$result = TmWorkflow::where ( ['STATUS' => 1] )
		->get ( ['id','name','isrun'] );
		
		return $result;
	}
	
	public function dashboard(Request $request) {
		$filterGroups = array(	'productionFilterGroup'	=> [],
								'frequenceFilterGroup'	=> [],
								'dateFilterGroup'		=> array(	['id'=>'date_begin','name'=>'From date'],
																	['id'=>'date_end',	'name'=>'To date']),
								'enableButton'			=> true,
								'enableSaveButton'		=> false,
				
		);
		$current_username 	= auth()->user()->username;
		$dashboard_row		= null;
		
		$input 				= $request->only(['id']);
		$id					= $input['id'];
		if ($id&&$id>0) {
			$dashboard_row	= Dashboard::find($id);
		}
		if (!$dashboard_row) {
			$cquery0 			= Dashboard::where("ID",auth()->user()->DASHBOARD_ID);
			$cquery1 			= Dashboard::where("IS_DEFAULT",1)->where("USER_NAME",$current_username);
			$cquery2 			= Dashboard::where("USER_NAME",$current_username);
			$cquery3 			= Dashboard::where("IS_DEFAULT",1)->where("TYPE",1);
			$cquery4 			= Dashboard::where("TYPE",1);
			$cquery5 			= Dashboard::where("IS_DEFAULT",1);
	// 		$query 				= $cquery0->union($cquery1)->union($cquery2)->union($cquery3)->union($cquery4);
			$queries			= [	$cquery0,
									$cquery1,
									$cquery2,
									$cquery3,
									$cquery4,
									$cquery5];
			foreach ( $queries as $query ) {
				$dashboard_row 	= $query->first();
				if($dashboard_row) break;
			}
		}

		return view ( 
				'front.dashboard',
				['filters'			=> $filterGroups,
				'dashboard_id'		=> $dashboard_row?$dashboard_row->ID:null,
				'dashboard_row'		=> $dashboard_row
		]);
	}
	
	public function dashboardConfig(Request $request) {
		$data 				= $request->all ();
		$dashboard_id		= array_key_exists("id", $data)?$data["id"]:0;
		$dashboard_row		= null;
		if ($dashboard_id>0) $dashboard_row = Dashboard::find($dashboard_id);
		return view (
					'config.dashboardconfig',	['dashboard_id'		=> $dashboard_row?$dashboard_row->ID:null,
												'dashboard_row'		=> $dashboard_row]
				);
	}
	
	public function editor() {
		return response()->file("/config/diagrameditor.xml");
	}
	
	public function taskman() {
		$filterGroups = array(
							  'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From Date'],['id'=>'date_end','name'=>'To Date']),
							'frequenceFilterGroup'		=> [
															["name"			=> "EbFunctions",
															"filterName"	=> "Run Task",
															"getMethod"		=> "loadBy",
															'dependences'	=> ["ExtensionEbFunctions"],
															"source"		=> [],
															'default'		=> ['ID'=>0,'NAME'=>'All']
															],
															["name"			=> "ExtensionEbFunctions",
															"getMethod"		=> "loadBy",
															"filterName"	=>	"Function Name",
															'default'		=> ['ID'=>0,'NAME'=>'All'],
															"source"		=> ['frequenceFilterGroup'=>["EbFunctions"]]],
							]
						);
		return view ( 'datavisualization.taskman',['filters'=>$filterGroups]);
	}
	
	public function filter(Request $request){
		$postData 		= $request->all();
		$filterGroups 	= array(	'productionFilterGroup'	=>[
																['name'=>'IntObjectType',
																	'independent'=>true],
															],
									'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																	"getMethod"		=> "loadBy",
																	"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType"]],
									]],
									'FacilityDependentMore'	=> ["ObjectName"],
									'extra' 				=> ['IntObjectType'],
									'enableButton' 			=> false
													);
		return view ( 'partials.editfilter',['filters'			=> $filterGroups,
				'prefix'			=> "secondary_",
				"currentData"		=> $postData
		]);
	}
	
	public function getRecordStatusSummaryData(Request $request){
		$data 				= $request->all ();
		$facility_id 		= $data['facility_id'];
		$facilityIds		= \Helper::getFacilityIds($data,$facility_id);
		$tables 			= $data['tables'];
		$dateFrom			= \Helper::parseDate($data['DATE_FROM']);
		$dateTo				= \Helper::parseDate($data['DATE_TO']);
		$query 				= null;
		$sql 				= "";
		foreach($tables as $oTable) if($oTable){
			$table			= $oTable;
			$q				= null;
			$date_field 	= "OCCUR_DATE";
			$table_parent	= null;
			$ralation_id	= null;
            if(strpos($table, "FLOW_") !== false){
                $table_parent = Flow::getTableName();
                $ralation_id = "FLOW_ID";
            }
			elseif (strpos($table, "ENERGY_UNIT_STATUS") !== false){
                $table_parent = EnergyUnit::getTableName();
                $ralation_id = "EU_ID";
				$date_field = "EFFECTIVE_DATE";
            }
			elseif (strpos($table, "ENERGY_UNIT_") !== false){
                $table_parent = EnergyUnit::getTableName();
                $ralation_id = "EU_ID";
            }
            elseif (strpos(strtoupper($table), "KEYSTORE_TANK") !== false) {
            	$table_parent = KeystoreTank::getTableName();
            	$ralation_id = "KEYSTORE_TANK_ID";
            }
            elseif (strpos(strtoupper($table), "KEYSTORE_STORAGE") !== false) {
            	$table_parent = KeystoreStorage::getTableName();
            	$ralation_id = "KEYSTORE_STORAGE_ID";
            }
            elseif (strpos(strtoupper($table), "KEYSTORE_INJECTION") !== false) {
            	$table_parent = KeystoreInjectionPoint::getTableName();
            	$ralation_id 	= "INJECTION_POINT_ID";
            	$mdl			= \Helper::getModelName($table);
            	if (method_exists($mdl, "buildQueryBy")) {
	            	$objectTypeTables		= CodeInjectPoint::loadActive();
	            	$objectTypeTables->each(function ($item, $key) use ($mdl,$facilityIds,&$query,$dateFrom,$dateTo,$date_field){
	            		$objectTypeTable	= $item->CODE;
	            		$q					= $mdl::buildQueryByCode($facilityIds,$objectTypeTable,[$date_field],$dateFrom,$dateTo);
		            	$q 					= $q->selectRaw("sum(case when RECORD_STATUS='P' then 1 else 0 end) P, sum(case when RECORD_STATUS='V' then 1 else 0 end) V, sum(case when RECORD_STATUS='A' then 1 else 0 end) A")
		            						->groupBy("$date_field");
	            		$query 				= $query?$query->unionAll($q):$q;
	            	});
	            	
            	}
            	continue;
            }
            elseif (strpos($table, "TANK_") !== false) {
                $table_parent = Tank::getTableName();
                $ralation_id = "TANK_ID";
            }
            elseif (strpos($table, "STORAGE_") !== false) {
                $table_parent = Storage::getTableName();
                $ralation_id = "STORAGE_ID";
            }
            elseif (strpos($table, "EQUIPMENT_") !== false) {
                $table_parent = Equipment::getTableName();
                $ralation_id = "EQUIPMENT_ID";
            }
            elseif (strpos($table, "EU_TEST_") !== false) {
                $table_parent = EnergyUnit::getTableName();
                $ralation_id = "EU_ID";
				$date_field = "EFFECTIVE_DATE";
            }
            elseif (strpos($table, "KEYSTORE_") !== false) {
                $table_parent = Keystore::getTableName();
                $ralation_id = "";
            }
            elseif (strpos(strtoupper($table), "DEFERMENT") !== false) {
            	$date_field = Deferment::$dateField;
            }
            else{
            	$mdl			= \Helper::getModelName($table);
            	$q				= $mdl::whereIn("FACILITY_ID",$facilityIds);
            	$date_field 	= $mdl::$dateField;
            }
            
            if (!$q) {
				$q = DB::table($table);
	            if($table_parent && $ralation_id) $q = $q->join($table_parent, "$table.$ralation_id", '=', "$table_parent.ID")->whereIn("$table_parent.FACILITY_ID",$facilityIds);
	            else if (!$table_parent) {
	            	$q = $q->whereIn("$table.FACILITY_ID",$facilityIds);
	            }
            }
            $dField		= $table?"$table.$date_field":$date_field;
            $q = $q->selectRaw("$dField AS OCCUR_DATE, sum(case when RECORD_STATUS='P' then 1 else 0 end) P, sum(case when RECORD_STATUS='V' then 1 else 0 end) V, sum(case when RECORD_STATUS='A' then 1 else 0 end) A")
                ->whereDate("$dField",'>=',$dateFrom)
                ->whereDate("$dField",'<=',$dateTo)
                ->groupBy("$dField");
			$query = $query?$query->unionAll($q):$q;
		}
		$P = [];
		$V = [];
		$A = [];
//        $merge  = [];
		if($query){
			//$rows = $query->groupBy('OCCUR_DATE')->select(['OCCUR_DATE','P','V','A'])->get();
			$rows = DB::select("SELECT OCCUR_DATE,sum(P) as P,sum(V) as V, sum(A) as A FROM ({$query->toSql()}) AS t GROUP BY OCCUR_DATE order by OCCUR_DATE", $query->getBindings());
			foreach($rows as $row){
                $row->OCCUR_DATE = str_replace("00:00:00","",$row->OCCUR_DATE);
				if($row->P||$row->V||$row->A) {
					$P[] = [$row->OCCUR_DATE, (int)$row->P];
					$V[] = [$row->OCCUR_DATE, (int)$row->V];
					$A[] = [$row->OCCUR_DATE, (int)$row->A];
				}
				
                /*$merge["P"][]    = (int)$row->P;
                $merge["V"][]    = (int)$row->V;
                $merge["A"][]    = (int)$row->A;*/
			}
		}
		return response ()->json ( [
            'P' => $P,
            'V' => $V,
            'A' => $A,
//            'merger'    =>$merge,
            'SUM' => $rows,
		] );
	}
}
