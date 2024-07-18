<?php
$currentSubmenu ='/fo/env';
$tables 		= ['Environmental'	=>['name'=>'Environmental']];
?>
@extends('front.comment')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/env/load";
	actions.saveUrl = "/env/save";
	actions.getCategoryColumn = function (tab){
        return "ENV_CATEGORY";
    };
</script>
@stop
