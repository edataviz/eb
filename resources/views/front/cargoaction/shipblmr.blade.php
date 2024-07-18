<?php
	$currentSubmenu ='/pd/shipblmr';
	$tables = ['ShipCargoBlmr'	=>['name'=>'Load']];
	$detailTableTab = 'ShipCargoBlmrData';
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/shipblmr/load";
 	actions.saveUrl = "/shipblmr/save";
	actions['idNameOfDetail'] = ['BLMR_ID', 'ID'];

	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="ShipCargoBlmr";
	};

	actions.getAddButtonHandler = actions.getDefaultAddButtonHandler;
	
	addingOptions.keepColumns = ['MEASURED_ITEM','ITEM_UOM','FORMULA_ID'];

	blmr_id = 0;

	actions['doMoreAddingRow'] = function(addingRow){
		addingRow['BLMR_ID'] 		= blmr_id;
// 		addingRow['STORAGE_ID'] 	= voyageBundle.STORAGE_ID;
		return addingRow;
	}

	actions.renderFirsEditColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var html = '<a id="delete_row_'+id+'" class="actionLink">&nbsp;Delete</a>';
		if(!isAdding){
			html += '<a id="cal_row_'+id+'" class="actionLink">&nbsp;Calculate</a>';
		}
		else html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		return html;
	};

	actions['addMoreHandle']  = function ( table,rowData,td,tab) {
		var id = rowData['DT_RowId'];
		var moreFunction = function(e){
		    postData = {id:id};
		    docalculate(postData);
		};
//		$(td).find('#cal_row_'+id).click(editFunction);
		table.$('#cal_row_'+id).click(moreFunction);
	};

	editBox.initExtraPostData = function (id,rowData){
										blmr_id = id;
								 		return 	{
									 			id			: id,
									 		};
								 	};

	docalculate = function (postData){
		showWaiting();
	    $.ajax({
			url: '/shipblmrdetail/cal',
			type: "post",
			data: postData,
			success:function(data){
				console.log ( "send cal  success "/* +JSON.stringify(data) */);
// 				alert("calculate success");
				hideWaiting();
				actions.saveSuccess(data);
			},
			error: function(data) {
				alert("ERROR "+data['responseText']);
				console.log ( "calculate error ");
				hideWaiting();
			}
		});
 	};

	var voyageBundle;
	editBox.initExtraPostData = function (id,rowData){
		blmr_id = id;
		voyageBundle = {
							id			: id,
							VOYAGE_ID	: id,
							STORAGE_ID	: rowData.STORAGE_ID,
							CARGO_ID	: rowData.CARGO_ID,
							SCHEDULE_QTY: rowData.SCHEDULE_QTY,
							Facility	: actions.loadedData['ShipCargoBlmr'].Facility,
							tabTable	: '{{$detailTableTab}}',
						};
		return 	voyageBundle;
	};

    editBox.editGroupSuccess = function(data,id){
    	actions.loadSuccess(data);
	}
	
 	oAfterTable = actions.afterDataTable;
	actions.afterDataTable = function (table,tab){
		 oAfterTable(table,tab);
		 if(tab='{{$detailTableTab}}'){
			 jQuery('<button/>', {
				    id: 'more_'+tab,
				    title: 'Cal. all',
				    text: 'Cal. all'
				}).on( 'click', function(e){
					postData = {id : blmr_id, isAll:true};
				    docalculate(postData);
				})
			.appendTo("#toolbar_"+tab);
		 }
	};
	
	var oGetTableHeight = actions.getTableHeight;
	actions.getTableHeight = function (tab){
		if(tab == '{{$detailTableTab}}'){
			return "200px";
		}
		return oGetTableHeight(tab);
	};

    actions.getRenderFirsColumnFn = function (tab) {
        if(tab=='{{$detailTableTab}}') return actions.renderFirsEditColumn;
		return actions.renderFirsColumn;
	}
	
    actions.enableUpdateView = function(tab,postData){
		return tab=='ShipCargoBlmr';
	};
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/shipblmrdetail/load";
	editBox.saveUrl = '/shipblmrdetail/save';

	editBox['size'] = {	height : 350,
						width : 900,
			};
</script>
@stop
