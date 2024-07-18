var current_edit_job=-1;
function checkData()
{
	if(!$("#cboFacility").val())
	{
		alert("Please select facility");
		return false;
	}
	if(!$("#cboCEList").val())
	{
		alert("Please select calculation engine");
		return false;
	}
	return true;
}

function validateData()
{
	if(!checkData()) return;
	postRequest( 
             "validate_alloc_input.php",
             {facility_id:$("#cboFacility").val(),ce_id:$("#cboCEList").val(),date_begin:$('#date_begin').val(),date_end:$('#date_end').val()},
             function(data) {
             	$("#ret").html(data);
             }
          );
}

function AddJob()
{
	if($("#txtJobName").val().trim()=="")
	{
		alert("Please input job name");
		return;
	}
	postRequest( 
	             "index.php?act=addjob",
	             {
					 "job_name":$("#txtJobName").val(),
					 "value_type":$("#cboAllocValueType").val(),
					 "network_id":$('#cboNetworks').val(),
					 "alloc_daybyday":Number($("#chk_daybyday").prop("checked")),
					 "alloc_oil": Number($("#chk_oil").prop("checked")),
					 "alloc_gas": Number($("#chk_gas").prop("checked")),
					 "alloc_water": Number($("#chk_water").prop("checked")),
					 "alloc_gaslift": Number($("#chk_gaslift").prop("checked")),
					 "alloc_condensate": Number($("#chk_condensate").prop("checked")),
					 "alloc_comp": Number($("#chk_comp").prop("checked"))
				 },
	             function(data) {
					hide_edit_job(); 
	             	$('#cboNetworks').change();
	             }
	          );

	$("#txtJobName").val('');
	$(".chk_box :checked").attr("checked", false);

}

function deleteJob(job_id)
{
	if(!confirm("Are you sure to delete this job?")) return;
	postRequest( 
	             "index.php?act=deletejob",
	             {"job_id":job_id},
	             function(data) {
	             	$('#cboNetworks').change();
	             }
	          );
}
function editJob(job_id)
{
	current_edit_job=job_id;
	var name=$("#QjobName_"+job_id).text();
	
	$("#txtJobName").val(name);
	var type=$("#Qavt_"+job_id).attr('value');
	$("#cboAllocValueType").val(type);
	$("#txtJobName").focus().select();
//Check for checkboxs
	var phase=$("#Qallocphase_"+job_id).text();
	var t,t_gl; //temp variant
	$("#chk_daybyday").attr("checked", $("#Qrowjob_"+job_id).attr("daybyday")==1);
	if(phase.indexOf("Oil")>-1) t=true; else t=false; $("#chk_oil").attr("checked", t)
	if(phase.indexOf("Gas-lift")>-1) t_gl=true; else t_gl=false; $("#chk_gaslift").attr("checked", t_gl);	
	if(t_gl)
	{
		if(phase.indexOf("Gas,")>-1) t=true; else t=false; $("#chk_gas").attr("checked", t);	
	}
	else
		if(phase.indexOf("Gas")>-1) t=true; else t=false; $("#chk_gas").attr("checked", t);	

	if(phase.indexOf("Water")>-1) t=true; else t=false; $("#chk_water").attr("checked", t);	
	if(phase.indexOf("Condensate")>-1) t=true; else t=false; $("#chk_condensate").attr("checked", t);	
	if(phase.indexOf("Comp")>-1) t=true; else t=false; $("#chk_comp").attr("checked", t);
	
	$("#chk_gas").change();
	$("#QAddJob").css("display", "none");
	$("#QSaveJobEdit").css("display", "inline");
	
	$("#QsaveEdit").attr("href", "javascript:saveEdit("+job_id+")");
	showEditJob();
}
function cancelEdit(){
//	current_edit_job=-1;
	$("#txtJobName").val('');
	$("#cboAllocValueType option:selected").prop("selected", false);
	$("#QAddJob").css("display", "inline");
	$("#QSaveJobEdit").css("display", "none");
	$(".chk_box :checked").attr("checked", false);
	$("#chk_gas").change();
	
	$("#bodyJobsList tr").removeClass("current_edit_job");
}
function saveEdit(job_id){
	var clone=0;
	if(job_id==-1) 
	{
		clone=1;
	}
	var name=$("#txtJobName").val();
	var value_type=$("#cboAllocValueType").val();
	postRequest(
				"index.php?act=editJob",
				{
					"id":current_job_id,
					"clone":clone,
					"name":name,
					"value_type":value_type,
					"alloc_daybyday":Number($("#chk_daybyday").prop("checked")),
					"alloc_oil": Number($("#chk_oil").prop("checked")),
					"alloc_gas": Number($("#chk_gas").prop("checked")),
					"alloc_water": Number($("#chk_water").prop("checked")),
					"alloc_gaslift": Number($("#chk_gaslift").prop("checked")),
					"alloc_condensate": Number($("#chk_condensate").prop("checked")),
					"alloc_comp": Number($("#chk_comp").prop("checked"))
				},
				function(data){
					if(data!='ok')
						alert(data);
					hide_edit_job();
					$('#cboNetworks').change();
				}
				);
	cancelEdit();
	$(".chk_box :checked").prop("checked", false);
}

var current_job_id, current_job_name;
function showJobDiagram()
{
	if(current_job_id>0)
	{
		$("#iframe_ceflow").attr('src',"../ceflow/jobdiagram.php?job_id="+current_job_id);	
	}
	$("#diagram_box").dialog({
		width: 1060,
		height: 520,
		modal: true,
		title: "Job diagram"});
}

function loadRunnersList(job_id, job_name, not_reload_runners)
{
	if(not_reload_runners==true){
		if(job_id==current_job_id) return;
	}
	current_job_id=job_id;
	current_job_name=job_name;
	
	$("#bodyJobsList tr").removeClass("current_job");
	$("#Qrowjob_"+job_id).addClass("current_job");
	
    $('#bodyRunnersList').html('');
    $('#current_job_name').html(job_name);
	
	if(job_id<=0) return;
	postRequest( 
	             "index.php?act=getrunnerslist",
	             {"job_id":job_id},
	             function(data) {
					var ss=data.split("#$%");
	             	$('#bodyRunnersList').html(ss[0]);
					$("#cond_from_runner").html(ss[1]);
					$("#cond_to_runner").html(ss[1]);
					defaultBoxAddRunner();
					showRunnersList();
	             }
	          );
	cancelEdit();
}
function loadConditionsList(job_id)	
{
	current_job_id=job_id;
	
    $('#bodyConditionsList').html('');
	
	if(job_id<=0) return;
	postRequest( 
	             "index.php?act=getconditionslist",
	             {"job_id":job_id},
	             function(data) {
	             	$('#bodyConditionsList').html(data);
	             }
	          );
}
function clearAllocData()
{
	
}
function defaultBoxAddRunner()
{
	$("#Qaction").text('Add Runner');
	//$("#boxAddRunner").hide();
	$("#objsFrom").html('');
	$("#objsTo").html('');
	$("#cboObjects option:selected").prop("selected", false)
}
function showAddRunner()
{
	vRunnerFrom="";
	vRunnerTo="";
	$("#objsFrom").html("");
	$("#objsTo").html("");
	$("#txtRunnerOrder").val('');
	$("#txtRunnerName").val('');
	$('#boxAddRunner').show();
	$('html,body').animate({scrollTop: $(document).height()}, 600);
	
	$("#QsaveRunnerEdit").css("display", "none");
	$("#QsaveRunnerCopy").css("display", "none");
	$("#QaddRunner").css("display", "");

	$("#Qinsertnew").css("display", "");
	$("#Qinsertedit").css("display", "none");
	$("#Qaction").text('Add Runner');
	
	show_edit_runner();
}
function editRunner(runner_id)
{	
	$("#QsaveRunnerEdit").css("display", "");
	$("#QsaveRunnerCopy").css("display", "");
	$("#QaddRunner").css("display", "none");
	
	$("#txtRunnerName").val($("#Qrunner_name_"+runner_id).text());
	$("#txtRunnerOrder").val($("#Qorder_"+runner_id).text());
	$("#cboRunnerAllocType").val($("#alloc_type_"+runner_id).text());
	$("#cboTheorPhase").val($("#theor_phase_"+runner_id).text());
	$("#cboTheorValueType").val($("#theor_value_type_"+runner_id).text());
	

	$("#objsFrom").html($("#Qobjectfrom_"+runner_id).html());
		$("#objsFrom span").append("<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span>");

	$("#objsTo").html($("#Qobjectto_"+runner_id).html());
		$("#objsTo span").append("<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span>");
//Get value fixed
		var fix="";
		$("#objsTo span[o_id]").each(function(){
			fix=($(this).attr("fixed")==1? "checked": "");
			$(this).prepend("<input type='checkbox' "+fix+" class='chk_fixed'>");
			$(this).html($(this).html().replace("[fixed]",""));
		});
//Get value minus
		var minus="";
		$("#objsFrom span[o_id]").each(function(){
			minus=($(this).attr("minus")==1? "checked": "");
			$(this).prepend("<input type='checkbox' "+minus+" class='chk_fixed'>");
			$(this).html($(this).html().replace('[-] ',''));
		});
/*
		var fix="";
		alert(object.attr("fixed"));
		object.prepend("");
*/
	$("#Qinsertnew").css("display", "none");
	$("#Qinsertedit").css("display", "");
	
	$("#QsaveRunnerEdit").attr("onclick", "saveRunnerEdit("+runner_id+")");
	$("#QsaveRunnerCopy").attr("onclick", "saveRunnerEdit(-1)");
	$("#Qaction").text('Edit Runner');
	show_edit_runner();
}
function show_edit_runner(){
	$("#addRunner_box").dialog({
			width: 700,
			height: 480,
			modal: true,
			title: "Edit runner"});
}
function cancelAddRunner()
{
	$("#addRunner_box").dialog("close");
}

function addRunner()
{
	if($("#txtRunnerOrder").val().trim()=="")
	{
		alert("Please input runner order");
		return;
	}

//Edited by Q, get data from objto, skip vRunnerTo
	var toObject="";
	$("#objsTo span[o_id]").each(function(){
		var fx=Number($(this).find(":checkbox").is(":checked"));
		toObject+=(toObject==""?"":",")+$(this).attr("o_id")+":"+$(this).attr("o_type")+":"+fx;
	})
//alert("toObject: "+toObject +"/nvRunnerTo: "+vRunnerTo);
//return;
	postRequest( 
	             "index.php?act=addrunner",
	             {"job_id":current_job_id,"order":$('#txtRunnerOrder').val(),"alloc_type":$("#cboRunnerAllocType").val(),"theor_phase":$("#cboTheorPhase").val(),"theor_value_type":$("#cboTheorValueType").val(),"obj_from":vRunnerFrom,"obj_to":toObject},
	             function(data) {
					//$('#boxAddRunner').hide();
					$("#edit_close").trigger("click");
	             	loadRunnersList(current_job_id, current_job_name);
	             }
	          );	
}

var vRunnerFrom,vRunnerTo;
function addRunnerFrom()
{
	var o_type=$("#cboObjType").val();
	var o_id=$("#cboObjects").val();
if(o_id==null) return;
if($("#objsFrom span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1 || $("#objsTo span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1) return;
	var o_name=$("#cboObjects option:selected").text();
	var object="<span id='' o_type='"+o_type+"' o_id='"+o_id+"' style='display:block'>"+o_name+"<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsFrom").append(object);

	vRunnerFrom += (vRunnerFrom==""?"":",")+$("#cboObjects").val()+":"+$("#cboObjType").val();
}
function addRunnerTo()
{
	var o_type=$("#cboObjType").val();
	var o_id=$("#cboObjects").val();
if(o_id==null) return;
if($("#objsFrom span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1 || $("#objsTo span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1) return;
	var o_name=$("#cboObjects option:selected").text();
	var object="<span id='' o_type='"+o_type+"' o_id='"+o_id+"' style='display:block'><input type='checkbox' class='chk_fixed'>"+o_name+"<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsTo").append(object);
	
	vRunnerTo += (vRunnerTo==""?"":",")+$("#cboObjects").val()+":"+$("#cboObjType").val();
}
function editAddRunnerFrom(){
	//o_type = object type
	var o_type=$("#cboObjType").val();
	var o_id=$("#cboObjects").val();
if(o_id==null) return;
if($("#objsFrom span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1 || $("#objsTo span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1) return;
	var o_name=$("#cboObjects option:selected").text();
	var object="<span id='' o_type='"+o_type+"' o_id='"+o_id+"' style='display:block'><input type='checkbox' class='chk_fixed'>"+o_name+"<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsFrom").append(object);
}
function editAddRunnerTo(){
	//o_type = object type
	var o_type=$("#cboObjType").val();
	var o_id=$("#cboObjects").val();
if(o_id==null) return;
if($("#objsFrom span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1 || $("#objsTo span[o_type='"+o_type+"'][o_id='"+o_id+"']").length==1) return;
	var o_name=$("#cboObjects option:selected").text();
	var object="<span id='' o_type='"+o_type+"' o_id='"+o_id+"' style='display:block'><input type='checkbox' class='chk_fixed'>"+o_name+"<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsTo").append(object);
}
function saveRunnerEdit(runner_id){
	if($("#txtRunnerOrder").val().trim()=="")
	{
		alert("Please input runner order");
		return;
	}
	
	var fromObject="";
	$("#objsFrom span[o_id]").each(function(){
		var fx=Number($(this).find(":checkbox").is(":checked"));
		fromObject+=(fromObject==""?"":",")+$(this).attr("o_id")+":"+$(this).attr("o_type")+":"+fx;
	})
	var toObject="";
	$("#objsTo span[o_id]").each(function(){
		var tx=Number($(this).find(":checkbox").is(":checked"));
		toObject+=(toObject==""?"":",")+$(this).attr("o_id")+":"+$(this).attr("o_type")+":"+tx;
	})

	postRequest(
				"index.php?act="+(runner_id<0?"addrunner":"saveEditRunner"),
				{
					"job_id":current_job_id,
					"runner_id":runner_id,
					"order": $("#txtRunnerOrder").val(),
					"runner_name": $("#txtRunnerName").val(),
					"alloc_type": $("#cboRunnerAllocType").val(),
					"theor_phase":$("#cboTheorPhase").val(),
					"theor_value_type":$("#cboTheorValueType").val(),
					"obj_from": fromObject,
					"obj_to": toObject
				},
				function(data){
					if(data!='ok')
						alert(data);
					else{
						alert("Saved successfully");
					}
					loadRunnersList(current_job_id, current_job_name);
					//$('#boxAddRunner').hide();
					$("#edit_close").trigger("click");
				}
				);
}
function deleteRunner(runner_id)
{
	if(!confirm("Are you sure to delete this runner?")) return;
	postRequest( 
	             "index.php?act=deleterunner",
	             {"runner_id":runner_id},
	             function(data) {
	             	loadRunnersList(current_job_id, current_job_name);
	             }
	          );

}
function removeObject(element){
	var a=$(element).parent().remove();
}
function checkAllocDate()
{
	var d1 = $("#date_begin").datepicker('getDate');
	var d2 = $("#date_end").datepicker('getDate');
	var d = new Date("January 01, 2016 00:00:00");
	if(d1<d || d2<d){
		alert("Can not run allocation for the date earlier than 01/01/2016.");
		return false;
	}
	return true;
}
function runDayJob(job_id,day){
		$("#allocLog").html("Allocation is running for "+day);
var dd=day+"";
if(day<10) dd="0"+dd;
dd="01/"+dd+"/2016";
//alert(dd);
//return;
		postRequest( 
		             "run.php",
		             {act:"run","job_id":job_id,from_date:dd,to_date:dd},
		             function(data) {
		             	$("#allocLog").html(data);
		             	alert("Allocation job completed "+day);
						if(day<31)
							setTimeout(function(){ runDayJob(job_id,day+1); }, 100);
		             }
		          );
}
function doRunJob(job_id)
{
/*
	$('#boxRunAlloc').show();
	runDayJob(job_id,1);
	return;
*/
	if(!checkAllocDate()){
		return;
	}
	$('#boxRunAlloc').show();
	if(isCheckAlloc){
		$("#allocLog").html("Allocation checking in progress...");
		postRequest( 
		             "run.php",
		             {act:"check","job_id":job_id,from_date:$("#date_begin").val(),to_date:$("#date_end").val()},
		             function(data) {
		             	$("#allocLog").html(data);
		             	alert("Checking allocation job completed");
		             }
		          );
	}	
	else{
		if(!confirm("Run allocation from date "+$("#date_begin").val()+" to date "+$("#date_end").val()+". Continue?")) return;
		$("#allocLog").html("Allocation is running...");
		postRequest( 
		             "run.php",
		             {act:"run","job_id":job_id,from_date:$("#date_begin").val(),to_date:$("#date_end").val()},
		             function(data) {
		             	$("#allocLog").html(data);
		             	alert("Allocation job completed");
		             }
		          );
	}
}
function showRunAllocDialog(){
	$("#chk_run_alloc_daybyday").attr("checked", $("#Qrowjob_"+current_job_id).attr("daybyday")==1);
	$("#boxRunAllocForm").dialog({
		width: 400,
		height: 240,
		modal: true,
		title: "Run Allocation Job"});
}
var allocRunType="";
var allocObjID="";
function runRunner(id)
{
	$("#buttonRunAlloc").val("Run Allocation");
	isCheckAlloc=false;
	allocRunType="runner";
	allocObjID=id;
	showRunAllocDialog();
}
var isCheckAlloc=false;
function runJob(id)
{
	$("#buttonRunAlloc").val("Run Allocation");
	isCheckAlloc=false;
	allocRunType="job";
	allocObjID=id;
	showRunAllocDialog();
}
function checkJob(id)
{
	$("#buttonRunAlloc").val("Simulate Allocation");
	isCheckAlloc=true;
	allocRunType="job";
	allocObjID=id;
	showRunAllocDialog();
}
function checkRunner(id)
{
	$("#buttonRunAlloc").val("Simulate Allocation");
	isCheckAlloc=true;
	allocRunType="runner";
	allocObjID=id;
	showRunAllocDialog();
}
function runAllocation()
{
	hideAllocationForm();
	if(allocRunType=="runner" && allocObjID>0)
		doRunRunner(allocObjID);
	else if(allocRunType=="job" && allocObjID>0)
		doRunJob(allocObjID);
	else
	{
		alert("Nothing to run");
	}
}
function hideAllocationForm()
{
	$("#boxRunAllocForm").dialog("close");
}
function doRunRunner(runner_id)
{
	if(!checkAllocDate()){
		return;
	}
	$('#boxRunAlloc').show();
	if(isCheckAlloc){
		$("#allocLog").html("Allocation checking in progress...");
		postRequest( 
		             "run.php",
		             {act:"check","runner_id":runner_id,from_date:$("#date_begin").val(),to_date:$("#date_end").val()},
		             function(data) {
		             	$("#allocLog").html(data);
		             	alert("Checking runner completed");
		             }
		          );
	}	
	else{
		if(!confirm("Run allocation from date "+$("#date_begin").val()+" to date "+$("#date_end").val()+". Continue?")) return;
		$("#allocLog").html("Allocation is running...");
		postRequest( 
		             "run.php",
		             {act:"run","runner_id":runner_id,from_date:$("#date_begin").val(),to_date:$("#date_end").val()},
		             function(data) {
		             	$("#allocLog").html(data);
		             	alert("Allocation runner completed");
		             }
		          );
	}
}

function hideAllocResult()
{
	$('#boxRunAlloc').hide();
}
function showAllocResult()
{
	$('#boxRunAlloc').show();
}
$('#cboNetworks').change(function(e){
    $('#bodyJobsList').html('');   // clear the existing options
	postRequest( 
	             "index.php?act=getjobslist",
	             {"network_id":$(this).val()},
	             function(data) {
	             	$('#bodyJobsList').html(data);
	             	loadRunnersList(0);
					if(current_edit_job==-1)
						$("#bodyJobsList tr").eq(0).trigger("click");
					else
						$("#bodyJobsList").find("#Qrowjob_"+current_edit_job).trigger("click");
	             }
	          );
});

$("#chk_gas").change(function(){
	var t=$(this).prop("checked");
	if (t==false) $("#chk_comp").prop("checked", false);
	t=(t? '': 'none');
	$("#chk_comp").parent().css("display", t);
})
var objTypeName=["","FLOW","ENERGY_UNIT","TANK","STORAGE","TANK"];
$('#cboObjType').change(function(e){$('#cboFacility').change();});
$('#cboFacility').change(function(e){
    $('#cboObjects').html('');   // clear the existing options
	postRequest( 
	             "../common/getcodelist.php",
	             {table:objTypeName[$("#cboObjType").val()],id:"",parent_field:"facility_id",parent_value:$(this).val()},
	             function(data) {
	             	$('#cboObjects').html(data);
	             }
	          );
});
var func_code="<?php echo $RIGHT_CODE; ?>";
$().ready(function() {
	$("#MySplitter").height($(window).height()-200);
	$("#MySplitter").splitter({
		type: "h", 
	});
});
$(function() {
	$("#pageheader").load("../home/header.php?menu=ce");
	var d = new Date();
	$("#date_begin").val(""+zeroFill(1+d.getMonth(),2)+"/01/"+d.getFullYear());
	$("#date_end").val(""+zeroFill(1+d.getMonth(),2)+"/"+zeroFill(d.getDate(),2)+"/"+d.getFullYear());
	$( "#date_begin" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});
	
	$( "#date_end" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateFormat:"mm/dd/yy"
	});
	$("#tabs").tabs();
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"FACILITY",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboFacility').html(data);
	             	$('#cboFacility').change();
	             }
	          );
	postRequest( 
	             "index.php?act=getJobGroups",
	             {},
	             function(data) {
	             	$('#cboNetworks').html(data);
	             	$('#cboNetworks').change();
	             }
	          );
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"code_alloc_value_type",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboAllocValueType').html(data);
	             }
	          );
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"code_alloc_type",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboRunnerAllocType').html(data);
	             }
	          );
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"code_flow_phase",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboTheorPhase').html("<option value=''>(No change)</option>"+data);
	             }
	          );
	postRequest( 
	             "../common/getcodelist.php",
	             {table:"code_alloc_value_type",id:"",parent_field:"",parent_value:""},
	             function(data) {
	             	$('#cboTheorValueType').html("<option value=''>(No change)</option>"+data);
	             }
	          );
	          
});