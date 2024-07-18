<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'NETWORK MODELS', 'link' => 'diagram'),
		array('title' => 'DATA VIEWS', 'link' => 'data views'),
		array('title' => 'REPORT', 'link' => 'report'),
		array('title' => 'ADVANCED GRAPH', 'link' => 'advancedgraph'),
		array('title' => 'TASK MANAGER', 'link' => 'taskmanager'),
		array('title' => 'WORKFLOW', 'link' => 'workflow')
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
	
@section('main')
<link rel="stylesheet" href="/common/css/common.css"/>
<link rel="stylesheet" href="/common/css/styleTab.css"/>
<link rel="stylesheet" href="/common/css/admin.css">

<div id="content">
	<div class="title">
		@yield('title')
	</div>
		@yield('group')		

		@yield('content')
</div>
@yield('adaptData')

@stop

