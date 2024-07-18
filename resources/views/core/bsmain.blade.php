<?php
if (!isset($currentSubmenu)) $currentSubmenu ='';
$enableFilter	= isset($enableFilter)?$enableFilter:true;
$useBootrapCss	= isset($useBootrapCss)?$useBootrapCss:true;
$useAdminCSS	= isset($useAdminCSS)?$useAdminCSS:false;

$currentClass = $currentSubmenu;
if($currentClass!=''){
	$spits = explode("/", $currentClass);
	$currentClass = $spits[count($spits)-1];
}
$useFeatures	= isset($useFeatures)	? $useFeatures	:[];

?>

@foreach( $useFeatures as $feature )
	<?php $fname	= $feature['name'];?>
	@include("partials.$fname",$feature['data'])
@endforeach

@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])
@section('ebfilter')
	@if($enableFilter)
		@include('group.production')
	@endif
@stop
@section('main')
<div class="rootMain {{$currentClass}}">
	<div id="functionName" style="padding:10px 10px 10px 0px;font-size:16pt;display:none">@yield('funtionName')</div>
	<div id="ebfilter" style="width:100%; clear:both">@yield('ebfilter')</div>
	<div id="mainContent" style="width:100%; clear:both">@yield('content')</div>
</div>
@yield('extraAdaptData')
@yield('adaptData')
@yield('endDdaptData')
@foreach( $useFeatures as $feature )
	<?php $fname	= $feature['name'];?>
	@yield("$fname")
@endforeach
@stop

@section('script')
	@if($useBootrapCss)
	<link href="/common/css/bootstrap.css?2" rel="stylesheet"/>
	<link href="/common/css/bootstrap-responsive.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-datetimepicker.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-editable.css" rel="stylesheet"/>
	@endif
    @if($useAdminCSS)	
    <link rel="stylesheet" href="/common/css/admin.css">
    @endif
	<link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
	<link href="/common/css/fixedHeader.dataTables.min.css" rel="stylesheet"/>
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	<script src="/common/js/tableHeadFixer.js"></script>

 	<script src="/common/js/bootstrap.js"></script>
	<script src="/common/js/jquery-ui.js"></script>
	<script src="/common/js/bootstrap-datetimepicker.js"></script>
	<script src="/common/js/bootstrap-editable.js?11"></script>
	<script src="/common/js/eb.js?110"></script>
	
@stop

@section('modalWindow')
	@include('core.history')
@stop
