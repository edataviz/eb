<?php
	use \App\Models\Network;
	use \App\Models\AllocJob;
	use \App\Models\TmTask;
	use \App\Models\Facility;
	use \App\Models\TmWorkflow;
	use \App\Models\EnergyUnitGroup;
	use \App\Models\CodeAllocType;
	use \App\Models\CodePlanType;
	use \App\Models\CodeForecastType;
	use \App\Models\CodeProductType;
	use \App\Models\EnergyUnit;
	use \App\Models\CodeFlowPhase ;
	use \App\Models\CodeEventType ;
	use \App\Models\CodeReadingFrequency ;
	use \App\Http\Controllers\InterfaceController ;
	
	$currentSubmenu ='/dv/taskman';
	$tables = ['TmTask'	=>['name'=>'Task'],
	];
	$isAction = true;
	
	$facility			= Facility::all();
	$taskStatus			= TmTask::loadStatus();
	$network			= NETWORK::getTableName();
	$allocJob			= AllocJob::getTableName();
	$networks 			= Network::join($allocJob,"$network.ID", '=', "$allocJob.NETWORK_ID")->distinct("$network.ID")->select("$network.ID","$network.NAME")->get();
	$tmWorkflows		= TmWorkflow::loadActive();
	$codeFlowPhase		= CodeFlowPhase::loadActive();
	$codeReadingFrequency= CodeReadingFrequency::loadActive();
	$codeEventType		= CodeEventType::loadActive();
	$energyUnitGroup	= EnergyUnitGroup::all();
	$codeAllocType		= CodeAllocType::loadActive();
	$codePlanType		= CodePlanType::loadActive();
	$codeForecastType	= CodeForecastType::loadActive();
	$codeProductType	= CodeProductType::loadActive();
	$energyUnit			= EnergyUnit::all();
	
	$itfController		= new InterfaceController();
	$intConnection		= $itfController->loadCon("PI");
	$calMethod			= [
			['ID'	=> 'all', 'NAME' => 'All'],
			['ID'	=> 'last', 'NAME' => 'Last'],
			['ID'	=> 'first', 'NAME' => 'First'],
			['ID'	=> 'max', 'NAME' => 'Max'],
			['ID'	=> 'min', 'NAME' => 'Min'],
			['ID'	=> 'average', 'NAME' => 'Average'],
			['ID'	=> 'interpolation', 'NAME' => 'Interpolation'],
	];
?>

@extends('core.pm')

@section('adaptData')
@parent
<script src="/common/edittable/event.js?52"></script>

<script>
	var networks 			= <?php echo json_encode($networks); ?>;
	var taskStatus 			= <?php echo json_encode($taskStatus); ?>;
	var facility 			= <?php echo json_encode($facility); ?>;
	var tmWorkflows 		= <?php echo json_encode($tmWorkflows); ?>;
	var codeReadingFrequency= <?php echo json_encode($codeReadingFrequency		);		?>;
	var codeFlowPhase		= <?php echo json_encode($codeFlowPhase		);		?>;
	var codeEventType		= <?php echo json_encode($codeEventType		);		?>;
	var energyUnitGroup		= <?php echo json_encode($energyUnitGroup	);	?>;
	var codeAllocType		= <?php echo json_encode($codeAllocType		);	?>;
	var codePlanType		= <?php echo json_encode($codePlanType		);	?>;
	var codeForecastType	= <?php echo json_encode($codeForecastType	); ?>;
	var codeProductType		= <?php echo json_encode($codeProductType	);	?>;
	var energyUnit			= <?php echo json_encode($energyUnit		);		?>;
	var intConnection		= <?php echo json_encode($intConnection	); ?>;
	var calMethod			= <?php echo json_encode($calMethod	);		?>;
	
	actions.loadUrl 		= "/taskman/load";
	actions.saveUrl 		= "/taskman/save";

	$( document ).ready(function() {
	    console.log( "ready!" );
 		actions.doLoad(true);
	});
	
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
					},
	};

	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink"><img alt="delete" title="Delete" src="/images/delete.png"></a>';
		html += '<a onclick="actions.sendCommandJob('+id+',this,\'refresh\')" class="actionLink"><img alt="Stop" title="Stop" height="20" src="/images/refresh.png?1"></a>';
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
 			var status	= (typeof rowData.status!="undefined") ? rowData.status:rowData.STATUS;
 			switch(status){
 			case {{TmTask::STOPPED}}:
 			case {{TmTask::CANCELLING}}:
 			case {{TmTask::DONE}}:
 			case '{{TmTask::STOPPED}}':
 			case '{{TmTask::CANCELLING}}':
 			case '{{TmTask::DONE}}':
				html += '<a onclick="actions.sendCommandJob('+id+',this,\'start\')" class="actionLink"><img alt="Run" title="Run" src="/images/run.png"></a>';
 	 			break;
 			case {{TmTask::STARTING}}:
 			case {{TmTask::READY}}:
 			case {{TmTask::RUNNING}}:
 			case '{{TmTask::STARTING}}':
 			case '{{TmTask::READY}}':
 			case '{{TmTask::RUNNING}}':
				html += '<a onclick="actions.sendCommandJob('+id+',this,\'stop\')" class="actionLink"><img alt="Stop" title="Stop" src="/images/stop.png"></a>';
 	 			break;
 			}
 			var commandText	= actions.getTaskStatus(status);
			html += ' ' + commandText;
		}
		return html;
	};
	actions.getTaskStatus  = function ( command) {
		var result = $.grep(taskStatus, function(e){ 
       	 	return e.ID == command||e.id == command;
        });
	    if (result.length > 0 ) {
		    if(typeof result[0]["NAME"] != "undefined") return result[0]["NAME"];
		    else if(typeof result[0]["name"] != "undefined") return result[0]["name"];
	    }
	    return command;
	}

	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
		if(columnName=="task_code"||columnName=="TASK_CODE"){
			var dependence = typeof rowData.task_config != "undefined"? 'task_config':'TASK_CONFIG';
			var DT_RowId = rowData['DT_RowId'];
			var dependencetd = $('#'+DT_RowId+" ."+dependence);
			actions.applyEditable(tab,"EVENT",dependencetd, null, rowData, dependence);
		}
		actions.createdFirstCellColumnByTable(table,rowData,td,tab);
	}

	actions.sendCommandJob  = function ( id, element,command) {
		$(element).html('<a class="actionLink"><img alt="loading" title="loading" src="/images/spinner.gif"></a>');
        var table 		= $('#table_TmTask').DataTable();
		var row 		= table.row('#'+id);
		var rowData 	= row.data();
		$.ajax({
			url	: '/taskman/update/'+command+'/'+id,
			type: "post",
			data: {id	: id},
			success:function(data){
				console.log ( "attemp "+command+" Job success with code "+data.CODE+" status code"+data.status+" status");
				jQuery.extend(rowData,data.task)
				/* rowData.status	= data.task.status;
				rowData.command	= data.task.command;
				rowData.command	= data.task.command; */
				row.data(rowData).draw();
						
			},
			error: function(data) {
				console.log ( "attemp "+command+" Job error");
				row.data(rowData).draw();
				actions.loadError(data);
			}
		});
	};

	actions.configEventType = function (editable,columnName,cellData,rowData){
		if(columnName=="task_config"||columnName=="TASK_CONFIG") {
			if(cellData!=null) {
				cellData.networks 				= networks;
				cellData.facility 				= facility;
				cellData.tmWorkflows 			= tmWorkflows;
				cellData.codeFlowPhase			= codeFlowPhase;
				cellData.codeEventType			= codeEventType;
				cellData.energyUnitGroup		= energyUnitGroup	;
				cellData.codeAllocType	    	= codeAllocType	;    
				cellData.codePlanType	    	= codePlanType	;    
				cellData.codeForecastType   	= codeForecastType;   
				cellData.codeProductType		= codeProductType	;
				cellData.energyUnit		    	= energyUnit		;    
				cellData.codeReadingFrequency 	= codeReadingFrequency		; 
				cellData.intConnection		    = intConnection		;    
				cellData.calMethod 				= calMethod		;  
			}
			editable.configType = typeof rowData.task_code != "undefined"?rowData.task_code:rowData.TASK_CODE;
			editable.placement 	= "bottom";
		}
	}

	oPreEditableShow = actions.preEditableShow;
	actions.preEditableShow  = function(){
		oPreEditableShow();
		firstTime = true;
	};

	actions.renderEventConfig = function( columnName,data2, type2, row){
		if(data2==null) return "config";
		if(columnName=="task_config"||columnName=="TASK_CONFIG") {
			return typeof data2=="object"&&typeof data2.name != "undefined"? data2.name:
				(typeof data2.NAME != "undefined"? data2.NAME:"config");
		}
		else  if(columnName=="time_config"||columnName=="TIME_CONFIG") {
			return typeof data2=="object"&&typeof data2.FREQUENCEMODE != "undefined"? data2.FREQUENCEMODE:"config";
		}
		return "config";
	}

	$("#ebFilters_").append("<div id='config_'><span id='config_'> aaaa</span></div>");
	
	</script>
@stop

