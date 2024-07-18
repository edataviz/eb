<?php
include('db.php');

function loadCodes($table_name){
	$sql = "select ID, NAME from $table_name where ACTIVE=1 order by `ORDER`, NAME";
	$r=getData($sql);
	$options = [];
	foreach($r as $row){
		$options[]=['value' => $row[ID], 'text' => $row[NAME]];//"<option value='$row[ID]'>$row[NAME]</option>";
	}
	return $options;
}

$sql = "select * from (select ID, NAME,(select count(1) from DC_POINT where DC_POINT.ROUTE_ID=DC_ROUTE.ID) TOTAL_POINT from DC_ROUTE) x where TOTAL_POINT>0";
$r=getData($sql);
$routes = [];
foreach($r as $row){
	$routes[] = ['key' => "R_$row[ID]", 'id' => $row["ID"], 'name' => $row["NAME"], 'total' => $row["TOTAL_POINT"], 'complete' => '0'];
}
$sql = "select ID, NAME, ROUTE_ID,
(select count(1) from DC_POINT_FLOW a, FLOW b where a.POINT_ID=DC_POINT.ID and a.FLOW_ID=b.ID) TOTAL_FLOW, 
(select count(1) from DC_POINT_EU a, ENERGY_UNIT b where a.POINT_ID=DC_POINT.ID and a.EU_ID=b.ID) TOTAL_EU, 
(select count(1) from DC_POINT_TANK a, TANK b where a.POINT_ID=DC_POINT.ID and a.TANK_ID=b.ID) TOTAL_TANK, 
(select count(1) from DC_POINT_EQUIPMENT a, EQUIPMENT b where a.POINT_ID=DC_POINT.ID and a.EQUIPMENT_ID=b.ID) TOTAL_EQ
from DC_POINT";
$r=getData($sql);
$points = [];
foreach($r as $row){
	$points["P_$row[ID]"] = ['id' => $row["ID"], 'name' => $row["NAME"], 'route_id' => $row["ROUTE_ID"], 'complete' => false, 
		'FL' => $row["TOTAL_FLOW"], 'EU' => $row["TOTAL_EU"], 'TA' => $row["TOTAL_TANK"], 'EQ' => $row["TOTAL_EQ"],
		'objects' => []
		];
}

$sql = "select b.ID OBJ_ID, b.FACILITY_ID, b.NAME OBJ_NAME, a.POINT_ID, 'FL' OBJ_TYPE, c.CODE INPUT_FREQ from DC_POINT_FLOW a, FLOW b, code_reading_frequency c where a.FLOW_ID = b.ID and b.RECORD_FREQUENCY=c.ID
union all
select b.ID OBJ_ID, b.FACILITY_ID, b.NAME OBJ_NAME, a.POINT_ID, 'EU' OBJ_TYPE, c.CODE INPUT_FREQ from DC_POINT_EU a, ENERGY_UNIT b, code_reading_frequency c where a.EU_ID = b.ID and b.DATA_FREQ=c.ID
union all
select b.ID OBJ_ID, b.FACILITY_ID, b.NAME OBJ_NAME, a.POINT_ID, 'TA' OBJ_TYPE, 'DAY' INPUT_FREQ from DC_POINT_TANK a, TANK b where a.TANK_ID = b.ID
union all
select b.ID OBJ_ID, b.FACILITY_ID, b.NAME OBJ_NAME, a.POINT_ID, 'EQ' OBJ_TYPE, 'DAY' INPUT_FREQ from DC_POINT_EQUIPMENT a, EQUIPMENT b where a.EQUIPMENT_ID = b.ID
";
$r=getData($sql);
$objects = [];
$object_ids = [];
$facility_ids = [];
foreach($r as $row){
	$obj_key = $row['OBJ_TYPE'].'_'.$row['OBJ_ID'];
	$objects[$obj_key] = ['id' => $row["OBJ_ID"], /*'point_id' => $row["POINT_ID"],*/ 'name' => $row["OBJ_NAME"],'type' => $row["OBJ_TYPE"], 'input_freq' => $row["INPUT_FREQ"]];
	if($row["OBJ_TYPE"]=='EU'){
		$event_phases = [];
		$sql = "select EVENT_TYPE, FLOW_PHASE from eu_phase_config where EU_ID=".$row["OBJ_ID"]." and ACTIVE=1 order by ORDERS, EVENT_TYPE, FLOW_PHASE";
		$res=getData($sql);
		foreach($res as $re){
			$event_phases[$re['EVENT_TYPE']][] = $re['FLOW_PHASE'];
		}
		$objects[$obj_key]['event_phases'] = $event_phases;
	}
	$object_ids[$row['OBJ_TYPE']][] = $row['OBJ_ID'];
	$facility_ids[$row['OBJ_TYPE']] = $row['FACILITY_ID'];
	$points["P_$row[POINT_ID]"]['objects'][] = $obj_key;
	//$points["P_$row[POINT_ID]"][$row['OBJ_TYPE']] += 1;
}

//convert point object to array
$points_arr = [];
foreach($points as $key => $point){
	$point['key'] = $key;
	$points_arr[] = $point;
}

$days = $_REQUEST['days'];
if(!$days) $days=5;
$data_store = strtolower($_REQUEST['data_type']);
$_dt = ($data_store=="fdc"?"FDC_":"");

$data_types = ['1'=>'t','2'=>'n','3'=>'d'];
$data_table = ['FL' => "FLOW_DATA_{$_dt}VALUE", 'EU' => "ENERGY_UNIT_DATA_{$_dt}VALUE", 'TA' => "TANK_DATA_{$_dt}VALUE", 'EQ' => 'EQUIPMENT_DATA_VALUE'];
$obj_id_field = ['FL' => 'FLOW_ID', 'EU' => 'EU_ID', 'TA' => 'TANK_ID', 'EQ' => 'EQUIPMENT_ID'];

$field_configs = [];
/*
$obj_types = ['FL','EU','TA','EQ'];
foreach($obj_types as $obj_type){
	$field_configs[$obj_type]['OCCUR_DATE'] = array (
			'name' => 'Occur date',
			'data_type' => 'd',
			'control_type' => 'd',
			'enable' => true,
		  );
	if($obj_type=='EU'){
		$field_configs[$obj_type]['EVENT_TYPE'] = array (
				'name' => 'Event type',
				'data_type' => 'n',
				'control_type' => 'l',
				'list' => 'CODE_EVENT_TYPE',
				'enable' => true,
			  );
		$field_configs[$obj_type]['FLOW_PHASE'] = array (
				'name' => 'Flow phase',
				'data_type' => 'n',
				'control_type' => 'l',
				'list' => 'CODE_FLOW_PHASE',
				'enable' => true,
			  );
	}
}
*/
$lists = [];
$lists['CODE_FLOW_PHASE'] = loadCodes('CODE_FLOW_PHASE');
$lists['CODE_EVENT_TYPE'] = loadCodes('CODE_EVENT_TYPE');

$data_fields = [];
$config_id_FL = getOneValue("select ID from cfg_config where TABLE_NAME='$data_table[FL]' and facility_id = 0".$facility_ids['FL']);
$config_id_EU = getOneValue("select ID from cfg_config where TABLE_NAME='$data_table[EU]' and facility_id = 0".$facility_ids['EU']);
$config_id_TA = getOneValue("select ID from cfg_config where TABLE_NAME='$data_table[TA]' and facility_id = 0".$facility_ids['TA']);
$config_id_EQ = getOneValue("select ID from cfg_config where TABLE_NAME='$data_table[EQ]' and facility_id = 0".$facility_ids['EQ']);

$configFL = ($config_id_FL>0?"config_id=$config_id_FL":"CONFIG_ID is null");
$configEU = ($config_id_EU>0?"config_id=$config_id_EU":"CONFIG_ID is null");
$configTA = ($config_id_TA>0?"config_id=$config_id_TA":"CONFIG_ID is null");
$configEQ = ($config_id_EQ>0?"config_id=$config_id_EQ":"CONFIG_ID is null");

$sql = "select a.*, UPPER(b.REFERENCED_TABLE_NAME) REF_TABLE from (
select a.*, 'FL' OBJECT_TYPE from $db_schema.cfg_field_props a where $configFL and TABLE_NAME='$data_table[FL]'
union all
select a.*, 'EU' OBJECT_TYPE from $db_schema.cfg_field_props a where $configEU and TABLE_NAME='$data_table[EU]'
union all
select a.*, 'TA' OBJECT_TYPE from $db_schema.cfg_field_props a where $configTA and TABLE_NAME='$data_table[TA]'
union all
select a.*, 'EQ' OBJECT_TYPE from $db_schema.cfg_field_props a where $configEQ and TABLE_NAME='$data_table[EQ]'
) a left join information_schema.KEY_COLUMN_USAGE b on UPPER(b.TABLE_SCHEMA)=UPPER('$db_schema') and UPPER(b.table_name)=UPPER(a.TABLE_NAME) and UPPER(b.COLUMN_NAME)=UPPER(a.COLUMN_NAME)
where a.DATA_METHOD=1 and USE_FDC=1
order by a.OBJECT_TYPE,a.FIELD_ORDER
";

//echo $sql; exit;

$r=getData($sql);
foreach($r as $row){
	//check field foreign key (to build list)
	$ref_table = $row['REF_TABLE'];
	if(substr($ref_table,0,5)=="CODE_" && !array_key_exists($ref_table,$lists)){
		$lists[$ref_table] = loadCodes($ref_table);
	}
	$column_name = $row['COLUMN_NAME'];
	$attr = array (
			'field' => $column_name,
			'name' => $row['LABEL']?$row['LABEL']:$column_name,
			'data_type' => $data_types[$row['INPUT_TYPE']],
			'control_type' => $data_types[$row['INPUT_TYPE']],
			'enable' => $row['DATA_METHOD']==1,
			'mandatory' => $row['IS_MANDATORY']==1,
		  );
	if($ref_table && array_key_exists($ref_table,$lists)){
		$attr['control_type'] = 'l';
		$attr['list'] = $ref_table;
	}
	if($data_types[$row['INPUT_TYPE']]=='n'){		
		$attr['format'] = $row['VALUE_FORMAT'];
		$attr['decimals'] = strlen(explode('.',$row['VALUE_FORMAT'])[1]);
	}
	$field_configs[$row['OBJECT_TYPE']][] = $attr;
	$data_fields[$row['OBJECT_TYPE']][] = $column_name;
}

//$data = [];
foreach($data_fields as $obj_type => $arr_fields)
	if(count($object_ids[$obj_type])>0) {
	$fields = join(",",$arr_fields);
	$ids = join(",", $object_ids[$obj_type]);
	$sql= "select $obj_id_field[$obj_type] OBJ_ID, OCCUR_DATE, ".($obj_type=='EU'?'FLOW_PHASE, EVENT_TYPE, ':'')."$fields from $data_table[$obj_type] where OCCUR_DATE>=DATE_SUB(date(now()), INTERVAL $days DAY) and $obj_id_field[$obj_type] in (-1,$ids)";
//echo $sql;
	$r=getData($sql);
	foreach($r as $row){
		$key = $obj_type.'_'.$row['OBJ_ID'].'_'.$row['OCCUR_DATE'].($obj_type=='EU'?'_'.$row['FLOW_PHASE'].'_'.$row['EVENT_TYPE']:'');
		//$data[$key]['id'] = $obj_type.'_'.$row['OBJ_ID'];
		//$data[$key]['OBJ_ID'] = $row['OBJ_ID'];
		//$data[$key]['OCCUR_DATE'] = $row['OCCUR_DATE'];
		if($obj_type=='EU'){
			//$data[$key]['FLOW_PHASE'] = $row['FLOW_PHASE'];
			//$data[$key]['EVENT_TYPE'] = $row['EVENT_TYPE'];
		}
		foreach($arr_fields as $field){
			$data[$key][$field] = utf8_encode($row[$field]);
		}
	}
}

	$response = array (

	  'routes' => $routes,

	  'points' => $points_arr,

	  'objects' => $objects,

	  'object_types' => 

	  array (

		'FL' => 'FLOW',

		'EU' => 'ENERGY UNIT',

		'TA' => 'TANK',

		'EQ' => 'EQUIPMENT',

	  ),

	  'data_types' => 

	  array (

		'n' => 'Number',

		't' => 'Text',

		'd' => 'Date',

	  ),

	  'control_types' => 

	  array (

		'n' => 'Number input',

		't' => 'Text input',

		'd' => 'Date picker',

		'l' => 'List',

	  ),

	  'lists' => $lists,

	  'object_attrs' => $field_configs,
	  'object_props' => $object_props,

	  'object_details' => $data,
	
	  'object_key_attrs' =>
		array (
			'FL' => array ('OBJ_ID','OCCUR_DATE'),
			'EU' => array ('OBJ_ID','OCCUR_DATE','FLOW_PHASE','EVENT_TYPE'),
			'TA' => array ('OBJ_ID','OCCUR_DATE'),
			'EQ' => array ('OBJ_ID','OCCUR_DATE'),
		),
	  'object_data_attrs' =>
		array (
			'FL' => array ('OBJ_ID','OCCUR_DATE'),
			'EU' => array ('OBJ_ID','OCCUR_DATE','FLOW_PHASE','EVENT_TYPE'),
			'TA' => array ('OBJ_ID','OCCUR_DATE'),
			'EQ' => array ('OBJ_ID','OCCUR_DATE'),
		),
	  'data_store' => $data_store,
	);

	

	header('Content-Type: application/json');
	echo json_encode($response)

	//print_r($response);	

?>