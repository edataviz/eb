<meta name="_token"
	content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/css/style.css">
<link rel="stylesheet" href="/common/css/jquery-ui.css">

<script src="/common/js/jquery-1.9.1.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="/common/js/utils.js?12"></script>
<script src="/common/js/wfshow.js?20"></script>
<script type="text/javascript" src="/common/js/mxClient.js?3"></script>
<script type="text/javascript" src="/common/js/mxApplication.js?3"></script>

<?php
// $username=$current_username;
$wf_id = isset ( $_REQUEST ["wf_id"] ) ? $_REQUEST ["wf_id"] : null;
?>

<script type="text/javascript">	
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	if(_wfshow.count == 0){
		parent.hide_wf_loading();
	}
	
	$('#cbo_wflist').change(function(){
		var wfid=$(this).val();
		if(wfid>0)
			loadSavedDiagram(wfid);	
		else
			clearGraph();
	});
	
	$('#cmd_finish_task').click(function(){
		task_id=$('#task_process .name').attr('task_id');
		if(task_id>0){
			param = {
				'ID' : task_id
			}			
		 	sendAjax('/finish_workflowtask', param, function(data){
		 		loadWorkflow();
			}); 
			if(parent) parent.loadTasksCounting();
		}
	})
	
});

var _wfshow = {
	count : 0,
	
	loadData:function(){
		param = {}
		sendAjax('reLoadtTmworkflow', param, function(data){
			_wfshow.loadCbo(data);
		});
		if(parent) parent.loadTasksCounting();
	},
	
	loadCbo : function(_data){	
		var cbo = '';
		for(var v in _data){
			cbo += ' 		<option value="' + _data[v].ID + '">' + _data[v].NAME + '</option>';
			_wfshow.count += 1;
		}
		$('#cbo_wflist').html(cbo);
		$('#cbo_wflist').change();
	}
}
</script>

@if (count($tmworkflow) > 0)
<body
	onload="<?php	echo " new mxApplication('/config/diagrameditor-workflow.xml?3');";?>"
	style="margin: 0px; background: white; overflow: hidden">
	<div id='wflist'
		style='margin-top: 5px; width: 100%; height: 30px; border-top: 0px solid #aaaaaa; padding-top: 0px; background: white'>
		<b> Workflow </b> <select id='cbo_wflist'
			style='min-width: 200px; height: 30px; margin: 0 2px; border-color: #378de5'>
			@foreach($tmworkflow as $unit)
			<option value="{!!$unit->ID!!}">{!!$unit->NAME!!}</option>
			@endforeach
		</select> <input type='button' value='Refresh'
			style="margin: 0 2px; height: 30px; width: 80px"
			onclick="_wfshow.loadData();">
		<button type='text' id='cmd_open_task' onclick="openTask()"
			style='display: none; margin: 0 2px; width: 100px; height: 30px'>Open
			Task</button>
		<button type='text' id='cmd_finish_task'
			style='display: none; margin: 0 2px; width: 120px; height: 30px'>Finish
			Task</button>
	</div>


	<center>
		<div id="graph"
			style="margin-top: 15px; position: relative; height: calc(100% - 60px); width: 100%; box-sizing: border-box; cursor: default; overflow: hidden; border: 0px solid #a0a0a0;">
		</div>
	</center>
	
	<div id='task_process'
		style='display: none; text-align: center; padding-top: 0px'>
		<span id='task_info' style="display: none">Task Name:<span
			class='name' task_id='0'></span></span>
	</div>
	<div style="display: none; padding: 10px;" id="toolbar"></div>
</body>
@else
	<h4 style="text-align: center;color: #5555559c;margin-top: 200px;">You have no active task.</h4>
@endif