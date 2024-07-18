var current_edit_job = -1;
function checkData() {
	if (!$("#cboFacility").val()) {
		alert("Please select facility");
	}
	if (!$("#cboCEList").val()) {
		alert("Please select calculation engine");
		return false;
	}
	return true;
}

var edit_job = -1;
function AddJob() {
	if ($("#txtJobName").val().trim() == "") {
		alert("Please input job name");
		return;
	}

	param = {
		'NAME' : $("#txtJobName").val(),
		'NETWORK_ID' : $('#cboNetworks').val(),
		'VALUE_TYPE' : $("#cboAllocValueType").val(),
		'ALLOC_GAS' : Number($("#chk_gas").prop("checked")),
		'ALLOC_OIL' : Number($("#chk_oil").prop("checked")),
		'ALLOC_WATER' : Number($("#chk_water").prop("checked")),
		'ALLOC_COMP' : Number($("#chk_comp").prop("checked")),
		'ALLOC_GASLIFT' : Number($("#chk_gaslift").prop("checked")),
		'ALLOC_CONDENSATE' : Number($("#chk_condensate").prop("checked")),
		'DAY_BY_DAY' : Number($("#chk_daybyday").prop("checked")),
		'BEGIN_DATE' : $("#job_begin_date").val(),
		'END_DATE' : $("#job_end_date").val()
	}
	
	sendAjaxNotMessage('/addJob', param, function(data){
		hide_edit_job();
		$('#cboNetworks').change();
        edit_job = 0;
	});
	$("#txtJobName").val('');
	$(".chk_box :checked").attr("checked", false);
}

function deleteJob(job_id) {
	if (!confirm("Are you sure to delete this job?"))
		return;
	param = {
			"job_id" : job_id	
	}
	
	sendAjax('/deletejob', param, function(data){
		$('#cboNetworks').change();
        edit_job = -1;
	});
	
	/*postRequest("index.php?act=deletejob", {
		"job_id" : job_id
	}, function(data) {
		$('#cboNetworks').change();
	});*/
}

var id_alloc_job = 0;
function editJob(job_id) {
    id_alloc_job = job_id;
	current_edit_job = job_id;
	var name = $("#QjobName_" + job_id).text();

	$("#txtJobName").val(name);
	var type = $("#Qavt_" + job_id).attr('value');
	$("#cboAllocValueType").val(type);
	$("#txtJobName").focus().select();
	// Check for checkboxs
	var phase = $("#Qallocphase_" + job_id).text();
	var t, t_gl; // temp variant
	$("#chk_daybyday").attr("checked",
			$("#Qrowjob_" + job_id).attr("daybyday") == 1);
	if (phase.indexOf("Oil") > -1)
		t = true;
	else
		t = false;
	$("#chk_oil").attr("checked", t)
	if (phase.indexOf("Gas-lift") > -1)
		t_gl = true;
	else
		t_gl = false;
	$("#chk_gaslift").attr("checked", t_gl);
	if (t_gl) {
		if (phase.indexOf("Gas,") > -1)
			t = true;
		else
			t = false;
		$("#chk_gas").attr("checked", t);
	} else if (phase.indexOf("Gas") > -1)
		t = true;
	else
		t = false;
	$("#chk_gas").attr("checked", t);

	if (phase.indexOf("Water") > -1)
		t = true;
	else
		t = false;
	$("#chk_water").attr("checked", t);
	if (phase.indexOf("Condensate") > -1)
		t = true;
	else
		t = false;
	$("#chk_condensate").attr("checked", t);
	if (phase.indexOf("Comp") > -1)
		t = true;
	else
		t = false;
	$("#chk_comp").attr("checked", t);

	$("#chk_gas").change();
	$("#QAddJob").css("display", "none");
	$("#QSaveJobEdit").css("display", "inline");

    var begin_date = $("#Qbegindate_" + job_id).text();
    var end_date = $("#Qenddate_" + job_id).text();
	$("#job_begin_date").val(begin_date);
	$("#job_end_date").val(end_date);

	$("#QsaveEdit").attr("href", "javascript:saveEdit(" + job_id + ")");
	showEditJob();
}

function cancelEdit() {
	// current_edit_job=-1;
	$("#txtJobName").val('');
	$("#cboAllocValueType option:selected").prop("selected", false);
	$("#QAddJob").css("display", "inline");
	$("#QSaveJobEdit").css("display", "none");
	$(".chk_box :checked").attr("checked", false);
	$("#chk_gas").change();

	$("#bodyJobsList tr").removeClass("current_edit_job");
}

function saveEdit(job_id) {
	var clone = 0;
	if (job_id == -1) {
		clone = 1;
	}
	var name = $("#txtJobName").val();
	var value_type = $("#cboAllocValueType").val();
	
	param = {
			"id" : current_job_id,
			"clone" : clone,
			"name" : name,
			"value_type" : value_type,
			"alloc_daybyday" : Number($("#chk_daybyday").prop("checked")),
			"alloc_oil" : Number($("#chk_oil").prop("checked")),
			"alloc_gas" : Number($("#chk_gas").prop("checked")),
			"alloc_water" : Number($("#chk_water").prop("checked")),
			"alloc_gaslift" : Number($("#chk_gaslift").prop("checked")),
			"alloc_condensate" : Number($("#chk_condensate").prop("checked")),
			"alloc_comp" : Number($("#chk_comp").prop("checked")),
			"begin_date" : $("#job_begin_date").val(),
			"end_date" : $("#job_end_date").val()
		};
		
		sendAjax('/editJob', param, function(data){
			if (data != 'ok')
				alert(data);
			hide_edit_job();
			$('#cboNetworks').change();
            edit_job = (job_id == -1) ? 0 : id_alloc_job;
		});

	cancelEdit();
    hide_edit_job();
	$(".chk_box :checked").prop("checked", false);
}

var current_job_id, current_job_name;
function showJobDiagram() {
	if (current_job_id > 0) {
		$("#iframe_ceflow").attr('src','jobdiagram/'+current_job_id);
	}
	$("#diagram_box").dialog({
		width : 1060,
		height : 520,
		modal : true,
		title : "Job diagram"
	});
}

function loadRunnersList(job_id, job_name, not_reload_runners) {
	if (not_reload_runners == true) {
		if (job_id == current_job_id)
			return;
	}
	current_job_id = job_id;
	current_job_name = job_name;

	$("#bodyJobsList tr").removeClass("current_job");
	$("#Qrowjob_" + job_id).addClass("current_job");

	$('#bodyRunnersList').html('');
	$('#current_job_name').html(job_name);
	$("#chkSelectRunners").prop('checked', false);

	if (job_id <= 0)
		return;
	
	param = {
		'job_id' : job_id
	};
	
	sendAjax('/getrunnerslist', param, function(data){
		//console.log(data);
		if (data == null) return;
		var ss = data.split("#$%");
		$('#bodyRunnersList').html(ss[0]);
		$("#cond_from_runner").html(ss[1]);
		$("#cond_to_runner").html(ss[1]);
		defaultBoxAddRunner();
		showRunnersList();
	});
	cancelEdit();
}
function loadConditionsList(job_id) {
	current_job_id = job_id;

	$('#bodyConditionsList').html('');

	if (job_id <= 0)
		return;
	
	param = {
		'job_id' : job_id	
	}
	
	sendAjaxNotMessage('/getconditionslist', param, function(data){
		$('#bodyConditionsList').html(data);
	});
}
function clearAllocData() {

}
function defaultBoxAddRunner() {
	$("#Qaction").text('Add Runner');
	// $("#boxAddRunner").hide();
	$("#objsFrom").html('');
	$("#objsTo").html('');
	$("#cboObjects option:selected").prop("selected", false)
}
function showAddRunner() {
	vRunnerFrom = "";
	vRunnerTo = "";
    $("#cboTheorValueType option:first-child").attr('selected','selected');
    $("#cboTheorPhase option:first-child").attr('selected','selected');
    $("#runner_begin_date").val("");
    $("#runner_end_date").val("");
	$("#objsFrom").html("");
	$("#objsTo").html("");
	$("#txtRunnerOrder").val('');
	$('#chkFifo').prop('checked', false);
	$('#chkExcelAllocation').prop('checked', false);
	$("#txtRunnerName").val('');
	$('#boxAddRunner').show();
	$('html,body').animate({
		scrollTop : $(document).height()
	}, 600);

	$("#QsaveRunnerEdit").css("display", "none");
	$("#QsaveRunnerCopy").css("display", "none");
	$("#QaddRunner").css("display", "");

	$("#Qinsertnew").css("display", "");
	$("#Qinsertedit").css("display", "none");
	$("#Qaction").text('Add Runner');
	$("#chkExcelAllocation").prop('checked', false);

	show_edit_runner("add");
}
function editRunner(runner_id) {
	$("#QsaveRunnerEdit").css("display", "");
	$("#QsaveRunnerCopy").css("display", "");
	$("#QaddRunner").css("display", "none");

	$("#txtRunnerName").val($("#Qrunner_name" + runner_id).text());
	$("#txtRunnerOrder").val($("#Qorder" + runner_id).text());
	$('#chkFifo').prop('checked', $("#fifo" + runner_id).text()=='Y');
	$('#chkFifo').change();
	$("#BaAddress").val($("#QBaAddress" + runner_id).text());
	$("#cboRunnerAllocType").val($("#alloc_type" + runner_id).text());
	$("#cboTheorPhase").val($("#theor_phase" + runner_id).text());
	$("#cboTheorValueType").val($("#theor_value_type" + runner_id).text());
	$("#cboAllocFromOption").val($("#runner_item" + runner_id).data("from_option"));
	$("#cboAllocToOption").val($("#runner_item" + runner_id).data("to_option"));
	$("#run_sql").val($("#run_sql" + runner_id).text());
	var excelFile = $("#excel_template" + runner_id).text();
	$("#linkExcelFile").html(excelFile);
	$("#linkExcelFile").attr('href', "javascript:downloadFile('/downloadAllocTemplateFile/"+excelFile+"')");
	$("#chkExcelAllocation").prop('checked', $("#use_excel" + runner_id).text()=='Y');

    $("#runner_begin_date").val($("#Qbegindate" + runner_id).text());
    $("#runner_end_date").val($("#Qenddate" + runner_id).text());

	$("#objsFrom").html($("#Qobjectfrom" + runner_id).html());
	$("#objsFrom span")
			.append(
					"<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span>");

	$("#objsTo").html($("#Qobjectto" + runner_id).html());
	$("#objsTo span")
			.append(
					"<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span>");
	// Get value fixed
	var fix = "";
	$("#objsTo span[o_id]").each(
			function() {
				fix = ($(this).attr("fixed") == 1 ? "checked" : "");
				$(this)
						.prepend(
								"<input type='checkbox' " + fix
										+ " class='chk_fixed'>");
				$(this).html($(this).html().replace("[fixed]", ""));
			});
	// Get value minus
	var minus = "";
	$("#objsFrom span[o_id]").each(
			function() {
				minus = ($(this).attr("minus") == 1 ? "checked" : "");
				$(this).prepend(
						"<input type='checkbox' " + minus
								+ " class='chk_fixed'>");
				$(this).html($(this).html().replace('[-] ', ''));
			});
	/*
	 * var fix=""; alert(object.attr("fixed")); object.prepend("");
	 */
	$("#Qinsertnew").css("display", "none");
	$("#Qinsertedit").css("display", "");

	$("#QsaveRunnerEdit").attr("onclick", "saveRunnerEdit(" + runner_id + ")");
	$("#QsaveRunnerCopy").attr("onclick", "saveRunnerEdit(-1)");
	$("#Qaction").text('Edit Runner');
	show_edit_runner("edit");
}
function show_edit_runner(str) {
    var title = (str == 'add') ? "Add runner" : "Edit runner";
	$("#frmExcel")[0].reset();
	chkExcelChanged();
	$("#addRunner_box").dialog({
		width : 900,
		height : 530,
		modal : true,
		title : title
	});
	
	$("#cboObjType").change();
}
function closeBoxEditRunner() {
	$("#addRunner_box").dialog("close");
}

function addRunner() {
	if ($("#txtRunnerOrder").val().trim() == "") {
		alert("Please input runner order");
		return;
	}

	// Edited by Q, get data from objto, skip vRunnerTo
	var toObject = "";
	$("#objsTo span[o_id]").each(
		function() {
			var fx = Number($(this).find(":checkbox").is(":checked"));
			toObject += (toObject == "" ? "" : ",") + $(this).attr("o_id")
					+ ":" + $(this).attr("o_type") + ":" + fx;
		}
	);
	
	param = {
		"job_id" : current_job_id,
		"order" : $('#txtRunnerOrder').val(),
		"alloc_type" : $("#cboRunnerAllocType").val(),
		"theor_phase" : $("#cboTheorPhase").val(),
		"theor_value_type" : $("#cboTheorValueType").val(),
		"from_option" : $("#cboAllocFromOption").val(),
		"to_option" : $("#cboAllocToOption").val(),
		"obj_from" : vRunnerFrom,
		"obj_to" : toObject	,
        "runner_name" :  $("#txtRunnerName").val(),	
        "begin_date" : $("#runner_begin_date").val(),
        "end_date" : $("#runner_end_date").val()
	}
	
	sendAjaxNotMessage('/addrunner', param, function(data){
		closeBoxEditRunner();
		loadRunnersList(current_job_id, current_job_name);
	});
}

var vRunnerFrom, vRunnerTo;
function addRunnerFrom() {
	var o_type = $("#cboObjType").val();
	var o_id = $("#cboObjects").val();
	if (o_id == null)
		return;
	if ($("#objsFrom span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1
			|| $("#objsTo span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1)
		return;
	var o_name = $("#cboObjects option:selected").text();
	var object = "<span id='' o_type='"
			+ o_type
			+ "' o_id='"
			+ o_id
			+ "' style='display:block'><input type='checkbox' class='chk_fixed'>"
			+ o_name
			+ "<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsFrom").append(object);

	vRunnerFrom += (vRunnerFrom == "" ? "" : ",") + $("#cboObjects").val()
			+ ":" + $("#cboObjType").val();
}
function addRunnerTo() {
	var o_type = $("#cboObjType").val();
	var o_id = $("#cboObjects").val();
	if (o_id == null)
		return;
	if ($("#objsFrom span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1
			|| $("#objsTo span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1)
		return;
	var o_name = $("#cboObjects option:selected").text();
	var object = "<span id='' o_type='"
			+ o_type
			+ "' o_id='"
			+ o_id
			+ "' style='display:block'><input type='checkbox' class='chk_fixed'>"
			+ o_name
			+ "<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsTo").append(object);

	vRunnerTo += (vRunnerTo == "" ? "" : ",") + $("#cboObjects").val() + ":"
			+ $("#cboObjType").val();
}
function editAddRunnerFrom() {
	// o_type = object type
	var o_type = $("#cboObjType").val();
	var o_id = $("#cboObjects").val();
	if (o_id == null)
		return;
	if ($("#objsFrom span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1
			|| $("#objsTo span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1)
		return;
	var o_name = $("#cboObjects option:selected").text();
	var object = "<span id='' o_type='"
			+ o_type
			+ "' o_id='"
			+ o_id
			+ "' style='display:block'><input type='checkbox' class='chk_fixed'>"
			+ o_name
			+ "<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsFrom").append(object);
}
function editAddRunnerTo() {
	// o_type = object type
	var o_type = $("#cboObjType").val();
	var o_id = $("#cboObjects").val();
	if (o_id == null)
		return;
	if ($("#objsFrom span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1
			|| $("#objsTo span[o_type='" + o_type + "'][o_id='" + o_id + "']").length == 1)
		return;
	var o_name = $("#cboObjects option:selected").text();
	var object = "<span id='' o_type='"
			+ o_type
			+ "' o_id='"
			+ o_id
			+ "' style='display:block'><input type='checkbox' class='chk_fixed'>"
			+ o_name
			+ "<span class='ui-icon ui-icon-close' onclick='removeObject(this)'>x</span></span>";
	$("#objsTo").append(object);
}
function saveRunnerEdit(runner_id) {

	if($("#linkExcelFile").html()=="" && $("#chkExcelAllocation").is(':checked') && $("#fileExcel").val()==""){
		alert('Please select a template file');
		return;
	}

	if ($("#txtRunnerOrder").val().trim() == "") {
		alert("Please input runner order");
		return;
	}

	var fromObject = "";
	$("#objsFrom span[o_id]").each(
			function() {
				var fx = Number($(this).find(":checkbox").is(":checked"));
				fromObject += (fromObject == "" ? "" : ",")
						+ $(this).attr("o_id") + ":" + $(this).attr("o_type")
						+ ":" + fx;
			})
	var toObject = "";
	$("#objsTo span[o_id]").each(
			function() {
				var tx = Number($(this).find(":checkbox").is(":checked"));
				toObject += (toObject == "" ? "" : ",") + $(this).attr("o_id")
						+ ":" + $(this).attr("o_type") + ":" + tx;
			})
			
	var url = "";
	if(runner_id > 0)
		url = "/saveEditRunner";
	else
		url = "/addrunner";

	var formData = new FormData();
	formData.append('file', $("#chkExcelAllocation").is(':checked')?$("#fileExcel")[0].files[0]:'');
	formData.append('job_id', current_job_id);
	formData.append('runner_id', runner_id);
	formData.append('order', $("#txtRunnerOrder").val());
	formData.append('fifo', $("#chkFifo").is(':checked')?'Y':'N');
	formData.append('runner_name', $("#txtRunnerName").val());
	formData.append('alloc_type', $("#cboRunnerAllocType").val());
	formData.append('theor_phase', $("#cboTheorPhase").val());
	formData.append('theor_value_type', $("#cboTheorValueType").val());
	formData.append('from_option', $("#cboAllocFromOption").val());
	formData.append('to_option', $("#cboAllocToOption").val());
	formData.append('obj_from', fromObject);
	formData.append('obj_to', toObject);
	formData.append('begin_date', $("#runner_begin_date").val());
	formData.append('end_date', $("#runner_end_date").val());
	formData.append('BA_ID', $("#BaAddressDiv").css('display') == 'none'?0:$("#BaAddress").val());
	formData.append('run_sql', $("#run_sql").val());
	formData.append('use_excel', $("#chkExcelAllocation").is(':checked')?'Y':'N');
	formData.append('file_name', $("#linkExcelFile").html());
	formData.append('file', $("#chkExcelAllocation").is(':checked') && $("#fileExcel").val()!=""?$("#fileExcel")[0].files[0]:'');
	
	$.ajax({
		type: "POST",
		url: url,
		data: formData,
		processData: false,
		contentType: false,
		dataType: 'json',
		cache: false,
		success: function(data){
			if (data != 'ok')
				alert(data);
			else {
				$("#frmExcel")[0].reset();
				alert("Saved successfully");
			}
			closeBoxEditRunner();
			loadRunnersList(current_job_id, current_job_name);
		},
		error: function(data) {
			console.log(data);
			alert("Error");
		}
	});
}
function deleteRunner(runner_id) {
	if (!confirm("Are you sure to delete this runner?"))
		return;
	
	param = {
			"runner_id" : runner_id
	}
	
	sendAjax('/deleterunner', param, function(data){
		loadRunnersList(current_job_id, current_job_name);
	});

}
function removeObject(element) {
	var a = $(element).parent().remove();
}
function checkAllocDate() {
	var d1 = $("#date_begin").datepicker('getDate');
	var d2 = $("#date_end").datepicker('getDate');
	var d = new Date("January 01, 2016 00:00:00");
	if (d1 < d || d2 < d) {
		alert("Can not run allocation for the date earlier than 01/01/2016.");
		return false;
	}
	return true;
}
function runDayJob(job_id, day) {
	$("#allocLog").html("Allocation is running for " + day);
	var dd = day + "";
	if (day < 10)
		dd = "0" + dd;
	dd = "01/" + dd + "/2016";
	// alert(dd);
	// return;
	postRequest("run.php", {
		act : "run",
		"job_id" : job_id,
		from_date : dd,
		to_date : dd
	}, function(data) {
		$("#allocLog").html(data);
		alert("Allocation job completed " + day);
		if (day < 31)
			setTimeout(function() {
				runDayJob(job_id, day + 1);
			}, 100);
	});
}
function doRunJob(job_id) {

	if (!checkAllocDate()) {
		return;
	}
	$('#boxRunAlloc').show();
	if (isCheckAlloc) {
		$("#allocLog").html("Allocation checking in progress...");
		
		param = {
				'act' : 'check',
				'job_id' : job_id,
				'from_date' : dateToString($("#date_begin").datepicker('getDate')),
				'to_date' : dateToString($("#date_end").datepicker('getDate'))
		};
		
		sendAjax('/run_runner', param, function(data){
			$("#allocLog").html(data);
			alert("Checking allocation job completed");
		});
	} else {
		if (!confirm("Run allocation from date " + $("#date_begin").val()
				+ " to date " + $("#date_end").val() + ". Continue?"))
			return;
		$("#allocLog").html("Allocation is running...");
		
		param = {
				'act' : 'run',
				'job_id' : job_id,
				'from_date' : dateToString($("#date_begin").datepicker('getDate')),
				'to_date' : dateToString($("#date_end").datepicker('getDate'))
		};
		
		sendAjax('/run_runner', param, function(data){
			$("#allocLog").html(data);
			alert("Allocation job completed");
		});
	}
}
function showRunAllocDialog(action,str,job_name) {
	action = action!==undefined?action:'';
	str = str!==undefined?str:'';
	job_name = job_name!==undefined?job_name:'';
	$("#chk_run_alloc_daybyday").attr("checked",
			$("#Qrowjob_" + current_job_id).attr("daybyday") == 1);
	var title = action + str + job_name;
	$("#boxRunAllocForm").dialog({
		width : 400,
		height : 240,
		modal : true,
		title : title
	});
}
var allocRunType = "";
var allocObjID = "";
function runRunner(id) {
	$("#buttonRunAlloc").val("Run Allocation");
	isCheckAlloc = false;
	allocRunType = "runner";
	allocObjID = id;
    var runners_name = $("#Qrunner_name_"+id).text();
	showRunAllocDialog('Run ','Allocation Runner ',runners_name);
}
var isCheckAlloc = false;
function runJob(id) {
	$("#buttonRunAlloc").val("Run Allocation");
	isCheckAlloc = false;
	allocRunType = "job";
	allocObjID = id;
    var job_name = $("#QjobName_"+id).text();
    showRunAllocDialog('Run ','Allocation Job ',job_name);
}
function checkJob(id) {
	$("#buttonRunAlloc").val("Simulate Allocation");
	isCheckAlloc = true;
	allocRunType = "job";
	allocObjID = id;
	var job_name = $("#QjobName_"+id).text();
	showRunAllocDialog('Simulate ','Allocation Job ',job_name);
}
function checkRunner(id) {
	$("#buttonRunAlloc").val("Simulate Allocation");
	isCheckAlloc = true;
	allocRunType = "runner";
	allocObjID = id;
    var runners_name = $("#Qrunner_name_"+id).text();
	showRunAllocDialog('Simulate ','Allocation Runner ',runners_name);
}
function runAllocation() {
	hideAllocationForm();
	if (allocRunType == "runner" && allocObjID > 0)
		doRunRunner(allocObjID);
	else if (allocRunType == "job" && allocObjID > 0)
		doRunJob(allocObjID);
	else {
		alert("Nothing to run");
	}
}
function hideAllocationForm() {
	$("#boxRunAllocForm").dialog("close");
}
function doRunRunner(runner_id) {
	if (!checkAllocDate()) {
		return;
	}
	$('#boxRunAlloc').show();
	if (isCheckAlloc) {
		$("#allocLog").html("Allocation checking in progress...");
		
		param = {
			'act' : 'check',
			'runner_id' : runner_id,
			'job_id' : -1,
			'from_date' : dateToString($("#date_begin").datepicker('getDate')),
			'to_date' : dateToString($("#date_end").datepicker('getDate'))
		};
		
		sendAjax('/run_runner', param, function(data){
			$("#allocLog").html(data);
			alert("Checking runner completed");
		});

	} else {
		if (!confirm("Run allocation from date " + $("#date_begin").val()
				+ " to date " + $("#date_end").val() + ". Continue?"))
			return;
		$("#allocLog").html("Allocation is running...");
		
		param = {
				'act' : 'run',
				'runner_id' : runner_id,
				'job_id' : -1,
				'from_date' : dateToString($("#date_begin").datepicker('getDate')),
				'to_date' : dateToString($("#date_end").datepicker('getDate'))
		};
		
		sendAjax('/run_runner', param, function(data){
			$("#allocLog").html(data);
			alert("Allocation runner completed");
		});
	}
}

function hideAllocResult() {
	$('#boxRunAlloc').hide();
}
function showAllocResult() {
	$('#boxRunAlloc').show();
}

function loadCbo(data){
	var cbo = '';
	$('#cboObjects').html(cbo);
	for(var v in data){
		cbo +='<option value="'+data[v].ID+'">'+data[v].NAME+'</option>';
	}

	$('#cboObjects').html(cbo);
	$("#cboObjects").prop("disabled", false);  
}