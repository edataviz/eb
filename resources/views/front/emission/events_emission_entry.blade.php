<?php
$currentSubmenu ='/ghg/ee/events';
$mainTab        = 'EmissionEventDataValue';
$tables         = [$mainTab	=>['name'=>'Data Input']];
$isAction = true;
$detailTableTab = 'EmissionEventRelDataValue';
?>

@extends('core.es')
@section('funtionName')
    EVENTS
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/events/ee/load";
        actions.saveUrl = "/events/ee/save";

        var pid;
        var date_parent;
        var oInitExtraPostData = editBox.initExtraPostData;
        editBox.initExtraPostData = function (id,rowData){
        var idata = oInitExtraPostData(id,rowData);
        pid = id;
        date_parent = rowData['BEGIN_DATE'];
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

        // Load content
        actions.extraDataSetColumns = {'OBJECT_ID':'OBJECT_TYPE'};

        source['OBJECT_TYPE']={dependenceColumnName	:	['OBJECT_ID'],
            url						: 	'/events/ee/loadsrc'
        };

        source.initRequest = function(tab,columnName,newValue,collection){
            postData = actions.loadedData[tab];
            var srcType = null;
            var result = $.grep(collection, function(e){
                return e['ID'] == newValue||e['id'] == newValue;
            });
            if (result.length > 0) {
                srcType = typeof result[0]['CODE'] != "undefined"?result[0]['CODE'] :result[0]['code'];
            }
            else return null;

            srcData = {name : columnName,
                value : newValue,
                srcType : srcType,
                Facility : postData['Facility']};
            return srcData;
        }
    </script>
@stop


@section('editBoxParams')
    @parent
    <script>
        editBox.loadUrl = "/eee/load";
        editBox.saveUrl = "/eee/save";
    </script>
@stop


