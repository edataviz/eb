<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$enableFilter = false;
$subMenus = [
		array('title' => 'NETWORK MODELS', 'link' => 'diagram'),
		array('title' => 'DATA VIEWS', 'link' => 'dataview'),
		array('title' => 'REPORT', 'link' => 'workreport'),
		array('title' => 'ADVANCED GRAPH', 'link' => 'graph'),
		array('title' => 'TASK MANAGER', 'link' => 'approvedata'),
		array('title' => 'WORKFLOW', 'link' => 'workflow')
];
$useFeatures	= isset($useFeatures)	? $useFeatures	:[];
?>
@extends('core.bsmain',['subMenus' 		=> $subMenus,
						'useFeatures'	=> $useFeatures])
@section('script')
	<script type="text/javascript" src="/common/js/mxClient.js"></script>
	<script type="text/javascript" src="/common/js/mxApplication.js?4"></script>
	<script  type="text/javascript" src="/common/js/base64.js"></script>
	<script src="/common/js/svgtopng.js"></script>
	<script src="/common/js/skinable_tabs.min.js"></script>
	<link rel="stylesheet" href="/common/css/diagram.css"/>
	<link rel="stylesheet" href="/common/css/common.css"/>
	<link rel="stylesheet" href="/common/css/styleTab.css"/>
@stop	
