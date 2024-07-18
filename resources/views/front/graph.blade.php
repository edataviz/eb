<?php
$isAction 			= true;
$currentSubmenu 	= isset($currentSubmenu)?$currentSubmenu:'/graph';
$functionName		= isset($functionName)?$functionName:"graph";
$graphViewId 		= 'inputObjectName';
$useFeatures		= [
						['name'	=>	"filter_modify",
						"data"	=>	["isFilterModify"	=> true]],
						['name'	=>	"object_input",
						"data"	=>	["floatViewId"	=> $graphViewId]],
					];
$subMenus = [
		array('title' => 'NETWORK MODELS', 'link' => 'diagram'),
		array('title' => 'DATA VIEWS', 'link' => 'dataview'),
		array('title' => 'REPORT', 'link' => 'workreport'),
		array('title' => 'ADVANCED GRAPH', 'link' => 'graph'),
		array('title' => 'TASK MANAGER', 'link' => 'approvedata'),
		array('title' => 'WORKFLOW', 'link' => 'workflow')
];
if (!isset($floatContents)) $floatContents = ['editBoxContentview' ,$graphViewId];
else $floatContents[] = $graphViewId;

$timebaseArray			= [	"5 minute","10 minute","20 minute","30 minute","1 hour","2 hour","4 hour","8 hour","16 hour","1 day","2 day","4 day",
							"1 week","2 week","1 month","2 month","3 month","4 month","6 month","8 month","10 month","1 year","2 year"];
$sampleIntervalArray	= [	"5 second","10 second","20 second","30 second","1 minute","2 minute","5 minute","10 minute","30 minute",
							"1 hour","3 hour","6 hour","12 hour","24 hour"];


$tCollection			= \Helper::getIntervals($timebaseArray);
$sCollection			= \Helper::getIntervals($sampleIntervalArray);
$timeBase		= array("name"			=> "Timebase",
						"id"			=> "Timebase",
						"collection"	=> $tCollection,
						"enableTitle"	=> false);
		
$sampleInterval	= array("name"			=> "SampleInterval",
						"id"			=> "SampleInterval",
						"collection"	=> $sCollection,
						"enableTitle"	=> false);
$lastDate 	 	= array('id'=>'LastDate','name'=>'To Date','value' => \Carbon\Carbon::now());
?>

@section('frequenceFilterGroupMore')
<table border="0" class="clearBoth" style="width: 100%;">
<!--
	<tr style="display:none">
		<td>
			<b>Y axis: Position </b>
			<select id="cboYPos" style="width: auto">
				<option value="L">Left</option>
				<option value="R">Right</option>
			</select>
		</td>
		<td>
			<b> Text </b>
			<input name="txt_y_unit" id="txt_y_unit" value="">
		</td>
		<td align="right">
			<button class="myButton"onclick="_graph.addObject()" style="width: 61px">Add</button>
		</td>
	</tr>
-->
	<tr>
		<td colspan="2" align="left">
		<a href="javascript: _graph.addRealtimeTag()">Add realtime tag</a>
		|  <a href="javascript: _graph.configRealtimeTag()">Settings</a>
		</td>
		<td>
		</td>
		<td align="right" colspan="1">
<!--			<button class="myButton"onclick="_graph.addObject()" style="width: 61px">Add</button> -->
		</td>
	</tr>
	<tr style="display:">
		<td>
			<b>Y axis: Position </b>
			<select id="cboYPos" style="width: auto">
				<option value="L">Left</option>
				<option value="R">Right</option>
			</select>
		</td>
		<td>
			<b> Text </b>
			<input name="txt_y_unit" id="txt_y_unit" value="">
		</td>
		<td align="right">
			<button class="myButton"onclick="_graph.addObject()" style="width: 61px">Add</button>
		</td>
	</tr>
	<tr>
		<td><b>Chart title</b></td>
		<span style="display:none"><b>Min</b> <input name="txt_min"
			id="txt_min" value="" style="width: 100px; margin-right: 10px"><b>Max</b>
			<input name="txt_max" id="txt_max" value="" style="width: 100px">
		</span>
	</tr>
	<tr>
		<td colspan="3"><input type="text" id="chartTitle"
			name="chartTitle"
			style="width: 98%; padding: 2px;"></input></td>
	</tr>
</table>
@stop

@section('graph_object_view')
<div id="tdObjectContainer" valign="top"
	style="min-width:420px;
	 	max-width: 460px;
		overflow:hidden;
		box-sizing: border-box;
		 overflow: auto;
	  	height: 113px;
	  	padding: 5px;
	    border: 1px solid #bbbbbb;
	    background: #eeeeee">
     <ul id="chartObjectContainer" class="ListStyleNone">
	</ul>
</div>

<div id="realtimeTagSetting" style="display:none">
	<div class="clearBoth" style="padding:5px" >
		<b class="floatLeft" style="padding:5px">Timebase</b>{{\Helper::filter($timeBase)}}
	</div>
	<br/>
	<div class="clearBoth" style="padding:5px" >
		<b class="floatLeft" style="padding:5px">Sample Interval</b>{{\Helper::filter($sampleInterval)}}
	</div>
	<div class="clearBoth" style="padding:5px" >
        <div><b>To Date</b>
        <input id='LastDate' style='width: 180px;' type='hidden'
               name='date_begin' size='10'>
	</div>
</div>
@stop

@section("handleAction")
<script> 
var _graph = {

		loadObjType : 1,

		currentChartID : 0,

		lastObjectType : "",
		
		loadObjecType : function(){
			var cbo = '';
			cbo += ' <div class="filter">';
			cbo += ' 	<div><b> Object type </b></div>';
			cbo += ' 	<select id = "cboObjectType" onchange="_graph.cboObjectTypeOnChange()">';
			cbo += ' 		<option selected value="FLOW/FLOW/FL_DATA/Flow">Flow</option>';
			cbo += ' 		<option value="ENERGY_UNIT/ENERGY_UNIT/EU_DATA/Energy Unit">Energy Unit</option>';
			cbo += ' 		<option value="TANK/TANK/TANK/Tank">Tank</option>';
			cbo += ' 		<option value="STORAGE/STORAGE/STORAGE/Storage">Storage</option>';
			cbo += ' 		<option value="ENERGY_UNIT/EU_TEST/EU_TEST/Energy Unit">Well Test</option>';
			cbo += ' 		<option value="LO_AREA/LO_AREA//Area">Area</option>';
			cbo += ' 	</select>';
			cbo += ' </div>';

			return cbo;
		},
		addRealtimeTag : function(){
			$('#listCharts').html("Loading...")
			$( "#listCharts" ).dialog({
				height: 400,
				width: 600,
				modal: true,
				buttons	: {
					"Apply": function applySurveillance(){
						var tagInputs	= $('#listCharts input').each(function(index, element) {
							if($(element).is(":checked"))_graph.addObject($(element).val());
						});

						var other_tag=$("#otherTagsInput").val().trim();
						if(other_tag!=""){
							var splits = other_tag.split(",");
							$.each(splits, function(key, split) {
								_graph.addObject(split);
						    });
						}
						$("#listCharts").dialog("close");
					},
					"Cancel": function(){
						$("#listCharts").dialog("close");
					}
				},
				title: "Select tags",
			});

			/* $('#listCharts').html('<table border="0" cellpadding="3" id="table_IntTagMapping" class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;"></table>')
			actions.doLoad(true);	 */		
			sendAjaxNotMessage('/tags/all', {}, function(data){
// 				console.log(data);
				$('#listCharts').html("");
				if(data.length > 0){
					var tagsList = $("<div>").attr({ "class" : "TagSelection"});
					var input;
					var lb;
					var itable = $('<table></table>').addClass('fixedtable nowrap display dataTable').css("width","90%");
                    itable.append($('<thead>').append($('<tr>').append($('<td>')).append($('<td>')).append($('<td>'))));//.append(input)).append($('<td>').append(lb))
                    $.each(data, function( dindex, dvalue ) {
						input = $('<input/>').attr({ type: 'checkbox',value :dvalue.TAG_ID})
						.css("width","18px")
						.css("height","18px")
						.css("margin-top","0");
						lb = $('<span class="MidleAlignInput" />').html(dvalue.TAG_ID);
						itable.append($('<tr>').append($('<td>').append(input)).append($('<td>').append(lb))
								.append($('<td>').append($('<span class="MidleAlignInput" />').html(dvalue.OBJECT_NAME))));
					});
					itable.appendTo(tagsList);
					tagsList.appendTo($("#listCharts" ));
				}
				else 
					$('#listCharts').html("Empty tag mapping");
				var cdiv = $("<div>").attr({ "class" : "OtherTags"}).text("Other tags ");
				$('<input/>').attr({ type: 'text', id : "otherTagsInput"}).css("width","80%").appendTo(cdiv);
				cdiv.appendTo($( "#listCharts" ));
				if (itable){
                    $('div[id^=DataTables_Table_][id$=_filter]').remove();
                    itable.DataTable({
                        "paging":   false,
                        "ordering": false,
                        "info":     false,
                        columnDefs: [
                            { width: 20, targets: 0 }
                        ],
                    } );
                    $('div[id^=DataTables_Table_][id$=_filter]')
						.insertAfter($("#ui-id-1"))
						.css("position","absolute").css("right","30px")
                        .css("top","0")
						.parent().css("padding","10px");
                    var textInput = $('div[id^=DataTables_Table_][id$=_filter] input');
                    textInput.css('margin-left','15px').focus();
                    $('div[id^=DataTables_Table_][id$=_filter] label').contents().first().replaceWith("Search");
                    $('body').on('click', '.ui-dialog-titlebar', function() {
                        $('div[id^=DataTables_Table_][id$=_filter] input').focus();
                    });

                    var clearBtn = $('<span id="clear_value" style="position: absolute; top: 2px; right: 6px; cursor: pointer; color: blue; font-weight: bold;" title="Clear">×</span>');
                    clearBtn.css('display', "none");
                    clearBtn.insertAfter(textInput);
                    textInput.keyup( function() {
                        clearBtn.css('display',(this.value.length) ? "block" : "none");
                    });
                    clearBtn.click(function() {
                        clearBtn.css('display', "none");
                        textInput.val("");
                        textInput.keyup();
                    });
                }
			});
		},
		configRealtimeTag : function(){
			$( "#realtimeTagSetting" ).css("display","block");
			$( "#realtimeTagSetting" ).dialog({
				height: 400,
				width: 600,
				modal: true,
				buttons	: {
					"Apply": function applySurveillance(){
						$("#realtimeTagSetting").dialog("close");
					},
					"Cancel": function(){
						$("#realtimeTagSetting").dialog("close");
					}
				},
				title: "Realtime Chart Settings",
			});
		},
		loadObjects : function(){
			var objectType = $("#cboObjectType").val();
			if(objectType == _graph.lastObjectType) return;
			var ss = objectType.split("/");
			$("#txtObjectName").html(ss[ss.length-1]);

			param = {
				'date_begin' : $('#date_begin').val(),
				'date_end' : $('#date_end').val(),
				'object_type' : objectType,
				'facility_id' : $("#Facility").val(),
				'product_type' : $('#Product').val()
			};
			
			$("#cboObjectName").prop("disabled", true); 
			sendAjaxNotMessage('/loadVizObjects', param, function(data){
				$('#txtObjectName').val($("#cboObjectType ").text());
				adminControl.reloadCbo('cboObjectName',data);
				_graph.loadCbo('cboObjectNameTable',data.tab);

				_graph.lastObjectType = objectType;
				if($("#cboObjectType").val().indexOf("ENERGY_UNIT") > -1)
				{
					_graph.loadEUPhase();
				}
			});
		},
		setValueDefault : function(){
			$('#ProductionUnit').val($('#h_production_unit_id').val());
			$('#Area').val($('#h_area_id').val());
			$('#Facility').val($('#h_facility_id').val());
			$('#date_begin').val($('#h_date_begin').val());
			$('#end_date').val($('#h_date_end').val());
		},
		loadEUPhase : function(){
			param = {
				'eu_id' : $('#cboObjectName').val(),
			};
			
			$("#cboEUFlowPhase").prop("disabled", true); 
			sendAjaxNotMessage('/loadEUPhase', param, function(data){
				adminControl.reloadCbo('cboEUFlowPhase',data);
			});
		},
		cboObjectTypeOnChange : function()
		{
			if($("#cboObjectType").val().indexOf("EU_TEST") > -1)
			{
				$(".eutest_table").show();
				$(".object_table").hide();
				$(".phase_type").show();
			}
			else
			{
				$(".eutest_table").hide();
				$(".object_table").show();
			}
			if($("#cboObjectType").val().indexOf("ENERGY_UNIT/") > -1 && $("#cboObjectType").val().indexOf("EU_TEST/") < 0){
				$(".phase_type").show();
			}else{
				$(".phase_type").hide();
			}
			
			/* $("#cboObjectNameTable").val($("#cboObjectNameTable").find('option:visible:first').attr("value"));
			$("#cboObjectNameProps").val($("#cboObjectNameProps").find('option:visible:first').attr("value")); */
			
			_graph.loadObjects();
		},
		ObjectNameTableChange : function(){
			if($("#cboObjectNameTable").val()=="DATA_ALLOC" && $("#cboObjectType").val().indexOf("ENERGY_UNIT/") > -1)
				$(".alloc_type").show();
			else 
				$(".alloc_type").hide();
			if($("#cboObjectNameTable").val().endsWith("_PLAN"))
				$(".plan_type").show();
			else 
				$(".plan_type").hide();
			if($("#cboObjectNameTable").val().endsWith("_FORECAST"))
				$(".forecast_type").show();
			else 
				$(".forecast_type").hide();

			param = {
				'table' : $("#cboObjectNameTable").val()
			};
			
			sendAjaxNotMessage('/getProperty', param, function(data){
				_graph.loadCbo('cboObjectNameProps', data);
			});
		},
		addObject : function(tag){
// 			if($("#ObjectName").val()>0){
				var dataStore		= {	
						LoProductionUnit	:	$("#LoProductionUnit").val(),
						LoArea				:	$("#LoArea").val(),
						Facility			:	$("#Facility").val(),
						CodeProductType		:	$("#CodeProductType").val(),
						IntObjectType		:	tag?"TAG":$("#IntObjectType").val(),
						ObjectName			:	tag?tag:$("#ObjectName").val(),
						ObjectDataSource	:	$("#ObjectDataSource").val(),
						GraphObjectTypeProperty	:	$("#GraphObjectTypeProperty").val(),
						CodeFlowPhase		:	$("#CodeFlowPhase").val(),
						CodeEventType		:	$("#CodeEventType").val(),
						CodeAllocType		:	$("#CodeAllocType").val(),
						CodePlanType		:	$("#CodePlanType").val(),
						CodeForecastType	:	$("#CodeForecastType").val(),
						cboYPos				:	$("#cboYPos").val(),
						txt_y_unit			:	$("#txt_y_unit").val(),
						graph_cummulative	:	0,
					};
				var x =  editBox.getObjectValue(dataStore);
				if($("span[object_value='"+x+"']").length==0){
					var color="transparent";
					var texts = {};
					if(tag)
						texts = tag;
					else {
						var selects = $('#ebFilters_graph ').find('.filter:visible select');
						selects.each(function(index, element) {
							texts[element.name]		= $("#"+element.id+" option:selected").text();
						});
					}
					editBox.addObjectItem(color,dataStore,texts,x);
				}
				else
				{
					$("span[object_value='"+x+"']").effect("highlight", {}, 1000);
				}
// 			}
		},
		editColumn		:  function(element){
		},
		buildChartUrl	:  function(){
			document.getElementById("frameChart").contentWindow.document.write("<font family='Open Sans'>Generating chart...</font>");
			var title = encodeURIComponent($("#chartTitle").val());
			if(title == "") title = null;
			
			var minvalue = $("#txt_min").val();
			if(minvalue == "") minvalue = null;
			
			var maxvalue = $("#txt_max").val();
			if(maxvalue == "") maxvalue = null;
			
			var date_begin = $("#date_begin").val();
			var date_end = $("#date_end").val();
			var input = encodeURIComponent(_graph.getChartConfig(false));
			var iurl = "/loadchart?title="+title+
									"&minvalue="+minvalue+
                					"&facility="+$("#Facility").val()+
									"&maxvalue="+maxvalue+
									"&date_begin="+date_begin+
									"&date_end="+date_end+
									"&input="+input;
			return iurl;
		},
		draw : function()
		{
			if($(".x_item").length<=0) {
				alert("Please add object");
				return;
			}
			if(graphAssitance.isInputObjectsValidated(_graph.genChart,true)) _graph.genChart();
		},
		genChart : function(){
			$("html, body").animate({ scrollTop: $(document).height() }, 1000);
			var iurl	= _graph.buildChartUrl();
			if(typeof iurl == "string") $("#frameChart").attr("src",iurl);
		},
		newChart : function()
		{
			if($("#chartObjectContainer").children().length>0)
			{
				if(!confirm("Current chart will be clear. Do you want to continue?")) return;
			}
			_graph.currentChartID = 0;
			$("#chartObjectContainer").empty();
			$("#chartTitle").val("");
		},
		getChartConfig : function(isSave)
		{
			var s="";
			$(".x_item").each(function(){
				var x 				= $(this).attr("object_value");
				var objectNameText 	= $(this).children("span").text();
				if(isSave){
					var dataStore 	= $(this).find( "span:first" ).data();
					if(dataStore!==undefined && dataStore.ObjectNameVariable==true){
						dataStore.ObjectName 	= 0;
						dataStore.text		 	= graphAssitance.getObjectItemName(dataStore.text);
						objectNameText			= dataStore.text;
						x = editBox.getObjectValue(dataStore);
					}
				}
				s += (s==""?"":",")+x+":"+$(this).children("select").val()+":"+objectNameText+":#"+$(this).children("input").val();
		    });
		    var extensionConfig	=	{
				    				Timebase		: $("#Timebase").val(),
		    						SampleInterval	: $("#SampleInterval").val(),
		    						LastDate		: $("#LastDate").val()
	    						};
			if(extensionConfig.SampleInterval==null)extensionConfig.SampleInterval = extensionConfig.Timebase/{{$defaultNumber}};
			s += "\r\n"+JSON.stringify(extensionConfig);
			return s;
		},
		loadCharts : function()
		{
			$('#listCharts').html("Loading...")
			$( "#listCharts" ).dialog({
				height: 400,
				width: 600,
				modal: true,
				title: "Charts list",
			});
			
			param = {
			};
			
			sendAjaxNotMessage('/listCharts', param, function(data){
				_graph.showListChart(data);
			});
		},
		showListChart : function(_data){
			var data 	= _data.adv_chart;
			var str 	= "";
			$('#listCharts').html(str);
			/* for(var i =0; i < data.length; i++){
				str += "<span class='chart_info' id='chart_"+data[i]['ID']+"' min_value='"+data[i]['MIN_VALUE']+"' max_value='"+data[i]['MAX_VALUE']+"' chart_config='"+data[i]['CONFIG']+"' style='display:block;line-height:20px;margin:2px;'><a href='javascript:_graph.openChart("+data[i]['ID']+")'>"+data[i]['TITLE']+"</a> <img valign='middle' onclick='_graph.deleteChart("+data[i]['ID']+")' class='xclose' src='../img/x.png'></span>";
			}  */


			str += "<table width='100%' class='list table table-hover' cellpadding='5' cellspacing='0'>";
			str += "<tr>";
			str += "<td>#</td>";
			str += "<td><b>Chart title</b></td>";
			str += "<td><b>delete</b></td>";
			str += "</tr>";
			
			for(var i =0; i < data.length; i++){
				str += " <tr >";
				str += " <td>"+(i+1)+"</td>";
				str += " <td class='chart_info' id='chart_"+data[i]['ID']
						+"' min_value='"+checkValue(data[i]['MIN_VALUE'],'')
						+"' max_value='"+checkValue(data[i]['MAX_VALUE'],'')
						+"' chart_config='"+data[i]['CONFIG']
						+"' style='cursor:pointer;' onclick='_graph.openChart("+data[i]['ID']+");'><a href='#'>"+data[i]['TITLE']+"</a></td>";
				str += " <td align='center'><a href='#' class='action_del' onclick = '_graph.deleteChart("+data[i]['ID']+");'><img alt='Delete' title='Delete' src='/images/delete.png'></a></td>";
				str += " </tr>";
			}
			str += "</table>";


			
			$('#listCharts').html(str);
		},

		deleteChart : function(id)
		{
			if(!confirm("Do you want to delete this chart?")) return;
			param = {
					'ID' : id
			};
			sendAjaxNotMessage('/deleteChart', param, function(data){
				_graph.showListChart(data);
			});
		},
		openChart : function(id)
		{
			_graph.currentChartID=id;
			$("#chartTitle").val($("#chart_"+id).text());
			$("#txt_max").val(checkValue($("#chart_"+id).attr("max_value"),''));
			$("#txt_min").val(checkValue($("#chart_"+id).attr("min_value"),''));
			$("#chartObjectContainer").empty();
			var config		= $("#chart_"+id).attr("chart_config");
			var cfgs 		= graphAssitance.parseChartConfig(config);
			var dataStores	= cfgs.dataStores;
			var extension	= cfgs.extension;
			
			for(i=0;i<dataStores.length;i++){
				var dataStore = dataStores[i];
				var x =  editBox.getObjectValue(dataStore);
				editBox.addObjectItem(dataStore.color,dataStore,dataStore.text,x);
			}
			setColorPicker();

			if(typeof extension == "object" && extension!=null){
				if(extension.Timebase !== undefined ) 		{
					$("#Timebase").val(extension.Timebase);
					$("#Timebase").change();
				}
				if(extension.SampleInterval !== undefined ) $("#SampleInterval").val(extension.SampleInterval);
				if(extension.LastDate !== undefined ) $("#LastDate").val(extension.LastDate);
			}
			$('#listCharts').dialog("close");
			_graph.draw();
		},

		loadCbo : function(id, data){
			var cbo = '';
			$('#'+id).html(cbo);
			for(var v in data){
				cbo += ' 		<option value="' + data[v].CODE + '">' + data[v].NAME + '</option>';
			}
			$('#'+id).html(cbo);
			$('#'+id).change();
		},
		saveChart : function(isAddNew)
		{
			var config = _graph.getChartConfig(true);
			if(config == ""){alert("Chart's settings is not ready");return;}
			var title = $("#chartTitle").val();
			
			if(title == ""){
				alert("Please input chart's title");
				$("#chartTitle").focus();
				return;
			}
			if(isAddNew == true)
			{
				title = prompt("Please input chart's title",title);
				title = title.trim();
				if(title == "") return;
			}

			param = {
					'id' : (isAddNew?-1:_graph.currentChartID),
					'title' : title,
					'maxvalue' : $("#txt_max").val(),
					'minvalue' : $("#txt_min").val(),
					'config' : config
			};
			sendAjaxNotMessage('/saveChart', param, function(data){
				if(data.substr(0,3)=="ok:")
				{
					alert("Chart saved successfully");
					_graph.currentChartID=data.substr(3);
					$("#chartTitle").val(title);
				}
				else{
					alert(data);
				}
			});
		}
	}
	
	$("#genChartBtn").click(_graph.draw);
	$("#newChartBtn").click(_graph.newChart);
	$("#loadChartBtn").click(_graph.loadCharts);
	$("#saveChartBtn").click(function(){_graph.saveChart(false)});
	$("#saveAssChartBtn").click(function(){_graph.saveChart(true)});
</script>
@stop

@section('action_extra')
	@include('partials.diagram_action')
@stop

@extends('core.pm',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('chartContainer')
	 <iframe id="frameChart" style="width:98%;border:none;height: 400px; margin-top: 10px; margin-right: 10px"></iframe>
@stop

@section('content')
<style>
#filterFrequence {
	clear: both;
}
.alloc_type {
	display: none
}

.plan_type {
	display: none
}

.forecast_type {
	display: none
}

#chartObjectContainer {
	list-style-type: none;
	margin: 0;
	padding: 0;
}

#chartObjectContainer li {
	padding: 1;
}

#chartObjectContainer li span {
	
}

._colorpicker{border:1px solid #bbbbbb;cursor:pointer;margin:2px;width:30px}


.MidleAlignInput input{
	width: 18px;
    height: 15px;
    margin: 5px;
    vertical-align: middle;
}

.OtherTags{
    position: absolute;
    left: 0px;
    bottom: 0px;
    width: 100%;
    height: 50px;
    background: #e8e8e8;
    padding: 10px 5px;
    box-sizing: border-box;
}


.TagSelection{
    overflow-y: auto;
    height: 250px;
    line-height: 18px;
}

</style>
<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/graph/style.css" />
<link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>

<body style="margin: 0; min-width: 1000px;">
	<div id="listCharts" style="display: none; overflow: auto"></div>
	@yield("chartContainer")
</body>

<script>

	$(function(){
		var ebtoken = $('meta[name="_token"]').attr('content');
		$.ajaxSetup({
			headers: {
				'X-XSRF-Token': ebtoken
			}
		});

		$('#txtObjectName').val('Flow');
		$("#chartObjectContainer").sortable();

		$(".phase_type").hide();

		$('#cboObjectNameTable').change();
		filters.afterRenderingDependences("ObjectName");
		filters.preOnchange("IntObjectType");
		filters.preOnchange("ObjectDataSource");
	});

	function setColorPicker(){
		$('._colorpicker').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).css({"background":"#"+hex,"color":"#"+hex});
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		});
	}

	function showChartList()
	{
		$("#listCharts").show();
		$("#listFormulas").hide();
		$("#cbuttonChart").addClass("cbutton_active");
		$("#cbuttonFormula").removeClass("cbutton_active");
	}
	function showFormulaList()
	{
		$("#listCharts").hide();
		$("#listFormulas").show();
		$("#cbuttonChart").removeClass("cbutton_active");
		$("#cbuttonFormula").addClass("cbutton_active");
	}
	var timeoutLoading=null;
	function iframeOnload()
	{
		if(timeoutLoading!=null)
			clearTimeout(timeoutLoading);
		timeoutLoading=null;
	}
</script>
@stop



@section('endDdaptData')
@parent
<script>
	editBox.loadUrl = "/graph/filter";

	$('#Timebase').change(function(e){
		var cTimebase = $(this).val()/1;
		var minInterval = cTimebase/{{$defaultNumber}};
		$("#SampleInterval").val(minInterval);
		var issetValue = false;
		$("#SampleInterval > option").each(function() {
			if(minInterval>$(this).val() || $(this).val()>= cTimebase) $(this).attr('disabled','disabled')
			else {
				$(this).removeAttr('disabled');
				if(!issetValue) {
					$("#SampleInterval").val($(this).val());
					issetValue = true;
				}
			}
		});
	});
	$('#Timebase').change();

	actions.loadUrl = "/tagsMapping/load";
	actions.initData = function(){
		var tab = {'{{config("constants.tabTable")}}':"IntTagMapping"}
		return tab;
	}

    var dt = moment().format(configuration.time.DATETIME_FORMAT);
    $("#LastDate").val(dt);
    $( "#LastDate" ).datetimepicker({
        changeMonth	:true,
        changeYear	:true,
        format		:configuration['picker']['DATE_FORMAT']+" "+configuration['picker']['TIME_FORMAT'],
        showTimezone	: false,
        showMicrosec	: null,
        autoclose: true
    });
</script>
@stop

@section('extraAdaptData')
@parent
<script>
	var opreOnchange	= filters.preOnchange;
	filters.preOnchange		= function(id, dependentIds,more){
        if(typeof opreOnchange == "function") opreOnchange(id, dependentIds,more);
		var partials 		= id.split("_");
		var prefix 			= partials.length>1?partials[0]+"_":"";
		var model 			= partials.length>1?partials[1]:id;
		switch(model){
			case "ObjectName":
			    if($("#"+prefix+"IntObjectType").val()=='DEFERMENT')
                    $("#"+prefix+"ObjectName option:eq(0)").remove();
				return;
		}
	};
</script>
@stop
