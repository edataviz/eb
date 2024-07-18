<?php
	$currentSubmenu ='/config/dashboard';
	$enableFilter	= false;
	
	$advChart		= array("name"		=>"AdvChart",
							"id"		=> "cboSelectCharts",
							"modelName"	=> "AdvChart",
							"enableTitle"=>false,
							"getMethod" => "all", 
							"filterData"=>['ID', 'TITLE as NAME']);
	
	$report		= array("name"			=>"REPORT",
						"id"			=> "cboSelectReports",
						"enableTitle"	=>false,
						'getMethod'		=> "all",
						'filterData'	=> ['ID',\Helper::getIdentifierColumn("FILE")." as CODE", "NAME"],
						"modelName"		=> "RptReport");
	
	$tmWorkflow		= array("name"		=>"TmWorkflow",
							"id"		=> "cboSelectWorkflows",
							"enableTitle"=>false,
							"modelName"	=> "TmWorkflow");
	
	$network		= array("name"		=>"Network",
							"enableTitle"=>false,
							"id"		=> "cboSelectNetworkModels",
							"modelName"	=> "NetWork",
                            "getMethod" => "getDataWithNetworkType",
                            "filterData"=> 2);
	
	$sqlList		= array("name"		=> "SqlList",
							"enableTitle"=>false,
							"id"		=> "cboSelectDataViews",
							"modelName"	=> "SqlList",
							"getMethod" => "getCommonSql");
	
	$storageDisplayChart		= array("name"		=> "StorageDisplayChart",
							"id"		=> "cboSelectStorageDisplay",
							"enableTitle"=>false,
							"modelName"	=> "StorageDisplayChart",
							"getMethod" => "all",
							"filterData"=>['ID', 'TITLE as NAME']);
	
	$constraintDiagram		= array("name"		=> "ConstraintDiagram",
									"enableTitle"=>false,
									"id"		=> "cboSelectCons",
									"modelName"	=> "ConstraintDiagram");
	
	$dashboardGroup		= array("name"		=> "DashboardGroup",
									"enableTitle"=>false,
									"id"		=> "cboDashboardGroup",
							//"getMethod" => "all", 
							//"filterData"=>['ID', 'NAME'],
								"default" => ['ID'=>null, 'NAME'=>'(None)'],
									"modelName"	=> "DashboardGroup");
	
?>

@extends('front.dashboard')
@section('funtionName')
Dashboard Config
@stop

@section('script')
@parent
 	<link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
	<script src="/ckeditor/ckeditor.js"></script>
 	<script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>
@stop

@section('content')
<style type="text/css">
.contentContainer{
	min-height	: 500px;
}
div.container{
	width:150px;
	height:150px;
	padding:5px;
	background-color:#e0e0e0;
	position:absolute;
	top:150px;
	left:300px;
	border:1px solid #888888;
	overflow:hidden;
}
div.container span.title{
	display:block;
	position:absolute;
	border:0px solid #888888;
	background:#bbbbbb;
	opacity:0.8;
	padding:2px;
	cursor:pointer;
}
._colorpicker{cursor:pointer}
.ui-menu { width: 180px;padding:5px }
.ui-menu li{cursor:pointer;height:26px;line-height:26px;}
._colorpicker{border:1px solid #bbbbbb;cursor:pointer;margin:2px;width:50px}
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
<div id="boxSetBackground" style="display:none">
<p style="display:none" id="colorpickerHolder"></p>
</div>
<div id="boxImpLog" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:2">
	<div style="position: absolute;box-sizing: border-box;box-shadow: 0px 5px 30px rgba(0,0,0,0.5);left:10%;top:10%;padding:15px; width: 80%; height: 80%; z-index: 1; border:1px solid #999999; background:white">
	<input type="button" value="Close" onclick="$('#boxImpLog').fadeOut()" style="position:absolute;width:80px;height:30px;right:0px;top:-30px">
	<div id="logContent" style="width:100%;height:100%;overflow:auto">
	</div>
	</div>
</div>
<div id="filter_box" style="height:40px;background:#e0e0e0;padding-top:6px;box-sizing:border-box">
	<button style="width:100px;height:28px;" onclick="loaddashboards(true)">Load</button>
	<button style="width:100px;height:28px;" onclick="newdashboard()">New</button>
	<button style="width:100px;height:28px;" onclick="showSaveDialog()">Save</button>
	<button style="width:100px;height:28px;" onclick="$('#morefuncs').toggle()" id="button_morefuncs">More...</button>
	<button style="width:100px;height:28px;margin-left:20px" onclick="add_frame()">Add Frame</button>
	<div id="dashboard_name" style="position:relative;float: right;margin-right:10px;">Dashboard name</div>
	<div id="show_dashboard" style="position:relative;float: right;margin-right:10px;display:none;clear: both;">
	<a href="#" onclick="showDashboard()">
		Display dashboard
	</a>
	</div>
</div>
<ul id="morefuncs" style="display:none;position:absolute;left:622px;z-index:100">
  <li><div onclick="rename()">Rename</div></li>
  <li><div onclick="set_default()">Set Default</div></li>
  <li><div onclick="$('#colorpicker_0').click()">Set Background <input type="text" maxlength="6" size="6" style="background:transparent;color:transparent;" class="_colorpicker" id="colorpicker_0" value=""></div></li>
  <li style="display:none"><div onclick="$('#html5colorpicker').click();return;setbackground();" style="cursor:normal"><input type="color" id="html5colorpicker" style="float:right;width:50px;height:26px;border:none;background:none;padding:0px;cursor:pointer" onchange="clickColor(0, -1, -1, 5)" value="#ff0000">Set Background </div></li>
  <li><div onclick="deletedashboard()">Delete</div></li>
</ul>
<div id="boxDashboardList" style="display:none">
</div>

<div id="boxSelect" style="z-index:1000;display:none;position:fixed;top:0px;left:0px;width:100%;height:100%;background:rgba(0,0,0,0.55)">
<div style="position:absolute;top:50%;left:50%;width:800px;height:560px;margin-left:-400px;margin-top:-280px;background:white;border:1px solid #888888">
<div style="border-bottom:1px solid #888888;background:#eeeeee;height:30px;line-height:30px;font-size:10pt;padding-left:10px">
<b>Edit frame</b>
</div>
<div style="padding:10px;width:100%;">
<div style="position:absolute;width:200px;right:110px;height:30px;top:40px">Background: 
	<input type="text" maxlength="6" size="6" style="background:transparent;color:transparent;width:60px" class="_colorpicker" id="colorpicker_frame" value="">
</div>
<button style="position:absolute;width:100px;right:10px;height:30px;top:40px" onclick="okEdit()">OK</button>
<button style="position:absolute;width:100px;right:10px;height:30px;top:80px" onclick="$('#boxSelect').hide()">Cancel</button>
<!--
Title <input id="txt_title" type="text" style="width:calc(100% - 50px)" value=""><br><br> -->
Content type: <br>
<table>
<tbody>
	<tr>
		<td><input type="radio" name="frame_type" value="1"> Chart</td>
		<td>{{\Helper::filter($advChart)}}</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="2"> Workflow </td>
		<td>{{\Helper::filter($tmWorkflow)}}</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="3"> Report</td>
		<td>{{\Helper::filter($report)}}</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="5"> Network Model</td>
		<td>
			{{\Helper::filter($network)}}
			<select name="DATAREFRESH" id="cboSelectDataRefresh">
				<option value="-1">Manual</option>
				<option value="0">Once on loading</option>
				<option value="10">10 seconds</option>
				<option value="20">20 seconds</option>
				<option value="30">30 seconds</option>
				<option value="60">1 minute</option>
				<option value="120">2 minutes</option>
				<option value="300">5 minutes</option>
				<option value="600">10 minutes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="6"> Data View </td>
		<td>{{\Helper::filter($sqlList)}}</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="7"> Storage Display</td>
		<td>{{\Helper::filter($storageDisplayChart)}}</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="8"> Constraint </td>
		<td>{{\Helper::filter($constraintDiagram)}}</td>
	</tr>
	<tr>
		<td><input type="radio" name="frame_type" value="4"> Text <input id="txt_title" type="text" style="display:none; width:calc(100% - 60px)" value=""></td>
	</tr>
</tbody>
</table>
<div id="kkkkkkkkk" style="display:none">
            <textarea style="display:" name="editor1" id="editor1">
            </textarea>
</div>
</div>
</div>
</div>

<div id="frmSave" style="display: none;">
	<div class="form-group">
		<input type="hidden" id="txt_id" value=''> <label
			class='col-md-2 control-label'>Name</label>
		<div class="col-md-10">
			<input type="text" class="form-control" id="txt_name" style="width:450px">
		</div>
	</div>
	<div class="form-group" style="margin-top:5px">
		<label class='col-md-2 control-label'>Group</label>
		<div class="col-md-10">
			{{\Helper::filter($dashboardGroup)}}
		</div>
	</div>
</div>
<script>
notCachedList	= true;
function setColorPicker(){
	$('._colorpicker').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).css({"background":"#"+hex,"color":"#"+hex});
			$('body').css({"background":"#"+hex});
			$(el).ColorPickerHide();
			$( "#morefuncs" ).hide();
		},
		onHide:function(){$( "#morefuncs" ).hide();},
		onBeforeShow: function () {
			$('.colorpicker').css("z-index",100000);
			$(this).ColorPickerSetColor(this.value);
		}
	});
}
function deletedashboard(){
	if(confirm("Are you sure you want to delete this dashboard?")){

		var postData	= {
							tabTable : "Dashboard",
							deleteData	: {Dashboard	:	[dashboard_id]}
						};
		 ;
		//_alert(config);
		postRequest(
		             "/dashboard/save",postData
		            ,
		             function(data){
		            	 alert("Dashboard was deleted successfully");
						newdashboard();
					 }
		          );
	}
}
function rename(){
	var name=inputName();
	if(name!=""){
		if(dashboard_id>0){
			var postData	= {
					tabTable : "Dashboard",
					editedData	: {Dashboard	:	[{ID				: dashboard_id,
											       	 NAME				: name,
							       					}]
							}
						};
			 saveDashBoard(postData);
		}
	}
}
function newdashboard(){
	$(".container").remove();
	updateName("");
	dashboard_id=0;
	$('body').css("background-color","white");
}
function set_default(){
	var postData	= {
			tabTable : "Dashboard",
			editedData	: {Dashboard	:	[{	ID				: dashboard_id,
												IS_DEFAULT		: 1,
												USER_NAME		: username,
					       					}]
					}
				};
	 saveDashBoard(postData);
}
function setbackground(){
	$("#boxSetBackground").dialog({
					height: 350,
					width: 500,
					modal: true,
					title: "Dashboard Background Color",
					buttons: {
						"OK": function(){
							$('body').css('backgroundColor', $('#boxSetBackground').css('backgroundColor'));
							$("#boxSetBackground").dialog("close");
						},
						"Cancel": function(){
							$("#boxSetBackground").dialog("close");
						}
					}
				});
}
var dashboard_id=0;
var dashboard_name="";
function load_dash_board(obj){
	dashboard_id=Number($(obj).attr('dashboard_id'));
	group_id=$(obj).attr('group_id');
	if(dashboard_id>0) $("#show_dashboard").css("display","block");
	else $("#show_dashboard").css("display","none");

	var d_bg=$(obj).attr('d_bg');
	if(d_bg!=null && d_bg!="")
		$('body').css("background-color",d_bg);
	else
		$('body').css("background-color","white");

	$("#boxDashboardList").dialog("close");
	$(".container").remove();
	$("#cboDashboardGroup").val(group_id);
	updateName($(obj).text());
	var cf=JSON.parse($(obj).attr('config'));
	for(var i=0;i<cf.length;i++){
		create_container(cf[i]);
	}
}
function load_dash_board_info(d_config,d_id,d_name,d_group_id,d_bg){
	dashboard_id=d_id;
	$("#cboDashboardGroup").val(d_group_id);
	if(d_bg!=null && d_bg!=undefined) $('body').css("background-color",d_bg);
	$(".container").remove();
	updateName(d_name);
	var cf=JSON.parse(d_config);
	for(var i=0;i<cf.length;i++){
		create_container(cf[i]);
	}
}
function updateBoxContent(o){
	var html="<button onclick='edit_frame(this)'>Edit</button><button onclick='delete_frame(this)'>Delete</button><br><br>Type: "+arr_types[Number($(o).attr("d_type"))]+"<br>Content: "+getObjContent(Number($(o).attr("d_type")),$(o).attr("d_obj"),$(o).attr("d_ref"));
	$(o).html(html);
}
function okEdit(){
	var stype=$('input[type=radio][name=frame_type]:checked').val();
	$cur_box.attr("d_type",stype);
	$cur_box.attr("d_obj",getObjValue(stype));
    $cur_box.attr("d_ref",getRefreshValue(stype));
	updateBoxContent($cur_box);
	$("#boxSelect").hide();
}
var $cur_box;
function edit_frame(o){
	//var config=JSON.parse($(o).parent().attr('config'));
	var $box=$(o).parent();
	$cur_box=$box;
	$("input[name=frame_type][value=" + $box.attr("d_type") + "]").prop('checked', true);
	//$('input[type=radio][name=frame_type]').change();
	updateRadio($box.attr("d_type"));
	$("#boxSelect").show();
	/*
	$("#boxSelect").dialog({
					height: 450,
					width: 800,
					modal: true,
					title: "Edit frame",
					zIndex : -1,
					buttons: {
						"OK": function(){
							var stype=$('input[type=radio][name=frame_type]:checked').val();
							$box.attr("d_type",stype);
							$box.attr("d_obj",getObjValue(stype));
							updateBoxContent($box);
							$("#boxSelect").dialog("close");
						},
						"Cancel": function(){
							$("#boxSelect").dialog("close");
						}
					}

				});
	*/
}
function add_frame(){
	var config={"size":[400,300],"pos":[300,300],"title":"","type":"0","obj":"","refresh":"","background":""};
	create_container(config);
}
var arr_types=["","Chart","Workflow","Report","Text","Network Model","Data View","Storage Display","Constraint"];
function create_container(config){
	var html="";//"<button onclick='edit_frame(this)'>Edit</button><button onclick='delete_frame(this)'>Delete</button><br>Title: "+config.title+"<br>Type: "+arr_types[Number(config.type)]+"<br>Content: "+getObjContent(Number(config.type),config.obj);
	var $box = $( "<div d_title='"+config.title+"' d_type='"+config.type+"' d_obj='"+config.obj+"' d_ref='"+config.refresh+"' class='container'>"+html+"</div>" );
	$box.css("left",config.pos[0]);
	$box.css("top",config.pos[1]);
	$box.css("width",config.size[0]);
	$box.css("height",config.size[1]);
	$box.css("background",config.background?config.background:"");
	$("body").append($box);
	updateBoxContent($box);
	$box.resizable({
	  handles: "n, e, s, w"
	}).draggable();
}
function delete_frame(o){
	if(confirm("Are you sure you want to delete this frame?")){
		$(o).parent().remove();
	}
}
function getObjContent(type,obj,ref){
	ref = ref===undefined?null:ref;
	var s="";
	var v="";
	if(type==1){
		s=$("#cboSelectCharts option[value='"+obj+"']").text();
	}
	else if(type==2){
		s=$("#cboSelectWorkflows option[value='"+obj+"']").text();
	}
	else if(type==3){
		s=$("#cboSelectReports option[name='"+obj+"']").text();
	}
	else if(type==5){
		s=$("#cboSelectNetworkModels option[value='"+obj+"']").text();
		v=$("#cboSelectDataRefresh option[value='"+ref+"']").text();
	}
	else if(type==6){
		s=$("#cboSelectDataViews option[value='"+obj+"']").text();
	}
	else if(type==7){
		s=$("#cboSelectStorageDisplay option[value='"+obj+"']").text();
	}
	else if(type==8){
		s=$("#cboSelectCons option[value='"+obj+"']").text();
	}
	else if(type==4){
		s=Base64.decode(obj);
	}
    var sum = (type==5) ? s +", "+ v : s;
	return sum;
}
function getObjValue(type){
	var s="";
	if(type==1){
		s=$("#cboSelectCharts").val();
	}
	else if(type==2){
		s=$("#cboSelectWorkflows").val();
	}
	else if(type==3){
		s=$("#cboSelectReports option:selected").attr("name");
		// s=$("#cboSelectReports").val();
	}
	else if(type==5){
		s=$("#cboSelectNetworkModels").val();
	}
	else if(type==6){
		s=$("#cboSelectDataViews").val();
	}
	else if(type==7){
		s=$("#cboSelectStorageDisplay").val();
	}
	else if(type==8){
		s=$("#cboSelectCons").val();
	}
	else if(type==4){
		//s=$("#txt_title").val();
		s=CKEDITOR.instances.editor1.getData();
		s=Base64.encode(s);
	}
	return s;
}
function getRefreshValue(type){
    var s = "";
    if(type==5){
        s=$("#cboSelectDataRefresh").val();
    }
    return s;
}
function inputName(){
	var s = prompt("Please enter dashboard name", dashboard_name);
	if (s == null) {
		s="";
	}
	return s;
}
function updateName(s){
	dashboard_name=s;
	$("#txt_name").val(dashboard_name);
	$("#dashboard_name").text(dashboard_name);
}
function showSaveDialog(){
	$( "#frmSave" ).dialog({
		height: 270,
		width: 500,
		modal: true,
		title: "Save dashboad",
		buttons: {
			Save: function() {
				save(0);
				$( this ).dialog( "close" );
			},
			"Save as": function() {
				save(1);
				$( this ).dialog( "close" );
			},
			Close: function() {
				$( this ).dialog( "close" );
			}
		},
	});	
}
function save(save_as){
	var name=$("#txt_name").val();
	if(name==""){
		alert("Please input dashboard name");
		$("#txt_name").focus();
		return;
	} 
	var dashboardId	= dashboard_id;
	var editedData	= {	BACKGROUND		: $('body').css("background-color"),};
	if(dashboard_id<=0 || save_as==1){
		dashboardId			= "NEW_RECORD_DT_RowId_1000";
		editedData.isAdding	= true;
	}
	var config="";
	$(".container").each(function(){
		var d_obj=$(this).attr("d_obj");
		var d_ref=$(this).attr("d_ref");
		//if($(this).attr("d_type")=="4")
		//	d_obj=Base64.encode(d_obj);
		config+=(config==""?"":",")+'{"size":['+$(this).width()+','+$(this).height()+'],"pos":['+$(this).position().left+','+$(this).position().top+'],"title":"'+$(this).attr("d_title")+'","type":"'+$(this).attr("d_type")+'","obj":"'+d_obj+'","refresh":"'+d_ref+'"}';
	});
	config="["+config+"]";
	editedData.ID		= dashboardId;
	editedData.NAME		= name;
	editedData.DASHBOARD_GROUP_ID = $("#cboDashboardGroup").val();
	editedData.CONFIG	= config;

	var postData	= {
						tabTable : "Dashboard",
						editedData	: { Dashboard	:	[editedData] }
					};
	 ;
	 saveDashBoard(postData);
}

function saveDashBoard(postData){
	postRequest("/dashboard/save",postData,function(data){
		 if(data.resultTransaction !== undefined && data.resultTransaction.ids !== undefined && data.resultTransaction.ids.Dashboard  !== undefined
				 && data.resultTransaction.ids.Dashboard.length > 0 ){
			data.resultTransaction.ids.Dashboard[0];
			dashboard_id	= data.resultTransaction.ids.Dashboard[0];
			dashboard_name = data.postData.editedData.Dashboard[0].NAME;
			if(dashboard_name!==undefined)
				updateName(dashboard_name);
			alert("Data saved successfully");
		 }
		 else
			 alert("Data saved successfully but could not get Dashboard Id ");
		}
	);
}

function updateRadio(s){
	$("#cboSelectCharts").hide();
	$("#cboSelectWorkflows").hide();
	$("#cboSelectReports").hide();
	$("#cboSelectNetworkModels").hide();
	$("#cboSelectDataViews").hide();
	$("#cboSelectStorageDisplay").hide();
	$("#cboSelectCons").hide();
    $("#cboSelectDataRefresh").hide();
	//$("#txt_title").hide();
	$("#kkkkkkkkk").hide();
	if (s == '1') {
		$("#cboSelectCharts").val($cur_box.attr("d_obj"));
		$("#cboSelectCharts").show();
	}
	else if (s == '2') {
		$("#cboSelectWorkflows").val($cur_box.attr("d_obj"));
		$("#cboSelectWorkflows").show();
	}
	else if (s == '3') {
		// $("#cboSelectReports").val($cur_box.attr("d_obj"));
		$("#cboSelectReports option[name='"+$cur_box.attr("d_obj")+"']").prop('selected', true);
		$("#cboSelectReports").show();
	}
	else if (s == '4') {
		$("#kkkkkkkkk").show();
		if($cur_box.attr("d_type")=="4")
			CKEDITOR.instances.editor1.setData(Base64.decode($cur_box.attr("d_obj")));
		else
			CKEDITOR.instances.editor1.setData("");
	}
	else if (s == '5') {
	    var refresh = $cur_box.attr("d_ref");
		$("#cboSelectNetworkModels").val($cur_box.attr("d_obj"));
		$("#cboSelectNetworkModels").show();
		(refresh != '') ? $("#cboSelectDataRefresh").val(refresh) : $("#cboSelectDataRefresh").val('-1');
        $("#cboSelectDataRefresh").show();
	}
	else if (s == '6') {
		$("#cboSelectDataViews").val($cur_box.attr("d_obj"));
		$("#cboSelectDataViews").show();
	}
	else if (s == '7') {
		$("#cboSelectStorageDisplay").val($cur_box.attr("d_obj"));
		$("#cboSelectStorageDisplay").show();
	}
	else if (s == '8') {
		$("#cboSelectCons").val($cur_box.attr("d_obj"));
		$("#cboSelectCons").show();
	}
}
$('input[type=radio][name=frame_type]').change(function() {
	updateRadio(this.value);
});

function showDashboard(){
	if(dashboard_id>0){
		window.location.href="/dashboard?id="+dashboard_id;
	}
	else{
		alert("Please load dashboad first!");
	}
}
var update_content_pending=false;
$(function() {
<?php
	if($dashboard_id>0&&$dashboard_row){
		?>
		var configdb = <?php echo json_encode($dashboard_row->CONFIG); ?>
		
		load_dash_board_info(configdb,'{{$dashboard_id}}','{{$dashboard_row->NAME}}','{{$dashboard_row->DASHBOARD_GROUP_ID}}','{{$dashboard_row->BACKGROUND}}');	
		update_content_pending=true;
		$("#show_dashboard").css("display","block");
		<?php
// 		echo "load_dash_board_info('$dashboard_row->CONFIG',$dashboard_id,\"$dashboard_row->NAME\",\"$dashboard_row->BACKGROUND\"); update_content_pending=true;";
	}
?>
	CKEDITOR.config.width=780;
	CKEDITOR.config.height=155;
	CKEDITOR.replace( 'editor1' );
	
	setColorPicker();
 	$("#morefuncs").css({"left":$("#button_morefuncs").position().left,"top":$("#button_morefuncs").position().top+28});
	$( "#morefuncs" ).menu();
	$( "#morefuncs" ).click(function(){$( "#morefuncs" ).hide();});
	//reload();
	$('#ebfilter').css('display','none');
	
});

</script>

@stop
