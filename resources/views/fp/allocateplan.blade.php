<?php
	$currentSubmenu =	'/fp/allocateplan';
	$key 			= 	'allocateplan';
	
 ?>

@extends('fp.allocate')
@section('funtionName')
MANUAL ALLOCATE PLAN
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/allocateplan/load";
	actions.saveUrl = "/allocateplan/save";
</script>
@stop
