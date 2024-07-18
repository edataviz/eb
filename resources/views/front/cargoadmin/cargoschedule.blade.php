<?php
	$currentSubmenu ='/pd/cargoschedule';
	$tables = ['PdCargoSchedule'	=>['name'=>'Data Input']];
	$isAction = true;
?>

@extends('core.pd')
@section('funtionName')
CARGO SCHEDULE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargoschedule/load";
	actions.saveUrl = "/cargoschedule/save";
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

	actions.renderFirsColumn = actions.deleteActionColumn;

	actions.extraDataSetColumns = {'PD_TRANSIT_CARRIER_ID':'TRANSIT_TYPE'};
	
	source['TRANSIT_TYPE']		= {	dependenceColumnName	:	['PD_TRANSIT_CARRIER_ID'],
									url						: 	'/cargonomination/loadsrc'
									};

	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};
	
</script>
@stop

