<?php
	$currentSubmenu =	'/fp/allocateforecast';
	$key 			= 	'allocateforecast';
	
 ?>

@extends('fp.allocate')
@section('funtionName')
MANUAL ALLOCATE FORECAST
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/allocateforecast/load";
	actions.saveUrl = "/allocateforecast/save";
</script>
@stop
