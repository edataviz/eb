<?php
$currentSubmenu = '/viewconfig';
$listControls = [ 
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit' 
		),
		
		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id' 
		),
		
		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'fkey' => 'area_id' 
		),
		
		'CodeProductType' => array (
				'label' => 'Product',
				'ID' => 'CodeProductType',
				'default' => 'All' 
		) 
];
?>

@extends('core.bsconfig')

@section('title')
<div class="title">VIEW CONFIG</div>
@stop @section('group') @include('group.adminControl') @stop

@section('content')
<link rel="stylesheet" href="/common/css/admin.css">
<style>
.phase_type{display:none}
.alloc_type{display:none}
.plan_type{display:none}
.forecast_type{display:none}
  #plotItemObjectContainer { list-style-type: none; margin: 0; padding: 0;}
  #plotItemObjectContainer li {padding: 1;}
  #plotItemObjectContainer li span {}
</style>
<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})

	$("#plotItemObjectContainer").sortable();
});

var _viewconfig = {
		loadObjType : 1,

		loadTimelineType : 1,

		currentPlotItemID : 0,

		lastObjectType : "",

		loadObjecType : function(){
			var cbo = '';
			cbo += ' <div class="filter">';
			cbo += ' <div><b> Object type </b></div>';
			cbo += ' <select style="width:120" id="cboObjectType" onchange="_viewconfig.cboObjectTypeOnChange()" size="1" name="cboObjectType">';
			cbo += ' <option selected value="FLOW">Flow</option>';
			cbo += ' <option value="ENERGY_UNIT">Energy Unit</option>';
			cbo += ' <option value="TANK">Tank</option>';
			cbo += ' <option value="STORAGE">Storage</option>';
			cbo += ' <option value="EQUIPMENT">Equipment</option>';
			cbo += ' <option value="EU_TEST">Well test</option>';
			cbo += ' </select><button style="position:absolute;top:112px;margin-left:10px" onclick="_viewconfig.cboObjectTypeOnChange()">Refresh</butotn>';
			cbo += ' </div>';
			return cbo;
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
			if($("#cboObjectType").val().indexOf("ENERGY_UNIT") > -1 && $("#cboObjectType").val().indexOf("EU_TEST") < 0)
			{
				$(".phase_type").show();
			}
			else
				$(".phase_type").hide();

			$("#cboObjectNameTable").val($("#cboObjectNameTable").find('option:visible:first').attr("value"));
			$("#cboObjectNameProps").val($("#cboObjectNameProps").find('option:visible:first').attr("value"));

			_viewconfig.loadObjects();
		},
		loadObjects : function(){
			var objectType = $("#cboObjectType").val();
			//if(objectType == _viewconfig.lastObjectType) return;

			param = {
				'object_type' : objectType,
				'facility_id' : $("#Facility").val(),
				'product_type' : $('#Product').val()
			};
			
			$("#cboObjectName").prop("disabled", true); 
			sendAjax('/loadPlotObjects', param, function(data){
				$('#txtObjectName').val($("#cboObjectType ").text());

				_viewconfig.reloadCbo('cboObjectName',data.objectName);
				_viewconfig.reloadCbo('cboObjectNameTable',data.graphDataSource);

				_viewconfig.lastObjectType = objectType;
				if($("#cboObjectName").val().indexOf("ENERGY_UNIT") > -1)
				{
					_viewconfig.loadEUPhase();
				}
			});
		},

		reloadCbo : function(id, _data){
			$('#'+id).empty();

			var cbo = '';
			$('#'+id).html(cbo);
			for(var v in _data){
				if(id == 'cboObjectNameProps'){
					cbo += ' 		<option value="' + _data[v] + '">' + _data[v] + '</option>';
				}else if(id == 'cboObjectNameTable'){
					cbo += ' 		<option value="' + _data[v].NAME + '">' + _data[v].NAME + '</option>';
				}else{
					cbo += ' 		<option value="' + _data[v].ID + '">' + _data[v].NAME + '</option>';
				}
			}

			$('#'+id).html(cbo);
			$("#"+id).prop("disabled", false); 
			$("#"+id).change();
		},
		
		loadEUPhase : function(){
			param = {
				'eu_id' : $('#cboObjectName').val(),
			};
			
			$("#cboEUFlowPhase").prop("disabled", true); 
			sendAjaxNotMessage('/loadEUPhase', param, function(data){
				_viewconfig.reloadCbo('cboEUFlowPhase',data.result);
			});
		},
		
		cboObjectNameTableChange : function(){
			if($("#cboObjectNameTable").val()=="ENERGY_UNIT_DATA_ALLOC")
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
			
		    $('#cboObjectNameProps').html('');

		    param = {
				'TABLE_NAME' : $("#cboObjectNameTable option:selected").text(),
			};

		    $("#cboObjectNameProps").prop("disabled", true); 
			sendAjaxNotMessage('/getTableFields', param, function(data){
				_viewconfig.reloadCbo('cboObjectNameProps',data);
			});
		},
		addObject : function()
		{
			if($("#cboObjectName").val()>0)
			{
				var d=$("#cboObjectType").val().split("/");
				var s1="", s2="", phase="";
				if(d.length>1)
				{
					s1=d[1]+"_";
				}
				if(d.length>2)
				{
					s2=d[2]+"_";
				}
				if(d[0]=="ENERGY_UNIT")
				{
					phase=$("#cboEUFlowPhase").val();
				}
				var cons=$("#txtConstant").val();
				var math="";
				if(Number(cons) && cons.trim()!=""){
					var op=$("#cboOperation").val();
					math=op+cons;
				}
				var x="#"+$("#Facility").val()+":"+d[0]+":"+$("#cboObjectName").val()+":"+s1+$("#cboObjectNameTable").val()+":"+s2+$("#cboObjectNameProps").val()+":"+phase+":"+math+"~"+$("#cboAllocType").val()+"~"+$("#cboPlanType").val()+"~"+$("#cboForecastType").val();
				if($("span[config='"+x+"']").length==0)
				{
					var label=$("#cboObjectName option:selected").text();
					label=prompt("Add with label",label);
					if(label=="") return;

					var s="<li class='x_item' config='"+x+"'><span>"+label+"("+$("#cboObjectNameTable option:selected").text()+($("#cboEUFlowPhase").is(":visible")?"."+$("#cboEUFlowPhase option:selected").text():"")+"."+$("#cboObjectNameProps option:selected").text()+")"+math+"</span> "+'<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png"><br></li>';
					$("#plotItemObjectContainer").append(s);
				}
				else
				{
					$("span[config='"+x+"']").effect("highlight", {}, 1000);
				}
			}
		},
		newPlotItem : function()
		{
			if($("#plotItemObjectContainer").children().length>0)
			{
				if(!confirm("Current plotItem will be clear. Do you want to continue?")) return;
			}
			_viewconfig.currentPlotItemID = 0;
			$("#plotItemObjectContainer").empty();
			$("#plotItemTitle").val("");
		},
		loadPlotItems : function()
		{
			$('#listPlotItems').html("Loading...")
			$('#boxListPlotItems').fadeIn();
			param = {
			};
			sendAjaxNotMessage('/getListPlotItems', param, function(data){
				_viewconfig.showListPlotItem(data);
			});
			
		},
		showListPlotItem : function(data){
			var str = "";
			$('#listPlotItems').html(str);

			str += "<table width='100%' class='list table table-hover' cellpadding='5' cellspacing='0'>";
			str += "<tr>";
			str += "<td>#</td>";
			str += "<td><b>Plot item</b></td>";
			str += "<td><b>delete</b></td>";
			str += "</tr>";
			
			for(var i =0; i < data.length; i++){
			
				str += " <tr >";
				str += " <td>"+(i+1)+"</td>";
				str += " <td class='plotItem' id='plotItem_"+data[i]['ID']+"' plotItem_config='"+checkValue(data[i]['CONFIG'],'')+"' TIMELINE='"+checkValue(data[i]['TIMELINE'],'')+"' charttype='"+data[i]['CHART_TYPE']+"' style='cursor:pointer; width:470px' onclick='_viewconfig.openPlotItem("+data[i]['ID']+");'><a href='#'>"+data[i]['NAME']+"</a></td>";
				str += " <td align='center'><a href='#' class='action_del' onclick = '_viewconfig.deletePlotItem("+data[i]['ID']+");'><img alt='Delete' title='Delete' src='/images/delete.png'></a></td>";
				str += " </tr>";
			}
			str += "</table>";
			$('#listPlotItems').html(str);
		},
		openPlotItem : function(id)
		{
			_viewconfig.currentPlotItemID=id;
			$("#plotItemTitle").val($("#plotItem_"+id+" a:first").text());
			$("#plotItemObjectContainer").empty();
			var config=$("#plotItem_"+id).attr("plotItem_config");
			var timeline=$("#plotItem_"+id).attr("timeline");
			var charttype=$("#plotItem_"+id).attr("charttype");
			$("#cboTimeline").val(timeline);
			$("#cboChartType").val(charttype);
			var cfs=config.split(',');
			var i=0;
			for(i=0;i<cfs.length;i++)
			{
				var vals=cfs[i].split(':');
				{
					var text=vals.pop();
					var config=vals.join(":");
					var s="<li class='x_item' config='"+config+"'><span>"+text+"</span> "+'<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png"><br></li>';
					$("#plotItemObjectContainer").append(s);
				}
			}
			$('#boxListPlotItems').fadeOut()
		},
		deletePlotItem : function(id)
		{
			if(!confirm("Do you want to delete this Plot Item?")) return;
			param = {
					'ID' : id
			};
			sendAjaxNotMessage('/deletePlotItems', param, function(data){
				_viewconfig.showListPlotItem(data);
			});
		},
		savePlotItem : function(isAddNew)
		{
			var config = _viewconfig.getPlotItemConfig();
			if(config==""){alert("PlotItem's settings is not ready");return;}
			var title=$("#plotItemTitle").val();
			if(title==""){alert("Please input plotItem's title");$("#plotItemTitle").focus();return;}
			if(isAddNew==true)
			{
				title=prompt("Please input plotItem's title",title);
				title=title.trim();
				if(title=="") return;
			}

			param = {
					'id' : (isAddNew?-1:_viewconfig.currentPlotItemID),
					'title' : title,
					'config' : config,
					'timeline' : $("#cboTimeline").val(),
					'charttype' : $("#cboChartType").val()
			};
			sendAjaxNotMessage('/savePlotItems', param, function(data){
				if(data.substr(0,3)=="ok:")
				{
					alert("PlotItem saved successfully");
					_viewconfig.currentPlotItemID = data.substr(3);
					$("#plotItemTitle").val(title);
				}
				else{
					alert(data);
				}
			});
		},
		getPlotItemConfig : function()
		{
			var s="";
			$(".x_item").each(function(){
		        s += (s==""?"":",")+$(this).attr("config")+":"+$(this).children("span").text();
		    });
			return s;
		},
		
		genView : function(overwrite_id){
			var viewName=$("#plotItemTitle").val().trim();
			if(viewName==""){
				alert("Please input the name");
				$("#plotItemTitle").focus();
				return;
			}
			var config = _viewconfig.getPlotItemConfig();
			if(config==""){
				alert("PlotItem's settings is not ready");
				return;
			}
			
			param = {
					'id' : _viewconfig.currentPlotItemID,
					'view_name' : viewName,
					'config' : config,
					'overwrite_id' : overwrite_id
			};
				console.log(param);
			sendAjaxNotMessage('/genView', param, function(data){
				console.log(data);
				if(data==""){
					alert("SQL '"+viewName+"' generated successfully");
				}
				else if(data.substr(0,17)=="CONFIRM_OVERWRITE"){
					if(confirm("SQL '"+viewName+"' already exists. Overwite?")){
						_viewconfig.genView(data.substr(18));
					}
				}
				else
					alert(data);
			});
		}
}

</script>
<body style="margin: 0; min-width: 1000px;">
	<div id="boxListPlotItems"
		style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5)">
		<div style="position: absolute; background: white; border: 1px solid black; width: 600px; height: 400px; overflow: auto; left: 50%; top: 50%; margin-top: -200px; margin-left: -300px; box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.5);">
			<input class="myButton" type="button" value="Close"
				style="position: absolute; height: 30px; width: 80px; top: 0px; right: 0px"
				onclick="$('#boxListPlotItems').fadeOut()">
			<div style="width: 100%; height: 30px; border-bottom: 1px solid #cccccc; background: #eeeeee; color: black; padding: 0px; line-height: 30px;">
				<div class="cbutton cbutton_active" id="cbuttonPlotItem" onclick="showPlotItemList()">Plot Item list </div>
			</div>
			<div id="listPlotItems"	style="padding: 20px; height: 328px; overflow: auto"></div>
			<div id="listFormulas"	style="display: none; padding: 20px; height: 328px; overflow: auto"></div>
		</div>
	</div>
	
	<table border="0" height="300px" cellspacing="0" cellpadding="0" style="margin-top:10px;">
	<tr>
		<td style="box-sizing: border-box;padding:5px;border:1px solid #bbbbbb; background:#eeeeee;width:600px" valign="top">
	<table border="0" id="table1" width="100%">
		<tr>
			<td><b>Timeline</b></td>
			<td width='120'>
				<select style="width:200px" id="cboTimeline" onchange="" size="1" name="cboTimeline">
					<option selected value="0">All</option>
					<option value="1">Prior Date</option>
					<option value="2">Prior-to-Current</option>
					<option value="3">Current Date</option>
					<option value="4">Future Date</option>
					<option value="5">Current-to-Future</option>
				</select>
			</td>
			
			<td><b>Data source</b></td>
			<td><select style="width:200px;height:22px" id="cboObjectNameTable" onchange="_viewconfig.cboObjectNameTableChange();" size="1" name="cboObjectNameTable"></select></td>
			
		</tr>
		<tr>
			<td><b><span>Object</span></b></td>
			<td><select style="width:100%;height:22px" id="cboObjectName" onchange="_viewconfig.loadEUPhase();" size="1" name="cboObjectName"></select></td>
			<td ><b>Property</b></td>
			<td ><select style="width:200px;height:22px" id="cboObjectNameProps" size="1" name="cboObjectNameProps"></select></td>
		</tr>
		<tr>
			<td><b><span class="phase_type">Flow phase</span></b></td>
			<td><span class="phase_type"><select style="width:100%;" id="cboEUFlowPhase" size="1" name="cboEUFlowPhase"></select></span></td>		
			
		</tr>
		<tr>
			<td><b>Operation</b></td>
			<td> 
				<select id="cboOperation" style="width:80px;height:22px">
					<option value='+'>+</option>
					<option value='-'>-</option>
					<option value='*'>*</option>
					<option value='/'>/</option>
				</select>
				<input id="txtConstant" type='text' class='_numeric' style='width:112px;'>
				
			</td>
			<td>
				<span class="alloc_type"><b>Alloc type</b></span>
				<span class="plan_type"><b>Plan type</b></span>
				<span class="forecast_type"><b>Forecast type</b></span>
			</td>
			<td>
				<span class="alloc_type">
					<select style="width:200px;" id="cboAllocType" size="1" name="cboAllocType">
						@foreach($code_alloc_type as $alloc)
							<option value="{!!$alloc->ID!!}">{!!$alloc->NAME!!}</option> 
						@endforeach
					</select>
				</span>
				<span class="plan_type">
					<select style="width:200px;" id="cboPlanType" size="1" name="cboPlanType">
						@foreach($code_plan_type as $plan)
							<option value="{!!$plan->ID!!}">{!!$plan->NAME!!}</option> 
						@endforeach
					</select>
				</span>
				<span class="forecast_type">
					<select style="width:200px;" id="cboForecastType" size="1" name="cboForecastType">
						@foreach($code_forecast_type as $forecast)
							<option value="{!!$forecast->ID!!}">{!!$forecast->NAME!!}</option> 
						@endforeach
					</select>
				</span>	
			</td>
		</tr>
		<tr>
			<td><b>Plot item</b></td>
			<td colspan="3"><input type="text" style="width:420px" id = "plotItemTitle" name="plotItemTitle" size="15" value="">&nbsp;&nbsp;<button class="myButton" onclick="_viewconfig.addObject()" style="width: 61px; margin-right: 20px;">Add</button></td>
		</tr>
	</table>
</td>
		<td width="10">&nbsp;</td>
		<td valign="top" style="box-sizing: border-box;overflow:auto;height:300px;padding:5px;border:1px solid #bbbbbb; background:#eeeeee;width:532px;">
<ul id="plotItemObjectContainer">
</ul>
		</td>
		<td style="" rowspan="2" valign="top" align="right" width="180">
		<button class="myButton" onClick="_viewconfig.genView('')" style="display:;margin-bottom:10px;width: 170px; height: 50px">Generate SQL</button>
		<button class="myButton" onClick="_viewconfig.savePlotItem()" style="display:none;margin-bottom:3px;width: 170px; height: 30px">Save plotItem</button>
		<button class="myButton" onClick="_viewconfig.newPlotItem()" style="display:;margin-bottom:3px;width: 84px; height: 35px">New</button>
		<button class="myButton" onClick="_viewconfig.loadPlotItems()" style="display:;margin-bottom:3px;width: 83px; height: 35px">Load</button>
		<button class="myButton" onClick="_viewconfig.savePlotItem()" style="display:;margin-bottom:0px;width: 84px; height: 35px">Save</button>
		<button class="myButton" onClick="_viewconfig.savePlotItem(true)" style="display:;margin-bottom:0px;width: 83px; height: 35px">Save as</button>
</td>
	</tr>
</table>
</body>
@stop
