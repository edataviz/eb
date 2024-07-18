<?php
include('db.php');

$data_store = strtolower($_REQUEST['data_store']);
$object_details = $_REQUEST['object_details'];
$_dt = ($data_store=="fdc"?"FDC_":"");

$data_table = ['FL' => "FLOW_DATA_{$_dt}VALUE", 'EU' => "ENERGY_UNIT_DATA_{$_dt}VALUE", 'TA' => "TANK_DATA_{$_dt}VALUE", 'EQ' => 'EQUIPMENT_DATA_VALUE'];
$obj_id_field = ['FL' => 'FLOW_ID', 'EU' => 'EU_ID', 'TA' => 'TANK_ID', 'EQ' => 'EQUIPMENT_ID'];

$sqls= [];
foreach($object_details as $key=>$value){
	$keys = explode('_',$key);
	$obj_type = $keys[0];
	$obj_id = $keys[1];
	$date = $keys[2];
	$table = $data_table[$obj_type];
	$id_field = $obj_id_field[$obj_type];
	$_S = "";
	$_F = "";
	$_V = "";
	$where = "";
	if($obj_type == 'EU'){
		$flow_phase = $keys[3];
		$event_type = $keys[4];
		if(!$flow_phase || !$event_type) continue;
		$where = " and FLOW_PHASE=$flow_phase and EVENT_TYPE=$event_type";
		//$_S = "FLOW_PHASE=$flow_phase,EVENT_TYPE=$event_type";
		$_F = ($_F==""?"":",")."FLOW_PHASE,EVENT_TYPE";
		$_V = ($_V==""?"":",")."$flow_phase,$event_type";
	}
	foreach($value as $k => $v){
		$val = ($v===""?"null":"'$v'");
		$_S .= ($_S==""?"":",")."$k=$val";
		$_F .= ($_F==""?"":",")."$k";
		$_V .= ($_V==""?"":",").$val;
	}
	$sql = "select ID from $table where $id_field=$obj_id and OCCUR_DATE=".correctDate($date)."$where";
	$id = getOneValue($sql);
	$sql = "";
	if($id>0){
		$sql = "update $table set $_S where ID=$id";
	}
	else{
		$sql = "insert into $table($id_field,OCCUR_DATE,$_F) values($obj_id,".correctDate($date).",$_V)";
	}
	$sqls[] = $sql;
}
foreach($sqls as $sql)
	if($sql){
		//echo $sql."\n";
		$myfile = file_put_contents('logs.txt', $sql.PHP_EOL , FILE_APPEND | LOCK_EX);
		execSql($sql);
	}
echo "OK";
?>