<?php
	$currentSubmenu ='/pd/cargovoyage';
	$tables = ['PdVoyage'	=>['name'=>'Load']];
	$detailTableTab = 'PdVoyageDetail';
	$attributeTableTab = 'PdCodeContractAttribute';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargovoyage/load";
	actions.saveUrl = "/cargovoyage/save";

	actions['idNameOfDetail'] = ['VOYAGE_ID', 'ID','STORAGE_ID','CARGO_ID'];

	addingOptions.keepColumns = ['LIFTING_ACCOUNT','LOAD_UOM','BERTH_ID'];

	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdVoyage";
	};

	actions.getAddButtonHandler = actions.getDefaultAddButtonHandler;

 	var voyageBundle;
	editBox.initExtraPostData = function (id,rowData){
		voyageBundle = {
					 		id			: id,
					 		VOYAGE_ID	: id,
					 		STORAGE_ID	: rowData.STORAGE_ID,
					 		CARGO_ID	: rowData.CARGO_ID,
					 		SCHEDULE_QTY: rowData.SCHEDULE_QTY,
					 		Facility	: actions.loadedData['PdVoyage'].Facility,
					 		tabTable	: '{{$detailTableTab}}',
					 	};
	 		return 	voyageBundle;
	 	};


	oAfterTable = actions.afterDataTable;
	 actions.afterDataTable = function (table,tab){
		 oAfterTable(table,tab);
		 if(tab='{{$detailTableTab}}'){
			 jQuery('<button/>', {
				    id: 'more_'+tab,
				    title: 'Generate transport detail',
				    text: 'Generate transport detail'
				}).on( 'click', function(e){
					showWaiting();
		    		$.ajax({
						url: "/voyage/gentransport",
						type: "post",
						data: {VOYAGE_ID: voyageBundle.VOYAGE_ID},
		    			success:function(data){
		    				hideWaiting();
		    				console.log ( "success Generate transport detail ");
		     				alert("success");
		    			},
		    			error: function(data) {
		    				hideWaiting();
		    				alert("error!");
		    				console.log ( "error Generate transport detail ");
		    			}
		    		});
				})
				.appendTo("#toolbar_"+tab);
		 }
	};

	var oGetTableOption = actions.getTableOption;
	actions.getTableOption = function (data,tab) {
		if (tab == '{{$detailTableTab}}') {
			return {
	 			tableOption :	{
						autoWidth	: false,
						scrollX		: false,
						searching	: false,
						scrollY		: "200px",
						footerCallback : function ( row, data3, start, end, display ) {
							            var api = this.api();
							            columns = [4];
							            total = editBox.renderSumRow(api,columns);

							       	 	var currentQTY = parseFloat(voyageBundle.SCHEDULE_QTY);
							            errQtyNotMatch=(total!=currentQTY);
								        $( api.columns(4).footer() ).css("background",(errQtyNotMatch?"#ffaaaa":"#aaffaa"));
								        $( api.columns(5).footer() ).html(errQtyNotMatch?((total>currentQTY?"> ":"< ")+currentQTY+" <img src='../img/e.png' align='absmiddle' height=16>"):"");
			        	},
                    resetTableHtml : function(tabName) { return true},
				}
			};
		}
		return oGetTableOption(data,tab);
	};
	 
	 
	editBox['getEditTableOption'] = function(tab){
		return {
		 			tableOption :	{
							autoWidth	: false,
							scrollX		: false,
							searching	: false,
							scrollY		: "200px",
							footerCallback : function ( row, data3, start, end, display ) {
								            var api = this.api();
								            columns = [4];
								            total = editBox.renderSumRow(api,columns,0);

								       	 	var currentQTY = parseFloat(voyageBundle.SCHEDULE_QTY);
								            errQtyNotMatch=(total!=currentQTY);
									        $( api.columns(4).footer() ).css("background",(errQtyNotMatch?"#ffaaaa":"#aaffaa"));
									        $( api.columns(5).footer() ).html(errQtyNotMatch?((total>currentQTY?"> ":"< ")+currentQTY+" <img src='../img/e.png' align='absmiddle' height=16>"):"");
				        	}
					}
				};
	};

	actions.isRefreshFooter	= function(tab,columnName,newValue,type){
// 		return tab=="PdVoyage";
		return true;
	};
	
	actions['doMoreAddingRow'] = function(addingRow){
		addingRow['VOYAGE_ID'] 		= voyageBundle.VOYAGE_ID;
		addingRow['STORAGE_ID'] 	= voyageBundle.STORAGE_ID;
		addingRow['CARGO_ID'] 		= voyageBundle.CARGO_ID;
		return addingRow;
	}
	
	 var errQtyNotMatch=false;
	 editBox['notValidatedData'] = function(editId) {
		 if(errQtyNotMatch){
				if(!confirm("Total parcels quantity does not match with voyage scheduled quantity. Save anyway?"))
					return true;
				else return false;
		}
	    return false;
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
	editBox.loadUrl = "/voyage/load";
	editBox.saveUrl = '/voyage/save';

	editBox['size'] = {	height : 380,
						width : 900,
			};
</script>
@stop


@section('editBoxfooter')
	<tfoot>
		<tr>
			<td style="text-align:left" colspan="3"></td>
			<td style="text-align:left">Sum Load Qty:</td>
			<td id = "sum_qty_value" style="text-align:right;background: rgb(170, 255, 170);"></td>
			<td id = "qtyMatching" style="text-align:left" ></td>
			<td style="text-align:left" ></td>
		</tr>
	</tfoot>
@stop