<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		array('title' => 'RUN ALLOCATION', 'link' => 'allocrun'),
		array('title' => 'CONFIG ALLOCATION', 'link' => 'allocset')
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



