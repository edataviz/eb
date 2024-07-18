<?php
$subMenus = [array('title' => 'FLOW STREAM', 'link' => 'flow'),
		array('title' => 'ENERGY UNIT', 'link' => 'eu'),
		array('title' => 'STORAGE', 'link' => 'storage'),
		array('title' => 'TICKET', 'link' => 'ticket'),
		array('title' => 'WELL TEST', 'link' => 'eutest'),
		array('title' => 'DEFERMENT', 'link' => 'deferment'),
		array('title' => 'QUALITY', 'link' => 'quality')
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])


@section('adaptData')
@parent
<style type="text/css">
	#ebTabHeader {
		display: none;
	}
</style>
@stop
