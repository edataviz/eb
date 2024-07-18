<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<link rel="stylesheet" href="/common/css/admin.css">
<style>
table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc {
    text-align: left;
    background-color: #609CB9;
}
</style>
<div id="content">
	<div class="title">
		@yield('title')
	</div>
		@yield('group')		

		@yield('content')
</div>
@yield('adaptData')

@stop

