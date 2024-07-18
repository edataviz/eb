<?php
	$currentSubmenu ='/pd/storagedisplay';
	$tables = ['PdCargoSchedule'	=>['name'=>'Data Input']];
	$isAction = true;
?>

@extends('front.graph')
@section('funtionName')
STORAGE DISPLAY
@stop

@section('frequenceFilterGroupMore')
<table class="clearBoth" style="width: 100%;height: 115px;">
	<tr>
		<td align="right">
			<input type="checkbox" id="chkMinus"> Negative
		</td>
		<td align="right" colspan="1">
			<button class="myButton"onclick="_graph.addObject()" style="width: 61px">Add</button>
		</td>
	</tr>
	<tr>
		<td><b>Chart title</b></td>
	</tr>
	<tr>
		<td colspan="3">
			<input type="text" id="chartTitle"
					name="chartTitle"
					style="width: 200px; height: 17px; padding: 2px;"></input>
		</td>
	</tr>
</table>
@stop

@section('graph_extra_view')
<div style="
 	overflow: auto;    
    height: 100%;
    width: 300px;
    border: 1px solid #bbbbbb;
    max-height: 200px;
    background: #eeeeee;">
	<ul id="graphTank" class="ListStyleNone"></ul>
</div>
@stop

@section('content')
@parent
<style>
	.PlotViewConfig{
		width: 100%; 
	}
	.TankModelGraph{
	    margin: 5px;
	    display: block;
	    float: left;
	    width: 110px;
	    height: 80px;
	    background: url(../img/tank.png);
	    background-size: 100% 100%;
	    padding-top: 0px;
        max-height: 200px;
	}
	.TankModelGraphText{
		position: relative;
	    color: white;
	    text-align: center;
	    font-size: 8pt;
	    word-wrap: break-word;
	    padding: 2px;
	    top: 50%;
	}
</style>
<script>
var orenderDependenceHtml = renderDependenceHtml;
renderDependenceHtml = function(elementId,dependenceData) {
	if(elementId=="Tank"){
		var option = $('<li class="TankModelGraph" />');
		var text	= $('<p class="TankModelGraphText"></p>');
		var name = typeof(dependenceData.CODE) !== "undefined"?dependenceData.CODE:dependenceData.NAME;
		option.attr('name', name);
		option.attr('value', dependenceData.ID);
		text.text(dependenceData.NAME);
		option.append(text);
		return option;
	}
	else return orenderDependenceHtml(elementId,dependenceData);
};

$( document ).ready(function() {
    console.log( "ready!" );
    $("#select_container_Tank").hide();
    $("#Tank option").each(function(){
    	var li = renderDependenceHtml("Tank",{ID:this.value,	CODE:this.name,	NAME: this.text});
    	$("#graphTank").append(li);
    });
    $("#Tank").attr("id","originalTank");
    $("#graphTank").attr("id","Tank");
});

_graph.addObject = function(){
	if($("#PlotViewConfig").val()>0){
		var dataStore		= {	
				LoProductionUnit	:	$("#LoProductionUnit").val(),
				LoArea				:	$("#LoArea").val(),
				Facility			:	$("#Facility").val(),
				Storage				:	$("#Storage").val(),
				PlotViewConfig		:	$("#PlotViewConfig").val(),
				chkMinus			:	$("#chkMinus").prop('checked'),
			};
		var color			="transparent";
		var texts			= {
								PlotViewConfig		:	$("#PlotViewConfig option:selected").text(),
								chkMinus			:	$("#chkMinus").prop('checked'),
							};
		
		editBox.addObjectItem(color,dataStore,texts);
	}
}

_graph.buildChartUrl	=  function(){
	var chartTitle		= $("#chartTitle").val();
	var currentDiagram 	= [];
	var constraintPostData 	= {	
		date_begin	: $("#date_begin").val(),
		date_end	: $("#date_end").val(),
		title		: chartTitle,
		constraints	: currentDiagram,
		constraintId: 9,
	};
	editBox.requestGenDiagram(constraintPostData,false,true,function(data){
// 		editBox.renderContrainTable(data.constraints,false);
	});
	return false;
}

</script>
@stop


@section('extraAdaptData')
@parent
<script>
	var opreOnchange	= filters.preOnchange;
	filters.preOnchange		= function(id, dependentIds,more){
					var partials 		= id.split("_");
					var prefix 			= partials.length>1?partials[0]+"_":"";
					var model 			= partials.length>1?partials[1]:id;
					switch(model){
						case "Storage":
							$('#Tank').html("");
							return;
						break;
					}
					if(typeof opreOnchange == "function" ) opreOnchange(id, dependentIds,more);
				};
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/storagedisplay/filter";
	editBox.renderOutputText = function (texts){
		var prefix	 = texts.chkMinus?"[-] ":"";
		return 	prefix+texts.PlotViewConfig+" ";
	};
</script>
@stop


@section('chartContainer')
	@include('front.cargoadmin.storage_diagram')
@stop