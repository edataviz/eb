<?php
 	$tableTab		= isset($tableTab)?$tableTab:"ConstraintDiagram";
 ?>
 
 

@section('graph_object_view')
     <div id="container_{{$tableTab}}" class="date_filter" style="overflow-x: hidden;float:left;margin-right:10px;height: 100%;">
		<table id="table_{{$tableTab}}"
			class="fixedtable nowrap display">
		</table>
	</div>
@stop


@section('adaptData')
@parent
<script>
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};
	
	actions.enableUpdateView = function(tab,postData){
		return false;
	};
	
	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: true,
								scrollY			: false,
								"info"			: false,
								},
				invisible:[],
            resetTableHtml : function(tabName) { return false},
        };
		
	}

	actions.tableIsDragable	= function(tab){
		return true;
	}

	actions.getAddingRowIndex	= function () {
		return Math.random().toString(36).substring(5);
	};

	if(typeof addingOptions == "object") addingOptions.keepColumns = ['GROUP','COLOR'];

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var viewName = typeof rowData.viewName == "string"?rowData.viewName:"objects";
		viewName	 = "objects";
		html += '<a id="item_edit_'+id+'" class="actionLink clickable">'+viewName+'</a>';
		return html;
	};

	var orenderEditFilter =  editBox.renderEditFilter;
	editBox.renderEditFilter	= function(objects){
	    orenderEditFilter();
	    var list = editBox.renderObjectsList(objects);
		$("#objectList").html("");
	    $("#objectList").addClass("product_filter");
	    $("#editBoxContentview").css("float","left");
	    $("#editBoxContentview").css("width","54%");
	    list.appendTo($("#objectList"));
	}
	
	var addMoreHandle	= function ( table,rowData,td,tab) {
		var id = rowData['DT_RowId'];
		editBox.addMoreHandle(table,rowData,td,tab,table.$('#item_edit_'+id));
	};
	actions['addMoreHandle']  = addMoreHandle;

	var obuildFilterText = editBox.buildFilterText;
	editBox.buildFilterText = function(){
		 	var resultText 		= obuildFilterText();
			var	operationVal	= $("#txtConstant").val();
			var pvalue 			= parseFloat(operationVal);
			pvalue 				= isNaN(pvalue)? 0:pvalue;
			if(pvalue!=0){
				var	operation	= $("#cboOperation").val();
				var extraText	= operation!=null&&operationVal!=""&&operation!=""?""+operation+operationVal:"";
				resultText		+= extraText;
			}
			return resultText;
		}

	var currentDiagram = null;
	editBox.initNewDiagram = function(){
		currentDiagram = {	
			ID			: 'NEW_RECORD_DT_RowId_'+(index++),
			CONFIG		: '[]',
			NAME		: '',
			YCAPTION	: 'Oil Limit',
			isAdding	: true,
		};
	}

		
	var oAfterDataTable	= actions.afterDataTable;
	actions.afterDataTable = function (table,tab){
		if(typeof oAfterDataTable == 'function') oAfterDataTable(table,tab);
		var diagramTitle			= $('<input type="text" style="width:300px;margin-bottom: 3px;" id="txtDiagramName" name="txtDiagramName" size="15" value="">');
		var titleText 				= typeof editBox.getDiagramTitle =='function'? editBox.getDiagramTitle(currentDiagram):'';
		diagramTitle.val(titleText);
		var chartTitle	= "Chart title";
		if(typeof actions.getChartTitle == 'function') chartTitle	= actions.getChartTitle(tab);
		var contraintDiagramName 	= $('<div style="padding: 3px 0 0 0;"><b>'+chartTitle+'</b></div>');
		diagramTitle.appendTo(contraintDiagramName);
		contraintDiagramName.css("float","right");
		contraintDiagramName.appendTo($("#toolbar_"+tab));
		$("#toolbar_"+tab).css("width","100%");
		var ycaptionButton	= $(".dataTables_scrollHeadInner table thead th.YCAPTION");
		ycaptionButton.addClass('clickable');
		ycaptionButton.editable({
		    type			: 'text',
		    title			: 'Enter caption',	
		    showbuttons		: false,
		});
		if(typeof editBox.updateFilterView == "function") editBox.updateFilterView(currentDiagram);
	};

	$(document).ready(editBox.newConstrain);
	
</script>
@stop



@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/choke/filter";

	editBox.size = {
						height 	: 490,
						width 	: 950,
					};

	editBox.renderContrainTable = function (value,convertJson){
		var properties	= editBox.buildTableProperties(value);
		convertJson		= convertJson && typeof value.CONFIG == "string";
		var dataSet 	= editBox.extractDiagramTableData(value,convertJson);
		var tableData = {
				dataSet		: dataSet,
				properties	: properties,
				postData	: {'{{config("constants.tabTable")}}'	: '{{$tableTab}}'},
// 				columnDefs	: [],
				};
		if(typeof editBox.buildTableColumnDefs == 'function') tableData.uoms = editBox.buildTableColumnDefs(value,properties);
		currentDiagram		= value;
		actions.loadSuccess(tableData);
	}

	editBox.extractDiagramTableData = function (value,convertJson){
		return convertJson?JSON.parse(value.CONFIG):value.CONFIG;
	};
		
	editBox.loadConsList = function (){
 		success = function(data){
	    	$("#contrainList").html("");
	 		var dataSet = data.dataSet;
	 		var ul = $("<ul class='ListStyleNone'></ul>");
			$.each(dataSet, function( index, value) {
		    	var li 				= $("<li class='x_item'></li>");
				var span 			= $("<span></span>");
				var del				= $('<img valign="middle" class="xclose" src="/img/x.png">');
				span.appendTo(li);
				del.appendTo(li);

				del.click(function() {
					if(!confirm("Are you sure you want to delete this item?")) return;
					showWaiting();
					$.ajax({
						url			: actions.saveUrl,
						type		: "post",
						data		: {
											deleteData	: {
															{{$tableTab}}	: [value.ID]
															}
									},
						success		: function(data){
							hideWaiting();
							li.remove();
							console.log ( "delConstrain success ");
						},
						error		: function(data) {
							hideWaiting();
							console.log ( "delConstrain error "/*+JSON.stringify(data)*/);
							alert("delete Constrain error ");
						}
					});
				});
				
				span.data(value);
				span.click(function() {
					editBox.closeEditWindow(true);
					editBox.renderContrainTable(value,true);
					editBox.loadContrainValue();
				});
				span.addClass("clickable");
				var itemText;
				if(typeof editBox.getItemName == "function") itemText = editBox.getItemName(value);
				else  itemText	= value.NAME;
				span.text(itemText);
				li.appendTo(ul);
				
			});
			ul.appendTo($("#contrainList"));
		}
    	option = {
			    	title 		: "Plot Item list",
			 		postData 	: {tabTable : "{{$tableTab}}"},
			 		url 		: actions.loadUrl,
			 		viewId 		: 'contrainList',
			 		size		: {
										height 	: 300,
										width 	: 500,
									},
    	    	};
		$("#objectList").css('display','none');
		$("#viewNameDiv").css('display','none');
		$("#isAdditionalLabel").css('display','none');
		editBox.showDialog(option,success);
	    $("button[class=saveAction]").remove();
	}

	editBox.updateDiagramRowValue = function( index, row) {
		row.FACTOR			= row.FACTOR.replace(',','.');
		row.DT_RowId		= Math.random().toString(36).substring(10);
	};
	
	editBox.updateCurrentDiagramData = function( rows,convertJson) {
		currentDiagram.YCAPTION	= $(".dataTables_scrollHeadInner table thead th.YCAPTION:first").text();
	};

	editBox.updateCurrentContrain = function (convertJson,buildParamFunction){
		var table				= $('#table_{{$tableTab}}').DataTable();
		var rows				= table.data().toArray();
		$.each(rows,editBox.updateDiagramRowValue);
		currentDiagram.CONFIG	= typeof buildParamFunction == "function"? 
									buildParamFunction(convertJson,rows):
									(convertJson?JSON.stringify(rows):rows);
		if(typeof editBox.fillCurrentDiagram == "function") editBox.fillCurrentDiagram(currentDiagram);
		editBox.updateCurrentDiagramData(rows,convertJson);
	}
						
	editBox.saveConstrain = function (){
		editBox.updateCurrentContrain(true,editBox.getDiagramConfig);
		if(currentDiagram.TITLE==""){
			var s = prompt("Please enter diagram name", "diagram name");
			if (s == null || s== "") return;
			else currentDiagram.TITLE = s;
		}
		var saveData	= {
							editedData	: {
											{{$tableTab}}	: [currentDiagram]
										}
						};
		showWaiting();
		$.ajax({
			url			: actions.saveUrl,
			type		: "post",
			data		: saveData,
			success		: function(data){
				hideWaiting();
				console.log ( "saveConstrain success ");
				editBox.renderContrainTable(data.updatedData.{{$tableTab}}[0],true);
// 				alert("save successfully ");
			},
			error		: function(data) {
				hideWaiting();
				console.log ( "saveConstrain error "/*+JSON.stringify(data)*/);
				alert("saveConstrain error ");
			}
		});
	}	

	editBox.saveAsConstrain = function (){
		if(typeof currentDiagram == "object" && currentDiagram !=null){
			currentDiagram.isAdding	= true;
			editBox.saveConstrain();
		}
	}
	
	editBox.newConstrain = function (){
		editBox.initNewDiagram();
		editBox.renderContrainTable(currentDiagram,true);
	}

	editBox.genDiagramOfTable = function (){
		editBox.loadContrainValue();
	}

	editBox.loadContrainValue	= function (){
		editBox.updateCurrentContrain(false);
		var constraintPostData 			= {	
											date_begin	: $("#date_begin").val(),
											date_end	: $("#date_end").val(),
 											constraints	: currentDiagram,
// 											constraintId	: 9,
											};
		if(typeof editBox.genMoreDiagramPostData == 'function') editBox.genMoreDiagramPostData(constraintPostData);
		editBox.requestGenDiagram(constraintPostData,false,true,function(data){
			editBox.renderContrainTable(data.constraints,false);
		});
	}
	
 	editBox.buildTableProperties = function (constrain){
 	 	var first		= {};
 	 	first.width		= 80;
 	 	first.title		= "";
 	 	first.data		= "ID";
 		var properties 	= [
							first,
 	 		  				{	'data' 		: 'NAME',
 	 		  					'title' 	: 'Summary Items'  ,
 	 		  					'width'		: 90,
 	 		  					'INPUT_TYPE': 1,
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'GROUP',
 	 		  					'title' 	: 'Group'  ,
 	 		  					'width'		: 40,
 	 		  					'INPUT_TYPE': 1,
 	 		  					DATA_METHOD	: 1
 	 		  				},	 	
 	 		  				{	'data' 		: 'COLOR',
 	 		  					'title' 	: 'Color'  ,
 	 		  					'width'		: 30,
 	 		  					'INPUT_TYPE': 'color',
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'VALUE',
 	 		  					'title' 	: 'Value'  ,
 	 		  					'width'		: 40,
 	 		  					'INPUT_TYPE': 2,
 	 		  				},
 	 		  				{	'data' 		: 'FACTOR',
 	 		  					'title' 	: 'Factor'  ,
 	 		  					'width'		: 30,
 	 		  					'INPUT_TYPE': 2,
 	 		  					DATA_METHOD	: 1
 	 		  				},
 	 		  				{	'data' 		: 'YCAPTION',
 	 		  					'title' 	: constrain.YCAPTION,
 	 		  					'width'		: 80,
 	 		  					'INPUT_TYPE': 2
 	 		  				},
 		  		];
 		return properties;
	};

 	editBox.renderObjectsList = function (objects){
 		var tooltipContent = $("<div>");
   		var ul = $("<ul class='ListStyleNone'></ul>");
   		ul.sortable();
   		if(typeof objects == "object"){
		  	$.each(objects, function( index, object) {
		  		editBox.add2ObjectList(object,ul);
			});
   		}
		ul.appendTo(tooltipContent);
		return 	tooltipContent;
	};

	var focusOnCurrentSpan = function (span){
		if(typeof currentSpan != 'undefined') $(currentSpan).css("color","");
		span.css("color","#830253");
		currentSpan		=	span;
	}
	
	editBox.add2ObjectList = function (object,ul){
		if(typeof object.text == 'undefined' || object.text =='')
			object.text		= editBox.renderOutputText(object);
		var text			= object.text;
	    var li 				= $("<li class='x_item'></li>");
		var span 			= $("<span></span>");
		var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');
		span.text(text);
		span.click(function() {
			if(currentSpan==span) return;
			focusOnCurrentSpan(span);
			editBox.editRow(span,span);
		});
		span.addClass("clickable");
		span.data(object);
		span.appendTo(li);
		del.appendTo(li);
		li.appendTo(ul);
		return span;
	};

	editBox.addObject 	= function (close){
		var object 		= editBox.buildFilterData();
		var ul 			= $("#objectList ul:first");
		var text 		= editBox.buildFilterText();
		object.text		= text;
		var span		= editBox.add2ObjectList(object,ul);
		focusOnCurrentSpan(span);
	}

	</script>
	
	<style>
	#table_{{$tableTab}} tbody th, #table_{{$tableTab}} tbody td {
		padding: 2px;
	}
	</style>
@stop
 
<table id="diagramTableAction" class="floatLeft" style="">
	<tr>
		<td>@yield("graph_object_view")</td>
		<td>@yield("graph_extra_view")</td>
		<td rowspan="2" valign="top" align="center" width="180px">
			<button class="myButton" id="genChartBtn" 
				style="margin-bottom: 3px; width: 160px; height: 35px">Generate chart</button>
			<button class="myButton" id="newChartBtn" 
				style="display:; margin-bottom: 2px; width: 78px; height: 30px">New</button>
			<button class="myButton" id="loadChartBtn" 
				style="display:; margin-bottom: 2px; width: 78px; height: 30px">Load</button>
			<button class="myButton" id="saveChartBtn" 
				style="display:; margin-bottom: 0px; width: 78px; height: 30px">Save</button>
			<button class="myButton" id="saveAssChartBtn" 
				style="display:; margin-bottom: 0px; width: 78px; height: 30px">Save
				as</button>
		</td>
	</tr>
</table>

@section("handleAction")
<script>
	$("#genChartBtn").click(editBox.genDiagramOfTable);
	$("#newChartBtn").click(editBox.newConstrain);
	$("#loadChartBtn").click(editBox.loadConsList);
	$("#saveChartBtn").click(editBox.saveConstrain);
	$("#saveAssChartBtn").click(editBox.saveAsConstrain);
</script>
@stop

@yield("handleAction")
