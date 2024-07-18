<?php
$enableHeader	= false;
$enableFooter	= false;
$loadJQuery		= false;
?>
@extends('front.reports')

@section('script')
	<link rel="stylesheet" href="/common/css/diagram.css"/>
	<link rel="stylesheet" href="/common/css/common.css"/>
	<link rel="stylesheet" href="/common/css/styleTab.css"/>
@stop	


@section('actionForm')
<style>
		#wraper {width: auto}
</style>
<div class="form-group">
	<label class="col-md-2 control-label">Emails ( separated by  ';' )</label> 
	<div class="col-md-10"> 		
		<input type="text" class="form-control" name="txt_email" id="txt_email" placeholder="email" style="height: auto; width: 300px;"> 	
	</div> 
</div>
@stop