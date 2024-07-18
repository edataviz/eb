<?php
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =0;
if (!isset($isAction)) $isAction =false;
 	$useFeatures	= isset($useFeatures)?$useFeatures:
										 	[
					 							['name'	=>	"filter_modify",
					 							"data"	=>	["isFilterModify"	=> false,
					 										"isAction"			=> $isAction]],
										 	];
?>
@extends('core.bsmain',['subMenus' 		=> $subMenus,
						'useFeatures'	=> $useFeatures])

@section('content')
	@include('partials.dataTableTab')
@stop

