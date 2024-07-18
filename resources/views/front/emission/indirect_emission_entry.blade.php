<?php
$currentSubmenu ='/ghg/ee/indirect';
$mainTab        = 'EmissionIndirectDataValue';
$tables         = [$mainTab	=>['name'=>'Data Input']];
$isAction = true;
$detailTableTab = 'EmissionIndirRelDataValue';
?>

@extends('core.es')
@section('funtionName')
    INDIRECT
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/indirect/ee/load";
        actions.saveUrl = "/indirect/ee/save";

        var pid;
        var date_parent;
        var oInitExtraPostData = editBox.initExtraPostData;
        editBox.initExtraPostData = function (id,rowData){
        var idata = oInitExtraPostData(id,rowData);
        pid = id;
        //            date_parent = rowData['BEGIN_DATE'];
        date_parent = rowData['OCCUR_DATE'];
        idata.tabTable  = '{{$detailTableTab}}';
        return 	idata;
        };
        // lấy ra parent id
        actions['idNameOfDetail'] = ['PARENT_ID', 'ID','OCCUR_DATE'];

        actions['doMoreAddingRow'] = function(addingRow){
        addingRow['PARENT_ID'] = pid;
        if(typeof  date_parent != "undefined") addingRow['OCCUR_DATE'] = date_parent;
        return addingRow;
        }
    </script>
@stop

@section('editBoxParams')
    @parent
    <script>
        editBox.loadUrl = "/eei/load";
        editBox.saveUrl = '/eei/save';
    </script>
@stop


