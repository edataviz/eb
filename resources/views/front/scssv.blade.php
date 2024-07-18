<?php
$currentSubmenu ='/dc/scssv';
$tables = ['EuTestDataScssv'=>['name'=>'SCSSV']];
$isAction = true;
$lastFilter	=  ["EnergyUnitGroup","EnergyUnit"];
?>

@extends('core.pm')
@section('funtionName')
    SCSSV
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl 		= "/scssv/load";
        actions.saveUrl 		= "/scssv/save";

        actions.type = {
            idName:['EU_ID','ID','EFFECTIVE_DATE'],
            keyField:'DT_RowId',
            saveKeyField : function (model){
                return 'ID';
            },
        };
        addingOptions.keepColumns = ['EFFECTIVE_DATE'];
        actions.validating = function (reLoadParams){
            return true;
        }

        actions.getTimeValueBy = function(newValue,columnName,tab){
            if(columnName=='STATUS_DATE'){
                return moment.utc(newValue).format(configuration.time.DATE_FORMAT_UTC);
            }
            return moment(newValue).format(configuration.time.DATETIME_FORMAT_UTC);
        };
    </script>
@stop