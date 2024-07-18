<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\CodeController;
use Illuminate\Http\Request;

class DCController extends CodeController {
	
	public function __construct() {
		$this->isApplyFormulaAfterSaving 	= false;
        $this->middleware('auth:api');
	}
	
	public function dcLoadConfig(Request $request){
		$postData 		= $request->all();
		$fdc 			= array_key_exists("data_type", $postData)?($postData["data_type"]=="fdc"):false;
		$days			= array_key_exists("days", $postData)?$postData["days"]:7;
		$res=file_get_contents($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/dc/response.php?days='.$days.'&data_type='.($fdc?'fdc':''));
		return response()->json(json_decode($res));

		$kid			= config('database.default')==='oracle'?"concat('R_',dc_route.id)":
						(config('database.default')==='sqlsrv'?
								"('R_' + CAST(dc_route.id AS VARCHAR(10)))":
								"concat('R_',dc_route.id)");
		
		$routes 		= \DB::table("dc_route")->leftJoin('dc_point', 'dc_point.route_id', '=', 'dc_route.id')
							->select(\DB::raw("$kid as kid"),
									'dc_route.id', 
									'dc_route.name as name',
									\DB::raw("count(dc_point.id) as total"),
									\DB::raw("0 as complete"))
							->groupBy('dc_route.id','dc_route.name')
							->get();
		$routes 		= collect($routes)->keyBy('kid');
		foreach($routes as $i)
			unset($i->kid);
		
		$kid			= config('database.default')==='oracle'?"concat('P_',dc_point.id)":
						(config('database.default')==='sqlsrv'?
								"('P_' + CAST(dc_point.id AS VARCHAR(10)))":
								"concat('P_',dc_point.id)");
		$points 		= \DB::table("dc_point")
							->leftJoin('dc_point_flow', 'dc_point.id', '=', 'dc_point_flow.point_id')
							->leftJoin('dc_point_eu', 'dc_point.id', '=', 'dc_point_eu.point_id')
							->leftJoin('dc_point_tank', 'dc_point.id', '=', 'dc_point_tank.point_id')
							->leftJoin('dc_point_equipment', 'dc_point.id', '=', 'dc_point_equipment.point_id')
							->select(\DB::raw("$kid as kid"),
									'dc_point.id',
									'dc_point.name',
									'dc_point.route_id',
									\DB::raw(" 0 as complete"),
									\DB::raw("count(distinct dc_point_flow.id) as FL"),
									\DB::raw("count(distinct  dc_point_eu.id) as EU"),
									\DB::raw("count(distinct dc_point_tank.id) as TA"),
									\DB::raw("count(distinct dc_point_equipment.id) as EQ")
									)
							->groupBy('dc_point.id','dc_point.name','dc_point.route_id')
							->get();
		$points 		= collect($points)->keyBy('kid');
		foreach($points as $point){
			$point->complete = false;
			unset($point->kid);
		}
		
		$kid			= 	config('database.default')==='oracle'?"concat('FL_',flow.id)":
							(config('database.default')==='sqlsrv'?
									"('FL_' + CAST(flow.id AS VARCHAR(10)))":
									"concat('FL_',flow.id)");
		$fquery 			= \DB::table("dc_point_flow")
							->join('flow', 'flow.id', '=', 'dc_point_flow.flow_id')
							->select(\DB::raw("$kid as kid"),
									'flow.id',
									'flow.name',
									'dc_point_flow.point_id',
									\DB::raw("'FL' as type")
									);
		
		$kid			= 	config('database.default')==='oracle'?"concat('EU_',energy_unit.id)":
							(config('database.default')==='sqlsrv'?
									"('EU_' + CAST(energy_unit.id AS VARCHAR(10)))":
									"concat('EU_',energy_unit.id)");
		$equery 			= \DB::table("dc_point_eu")
							->join('energy_unit', 'energy_unit.id', '=', 'dc_point_eu.eu_id')
							->select(\DB::raw("$kid as kid"),
									'energy_unit.id',
									'energy_unit.name',
									'dc_point_eu.point_id',
									\DB::raw("'EU' as type")
									);
		$kid			= 	config('database.default')==='oracle'?"concat('TA_',tank.id)":
							(config('database.default')==='sqlsrv'?
									"('TA_' + CAST(tank.id AS VARCHAR(10)))":
									"concat('TA_',tank.id)");
		$tquery 		= \DB::table("dc_point_tank")
							->join('tank', 'tank.id', '=', 'dc_point_tank.tank_id')
							->select(\DB::raw("$kid as kid"),
									'tank.id',
									'tank.name',
									'dc_point_tank.point_id',
									\DB::raw("'TA' as type")
									);
		$kid			= 	config('database.default')==='oracle'?"concat('EQ_',equipment.id)":
							(config('database.default')==='sqlsrv'?
									"('EQ_' + CAST(equipment.id AS VARCHAR(10)))":
									"concat('EQ_',equipment.id)");
		$eqquery 		= \DB::table("dc_point_equipment")
						->join('equipment', 'equipment.id', '=', 'dc_point_equipment.equipment_id')
						->select(\DB::raw("$kid as kid"),
								'equipment.id',
								'equipment.name',
								'dc_point_equipment.point_id',
								\DB::raw("'EQ' as type")
								);
		$objects 		= $fquery->unionAll($equery)->unionAll($tquery)->unionAll($eqquery)->get();
		//$objects 		= $robjects->keyBy('kid');
		foreach($objects as $i){
			unset($i->kid);
			if(!isset($points['P_'.$i->point_id]->objects))
				$points['P_'.$i->point_id]->objects = [];
			$points['P_'.$i->point_id]->objects[] = $i->type.'_'.$i->id;
		}
		$robjects 		= collect($objects);
		$object_ids 	= $robjects->groupBy('type');
		
		$lists 			= [];
		$lists['CODE_FLOW_PHASE'] = $this->loadCodes('CODE_FLOW_PHASE');
		$lists['CODE_EVENT_TYPE'] = $this->loadCodes('CODE_EVENT_TYPE');
		
		
		$data_types 	= ['1'=>'t','2'=>'n','3'=>'d','4'=>'dp','5'=>'cb','6'=>'tp','7'=>'se','8'=>'ta'];
		$data_table 	= $this->getDataTable($postData);
		$obj_id_field 	= ['FL' => 'FLOW_ID', 'EU' => 'EU_ID', 'TA' => 'TANK_ID', 'EQ' => 'EQUIPMENT_ID'];
		
		$field_configs = [];
		$obj_types = ['FL','EU','TA','EQ'];
		$query = null;
		foreach($obj_types as $obj_type){
			$field_configs[$obj_type]['OCCUR_DATE'] = array (
					'name' => 'Occur date',
					'data_type' => 'd',
					'control_type' => 'd',
					'enable' => true,
			);
			if($obj_type=='EU'){
				$field_configs[$obj_type]['FLOW_PHASE'] = array (
						'name' => 'Flow phase',
						'data_type' => 'n',
						'control_type' => 'l',
						'list' => 'CODE_FLOW_PHASE',
						'enable' => true,
				);
				$field_configs[$obj_type]['EVENT_TYPE'] = array (
						'name' => 'Event type',
						'data_type' => 'n',
						'control_type' => 'l',
						'list' => 'CODE_EVENT_TYPE',
						'enable' => true,
				);
			}
			if ($obj_type!="EQ"){
				$cfgFieldProps 	= \App\Models\CfgFieldProps::getTableName();
				$cQuery 	= \App\Models\CfgFieldProps::where(["$cfgFieldProps.TABLE_NAME" => $data_table[$obj_type],
																"$cfgFieldProps.DATA_METHOD" => 1]);
				$refTable 	= null;
				if (config('database.default')==='mysql') {
					$KEY_COLUMN_USAGE 	= "information_schema.KEY_COLUMN_USAGE";
					$refTable = \DB::raw("UPPER($KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME) as REF_TABLE");
					$cQuery->leftJoin($KEY_COLUMN_USAGE,function ($aQuery) use ($cfgFieldProps,$KEY_COLUMN_USAGE){
									$db_schema = (config('database.default')==='sqlsrv'?config('database.connections.sqlsrv.schema'):ENV('DB_DATABASE'));
									$aQuery->where(\DB::raw("UPPER($KEY_COLUMN_USAGE.TABLE_SCHEMA)"), "=", \DB::raw("UPPER($db_schema)"));
									$aQuery->where(\DB::raw("UPPER($KEY_COLUMN_USAGE.table_name)"), "=", \DB::raw("UPPER($cfgFieldProps.TABLE_NAME)"));
									$aQuery->where(\DB::raw("UPPER($KEY_COLUMN_USAGE.COLUMN_NAME)"), "=", \DB::raw("UPPER($cfgFieldProps.COLUMN_NAME)"));
								});
				}
				elseif (config('database.default')==='oracle') {
					;
				}
				$selects = ["$cfgFieldProps.COLUMN_NAME","$cfgFieldProps.LABEL","$cfgFieldProps.INPUT_TYPE",
								"$cfgFieldProps.DATA_METHOD","$cfgFieldProps.FIELD_ORDER",
								\DB::raw("'$obj_type' as OBJECT_TYPE")];
				if($refTable)$selects[] = $refTable;
				$cQuery->select($selects);
				$query 		= $query?$query->union($cQuery):$cQuery;
			}
		}

		$rows 			= $query->orderBy("OBJECT_TYPE")->orderBy("FIELD_ORDER")->get();
		$data_fields 	= [];
		foreach($rows as $row){
			$ref_table = $row->REF_TABLE;
			if(substr($ref_table,0,5)=="CODE_" && !array_key_exists($ref_table,$lists)){
				$lists[$ref_table] = $this->loadCodes($ref_table);
			}
			$column_name = $row->COLUMN_NAME;
			$field_configs[$row->OBJECT_TYPE][$column_name] = array (
																	'name' 			=> $row->LABEL?$row->LABEL:$column_name,
																	'data_type' 	=> $data_types[$row->INPUT_TYPE],
																	'control_type' 	=> $data_types[$row->INPUT_TYPE],
																	'enable' 		=> $row->DATA_METHOD==1,
															);
			if($ref_table && array_key_exists($ref_table,$lists)){
				$field_configs[$row->OBJECT_TYPE][$column_name]['control_type'] = 'l';
				$field_configs[$row->OBJECT_TYPE][$column_name]['list'] = $ref_table;
			}
			$data_fields[$row->OBJECT_TYPE][] = $column_name;
		}
		
		$data 		= [];
		$load_data_days = array_key_exists("days", $postData)?$postData["days"]:7;
		foreach($data_fields as $obj_type => $arr_fields){
			$arr_fields[] = "$obj_id_field[$obj_type]";
			$arr_fields[] = "OCCUR_DATE";
			if ($obj_type=='EU'){
				$arr_fields[] = "FLOW_PHASE";
				$arr_fields[] = "EVENT_TYPE";
			}
			$arr_fields = array_unique($arr_fields);
			$oObjects = $object_ids->get($obj_type);
			if(!$oObjects) continue;
			$ids = $oObjects->pluck("id")->toArray();
			$originAttrCase 	= \Helper::setGetterUpperCase();
			$mdl 	= \Helper::getModelName($data_table[$obj_type]);
			$rows 	= $mdl::whereIn($obj_id_field[$obj_type],$ids)
						->whereDate("OCCUR_DATE",'>=',\Carbon\Carbon::now()->subDays($load_data_days))
						->select($arr_fields)
						->get();
			
			foreach($rows as $row){
				$occurDate = $row->OCCUR_DATE;
				$dateString = $occurDate && $occurDate instanceof \Carbon\Carbon?$occurDate->toDateString():$occurDate;
				$dateString = strlen($dateString)<=10?$dateString:substr($dateString, 0,10);
				$row->OCCUR_DATE = $dateString;
				$key = $obj_type.'_'.$row->{$obj_id_field[$obj_type]}.'_'.$dateString.($obj_type=='EU'?'_'.$row->FLOW_PHASE.'_'.$row->EVENT_TYPE:'');
				$data[$key]['OCCUR_DATE'] = $dateString;
				if($obj_type=='EU'){
					$data[$key]['FLOW_PHASE'] = $row->FLOW_PHASE;
					$data[$key]['EVENT_TYPE'] = $row->EVENT_TYPE;
				}
				foreach($arr_fields as $field){
					if ($field!='OCCUR_DATE') {
						$nField = $obj_id_field[$obj_type]==$field?"OBJ_ID":$field;
						$data[$key][$nField] = $row->$field;
					}
				}
			}
			\Helper::setGetterCase($originAttrCase);
		}
		
		$response 	= array('routes' 			=> $routes,
							'points' 			=> $points,
							'objects' 			=> $objects,
							'lists' 			=> $lists,
							'object_attrs' 		=> $field_configs,
							'object_details' 	=> $data,
	 						'fdc' 				=> $fdc,
							'load_data_days' 	=> $load_data_days,
							'object_types' 		=> array( 	'FL' 	=> 'FLOW',
															'EU' 	=> 'ENERGY UNIT',
															'TA' 	=> 'TANK',
															'EQ' 	=> 'EQUIPMENT'),
							'data_types' 		=> array( 	'n' 	=> 'Number',
															't' 	=> 'Text',
															'd' 	=> 'Date',),
							'control_types' 	=> array(	'n' 	=> 'Number input',
															't' 	=> 'Text input',
															'd' 	=> 'Date picker',
															'l' 	=> 'List',),
							'object_key_attrs' 	=> array ( 	'FL' 	=> array ('OBJ_ID','OCCUR_DATE'),
															'EU' 	=> array ('OBJ_ID','OCCUR_DATE','FLOW_PHASE','EVENT_TYPE'),
															'TA' 	=> array ('OBJ_ID','OCCUR_DATE'),
															'EQ' 	=> array ('OBJ_ID','OCCUR_DATE'),),
							'object_data_attrs' => array ( 	'FL' 	=> array ('OBJ_ID','OCCUR_DATE'),
															'EU' 	=> array ('OBJ_ID','OCCUR_DATE','FLOW_PHASE','EVENT_TYPE'),
															'TA' 	=> array ('OBJ_ID','OCCUR_DATE'),
															'EQ' 	=> array ('OBJ_ID','OCCUR_DATE'),),
		);
		return response()->json($response);
	}
	
	public function dcSave(Request $request){
		$originData = $request->all(); 
		$postData 	= $this->formatPostData($originData);
		$message 	= "save could not finish";
		try {
			$result 	= $this->doSave($postData,false);
			if (is_string($result)&&$result)
				$message = $result;
			else if (count($result)<=0) 
				$message = "empty post data";
			else if (array_key_exists("lockeds", $result))
				$message = "ok.But there some locked records: ".implode(", ", $result["lockeds"]);
			else
				$message = "ok";
		} catch (\Exception $e) {
			$message = $e?$e->getMessage():"unknow error";
		}
		return response()->json(["message" => $message]);
	}
	
	public function getDataTable($postData){
		$fdc = array_key_exists("data_type", $postData)?($postData["data_type"]=="fdc"):false;
		$_dt = ($fdc===true||$fdc=='true'?"FDC_":"");
		$data_table = ['FL' => "FLOW_DATA_{$_dt}VALUE", 'EU' => "ENERGY_UNIT_DATA_{$_dt}VALUE", 'TA' => "TANK_DATA_{$_dt}VALUE", 'EQ' => 'EQUIPMENT_DATA_VALUE'];
		return $data_table;
	}
	
	public function formatPostData($originData){
		$editedData 		= [];
		$data_table 		= $this->getDataTable($originData);
		$object_details = json_decode($originData["object_details"], true);
		foreach($object_details as $key => $row){
			$splits 		= explode("_", $key);
			if (count($splits)<=0) continue;
			
			$obj_type 		= $splits[0];
			if (count($splits)<3 || ($obj_type=="EU"&&count($splits)<5)) continue;
			if ($obj_type=="EU") {
				$row["FLOW_PHASE"] = $splits[3];
				$row[config("constants.euFlowPhase")] = $splits[3];
				$row["EVENT_TYPE"] = $splits[4];
				$row[config("constants.eventType")] = $splits[4];
			}
			
			$eloquentName 	= \Helper::getModelName($data_table[$obj_type]);
			$objectIdField 	= $eloquentName::$idField;
			
			if (!array_key_exists($objectIdField, $row)) $row[$objectIdField] = $splits[1];
			$dateField 		= $eloquentName::$dateField;
			if (!array_key_exists($dateField, $row)) $row[$dateField] = $splits[2];
			
			$mdlName 		= \Helper::getModelName($data_table[$obj_type],false);
			if (!array_key_exists($mdlName, $editedData))$editedData[$mdlName] = [];
			$editedData[$mdlName][] = $row;
				
		}
		$postData 			= ["editedData" 	=> $editedData];
		return $postData;
	}
}
