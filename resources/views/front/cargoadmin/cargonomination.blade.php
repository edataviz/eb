<?php
	$currentSubmenu ='/pd/cargonomination';
	$tables = ['PdCargoNomination'	=>['name'=>'Data Input']];
	$isAction = true;
?>

@extends('core.pd')
@section('funtionName')
CARGO NOMINATION
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargonomination/load";
	actions.saveUrl = "/cargonomination/save";
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};
	addingOptions.keepColumns = ['CARGO_ID','PD_TRANSIT_CARRIER_ID','TRANSIT_TYPE','NOMINATION_DATE','NOMINATION_UOM','NOMINATION_ADJ_TIME','PRIORITY','INCOTERM'];

	actions.extraDataSetColumns = {'PD_TRANSIT_CARRIER_ID':'TRANSIT_TYPE'};
		
	source['TRANSIT_TYPE']		= {	dependenceColumnName	:	['PD_TRANSIT_CARRIER_ID'],
									url						: 	'/cargonomination/loadsrc'
									};
	
	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			if(typeof rowData['CARGO_STATUS'] !== "undefined" && rowData['CARGO_STATUS'] !== null && (rowData['CARGO_STATUS'] == 3 || rowData['CARGO_STATUS'] == '3')){
				html += "<img class='nominate' src='../img/tick.png' height=16> <a id='edit_row_"+id+"' class='actionLink'>Reset</a>";
			}
			else{
				html += '<a id="edit_row_'+id+'" class="actionLink">Confirm</a>';
			}
		}
		return html;
	};
</script>
@stop


@section('editBoxParams')
@parent
<script>
	editBox.editRow = function (id,rowData){
 		showWaiting();
		isReset = (rowData['CARGO_STATUS'] == 3 || rowData['CARGO_STATUS'] == '3');
		actionText = isReset?'reset':'confirm';
		requestUrl = isReset?'/cargonomination/reset':'/cargonomination/confirm';
		$.ajax({
			url: requestUrl,
			type: "post",
			data: {
					nominationId		: id,	
				},
			success:function(data){
				hideWaiting();
				console.log (  actionText+"success: "/*+JSON.stringify(data)*/);
				alert(data.message);
				if(data.code=='NOT_EXIST'){
					//REMOVE ROW
					table = $('#table_PdCargoNomination').DataTable();
					row = table.row( '#'+id);
					row.remove().draw(false);
				}
				else if(data.code!='ERROR'){
					rowData.CARGO_STATUS = isReset?1:3;
					table = $('#table_PdCargoNomination').DataTable();
					row = table.row( '#'+id);
					row.data(rowData).draw(false);
				}
			},
			error: function(data) {
				hideWaiting();
				console.log ( actionText+"error: "/*+JSON.stringify(data)*/);
				alert(data.responseText);
			}
		});
    }
		
</script>
@stop

