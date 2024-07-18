<?php
$subMenus = [
		array('title' => 'USERS', 'link' => 'users'),
		array('title' => 'ROLES', 'link' => 'roles'),
		array('title' => 'AUDIT TRAIL', 'link' => 'audittrail'),
		array('title' => 'VALIDATE DATA', 'link' => 'validatedata'),
		array('title' => 'APPROVE DATA', 'link' => 'approvedata'),
		array('title' => 'LOCK DATA', 'link' => 'lockdata'),
		array('title' => 'USER LOG', 'link' => 'userlog'),
];
?>
@extends('core.bsadmin',['subMenus' => $subMenus, 'listControls' => $listControls])

@section('group')
	@include('group.adminControl')
@stop

@section('content')

@stop
