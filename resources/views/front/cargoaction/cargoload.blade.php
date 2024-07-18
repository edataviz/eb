<?php
	$currentSubmenu ='/pd/cargoload';
	$tables = ['PdCargoLoad'	=>['name'=>'Load']];
	$isLoad = 1;
	$detailTableTab = 'TerminalTimesheetData';
	$pageMain = 'PdCargoLoad';
?>

@extends('core.cargoaction')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargoload/load";
	actions.saveUrl = "/cargoload/save";
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoLoad";
	};

	    editBox.editGroupSuccess = function(data,id){
	    	actions.loadSuccess(data);
		}

	    editBox['buildExtraSaveDetailData'] = function(editId,saveUrl) {
	  		return {
	  					Facility	: voyageBundle.Facility,
		 	};
		};
		
	    actions.getRenderFirsColumnFn = function (tab) {
	        if(tab=='{{$detailTableTab}}') return actions.renderFirsEditColumn;
			return actions.renderFirsColumn;
		}
		
	    actions.enableUpdateView = function(tab,postData){
			return tab=='PdVoyage';
		};
</script>
@stop


@section('editBoxParams')
@parent
<script>
 	editBox.loadUrl = "/timesheet/load";
</script>
@stop