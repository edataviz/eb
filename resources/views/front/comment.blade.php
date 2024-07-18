<?php
$currentSubmenu = isset($currentSubmenu)?$currentSubmenu:'/fo/comment';
$tables 		= isset($tables)?$tables:['Comments'	=>['name'=>'Comments']];
$isAction 		= true;
?>
@extends('core.fo')

@section('funtionName')
COMMENT DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/comment/load";
	actions.saveUrl = "/comment/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
					},
				};
	
    var uoms_array = [];
    var uoms_array_id = [];
    var date_time = '';

    var ogetTableOption = actions.getTableOption;
    actions.getTableOption	= function(data,tab){
        // Date time
        var parse_final = moment();
        var parse_begin =  moment($("#date_begin").val(),configuration.time.DATETIME_FORMAT);
        var parse_end =  moment($("#date_end").val(),configuration.time.DATETIME_FORMAT);
        date_time = '';
        if(parse_final >= parse_begin && parse_end <= parse_end) date_time = parse_final;
        if(parse_final < parse_begin) date_time = parse_begin;
        if(parse_final > parse_end) date_time = parse_end;
        date_time = date_time.hour(0).minute(0).second(0).format(configuration.time.DATETIME_FORMAT_UTC);
        // Category
        uoms_array_id = [];
        uoms_array = [];
        var uoms = data["uoms"];
        if(uoms !== undefined && uoms != null){
            for (var j = 0 ; j<uoms.length ; j++){
                if (uoms[j]["COLUMN_NAME"] == actions.getCategoryColumn(tab)){
                    var data_array = uoms[j]["data"];
                    if (data_array.length > 0) {
                        for (var i = 0 ; i<data_array.length ; i++){
                            uoms_array.push(data_array[i]['NAME']);
                            uoms_array_id.push(data_array[i]['ID']);
						}
					}
				}
			}
        }
        return ogetTableOption(data,tab);
    };

    var oAfterDataTable  = actions.afterDataTable;
    actions.afterDataTable = function (table,tab){
        oAfterDataTable(table,tab);
        var allAll = $('<button class="addButton">Add All</button>');
        allAll.on( 'click', function(e){
// 			actions.disableKeepColumns = false;
            for (var i = 0 ; i<uoms_array_id.length ; i++){
                var addButtonHandle = actions.getAddButtonHandler(table,tab,function(addingRow){
                    addingRow[actions.getCategoryColumn(tab)]  = uoms_array_id[i];
                    addingRow['CREATED_DATE'] = date_time;
                    return addingRow;
                });
                addButtonHandle();
            }
// 			actions.disableKeepColumns = true;
        });
        allAll.appendTo($("#toolbar_"+tab));
    };

    actions.getCategoryColumn = function (tab){
        return "COMMENT_CATEGORY";
    };
</script>
@stop
