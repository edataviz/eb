<?php
error_reporting(0);

$db_server_name="localhost";
$db_user="nhneu_tung";
$db_pass="tung#3";
$db_schema="tenant1";
$db_type = "mysql";

$db_survey_conn = @mysql_connect($db_server_name, $db_user, $db_pass) or die ("Could not connect");
mysql_select_db($db_schema,$db_survey_conn);

function getOneValue($sSQL)
{
	$row=getOneRow($sSQL);
	return $row[0];
}

function getOneRow($sSQL)
{
	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	$row=mysql_fetch_array($result);
	return $row;
}

function getData($sSQL)
{
	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	while($row=mysql_fetch_assoc($result)){
		$data[] = $row;
	}
	return $data;
}

function correctDate($date, $format){
	global $db_type;
	if($db_type=='oracle'){
		if(!$format) $format='YYYY-MM-DD';
		return "TO_DATE('$date','$format')";
	}
	if($db_type=='mysql'){
		if(!$format) return "'$date'";
		return "STR_TO_DATE('$date','$format')";
	}
	return "'$date'";
}

function execSql($sSQL){
	mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
}
?>