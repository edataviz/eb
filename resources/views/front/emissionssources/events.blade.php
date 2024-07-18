<?php
$currentSubmenu ='/ghg/events';
$tables = ['EmissionEventDataValue'	=>['name'=>'Data Input']];
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
        actions.loadUrl = "/events/load";
        actions.saveUrl = "/events/save";

        actions['idNameOfDetail'] = ['PARENT_ID', 'ID','BEGIN_DATE'];

        actions.type = {
            idName:['ID'],
            keyField:'ID',
            saveKeyField : function (model){
                return 'ID';
            },
        };
        actions.renderFirsColumn = function ( data, type, rowData ) {
            var id = rowData['DT_RowId'];
            isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
            var html = '';
            if(isAdding){
                html += '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
            }else{
                html += '<a id="delete_row_' + id + '" class="actionLink">&nbsp;Delete</a>';
                html += '<a id="edit_row_' + id + '" class="actionLink">Select</a>';
            }
            return html;
        };
        actions.getRenderFirsColumnFn = function (tab) {
            if(tab=='{{$detailTableTab}}') return actions.renderFirsEditColumn;
            return actions.renderFirsColumn;
        }

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
        actions['idNameOfDetail'] = ['PARENT_ID', 'ID','BEGIN_DATE'];

        actions['doMoreAddingRow'] = function(addingRow){
            addingRow['PARENT_ID'] = pid;
//            addingRow['OCCUR_DATE'] = date_parent;
            if( date_parent === undefined) addingRow['OCCUR_DATE'] = date_parent;
            return addingRow;
        }
        //        editBox.hidenFields = [{name:'PARENT_ID',field:'PARENT_ID'}];

        editBox.editGroupSuccess = function(data,id){
            actions.loadSuccess(data);
        };

        actions.enableUpdateView = function(tab,postData){
            return tab=='EmissionCombDataValue';
        };
        actions.getAddButtonHandler = getAddButtonHandler;

        // Load content
        actions.extraDataSetColumns = {'OBJECT_ID':'OBJECT_TYPE'};

        source['OBJECT_TYPE']={dependenceColumnName	:	['OBJECT_ID'],
            url						: 	'/events/loadsrc'
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
        editBox.loadUrl = "/ese/load";
        editBox.saveUrl = "/ese/save";

        editBox['size'] = {	height : 350,
            width : 1400,
        };
    </script>
@stop


