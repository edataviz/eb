<?php
	$detailTableTab = isset($detailTableTab)?$detailTableTab:'TerminalTimesheetData';
	$attributeTableTab = 'PdCodeLoadActivity';
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions['idNameOfDetail'] = ['PARENT_ID', 'ACTIVITY_ID','ID','IS_LOAD'];

	editBox['getEditTableColumns'] = function(tab){
		return [{title:'NAME',data:'NAME',width:305}];
	}
	
	parentId = 0;
	editBox['filterField'] = 'ACTIVITY_ID';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow[editBox['filterField']] 		= selectRow.CODE;
		addingRow['PARENT_ID'] 					= parentId;
		addingRow['IS_LOAD'] 					= {{$isLoad}};
		return addingRow;
	};

	editBox['addMoreActionButton'] = function(table,tab){
	};
	
	editBox.initExtraPostData = function (id,rowData){
										parentId = id;
								 		return 	{
									 			id			: id,
									 		};
								 	};


	var voyageBundle;
	editBox.initExtraPostData = function (id,rowData){
		parentId = id;
		voyageBundle = {
							id			: id,
							VOYAGE_ID	: id,
							STORAGE_ID	: rowData.STORAGE_ID,
							CARGO_ID	: rowData.CARGO_ID,
							SCHEDULE_QTY: rowData.SCHEDULE_QTY,
							Facility	: actions.loadedData['{{$pageMain}}'].Facility,
							tabTable	: '{{$detailTableTab}}',
						};
		return 	voyageBundle;
	};


	var oGetTableHeight = actions.getTableHeight;
	actions.getTableHeight = function (tab){
		if(tab == 'TerminalTimesheetData'){
			return "200px";
		}
		return oGetTableHeight(tab);
	};

								 	
	editBox['moreActionTitle'] 	= 'Load activity set';
	var currentBox;
	var oAfterTable					= actions.afterDataTable;
	actions.afterDataTable = function (table,tab){
		 oAfterTable(table,tab);
		 if(tab='{{$detailTableTab}}'){
			var box = $("#toolbar_"+tab).find('#box_select_activity_set').first();
			box = box.length>0?box:$("#box_select_activity_set").clone();
			currentBox = box;
			 jQuery('<button/>', {
				    id		: 'more_'+tab,
				    title	: editBox['moreActionTitle'],
				    text	: editBox['moreActionTitle']
				}).on( 'click', function(e){
					if(box.is(":visible")){
						box.hide("slide", { direction: "down" }, 100);
					}
					else{
 						box.show("slide", { direction: "down" }, 300);
					}
				})
			.appendTo("#toolbar_"+tab);
			box.appendTo($("#toolbar_"+tab));
			
			editBox.addMoreActionButton(table,tab);
		 }
	};

	editBox['renderGotData'] = function(tab,data){
		return data;
	};

	function setActivitySet(id){
		currentBox.hide("slide", { direction: "down" }, 100);
		showWaiting();
	    $.ajax({
			url: editBox.activitiesUrl,
			type: "post",
			data: {id:id},
			success:function(data){
				hideWaiting();
				console.log ( "send  "+editBox.activitiesUrl+" success ");
				var updatedData = data.updatedData['{{$detailTableTab}}'];
				var otable = $('#table_{{$detailTableTab}}').DataTable();
				var tableData =  otable.data();
				var filterData = [];
				var unionData = [];
				$.each(updatedData, function( index, value ) {
					filters = $.grep(tableData,function(element,index) {
					  	return element[editBox['filterField'] ]==value[editBox['filterField']];
					});
					
					if(filters.length<=0
// 							||(typeof filters[0]['DT_RowId']=="string"&& filters[0]['DT_RowId'].startsWith("NEW_RECORD_DT_RowId_"))
							){
						var pvalue = value;
						if(typeof(editBox["putFieldsData"]) == "function"){
							pvalue = editBox.putFieldsData(value);
						}
						filterData.push(pvalue);
						unionData.push(pvalue['DT_RowId'] );
					}
	            });
				data.updatedData['{{$detailTableTab}}'] = filterData;
				actions.saveSuccess(data,true);
				$.each(unionData, function( index, DT_RowId ) {
					$('#'+DT_RowId).effect("highlight", {}, 5000);
	            });
	            
			},
			error: function(data) {
				hideWaiting();
				console.log ( "send  "+editBox.activitiesUrl+" error ");
			}
		});

	}

	editBox['saveFloatDialogSucess'] = function(data,saveUrl){
		actions.saveSuccess(data);
		otable = $('#table_{{$detailTableTab}}').DataTable();
		$.each(otable.data(), function( i, rowData ) {
			var id = rowData['DT_RowId'];
			if ((typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1)) {
				table.row($('#'+id)).remove().draw(false);
			}
		});
	}

	editBox['putFieldsData'] = function(value){
		var pvalue 					= value;
		pvalue['PARENT_ID'] 		= parentId;
		pvalue['START_TIME'] 		= '';
		pvalue['END_TIME'] 			= '';
		pvalue['COMMENT'] 			= '';
		pvalue['IS_LOAD'] 			= {{$isLoad}};
		pvalue['DT_RowId'] 			= 'NEW_RECORD_DT_RowId_'+(index++);
		pvalue['ID'] 				= value['DT_RowId'];
		return pvalue;
	}
								 	
</script>
@stop

@section('editBoxParams')
@parent
<script>
// 	editBox.loadUrl = "/timesheet/load";
	editBox.saveUrl 		= '/timesheet/save';
	editBox.activitiesUrl 	= '/timesheet/activities';
	
</script>
@stop
@section('floatMoreBox')
@parent
	@if(isset($activities))
		<div id="box_select_activity_set" style="display:none;border:1px solid #888;position:absolute;width:250px;bottom:44px;left:120px;background:white">
				<table border='0' style='width:100%' cellpadding='5' cellspacing='0'>
					@foreach($activities as $activity )
						<tr class='row_activity' style='cursor:pointer' onclick="setActivitySet({{$activity->SET_ID}})">
						<td>{{$activity->SET_NAME}}</td>
						</tr>
			 		@endforeach
				</table>
		</div>
	@endif
@stop


