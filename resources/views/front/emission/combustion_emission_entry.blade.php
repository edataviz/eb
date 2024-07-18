<?php
$currentSubmenu = '/ghg/ee/combustion';
$mainTab        = 'EmissionCombDataValue';
$tables         = [$mainTab	=>['name'=>'Data Input']];
$detailTableTab = 'EmissionCombRelDataValue';
$isAction = true;
?>

@extends('core.es')
@section('funtionName')
    CARGO ENTRY
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/combustion/ee/load";
        actions.saveUrl = "/combustion/ee/save";

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
        editBox.loadUrl = "/eec/load";
        editBox.saveUrl = '/eec/save';
    </script>
@stop


