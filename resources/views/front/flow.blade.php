<?php
	$currentSubmenu ='/dc/flow';
	$tables = ['FlowDataFdcValue'	=>['name'=>'FDC Value'],
				'FlowDataValue'		=>['name'=>'STD Value'],
				'FlowDataTheor'		=>['name'=>"Theoretical"],
				'FlowDataAlloc'		=>['name'=>"Allocation"],
				'FlowCompDataAlloc'	=>['name'=>"Composition Alloc"],
				'FlowDataPlan'		=>['name'=>"Plan"],
				'FlowDataForecast'	=>['name'=>"Forecast"],
	];
 	//$active = 0;
?>

@extends('core.pm')
@section('funtionName')
FLOW DATA CAPTURE
@stop

@section('adaptData')
<style>
.ui-front[aria-describedby="resave-dialog"]{
	z-index: 10000;
}
</style>
<div id="resave-dialog" style="padding:20px;display:none;z-index:10000">
	<span style="color:red">NOTICE: Please make sure you have backed up your data before performing this task!</span>
	<br>
	<br>
	From date <input id="resave-date-from" class="datepicker date-from" style="width:120px" autocomplete="off">
	To date <input id="resave-date-to" class="datepicker date-to" style="width:120px" autocomplete="off">
	<br>
	<br>
	<span>Status: <span id="resave-status"></span></span>
	<div style="height:5px;background:#ddd;margin:15px"><div id="resave-progress" style="height:100%;width:0px;background:#127ab8"></div></div>
</div>
@parent
<script>
	actions.loadUrl 		= "/code/load";
	actions.saveUrl 		= "/code/save";
	actions.historyUrl 		= "/code/history";
	
	actions.type = {
					idName:['{{config("constants.flowId")}}','{{config("constants.flFlowPhase")}}','CTV'],
					keyField:'{{config("constants.flowId")}}',
					saveKeyField : function (model){
						return '{{config("constants.flowIdColumn")}}';
					},
// 				,xIdName:'X_FL_ID'
					};

	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		$('.CodeAllocType, .CodePlanType, .CodeForecastType').css('display','none');
		if(activeTabID=='FlowDataAlloc'){
			$('.CodeAllocType').css('display','block');
		}
		else if(activeTabID=='FlowDataPlan'){
			$('.CodePlanType').css('display','block');
		}
		else if(activeTabID=='FlowDataForecast'){
			$('.CodeForecastType').css('display','block');
		}
		aLoadNeighbor();
	}

	var aLoadParams = actions.loadParams;
	actions.loadParams = function(reLoadParams) {
		var pr = aLoadParams(reLoadParams);
		pr['CodePlanType']		= $('#CodePlanType').val();
		pr['CodeForecastType']	= $('#CodeForecastType').val();
		return pr;
	}

	var backup_loadSuccess = actions.loadSuccess;
	var backup_saveSuccess = actions.saveSuccess;
	$.ui.dialog.prototype._focusTabbable = function(){};
	function showResaveBox(){
		var $box = $("#resave-box");
		$("#resave-progress").css("width", "0%");
		$("#resave-status").html("");
		isResaving = false;
		$box.show();
		$("#resave-dialog").dialog({
			height: 350,
			width: 600,
			position: { my: 'top', at: 'top+150' },
			modal: true,
			title: "Re-save data",
			buttons: [
				{
					id: "resave-button-start",
					text: "Start",
					click: function() {
						startResave()
					}
				},
				{
					text: "Cancel",
					click: function() {
						$( this ).dialog( "close" );
						$box.hide();
						actions.loadSuccess = backup_loadSuccess;
						actions.saveSuccess = backup_saveSuccess;
					}
				}
			],
			/*
			buttons: {
				"Start": function() {
					startResave()
				},
				Cancel: function() {
					$( this ).dialog( "close" );
					$box.hide();
					actions.loadSuccess = backup_loadSuccess;
					actions.saveSuccess = backup_saveSuccess;
				}
			},
			*/
		});
	}

	var isResaving = false;
	function startResave(){
		var d1 = $("#resave-date-from").datepicker('getDate');
		var d2 = $("#resave-date-to").datepicker('getDate');
		if(!d1 || !d2){
			alert("Please select date range");
			return;
		}
		else {
			if(isResaving) return;
			isResaving = true;
			$("#resave-button-start").button("disable").prop('disabled', true);
			const days = 1 + Math.round(Math.abs((d2 - d1) / (24 * 60 * 60 * 1000)));
			var d = d1, c = 0;
			d.setDate(d.getDate() - 1);
			actions.loadSuccess = function(data){
				backup_loadSuccess(data);
				setTimeout(function(){
					actions.doSave(true);
					if(!validated){
						actions.saveSuccess('');
					}
				},100)
			}
			actions.saveSuccess = function(data){
				if(d<d2){
					d.setDate(d.getDate() + 1);
					var progress = Math.round(c/days*100) + "%";
					$("#resave-status").html('<img src="/images/loading.gif" height="20" style="margin:0 5px"> <b>' + progress + "</b> - Working on date " + moment(d).format(configuration.time.DATE_FORMAT));
					$("#resave-progress").css("width", progress);
					c++;
					$('#date_begin').datepicker("setDate", d );
					setTimeout(function(){
						actions.doLoad(true);
					},100);
				}
				else{
					$("#resave-status").html("Complete");
					actions.loadSuccess = backup_loadSuccess;
					actions.saveSuccess = backup_saveSuccess;
					backup_saveSuccess(data);
					$("#resave-progress").css("width", "100%");
					isResaving = false;
					$("#resave-button-start").button("enable").prop('disabled', false);

				}
			}
			actions.saveSuccess('');
		}
	}
	
	$( document ).ready(function() {
	    //$("#FlowDataValue").css( "pointer-events", "none" );
	    //$("#FlowDataValue").css( "display", "none" );
		$("#resave-dialog .datepicker").datepicker({
			changeMonth	:	true,
			changeYear	:	true,
			dateFormat	:	jsFormat,
			onSelect: function() {
				var date = $(this).datepicker('getDate');
				if($(this).hasClass("date-from")){
					$("#resave-date-to").datepicker("change",{ minDate: date});
				}
				else if($(this).hasClass("date-to")){
					var code = $(this).attr("code");
					$("#resave-date-from").datepicker("change",{ maxDate: date});
				}
			}
		});
		$('<a href="javascript:showResaveBox()" style="margin: 10px 0 0 20px;display: initial;position: absolute;">Re-save by Date range</a>').insertAfter("#buttonSave");
	});

</script>
@stop
