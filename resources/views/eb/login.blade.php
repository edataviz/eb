<?php
$ssoUsername = "";
if(isset($loginMessage)){
    $loginMessageClass = 'red';
}
else {
    $loginMessage = 'Please login using your username and password';
    $loginMessageClass = '';
    $ssoUsername = config('constants.enableSSO') && array_key_exists('REMOTE_USER',  $_SERVER) ? $_SERVER['REMOTE_USER'] : "";
    $si = strpos($ssoUsername, '\\');
    if($si) $ssoUsername = substr($ssoUsername, $si + 1);
}
$nextURL = isset($nextURL) ? $nextURL : '';
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
        <div class="box-login-input center-box"{{ $ssoUsername ? ' sso' : '' }}>
            <div class="box-login-sub box-login-sub-normal">
                <h1>Login</h1>
                <div class="box-login-message {{ $loginMessageClass }}">{{ $loginMessage }}</div>
                <form form-login method="post" action="/auth/eblogin">
                {{ csrf_field() }}
                <input type="hidden" name="type" value="basic">
                <input type="hidden" name="url" value="{{ $nextURL }}">
                <span class="inp-login inp-login-username"><input placeholder="Username" name="username" /></span>
                <div class="inp-login-separator"></div>
                <span class="inp-login inp-login-password"><input placeholder="Password" name="password" type="password" /></span>
                </form>
                <div class="btn-login">Login</div>
                <!-- <a class="lnk-forgot-password" href="#">Forgot password?</a> -->
                <a class="lnk-login-using-azure-saml" href="/saml">Login using Microsoft Azure account</a>
            </div>
            <div class="box-login-sub box-login-sub-forgot">
                <h1>Forgot password</h1>
                <div class="box-login-message">Please enter your email address</div>
                <span class="inp-login inp-login-email"><input placeholder="Email address" id="inp-login-email" /></span>
                <div class="inp-login-separator"></div>
                <button class="btn-login btn-fp-submit">Submit</button>
                <button class="btn-login btn-fp-cancel">Cancel</button>
            </div>
            <div class="box-login-sub box-login-sso" sso-user="{{ $ssoUsername }}">
                <h1>Single Sign-On</h1>
                <div class="box-login-message-sso">System detects logged in user <span>{{ $ssoUsername }}</span><br> Do you want to continue with this user?</div>
                <div class="btn-login btn-sso-login">Yes</div>
                <div class="btn-login btn-sso-cancel">No</div>
            </div>
        </div>
    </div>
    <div class="copyright center-x-box">eDataViz LLC Copyright &copy; <script>document.write((new Date()).getFullYear());</script></div>
</div>
<script src="/js/login.js?1"></script>
@stop