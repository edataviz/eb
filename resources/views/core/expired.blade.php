<?php
	$text = $code=='valid'?'Input new license key':'Please contact administrator or input new license key'
?>
@extends('eb.index')
@section('head')
@parent
<link rel="stylesheet" href="/css/login.css">
@stop

@section('body')
<style>
.box-login-container div {
    box-sizing: border-box;
}
</style>
<div class="box-login-container center-box">
    <div class="box-login" forgot="0">
        <div class="box-login-intro">
            <i class="icon-logo-eb icon-logo-eb-login"></i>
            <h1>Energy Builder</h1>
            <a href="http://edataviz.com" target="_blank" class="powered" tabindex="-1">Powered by eDataViz LLC</a>
        </div>
        <div class="box-login-input center-box">
            <div class="box-login-sub box-login-sub-normal">
                <h1 style="color:{{$code=='valid'?'#127ab8':'red'}};">{!!$message!!}</h1>
				@if ($code=='valid')
				<div class="btn-login" onclick="location.href='/'">Continue</div><br><br>
				@endif
                <div class="box-login-message">{{$text}}</div>
				<form class="login100-form validate-form" action="/submitkey" method="post">
				<input placeholder="License key" id="txtLicenseKey" name="licenseKey" style="
					border-radius: 100px;
					padding: 10px 20px;
					font-size: 15px;
					font-weight: bold;
					width: 280px;
				">
                </form>
                <div class="btn-login" onclick="submitKey()">Submit</div>
            </div>
        </div>
    </div>
    <div class="copyright center-x-box">eDataViz LLC Copyright &copy; <script>document.write((new Date()).getFullYear());</script></div>
</div>
<script>
function submitKey(){
	var key = $("#txtLicenseKey").val().trim();
	if(key) 
		document.forms[0].submit();
	else
		alert("Please input license key");
}
$("#txtLicenseKey").focus();
</script>
@stop
