<?php
	$currentSubmenu ='/pd/liftaccmonthlyadjust';
	$tables 		= ['PdLiftingAccountMthData'	=>['name'=>'Data']];
	$isAction 		= true;
?>

@extends('core.pd')
@section('funtionName')
LIFTING ACCT MONTHLY DATA
@stop

@section('adaptData')
@parent
<script>
	$( document ).ready(function() {
	    console.log( "ready!" );
	    var onChangeFunction = function() {
		    if($('#PdLiftingAccount option').size()>0 ) actions.doLoad(true);
	    };
	    
	    $( "#PdLiftingAccount" ).change(onChangeFunction);
// 		actions.doLoad(true);
	});
	
	actions.loadUrl = "/liftaccmonthlyadjust/load";
 	actions.saveUrl = "/liftaccmonthlyadjust/save";
	actions.type = {
			idName:['ID', 'LIFTING_ACCOUNT_ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

	addingOptions.keepColumns = ['ADJUST_CODE','LIFTING_ACCOUNT_ID'];

	actions['doMoreAddingRow'] = function(addingRow){
		if(typeof(addingRow['LIFTING_ACCOUNT_ID']) === "undefined" || addingRow['LIFTING_ACCOUNT_ID'] =="" ){
			addingRow['LIFTING_ACCOUNT_ID'] 		= actions.loadedData["PdLiftingAccountMthData"]["PdLiftingAccount"];
		}
		return addingRow;
	}
	
	actions.renderDatePicker = function (editable,columnName,cellData, rowData){
		editable['viewformat'] = configuration.picker.DATE_FORMAT.replace("dd/", "").replace("/dd", "");
		editable['datepicker'] 	= 	{
						          		minViewMode	:1,
						          		maxViewMode	:3,
						            };
		
		return editable;
	}

	actions.renderDateFormat = function (data2,type2,rowrow){
		var format = configuration.time.DATE_FORMAT.replace("DD/", "").replace("/DD", "");
		if (data2.constructor.name == "Date") { 
			return moment.utc(data2).format(format);
			
		}
		return moment.utc(data2,configuration.time.DATETIME_FORMAT_UTC).format(format);
	};
</script>
@stop


