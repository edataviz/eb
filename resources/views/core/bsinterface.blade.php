<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'IMPORT DATA', 'link' => 'importdata'),
		array('title' => 'SOURCE CONFIG', 'link' => 'sourceconfig'),
		array('title' => 'DATA LOADER', 'link' => 'dataloader')
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('main')
<div id="content">
	<div class="title">
		@yield('title')
	</div>
	@yield('group')		

	@yield('content')
</div>
@yield('adaptData')

@stop



