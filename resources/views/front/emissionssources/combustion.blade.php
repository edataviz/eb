<?php
$currentSubmenu ='/ghg/combustion';
$tables = ['EmissionCombDataValue'	=>['name'=>'Data Input']];
$isAction = true;
$detailTableTab = 'EmissionCombRelDataValue';
?>

@extends('core.es')
@section('funtionName')
    CARGO ENTRY
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/combustion/load";
        actions.saveUrl = "/combustion/save";

        actions['idNameOfDetail'] = ['PARENT_ID', 'ID','OCCUR_DATE'];

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
            date_parent = rowData['OCCUR_DATE'];
            idata.tabTable  = '{{$detailTableTab}}';
            return 	idata;
        };
        // lấy ra parent id
        actions['idNameOfDetail'] = ['PARENT_ID', 'ID','OCCUR_DATE'];

        actions['doMoreAddingRow'] = function(addingRow){
            addingRow['PARENT_ID'] = pid;
//            addingRow['OCCUR_DATE'] = date_parent;
            if(typeof  date_parent != "undefined") addingRow['OCCUR_DATE'] = date_parent;
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

    </script>
@stop


@section('editBoxParams')
    @parent
    <script>
        editBox.loadUrl = "/esc/load";
        editBox.saveUrl = '/esc/save';

        editBox['size'] = {	height : 350,
            width : 1300,
        };
    </script>
@stop


