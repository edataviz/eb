<?php
	$currentSubmenu ='/pd/contractprogram';
	$tables = ['PdContractProgram'	=>['name'=>'Load']];
	$detailTableTab = 'PdContractProgramOpen';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/contractprogram/load";
	actions.saveUrl = "/contractprogram/save";
// 	actions['idNameOfDetail'] = ['CONTRACT_ID_INDEX', 'ATTRIBUTE_ID_INDEX','ID'];

	addingOptions.keepColumns = ['START_DATE',
	                         	'END_DATE', 
	 							'CONTRACT_ID', 
								'PROGRAM_TYPE', 
								'RUN_FREQUENCY'];

	actions.renderFirsColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var html = '';
		if(isAdding)
			html += '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		else 
			html += '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Open</a>';
		return html;
	};


	
	currentContractId = 0;
	editBox['filterField'] = 'ATTRIBUTE_ID';
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['ATTRIBUTE_ID'] 		= selectRow.CODE;
		addingRow['CONTRACT_ID'] 		= selectRow.NAME;
		addingRow['ATTRIBUTE_ID_INDEX'] = selectRow.ID;
		addingRow['CONTRACT_ID_INDEX'] 	= currentContractId;
		addingRow['CONTRACT_ID_INDEX'] 	= currentContractId;
		return addingRow;
	};


	editBox.editGroupSuccess = function(data,id){
		var detailTab 	= '{{$detailTableTab}}';
		$("#"+detailTab).html(data);
	}

	editBox.initExtraPostData = function (id,rowData){
									currentContractId = id;
								 		return 	{
									 			code		: rowData.CODE,
										 		storage_id	: $("#Storage").val(),
									 			contract_id	: rowData.CONTRACT_ID
									 		};
								 	};

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/contractprogram/open";
// 	editBox.saveUrl = '/contractprogram/save';

	editBox['size'] = {	height : 420,
						width : 1000,
			};
</script>
@stop

@section('editBoxContentview')
<div id="{{$detailTableTab}}">
</div>
@stop
