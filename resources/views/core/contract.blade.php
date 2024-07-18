<?php
	$isAction = true;
	if (!isset($detailTableTab)) $detailTableTab = '';
	if (!isset($attributeTableTab)) $attributeTableTab = '';
	if (!isset($contractAttributes)) $contractAttributes = '';
?>

@extends('core.pd')

@section('adaptData')
@parent
<script>
	var contractAttributes = <?php echo json_encode($contractAttributes); ?>;
	actions.type = {
			idName		: function (tab){
							if(tab=='{{$detailTableTab}}') return actions.idNameOfDetail;
							return ['ID'];
						},
			keyField:'ID',
			saveKeyField : function (model){
					return 'ID';
				},
			};

	actions.renderFirsColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var html = '';
		if(isAdding)
			html += '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		else 
			html += '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Select</a>';
		return html;
	};
	
	actions.renderFirsEditColumn = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		return html;
	};

	 actions.getRenderFirsColumnFn = function (tab) {
	        if(tab=='{{$detailTableTab}}') return actions.renderFirsEditColumn;
			return actions.renderFirsColumn;
		}

	getAddButtonHandler = actions.getAddButtonHandler;
	actions.getAddButtonHandler = function (otable,otab){
		if(otab=='{{$detailTableTab}}'){
			return function (e){
					var dialogOptions = {
							height: 450,
							width: 400,
							position: { my: 'top', at: 'top+100' },
							modal: true,
							title: 'Attributes',
						};
					$("#floatMoreBox").dialog(dialogOptions);

					tab = 'att_{{$attributeTableTab}}';
					options = {
			 					tableOption :	{
								 						searching			: true,
					 									autoWidth			: false,
					 									scrollX				: true,
					 									bInfo 				: false,
					 									scrollY				: "320px",
					 									renderFirsColumn 	: null,
					 									drawCallback	: function ( settings ) { 
					 								        var table = $('#table_'+tab).DataTable();
					 								        $('#table_'+tab+' tbody').on( 'click', 'tr', function () {
					 								            if ( $(this).hasClass('selected') ) {
						 							               	$('#table_'+tab+' tbody').off( 'click', 'tr');
					 								   				doMore = function(addingRow){
					 								   				 	selectRow = table.row('.selected').data();
						 								   				if (typeof(editBox.addAttribute) == "function") {
						 								   					addingRow =  editBox.addAttribute(addingRow,selectRow);
						 												}
					 								   					return addingRow;
					 								   				}
					 								   				getAddButtonHandler(otable,otab,doMore)();
						 							                table.$('tr.selected').removeClass('selected');
					 								   				$('#floatMoreBox').dialog('close');
					 								            }
					 								            else {
						 								            table.$('tr.selected').removeClass('selected');
					 								                $(this).addClass('selected');
					 								            }
					 								        } );
					 								    }
					 							}
						};
					tableData = otable.data();
					field = typeof(editBox.filterField) != "undefined"?editBox.filterField:'CODE';
					var attributeData = $.grep(contractAttributes,function(el,i) {
							filters = $.grep(tableData,function(element,index) {
							  	return element[field]==el.CODE;
							});
						  	return filters.length<=0
						});
           	    	
					var properties = typeof(editBox.getEditTableColumns) == "function"?editBox.getEditTableColumns(tab):[{title:'CODE',data:'CODE',width:80},{title:'NAME',data:'NAME',width:205}];
					subData = {	dataSet			: attributeData,
								properties		: properties
					          };
					etbl = renderTable(tab,subData,options);
				};
		}
		else return getAddButtonHandler(otable,otab);
	};

	editBox['initSavingDetailData'] = function(editId,saveUrl) {
		params 		= actions.loadSaveParams(true);
		editedData 	= {};
		deleteData 	= {};
		$.each(editBox.fields, function( index, value ) {
			editedData[value] 	= actions.editedData[value];
			deleteData[value] 	= actions.deleteData[value];
   		 });

		var result = {
		 		id			: editId,
		 		editedData	: editedData,
		 		deleteData	: deleteData,
		 }
		if(typeof editBox.buildExtraSaveDetailData == "function") {
			extra = editBox.buildExtraSaveDetailData(editId,saveUrl);
			if(typeof extra == "object" && extra!=null) 
				jQuery.extend(result, extra);
		}
  		return result;
	};

</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.fields = ['{{$detailTableTab}}'];
	editBox.enableRefresh = false;

	editBox['getEditTableOption'] = function(tab){
			return {
			 			tableOption :	{
								autoWidth	: false,
								scrollX		: false,
								scrollY		: "200px",
						}
					};
		};
		
	editBox.editGroupSuccess = function(data,id){
		tab 	= '{{$detailTableTab}}';
		options = editBox.getEditTableOption(tab);
		subData = data[tab];
		etbl = renderTable(tab,subData,options,actions.createdFirstCellColumn);
		if(etbl!=null) actions.afterDataTable(etbl,tab);
	}


    editBox.editGroupSuccess = function(data,id){
    	actions.loadSuccess(data);
	}
	
	editBox['saveFloatDialogSucess'] = function(data,saveUrl){
		actions.saveSuccess(data);
		close = false;
		return close;
	}

</script>
@stop

@section('editBoxContentview')
@parent
	<table border='0' cellpadding='0' style='width:100%;height:100%'>
		<tr>
			<td valign='top'>
				<div id="table_{{$detailTableTab}}_containerdiv" style='height:100%;overflow:auto'>
					<table id="table_{{$detailTableTab}}" class="fixedtable nowrap display">
					@yield('editBoxfooter')
					</table>
				</div>
			</td>
		</tr>
	</table>
@stop


@section('floatMoreBoxContent')
	<table id="table_att_{{$attributeTableTab}}" class="fixedtable nowrap display"></table>
@stop