<?php
$currentSubmenu 		= '/fieldsconfig';
$objectExtension 		= isset($objectExtension)?$objectExtension:[];
$useFeatures	= [
    ['name'	=>	"filter_modify",
        "data"	=>	["isFilterModify"	=> true,
            "isAction"			=> true]]];
$enableFilter = false;
?>
@extends('core.pm')

@section('editBoxContentview')
@stop

@section('content')
<link href="/common/css/style_field_config.css?1" rel="stylesheet">
<body style="margin:0; overflow-x:hidden">
    @include('core.fields_config',['cfg_data_source' => $cfg_data_source])
</body>
@stop