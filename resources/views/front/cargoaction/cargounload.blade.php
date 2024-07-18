<?php
	$currentSubmenu ='/pd/cargounload';
	$tables = ['PdCargoUnload'	=>['name'=>'Unload']];
	$isLoad = 0;
	$detailTableTab = 'TerminalTimesheetData';
	$pageMain = 'PdCargoUnload';
?>

@extends('core.cargoaction')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargounload/load";
	actions.saveUrl = "/cargounload/save";
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoUnload";
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
 	editBox.loadUrl = "/timesheet/unload";
 	editBox.saveUrl = '/timesheet/save_unload';
</script>
@stop