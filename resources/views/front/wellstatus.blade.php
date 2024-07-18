<?php
$currentSubmenu ='/dc/wellstatus';
$tables = ['EnergyUnitStatus'=>['name'=>'WELL STATUS']];
$isAction = true;
$lastFilter	=  ["EnergyUnitGroup","EnergyUnit"];
?>

@extends('core.pm')
@section('funtionName')
WELL STATUS
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/wellstatus/load";
	actions.saveUrl 		= "/wellstatus/save";
	actions.historyUrl 		= "/wellstatus/history";
	
	actions.type = {
					idName:['EU_ID','ID','EFFECTIVE_DATE'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
										return 'ID';
									},
					};
	addingOptions.keepColumns = ['EFFECTIVE_DATE'];

	actions.getTimeValueBy = function(newValue,columnName,tab){
		if(columnName=='STATUS_DATE'){
			return moment.utc(newValue).format(configuration.time.DATE_FORMAT_UTC);
		}
		return moment(newValue).format(configuration.time.DATETIME_FORMAT_UTC);
	};
</script>
@stop