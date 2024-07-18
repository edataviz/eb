<?php
	$currentSubmenu ='/pd/demurrageebo';
	$tables = ['PdDemurageEbo'	=>['name'=>'Load']];
	
	$isAction = true;
?>

@extends('core.pd')
@section('funtionName')
DEMURRAGE/EBO
@stop

@section('adaptData')
@parent
<script>

	actions.loadUrl = "/demurragreebo/load";
	actions.saveUrl = "/demurragreebo/save";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'ID';
					},
				};

	actions.renderFirsColumn  =  actions.defaultRenderFirsColumn;
	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};
	
	actions.getUomCollection = function(collection,columnName,cellData,rowData){
		if(columnName=="ACTIVITY_NAME") {
			var rs = [];
			var cls = rowData['terminal_timesheet_data'];
			$.each(cls, function( i, vl ) {
				 var result = $.grep(collection, function(e){
									return e['ID'] == vl.ACTIVITY_ID;
								});
				if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined") rs.push(result[0]);
            });
			return rs;
		}
		return collection;
	}
	source['ACTIVITY_NAME']	=	{	dependenceColumnName	:	['START_TIME', 'END_TIME', 'ELAPSE_TIME'],
									url						: 	null
		};

	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
 		if(columnName=='ACTIVITY_NAME') {
 			var table = $('#table_PdDemurageEbo').DataTable();
			var cls = rowData['terminal_timesheet_data'];

			var result = $.grep(cls, function(e){
								return e['ACTIVITY_ID'] == newValue;
							});
			if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined"){
	 	    	var row = table.row( '#'+rowData['DT_RowId']);
				rowData['START_TIME'] 		= result[0]['START_TIME'];
				rowData['END_TIME'] 		= result[0]['END_TIME'];
				rowData['ELAPSE_TIME'] 		= result[0]['ELAPSE_TIME'];
				rowData['RATE_HOUR'] 		= result[0]['RATE_HOUR'];
				rowData['AMOUNT'] 			= result[0]['AMOUNT'];
				rowData['OVERRIDE_AMOUNT'] 	= result[0]['OVERRIDE_AMOUNT'];
				row.data(rowData).draw();
			}
	           
		}
	}
/*

	actions.dominoColumnSuccess = function(_data, dependenceColumnNames, rowData, tab){
		var data = _data.dataSet;

		var table = $('#table_Demurrage').DataTable();

		rowData['START_TIME'] = data['START_TIME'];
		rowData['END_TIME'] = data['END_TIME'];
		rowData['ELAPSE_TIME'] = data['ELAPSE_TIME'];

    	row = table.row( '#'+rowData['DT_RowId']);
		row.data(rowData).draw(false);
			
	};

	$(function(){
		var ebtoken = $('meta[name="_token"]').attr('content');
		$.ajaxSetup({
			headers: {
				'X-XSRF-Token': ebtoken
			}
		});
		
		$('#buttonSave').prop('onclick',null).off('click');

		$('#buttonSave').click( function () { 

			var table = $('#table_Demurrage').dataTable();
			var xx = table.api().data();

			var sData = [];	
			for(var i = 0; i < xx.length; i++){
				sData.push(xx[i]); 
			}
			
		 	param = {
					'data' : sData
			}
			
			sendAjax('/demurragreebo/save', param, function(data){
				if(data == 'ok'){
					alert("Save successfully");
				}else{
					alert(data);
				}
			}); 
		});
	});
	*/
</script>
@stop