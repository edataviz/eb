<?php
	$currentSubmenu = isset($currentSubmenu)?$currentSubmenu:'/fp/choke';
	$tablePrepend	= isset($tablePrepend)?$tablePrepend:true;
	
	$key 			= 'choke';
 	$active 		= 0;
 	$isAction 		= true;
 	$floatContents 	= ['editBoxContentview','contrainList'];
 	$tableTab		= "ConstraintDiagram";
 	$useFeatures	= [
 							['name'	=>	"filter_modify",
 							"data"	=>	["isFilterModify"	=> true,
 										"isAction"			=> $isAction]],
 	];
	$filterGroups	= isset($editFilters)?$editFilters:\Helper::getCommonGroupFilter();
 	if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
//  	$tables = ['ConstraintDiagram'	:['name':'Constraint Diagram']];
 ?>


@extends('core.fp')

@section('funtionName')
Constrain diagrams
@stop

@section('action_extra')
	@if($tablePrepend)
		@include('partials.diagram_action')
	@endif
@stop

@section('script')
@parent
	<!-- <link rel="stylesheet" type="text/css" href="/common/tooltipster/css/tooltipster.bundle.min.css" />
    <script type="text/javascript" src="/common/tooltipster/js/tooltipster.bundle.min.js"></script> -->
    <link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
	<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>
@stop

@section('content')
	@include('choke.choke_diagram')
@stop

@section('editBoxContentview')
	@include('choke.editfilter',['filters'			=> $filterGroups,
				    			'prefix'			=> "secondary_",
						    	])
@stop

@section('extra_editBoxContentview')
<div id="objectListContainer" style="overflow-x: hidden;z-index: 1001;position: relative;float: right;width:44%;height:100%">
	<div id="objectList" style="height:80%;width:100%;overflow-x: hidden;"></div>
	<div id="viewNameDiv" style="width:100%;overflow-x: hidden;display:none">View Name<input id="viewName" type="text" style="width:auto"></div>
	<label id="isAdditionalLabel" style="display:none;float:left"><input id="isAdditional" class="cellCheckboxInput" type="checkbox" value="" size="15">Accumulate?</label>
</div>
@stop


@section('adaptData')
@parent
<script>
	actions.loadUrl = "/choke/load";
	actions.saveUrl = "/choke/save";
	
	actions.getChartTitle = function (tab){
		return "Choke Model Name";
	};
	
	editBox.fillCurrentDiagram = function (currentDiagram){
		currentDiagram.NAME		= $("#txtDiagramName").val();
	}

	editBox.getDiagramTitle = function(getDiagramTitle){
		return currentDiagram!=null?currentDiagram.NAME:"";
	}
	
</script>
@stop
