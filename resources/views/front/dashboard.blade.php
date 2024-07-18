<?php
	$currentSubmenu = isset($currentSubmenu)?$currentSubmenu:'/dashboard';
	$graphViewId 	= 'inputObjectName';
	$useFeatures 	= [
							['name'	=>	"filter_modify",
							"data"	=>	["isFilterModify"	=> false,
										"isAction"			=> false]],
							['name'	=>	"object_input",
							"data"	=>	["floatViewId"	=> $graphViewId]],
							['name'	=>	"sliding_menu",
							"data"	=>	[]],
					];
	
	if (!isset($floatContents)) $floatContents = ['editBoxContentview' ,$graphViewId];
	else $floatContents[] = $graphViewId;
	$enableFooter = false;
?>

@extends('core.bsmain',['subMenus' 		=> [],
						'useFeatures'	=> $useFeatures])
@section('script')
@parent
	<script src="/common/js//base64.js"></script>	
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	<script>
		var dashboardList = false;
		var notCachedList	= false;

		function createComponents(cf,d_id){
			var currentBox;
			for(var i=0;i<cf.length;i++){
				currentBox = create_container(cf[i],d_id);
			}
			if(cf.length==1){
				currentBox.css("left",10);
				currentBox.css("top",70);
				currentBox.css("width",$(window).width()-40);
				currentBox.css("height",$(window).height()-100);
				currentBox.css("padding",10);
			}
		}
		
		function load_dash_board(obj){
			dashboard_id=Number($(obj).attr('dashboard_id'));
			d_bg=$(obj).attr('d_bg');
			$("#boxDashboardList").dialog("close");
			$(".container").remove();
			$("#dashboard_name").html($(obj).text());
			//updateName($(obj).html());
			//alert($(obj).attr("config"));
			var cf=JSON.parse($(obj).attr('config'));
			var d_id=$(obj).attr('dashboard_id');
			createComponents(cf,d_id);
			reload();
		}

		function loaddashboards(enableDelete){
			enableDelete = enableDelete===undefined?false:enableDelete;
			$("#boxDashboardList").dialog({
							height: 350,
							width: 500,
							modal: true,
							title: "Select dashboard",
						});
// 			if(dashboardList==false||notCachedList){
			$("#boxDashboardList").html("Loading...");
			postRequest( "/dashboard/all",
						 {},
						 function(data){
							dashboardList=data;
							$("#boxDashboardList").html("");
							var elist = null;
							var table = $("<table id='table_dashboard_list'>");
							var group_name="";
							$.each(data, function( dindex, dvalue ) {
								if(dvalue.GROUP_NAME!=group_name){
									if(elist!=null){
										var tr=$("<tr>");
										var td1=$("<td>");
										var td2=$("<td>");
										td1.html('<b>'+group_name+'</b>');
										elist.appendTo(td2);
										tr.append(td1).append(td2);
										table.append(tr);
									}
									group_name = dvalue.GROUP_NAME;
									elist = $("<ul class='ListStyleNone' style='margin:0;padding:0'>");
								}
								var li = $("<li class='x_item'  style='cursor:pointer'></li>");
								li.attr("d_bg",dvalue.BACKGROUND);
								li.attr("dashboard_id",dvalue.ID);
								li.attr("group_id",dvalue.DASHBOARD_GROUP_ID);
								li.attr("config",dvalue.CONFIG);
								li.text(dvalue.NAME);
								li.click(function() {
									load_dash_board(li);
								});
								if(enableDelete){
									var del				= $('<img valign="middle" class="xclose" src="/img/x.png">');
									del.appendTo(li);
									del.click(function(e) {
										e.stopPropagation();
										if(!confirm("Are you sure you want to delete this item?")) return;
										showWaiting();
										$.ajax({
											url			: "/dashboard/save",
											type		: "post",
											data		: {
																deleteData	: {
																				Dashboard	: [dvalue.ID]
																				}
														},
											success		: function(data){
												hideWaiting();
												li.remove();
												console.log ( "delConstrain success ");
											},
											error		: function(data) {
												hideWaiting();
												console.log ( "dashboard error "/*+JSON.stringify(data)*/);
												alert("delete dashboard error ");
											}
										});
									});
								}
								li.appendTo(elist);
							});
							if(elist!=null){
								var tr=$("<tr>");
								var td1=$("<td>");
								var td2=$("<td>");
								td1.html(group_name);
								elist.appendTo(td2);
								tr.append(td1).append(td2);
								table.append(tr);
							}
							table.appendTo($("#boxDashboardList"));
						 }
					  );		
// 			}
		}
	</script>

@stop

@section('first_filter')
	<b><span id="dashboard_name">
		@if($dashboard_row)
			{{$dashboard_row->NAME}}
		@else
			<?php echo "Dashboard name"; ?>
		@endif
	</span></b><br>
	<div class="action_filter floatLeft">
			<input type="button" value="Config" id="buttonLoadData" name="B33"
				onClick="config()" style="width: 85px; height: 26px;float:left;margin-top:7px;    margin-left: 2px;">
	</div>
	
	<div style="right:5px;top:5px;z-index:10;text-align:right;margin-right: 10px;">
	<div class="action_filter floatLeft" style="margin-bottom: 20px;">
			<input type="button" value="Change Dashboard"  name="B33"
				onClick="loaddashboards()" style="height: 26px;float:left;margin-top:7px;    margin-left: 2px;">
	</div>
</div>
@stop


@section('content')
<style type="text/css">
		.documentBody{
		    overflow-x: hidden;
		}
		.filterContainer {
			width	:100%;
		    background-color: #E6E6E6;
		}
		div.container{
			padding		:5px;
			position	:absolute;
			border		:1px solid #888888;
		}
		div.container span.title{
			display:block;
			position:absolute;
			border:0px solid #888888;
			//width:auto;
			//height:20px;
			background:#bbbbbb;
			opacity:0.8;
			padding:2px;
			cursor:pointer;
		}
		div.container iframe{
			border:0;
			margin:0;
			padding:0px;
			width:100%;
			height:100%;
		}
		.dashboardContainer{
			background	: white;
		    overflow: scroll;
		}
		
		.bootsidemenu > .menu-wrapper {
			background-color: #E6E6E6;
		}
		
		.navi {
		    margin-bottom: 0;
		}
		#table_dashboard_list{
			width: 100%;
			border-collapse: collapse;
		}
		#table_dashboard_list td{
			padding:10px;
			border-bottom:1px solid gray;
		}
#table_dashboard_list tr:last-child td{
	border-bottom:none;
}
	</style>
<div id="boxDashboardList" style="display:none">
</div>
<div id="boxImpLog" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:2">
	<div style="position: absolute;box-sizing: border-box;box-shadow: 0px 5px 30px rgba(0,0,0,0.5);left:10%;top:10%;padding:15px; width: 80%; height: 80%; z-index: 1; border:1px solid #999999; background:white">
	<input type="button" value="Close" onclick="$('#boxImpLog').fadeOut()" style="position:absolute;width:80px;height:30px;right:0px;top:-30px">
	<div id="logContent" style="width:100%;height:100%;overflow:auto">
	</div>
	</div>
</div>
<div id="pageheader" style="height:100px;"></div>

<div id="boxSelectChart" style="display:none">
<select id="cboSelectCharts"></select>
</div>
<div id="boxSelectWorkflow" style="display:none">
<select id="cboSelectWorkflows"></select>
</div>
<div id="boxSelectReport" style="display:none">
<select id="cboSelectReports"></select>
</div>

<script>
var dashboard_id='{{$dashboard_id}}';
<?php
if($dashboard_id>0){
	echo "var cf=JSON.parse('$dashboard_row->CONFIG'),d_bg='$dashboard_row->BACKGROUND';";
}
else
	echo "var cf=[],d_bg='';";
?>

createComponents(cf);

function create_container(config, d_id){
	var html="";
	var $box = null;
	if(config.type=="1"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectChart()">Chart</span>'+
	'<iframe class="dashboardContainer" id="if'+d_id+'" src=""></iframe>'+
	'<div style="top: 5px;position: absolute;"><a onclick="graphAssitance.getChartConfigById('+config.obj+',this)" class="actionLink"><img alt="Refresh" height="20" title="Refresh" src="/images/refresh.png"></a></div>'+
	'</div>';
	}
	else if(config.type=="2"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectWorkflow()">Workflow</span>'+
	'<iframe class="dashboardContainer" id="if'+d_id+'" src=""></iframe>'+
	'</div>';
	}
	else if(config.type=="3"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Report</span>'+
	'<iframe class="dashboardContainer" id="if'+d_id+'" src="">'+
	'</div>';
	}
	else if(config.type=="5"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Network Model</span>'+
	'<iframe class="dashboardContainer" id="if'+d_id+'" src="" refresh="'+config.refresh+'">'+
	'</div>';
	}
	else if(config.type=="6"){
		/* html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Data View</span>'+
	'<iframe class="dashboardContainer" id="if'+d_id+'" src="">'+
	'</div>'; */

		html='<div class="container">'+
		'<div class="dashboardContainer" style="width: 100%;height: 100%;"></div>'+
		'</div>';
	}
	else if(config.type=="7"){
		html='<div class="container">'+
	//'<span class="title" onclick="selectReport()">Storage Display</span>'+
	'<iframe class="dashboardContainer" id="if'+d_id+'">'+
	'</div>';
	}
	else if(config.type=="8"){
		html='<div class="container">'+
		'<div class="dashboardContainer" style="width: 100%;height: 100%;"></div>'+
// 		'<iframe id="if'+d_id+'" src="">'+
		'</div>';
	}
	else if(config.type=="4"){
		html='<div class="container">'+
		Base64.decode(config.obj)+
	//'<textarea style="width:100%;height:100%;border:none">'+config.obj+'</textarea>'+
	'</div>';
	}
	var $box = $(html);
	$box.attr("dashboard_id",d_id);
	$box.attr("d_type",config.type);
	$box.attr("d_title",config.title);
	$box.attr("d_obj",(config.type=="4"?"":config.obj));
	$box.css("left",config.pos[0]);
	var dTop = config.pos[1] !== undefined?config.pos[1] -50:0;
	$box.css("top",dTop);
	$box.css("width",config.size[0]);
	$box.css("height",config.size[1]);
	$("#mainContent").append($box);
	return $box;
}

function config(){
  //var win = window.open('config.php?dashboard_id='+dashboard_id, '_blank');
  //win.focus();
  location.href='/config/dashboard?id='+dashboard_id;
}
function selectChart(){
	$("#boxSelectChart").dialog({
					height: 200,
					width: 400,
					modal: true,
					title: "Select chart",
					buttons: {
						"OK": function(){
							$("#ifChart").attr("chart_id",$("#cboSelectCharts").val());
							loadChart();
							$("#boxSelectChart").dialog("close");
	postRequest( 
	             "index.php?act=setchart",
	             {chart_id:$("#cboSelectCharts").val()},
	             function(data){
					 if(data!="") alert(data);
				 }
	          );
						},
						"Cancel": function(){
							$("#boxSelectChart").dialog("close");
						}
					}
				});
}
function selectWorkflow(){
	$("#boxSelectWorkflow").dialog({
					height: 200,
					width: 400,
					modal: true,
					title: "Select workflow",
					buttons: {
						"OK": function(){
							$("#ifWorkflow").attr("wf_id",$("#cboSelectWorkflows").val());
							loadWorkflow();
							$("#boxSelectWorkflow").dialog("close");
	postRequest( 
	             "index.php?act=setworkflow",
	             {workflow_id:$("#cboSelectWorkflows").val()},
	             function(data){
					 if(data!="") alert(data);
				 }
	          );
						},
						"Cancel": function(){
							$("#boxSelectWorkflow").dialog("close");
						}
					}
				});
}
function selectReport(){
	$("#boxSelectReport").dialog({
					height: 200,
					width: 400,
					modal: true,
					title: "Select report",
					buttons: {
						"OK": function(){
							$("#ifReport").attr("reportfile",$("#cboSelectReports").val());
							loadReport();
							$("#boxSelectReport").dialog("close");
	postRequest( 
	             "index.php?act=setreport",
	             {reportfile:$("#cboSelectReports").val()},
	             function(data){
					 if(data!="") alert(data);
				 }
	          );
						},
						"Cancel": function(){
							$("#boxSelectReport").dialog("close");
						}
					}
				});
}
var bgcolor="";
function loadChart(o){
	var obj 	= $(o).parent().data();
	var dataStores 		= Object.keys(obj).map(function (key) { return obj[key]; });
	if(dataStores.length >0) {
		if(!graphAssitance.isInputObjectsValidated(function(inputedDataStores){
				var input 		= encodeURIComponent(graphAssitance.getChartConfigText(inputedDataStores,$(o)));
				var date_begin 	= $("#date_begin").val();
				var date_end 	= $("#date_end").val();
				var iurl = 	"/loadchart?bgcolor="+
							"&date_begin="+date_begin+
							"&date_end="+date_end+
							"&nolegend"+
                    		"&facility="+$("#Facility").val()+
							"&input="+input;
				$(o).attr("src",iurl);
			},true,dataStores)) return;
	}
	var date_begin 	= $("#date_begin").val();
	var date_end 	= $("#date_end").val();
	var iurl = 	"/loadchart?bgcolor="+bgcolor+
				"&date_begin="+date_begin+
				"&date_end="+date_end+
        		"&facility="+$("#Facility").val()+
				"&chart_id="+$(o).parent().attr("d_obj")+
				"&nolegend";
	$(o).attr("src",iurl);
// 	$(o).attr("src","../graph/advgraph_loadchart.php?bgcolor="+bgcolor+"&date_begin="+$("#date_begin").val()+"&date_end="+$("#date_end").val()+"&chart_id="+$(o).parent().attr("d_obj")+"&nolegend");	
}
function loadWorkflow(o){
	//document.getElementById("ifWorkflow").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","/loadWfShow?bgcolor="+bgcolor+"&wf_id="+$(o).parent().attr("d_obj")+"&onlyshow");
}
var Months=["January","February","March","April","May","June","July","August","September","October","November","December"];
function loadReport(o){
	/* var ds=$("#date_begin").val().split("/");
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
	$(o).attr("src","../report/"+$(o).parent().attr("d_obj")+".php?bgcolor="+bgcolor+"&export=HTML&startDate="+ds[2]+"/"+ds[0]+"/01&report_time="+Months[Number(ds[0])-1]+"%20"+ds[2]+"&facility_id=");

 */
 	var date_begin	= $("#date_begin").datepicker( "getDate" );
 	var day 		= date_begin.getDate();
 	var month 		= date_begin.getMonth() + 1;
 	var year 		= date_begin.getFullYear();
	var iurl = 	"/report/viewreport.php?export=HTML&file="+$(o).parent().attr("d_obj")+
		"&bgcolor="+bgcolor+
		"&begin_date__T_3="+year+"-"+month+"-"+day;
	$(o).attr("src",iurl);	
}
function loadNetworkModel(o){
	var ds=$("#date_begin").val().split("/");
	var refresh = $(o).attr("refresh");
 	$(o).attr("src","/diagram/networkmodel?bgcolor="+bgcolor
 		 	+"&id="+$(o).parent().attr("d_obj")
 		 	+"&onlyshow&date="+$("#date_begin").val()
 		 	+"&refresh="+refresh);
}

function loadDataView(o,rows_in_page,page){
	var d1=$("#date_begin").val();
	var d2=$("#date_end").val();
	//document.getElementById("ifReport").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
// 	$(o).attr("src","../view/show.php?bgcolor="+bgcolor+"&v="+$(o).parent().attr("d_obj")+"&begin_date="+d1+"&end_date="+d2);
	var date_begin	= $("#date_begin").datepicker( "getDate" );
 	var month 		= date_begin.getMonth() + 1;              
 	var year 		= date_begin.getFullYear();
 	var day 		= date_begin.getDate();
 	d1				= ""+year+"-"+month+"-"+day;

 	date_begin		= $("#date_end").datepicker( "getDate" );
 	month 			= date_begin.getMonth() + 1;              
 	year 			= date_begin.getFullYear();
 	day 			= date_begin.getDate();
 	d2				= ""+year+"-"+month+"-"+day;

    	var cond="SQLID:"+$(o).parent().attr("d_obj")+";{OCCUR_DATE}:OCCUR_DATE>='"+d1+"' and OCCUR_DATE<='"+d2+"'";
//    	var cond="SQLID:7;{OCCUR_DATE}:OCCUR_DATE>='"+d1+"' and OCCUR_DATE<='"+d2+"'";
	$.ajax({
		   url: "/loaddataview",
		   data:{
			   'sql': cond,
			   'rows_in_page': rows_in_page,
			   'page': page,
			   from_date	: d1,
			   to_date		: d2,
		   },
		   type: "POST",
		   success: function(re){
			   console.log("loaddataview success ");
			   o.html(re);
			   
			   table=$(".dataViewTable").dataTable({
					scrollY		: false,
					scrollX		: true,
					autoWidth	: true,
					searching	: false,
					paging		: false,
					info		: false,
					destroy		: true
				});
			   new $.fn.dataTable.FixedColumns(table,{leftColumns: 1});

			   $("#paging").find("span").addClass("clickable").on("click", function(){
				   loadDataView(o,rows_in_page,$(this).attr("page"));
				})
			   $("#go").click(function(){
							var pi=Number($("#txtpage").val());
							var pr=Number($("#paging span:last").attr("page"));
							if(pi<=pr && pi>0)
								{loadDataView(o,rows_in_page,pi);}
							else
								{alert("Invalid page");}
								
						});
		   },
		   error	: function(xhr, status, errorThrown){
						   console.log("loaddataview fail "+errorThrown);
					   }
		   });
}
function loadStorageDisplay(o){
 	var d1=$("#date_begin").val();
 	var d2=$("#date_end").val();
 	$(o).attr("src","/storagedisplay/diagram?id="+$(o).parent().attr("d_obj"));

	/* var storageDisplayPostData 	= {	
				date_begin		: $("#date_begin").val(),
				date_end		: $("#date_end").val(),
				constraintId	: $(o).parent().attr("d_obj"),
			};
	editBox.requestGenDiagram(storageDisplayPostData,o,false); */
	
}
function loadCons(o){
	var d1=$("#date_begin").val();
	var d2=$("#date_end").val();
// 	$(o).attr("src","/choke/diagram?bgcolor="+bgcolor+"&constraintId="+$(o).parent().attr("d_obj")+"&date_begin="+d1+"&date_end="+d2);

	var constraintPostData 	= {	
				date_begin		: $("#date_begin").val(),
				date_end		: $("#date_end").val(),
				constraintId	: $(o).parent().attr("d_obj"),
			};
	editBox.requestGenDiagram(constraintPostData,o,false);
}
function reload(){
	$(".dashboardContainer").each(function(){
		var iframe 	= $(this).parent().find("iframe:first");
		var dtype	= $(this).parent().attr("d_type");
		if(dtype=="1"){
			loadChart(iframe);
		}
		if(dtype=="2"){
			loadWorkflow(iframe);
		}
		if(dtype=="3"){
 			loadReport(iframe);
		}
		if(dtype=="5"){
 			loadNetworkModel(iframe);
		}
		if(dtype=="6"){
 			loadDataView($(this),25,1);
		}
		if(dtype=="7"){
   			loadStorageDisplay($(this));
		}
		if(dtype=="8"){
 			loadCons($(this));
		}
	});
	
	if(typeof d_bg != "undefined" && d_bg.length>1) 
		$('body').css("background-color",d_bg);
	else
		$('body').css("background-color","white");
}

$(function() {
 	actions.doLoad = reload;
 	reload();
});
</script>

<div id="constrainContain" class="container" style="display:none">
	@include('choke.choke_diagram')
</div>
@stop
