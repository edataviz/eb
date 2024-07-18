<?php
	if(!isset($currentSubmenu))
		$currentSubmenu ='/pd/storagedisplay';
	$tableTab		= "StorageDisplayChart";
	$tablePrepend	= false;
	$plotItems		= \App\Models\PlotViewConfig::all(); 
	
?>

@extends('fp.choke')

@section('graph_extra_view')
<div style="
 	overflow: auto;    
    height: 100%;
    width: 300px;
    display	:none;
    border: 1px solid #bbbbbb;
    /*max-height: 200px;*/
    background: #eeeeee;">
	<ul id="graphTank" class="ListStyleNone"></ul>
</div>
@stop

@section('action_extra')
	@include('partials.diagram_action')
	<script>
// 		editBox.loadUrl = "/storagedisplay/filter";
	 	editBox.buildTableProperties = function (constrain){
		 	var chartypes	= [	{ID	: "column", 	NAME	: "Column"},
								{ID	: "line", 		NAME	: "Line"},
			 	             	{ID	: "spline", 	NAME	: "Curved line"},
			 	             	{ID	: "area", 		NAME	: "Area"},
			 	             	{ID	: "areaspline", NAME	: "Curved Area"},
		 	             	 ];
	 	 	var first		= {};
	 	 	first.width		= 80;
	 	 	first.title		= "Plot name";
	 	 	first.data		= "ID";
	 		var properties 	= [
								first,
	 	 		  				{	'data' 		: 'PlotViewConfig',
	 	 		  					'title' 	: 'Plot name'  ,
	 	 		  					'width'		: 120,
	 	 		  					'INPUT_TYPE': 2,
	 	 		  					DATA_METHOD	: 1,
	 	 		  					columnDef	: {data	: plotItems},
	 	 		  				},
	 	 		  				{	'data' 		: 'FROM_DATE',
	 	 		  					'title' 	: 'From date'  ,
	 	 		  					'width'		: 100,
	 	 		  					'INPUT_TYPE': 3,
	 	 		  					DATA_METHOD	: 1
	 	 		  				},
	 	 		  				{	'data' 		: 'TO_DATE',
	 	 		  					'title' 	: 'To date'  ,
	 	 		  					'width'		: 100,
	 	 		  					'INPUT_TYPE': 3,
	 	 		  					DATA_METHOD	: 1
	 	 		  				},
	 	 		  				{	'data' 		: 'CHART_TYPE',
	 	 		  					'title' 	: 'Chart Type'  ,
	 	 		  					'width'		: 50,
	 	 		  					'INPUT_TYPE': 2,
	 	 		  					DATA_METHOD	: 1,
	 	 		  					columnDef	: {data	: chartypes},
	 	 		  				},	 	
	 	 		  				{	'data' 		: 'COLOR',
	 	 		  					'title' 	: 'Color'  ,
	 	 		  					'width'		: 30,
	 	 		  					'INPUT_TYPE': 'color',
	 	 		  					DATA_METHOD	: 1
	 	 		  				},
	 	 		  				{	'data' 		: 'GROUP',
	 	 		  					'title' 	: 'Group'  ,
	 	 		  					'width'		: 60,
	 	 		  					'INPUT_TYPE': 1,
	 	 		  					DATA_METHOD	: 1
	 	 		  				},
	 	 		  				{	'data' 		: 'NEGATIVE',
	 	 		  					'title' 	: 'Negative'  ,
	 	 		  					'width'		: 30,
	 	 		  					'INPUT_TYPE': 5,
	 	 		  					DATA_METHOD	: 1
	 	 		  				},
	 		  		];
	 		return properties;
		};

		editBox.buildTableColumnDefs = function (value,properties){
			var columnDefs		= [];
			$.each(properties, function( index, property ) {
				if(typeof property.columnDef == "object") {
					var uom = {
							targets	: index,
							data	: property.columnDef.data
						};
					if(property.data=="PlotViewConfig"){
						uom["render"] = function ( data, type, rowData ) {
							if(typeof rowData.viewName == "string") return rowData.viewName;
							var plotViewConfig	= parseFloat(rowData.PlotViewConfig);
							var plotName 	= 'view name';
							if(!isNaN(plotViewConfig)){
								var result = $.grep(plotItems, function(e){ 
					           	 	return e.ID == rowData.PlotViewConfig;
					            });
							    if (result.length > 0) plotName 	= result[0].NAME;
							}
							return plotName;
						};
					}
					columnDefs.push(uom);
				}
		   	});
		   	
			return columnDefs;
		}

		editBox.genMoreDiagramPostData	= function (constraintPostData){
// 			constraintPostData.date_mid	= $("#date_middle").val();		   	
// 			return constraintPostData;
		}
		editBox.getItemName = function (value){	
			return value.TITLE;
		}

		var oExtractDiagramTableData	= editBox.extractDiagramTableData;
		editBox.extractDiagramTableData = function (value,convertJson){
			var dataSet	= oExtractDiagramTableData(value,convertJson);
			if(typeof dataSet == 'object'){
				$.each(dataSet, function( index, rowData ) {
					if(typeof rowData.GROUP == "undefined" || rowData.GROUP == "")
						rowData.GROUP		= "MAIN";
				});
			}
			return dataSet;
		};
	</script>
@stop


@section('adaptData')
@parent
<script>
	actions.loadUrl = "/storagedisplay/load";
	actions.saveUrl = "/storagedisplay/save";
 	var plotItems 	= <?php echo json_encode($plotItems); ?>;

	actions['doMoreAddingRow'] = function(addingRow){
 		addingRow['FROM_DATE'] 	= moment.utc($("#date_begin").val(),configuration.time.DATE_FORMAT);
 		addingRow['TO_DATE'] 	= moment.utc($("#date_end").val(),configuration.time.DATE_FORMAT);
		return addingRow;
	}
	
	actions.getChartTitle = function (tab){
		return "Chart title";
	};

	editBox.fillCurrentDiagram = function (currentDiagram){
 		currentDiagram.TITLE		= $("#txtDiagramName").val();
		currentDiagram.FROM_DATE	= $("#date_begin").val();
		currentDiagram.TO_DATE		= $("#date_end").val();
// 		currentDiagram.CREATE_BY	= $("#txtDiagramName").val();
// 		currentDiagram.CREATE_DATE	= $("#txtDiagramName").val();
	}

	editBox.getDiagramTitle = function(getDiagramTitle){
		return currentDiagram!=null?currentDiagram.TITLE:"";
	}

	editBox.updateFilterView = function(currentDiagram){
		currentDiagram.FROM_DATE	= $("#date_begin").val();
		currentDiagram.TO_DATE		= $("#date_end").val();
	}
	 
	actions.getAddButtonHandler =  function (table,tab,doMore){
		return function () {
			var doMoreFunction = function(addingRow){
				if(typeof doMore == 'function') doMore(addingRow);
				addingRow.PlotViewConfig = $("#PlotViewConfig option:selected").val();
				addingRow.PlotViewConfig = addingRow.PlotViewConfig!=null&&addingRow.PlotViewConfig!=""?
											addingRow.PlotViewConfig:" ";
				addingRow.CHART_TYPE	 = "column";
				addingRow.OBJECTS		 = [];
				addingRow.viewName		 = null;
				return addingRow;
			}
			var func = actions.getDefaultAddButtonHandler(table,tab,doMoreFunction);
			func();
		}
	};
</script>
@stop


@section('content')
@include('datavisualization.storage_diagram')
<style>
	#filterFrequence {
		clear: none;
	}
	.PlotViewConfig{
		width: 100%; 
	}
	.TankModelGraph{
	    margin: 5px;
	    display: block;
	    float: left;
	    width: 110px;
	    height: 80px;
	    background: url(../img/tank.png);
	    background-size: 100% 100%;
	    padding-top: 0px;
        max-height: 200px;
	}
	.TankModelGraphText{
		position: relative;
	    color: white;
	    text-align: center;
	    font-size: 8pt;
	    word-wrap: break-word;
	    padding: 2px;
	    top: 50%;
	}
	#diagramTableAction{
		clear: both;
	}
</style>
<script>
	var orenderDependenceHtml = renderDependenceHtml;
	renderDependenceHtml = function(elementId,dependenceData) {
		if(elementId=="Tank"){
			var option = $('<li class="TankModelGraph" />');
			var text	= $('<p class="TankModelGraphText"></p>');
			var name = typeof(dependenceData.CODE) !== "undefined"?dependenceData.CODE:dependenceData.NAME;
			option.attr('name', name);
			option.attr('value', dependenceData.ID);
			text.text(dependenceData.NAME);
			option.append(text);
			return option;
		}
		else return orenderDependenceHtml(elementId,dependenceData);
	};
	
	$( document ).ready(function() {
	    console.log( "ready!" );
	    $("#select_container_Tank").hide();
	    $("#Tank option").each(function(){
	    	var li = renderDependenceHtml("Tank",{ID:this.value,	CODE:this.name,	NAME: this.text});
	    	$("#graphTank").append(li);
	    });
	    $("#Tank").attr("id","originalTank");
	    $("#graphTank").attr("id","Tank");
	});
	
</script>
@stop

@section("renderChart")
@parent
<script type="text/javascript">
	chartParameter.url = "/storagedisplay/loadchart";
</script>
@stop

@section('extraAdaptData')
@parent
<script>
	var opreOnchange	= filters.preOnchange;
	filters.preOnchange		= function(id, dependentIds,more){
					var partials 		= id.split("_");
					var prefix 			= partials.length>1?partials[0]+"_":"";
					var model 			= partials.length>1?partials[1]:id;
					switch(model){
						case "Storage":
							$('#Tank').html("");
							return;
						break;
					}
					if(typeof opreOnchange == "function" && $("#"+id).is(":visible")) opreOnchange(id, dependentIds,more);
				};
</script>
@stop

@section('endDdaptData')
@parent
<script>
	
	editBox.updateDiagramRowValue = function( index, row) {
		row.FROM_DATE		= moment.utc(row.FROM_DATE).format(configuration.time.DATE_FORMAT_UTC);
		row.TO_DATE			= moment.utc(row.TO_DATE).format(configuration.time.DATE_FORMAT_UTC);
	};
	
	editBox.updateCurrentDiagramData = function( rows,convertJson) {
	};

	editBox.initNewDiagram = function(){
		currentDiagram = {	
			ID			: 'NEW_RECORD_DT_RowId_'+(index++),
			CONFIG		: '[]',
			TITLE		: ''
		};
	}

	editBox.editObjectMoreHandle = function (table,rowData,td,tab) {
		if(typeof rowData.OBJECTS == "object" && rowData.OBJECTS.length >0 &&
				 (typeof rowData.PlotViewConfig == "undefined" || typeof rowData.ObjectPlotViewConfigId == "undefined" || rowData.PlotViewConfig == rowData.ObjectPlotViewConfigId)){
					editBox.renderEditFilter(rowData.OBJECTS);
		}
		else{
			$("#objectList").html("Loading...");
			$.ajax({
				url			: "/viewconfig/"+rowData.PlotViewConfig,
				type		: "post",
				data		: {},
				success		: function(data){
					rowData.ObjectPlotViewConfigId	= data.PlotViewConfig;
					var originObjects				= {};
					jQuery.extend(originObjects, rowData.OBJECTS);
					rowData.originObjects			= originObjects;
					rowData.OBJECTS					= data.objects;
					editBox.renderEditFilter(rowData.OBJECTS);
					console.log ( "viewconfig get success "+rowData.PlotViewConfig);
				},
				error		: function(data) {
					console.log ( "viewconfig get error "+rowData.PlotViewConfig);
					$("#objectList").html("load view config error !");
				}
			});
		}

		var plotViewConfig	= parseFloat(rowData.PlotViewConfig);
		var plotName 	= 'view name';
		if(!isNaN(plotViewConfig)){
			var result = $.grep(plotItems, function(e){ 
           	 	return e.ID == rowData.PlotViewConfig;
            });
		    if (result.length > 0) plotName 	= result[0].NAME;
		}
		
		$("#viewName").val(typeof rowData.viewName == "string" ? rowData.viewName : plotName);
		$("#viewNameDiv").show();
		$("#isAdditionalLabel").css('display','block');
		$("#isAdditional").prop('checked', (typeof rowData.isAdditional != "undefined" && 
				( rowData.isAdditional == "true" || rowData.isAdditional == true)));
	};

	editBox.getDiagramConfig = function (convertJson,rows){
		$.each(rows,function( index, row) {
			delete row.originObjects;
		});
		return convertJson?JSON.stringify(rows):rows;
	}

	editBox.updateMoreObject = function (rowData){
		rowData.viewName = $("#viewName").val();
		$("#"+rowData['DT_RowId']).find( ".PlotViewConfig:first" ).text(rowData.viewName);
		rowData.isAdditional = $("#isAdditional").prop('checked');
	}
</script>
@stop


