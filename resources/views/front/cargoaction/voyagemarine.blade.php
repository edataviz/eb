<?php
	$currentSubmenu ='/pd/voyagemarine';
	$tables = ['PdTransportShipDetail'	=>['name'=>'Load']];
	$detailTableTab = 'PdShipPortInformation';
	$isLoad = 0;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/voyagemarine/load";
	actions.saveUrl = "/voyagemarine/save";
	actions['idNameOfDetail'] = ['ID'];
	
	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};

	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Select</a>';
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a id="gen_row_'+id+'" class="actionLink">Generate BL/MR</a>';
		}
		return html;
	};
	actions.renderFirsEditColumn = null;
	actions['addMoreHandle']  = function ( table,rowData,td,tab) {
		var id = rowData['DT_RowId'];
		var moreFunction = function(e){
			showWaiting();
		    postData = {id:id};
		    $.ajax({
				url: '/voyagemarine/gen',
				type: "post",
				data: postData,
				success:function(data){
					console.log ( "send gen_row_  success : "/* +JSON.stringify(data) */);
					alert(JSON.stringify(data));
					hideWaiting();
				},
				error: function(data) {
					console.log ( "gen_row_ error ");
					hideWaiting();
				}
			});
		};
//		$(td).find('#edit_row_'+id).click(editFunction);
		table.$('#gen_row_'+id).click(moreFunction);
	};

	var voyageBundle;
	editBox.initExtraPostData = function (id,rowData){
		voyageBundle = {
					 		id			: id,
					 		VOYAGE_ID	: id,
					 		STORAGE_ID	: rowData.STORAGE_ID,
					 		CARGO_ID	: rowData.CARGO_ID,
					 		SCHEDULE_QTY: rowData.SCHEDULE_QTY,
					 		Facility	: actions.loadedData['PdTransportShipDetail'].Facility,
					 		tabTable	: '{{$detailTableTab}}',
					 	};
	 		return 	voyageBundle;
	 	};

		var oGetTableHeight = actions.getTableHeight;
		actions.getTableHeight = function (tab){
			if(tab == '{{$detailTableTab}}'){
				return "200px";
			}
			return oGetTableHeight(tab);
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
 	editBox.loadUrl = "/shipport/load";
 	editBox.saveUrl = "/shipport/save";
 	
</script>
@stop