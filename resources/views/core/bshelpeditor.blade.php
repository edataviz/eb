<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$subMenus = [
		
];
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('main')
<link rel="stylesheet" href="/common/css/admin.css">
<script src="/ckeditor/ckeditor.js?3"></script>
<div id="content">
	<div class="title">
		@yield('title')
	</div>
	@yield('content')
</div>
@yield('adaptData')

@stop



