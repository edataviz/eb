<?php
 	$isAction 		= true;
 	$floatContents 	= ['editBoxContentview','contrainList'];
 	$tableTab		= "ConstraintDiagram";
 	$useFeatures	= [
 							['name'	=>	"filter_modify",
 							"data"	=>	["isFilterModify"	=> true,
 										"isAction"			=> $isAction]],
 	];
 ?>
@extends('front.cargoadmin.storagedisplay')

@section('frequenceFilterGroupMore')
@stop

@section('content')
@parent
<style>
	#filterFrequence {
		clear: none;
	}
</style>
@stop

@section('graph_object_view')
<div id="tdObjectContainer" valign="top"
	style="min-width:420px;
		overflow:hidden;
		box-sizing: border-box;
		overflow: auto;
	  	height: 113px;
	  	padding: 5px;
	    border: 1px solid #bbbbbb;
	    background: #eeeeee">
     <div id="container_{{$tableTab}}" class="date_filter" style="overflow-x: hidden;float:left;margin-right:10px">
				<table border="0" cellpadding="3" id="table_{{$tableTab}}"
					class="fixedtable nowrap display">
				</table>
	</div>
</div>
@stop
