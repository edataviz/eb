<?php
	$currentSubmenu ='/pd/voyagepipeline';
	$tables = ['PdTransportPipelineDetail'	=>['name'=>'Load']];
	$isLoad = 0;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/voyagepipeline/load";
	actions.saveUrl = "/voyagepipeline/save";
	actions['idNameOfDetail'] = ['ID'];
	
	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};

	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
// 		var html = '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Select</a>';
		var html = '';
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a id="gen_row_'+id+'" class="actionLink">Generate</a>';
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
				url: '/voyagepipeline/gen',
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
	
</script>
@stop
