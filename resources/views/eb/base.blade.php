<?php
$user = auth()->user();
if(!$user->NAME) $user->NAME = $user->username;
if(!isset($EB)) $EB = [];
$EB['menu'] = \Helper::getUserMenu();
$EB['user'] = $user;
$originAttrCase = \Helper::setGetterUpperCase();
$EB['workspace'] = App\Models\UserWorkspace::where('USER_ID', '=', $user->ID)->select('*')->first();
if(!$EB['workspace']){
	$EB['workspace'] = [
		'DATE_FORMAT' => 'MM/DD/YYYY',
		'TIME_FORMAT' => 'HH:mm',
		'W_FACILITY_ID' => null,
	];
}
//print_r($EB['menu']);
//echo json_encode($EB['menu']);
//exit();
if(!$EB['workspace']['DATE_FORMAT']) $EB['workspace']['DATE_FORMAT'] = 'MM/DD/YYYY';
if(!$EB['workspace']['TIME_FORMAT']) $EB['workspace']['TIME_FORMAT'] = 'HH:mm';
$EB['facility'] = [
	'defaultValue' => $EB['workspace']['W_FACILITY_ID'],
	'items' => $user->getScopeFacility(true),
];
?>
@extends('eb.index')

@section('head')
<link rel="stylesheet" href="/lib/semantic-ui/semantic.min.css">
<link rel="stylesheet" href="/lib/jquery/jquery.daterangepicker.css">
<link rel="stylesheet" href="/lib/jquery-ui/jquery-ui.css">
@parent
<link rel="stylesheet" href="/css/base.css">
<link rel="stylesheet" href="/css/chat.css">

<script src="/lib/moment.js"></script>
<script src="/lib/jquery-ui/jquery-ui.min.js"></script> 
<script src="/lib/jquery/jquery.daterangepicker.js"></script> 
<script src="/lib/jquery/jquery.number.min.js"></script> 
<script src="/lib/semantic-ui/semantic.min.js"></script> 

<script src="/js/utils.js"></script> 
<script src="/js/base.js"></script>
@stop

@section('body')
<div id="main-container" style="display:">
<div header>
    <li logo action="menu"></li>
    <li eb><b>Energy Builder&#xae;</b></li>
	<li full main-menu></li>
	<!--
    <li action="chat"></li>
    <li action="setting"></li>
	<li action="notification"></li>
-->
    <li action="help" onclick="EB.loadHelp()"></li>
    <li action="user"></li>
</div>
<div toolbar></div>
<div aside action="user">
	<close></close>
	<div class="title"></div>
	<!--
	<div action="chat" width="600px" side-title="{{ trans('Chat') }}"></div>
	<div action="setting" width="400px" side-title="{{ trans('Settings') }}"></div>
	<div action="notification" width="400px" side-title="{{ trans('Notifications') }}"></div>
-->
	<div action="help" width="600px" id="boxHelp" side-title="{{ trans('Quick Guide') }}"></div>
	<div action="user" width="350px">
		<div user-item>
			<div avatar><script>document.write(Utils.genIconText('{{$user->NAME}}'))</script></div>
			<div name>{{ $user->NAME }}</div>
			<div sub>{{ $user->getUserRoleNames() }}</div>
		</div>
		<div user-action side-sub>
			<button>{{ trans('Change password') }}</button>
			<button onclick="location.href='/auth/logout'">{{ trans('Logout') }}</button>
		</div>
		<hr separator>
		<div user-setting side-sub>
			<div class="ui header">{{ trans('Settings') }}</div>
			<div content>
				<button onclick="location.href='/me/setting'">{{ trans('Preferences') }}</button>
			</div>
		</div>
		<!--
		<hr separator>
		<div user-task side-sub>
			<div class="ui header">{{ trans('Tasks') }}</div>
			<div content>
				<button>{{ trans('Load') }}</button>
			</div>
		</div>
		<hr separator>
		<div user-setting side-sub>
			<div class="ui header">{{ trans('Activities Logs') }}</div>
			<div content>
				<button>{{ trans('Load') }}</button>
			</div>
		</div>
-->
	</div>
</div>
<div main>
@yield('main')
</div>
<div footer></div>
</div>
@stop

@section('script')
<script>
$.extend(true, EB, {!!json_encode($EB)!!});
EB.buildMainMenu();
EB.buildNotificationBox();
</script>
@stop