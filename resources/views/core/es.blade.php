<?php
    $mainTab = isset($mainTab)?$mainTab:"";
?>
@extends('core.contract')

@section('adaptData')
    @parent
    <script>

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

        {{--var pid;--}}
        {{--var date_parent;--}}
        {{--var oInitExtraPostData = editBox.initExtraPostData;--}}
        {{--editBox.initExtraPostData = function (id,rowData){--}}
            {{--var idata = oInitExtraPostData(id,rowData);--}}
            {{--pid = id;--}}
{{--//            date_parent = rowData['BEGIN_DATE'];--}}
            {{--date_parent = rowData['OCCUR_DATE'];--}}
            {{--idata.tabTable  = '{{$detailTableTab}}';--}}
            {{--return 	idata;--}}
        {{--};--}}
        {{--// lấy ra parent id--}}
        {{--actions['idNameOfDetail'] = ['PARENT_ID', 'ID','OCCUR_DATE'];--}}

        {{--actions['doMoreAddingRow'] = function(addingRow){--}}
            {{--addingRow['PARENT_ID'] = pid;--}}
            {{--if(typeof  date_parent != "undefined") addingRow['OCCUR_DATE'] = date_parent;--}}
            {{--return addingRow;--}}
        {{--}--}}

        editBox.editGroupSuccess = function(data,id){
            actions.loadSuccess(data);
        };

        actions.enableUpdateView = function(tab,postData){
            return tab=='{{$mainTab}}';
        };
        actions.getAddButtonHandler = getAddButtonHandler;

        var oGetTableOption = actions.getTableOption;
        actions.getTableOption = function (data,tab) {
            if (tab == '{{$detailTableTab}}') {
                return {
                    tableOption :	{
                        autoWidth	: false,
                        scrollX		: false,
                        searching	: false,
                        scrollY		: "200px",
                    },
                    resetTableHtml : function(tabName) { return true}
                };
            }
            return oGetTableOption(data,tab);
        };
    </script>
@stop

@section('editBoxParams')
    @parent
    <script>
        editBox['size'] = {	height : 350,
            width : 1400,
        };
    </script>
@stop
