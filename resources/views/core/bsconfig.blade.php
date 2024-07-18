<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'FIELDS CONFIG', 'link' => 'fieldsconfig'),
		array('title' => 'TABLES DATA', 'link' => ''),
		array('title' => 'PD TABLES', 'link' => ''),
		array('title' => 'TAGS MAPPING', 'link' => ''),
		array('title' => 'FORMULA EDITOR', 'link' => 'formula'),
		array('title' => 'VIEW CONFIG', 'link' => 'viewconfig')
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('script')
	<link rel="stylesheet" href="/common/css/common.css"/>
@stop	
@section('main')
<div id="content">
	<div class="title">
		@yield('title')
	</div>
	@yield('group')		

	@yield('content')
</div>

@stop

