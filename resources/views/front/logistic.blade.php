<?php
$currentSubmenu ='/fo/logistic';
$tables = ['Logistic'	=>['name'=>'LOGISTIC']];
$isAction = true;
?>
@extends('core.fo')

@section('funtionName')
    LOGISTIC DATA CAPTURE
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/logistic/load";
        actions.saveUrl = "/logistic/save";
        actions.type = {
            idName:['ID','FACILITY_ID','ARRIVE_DATE'],
            keyField:'DT_RowId',
            saveKeyField : function (model){
                return 'DT_RowId';
            },
        };
    </script>
@stop
