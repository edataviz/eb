<?php
	$currentSubmenu =	'/fp/preos';
	$key 			= 	'preos';
 	$active = 1;
 ?>

@extends('core.fp')
@section('funtionName')
PENG-ROBINSON EQUATION OF STATE
@stop

@section('action_extra')
<div class="action_filter">
	<input type="button" value="Add" id="buttonAdd" name="buttonAdd" onClick="addObject()" style="width: 85px; height: 26px;foat:left;">
</div>
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/preos/load";
	actions.saveUrl = "/preos/run";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
							return 'ID';
						},
					};

	var objs="";

	function addObject()
 	{
 		var id=$("").val();
 		var s='<span style="display:block;margin:1px 0px" info="'+
 		$("#IntObjectType option:selected").attr('name')+
 		':'+
 		$("#ObjectName").val()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		'">'+$("#IntObjectType option:selected").text()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		' <img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="../img/x.png">';
 		
 		$("#selected_objects").append(s);
 	}
 	
	actions.initData = function(){

		var tab = {'{{config("constants.tabTable")}}'	:	'{{$key}}',
					'objs'								:	objs,
					cb_update_db						:	$("#chk_update_db").is(":checked"),
				};
		return tab;
	}

	var oloadValidating	= actions.loadValidating;
	actions.loadValidating = function (reLoadParams){
		objs = "";
		$('#selected_objects span').each(function(){
			objs+=(objs==""?"":";")+$(this).attr("info");
		});
		if(objs==""){
			alert("Please add object");
			return false;
		}
		$("#result").css("display","none");
		$('#container_{{$key}}').html('<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display"></table>');
		var rs 	= oloadValidating(reLoadParams);
		rs.objs	= objs;
		return rs;
	}

	showExtensionFields = function(data){
		$("#exe").html(data.exe);
	};

	showResultFields = function(data){
		resultHtml = '';
		$.each(data.result, function( index, value ) {
			resultHtml+= '<b>'+index+'</b><br/>'+value.join("<br/>")+'<br/>';
         });
		$.each(data.sqls, function( index, value ) {
			resultHtml+= ''+value+'<br/>';
         });
		$("#result_result").html(resultHtml);
		$("#result").css("display","block");
	};

	actions.getNumberRender = function (columnName,data,cellData, type2, row) {
		return cellData;
	}

	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: false,
								scrollY			: '350px',
								},
				invisible:[],
            	resetTableHtml : function(tabName) { return true}
        };
		
	}

	var preoValidating	= actions.validating;
	actions.validating = function (reLoadParams){
		var v = preoValidating(reLoadParams);
		if(!v) return v;
		objs = "";
		$('#selected_objects span').each(function(){
			objs+=(objs==""?"":";")+$(this).attr("info");
		});
		if(objs==""){
			alert("Please add object");
			return false;
		}
		$("#result").css("display","none");
		return true;
	}
</script>
@stop

@section('logName')
PREoS log:
@stop

@section('extensionFields')
<b>Run</b> <div id="exe"></div><br>
@stop

@include('core.runpanel')
