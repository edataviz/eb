<?php
	$currentSubmenu ='/pd-vef';
	$tables = ['PdTransitCarrierVefData'	=>['name'=>'Data Input']];
	$isAction = true;
?>

@extends('core.pd')
@section('funtionName')
TRANSIT CARRIER VEF
@stop

@section('adaptData')
@parent
<div style="display:none" id="boxAnalysis">
</div>
<script>
	actions.loadUrl = "/vef-load";
	actions.saveUrl = "/vef-save";
	actions.historyUrl = "/vef-history";

	window.onload=function() {
		$('#buttonLoadData').parent().append('<input type="button" value="4-Point Analysis" id="buttonAnalysis" onclick="analysis4Point()" style="height: 26px;margin-top: 18px;margin-left: 10px;">');
	}

	function analysis4Point(){
		$('#boxAnalysis').html('Loading...');
		$('#boxAnalysis').dialog({
			height: 400,
			width: 900,
			position: { my: 'top', at: 'top+150' },
			modal: true,
			title: "4-Point Analysis",
		});
		var ids = "";
		$('.cellcheckbox.SELECTED').each(function(){
			if($(this).find('.cellCheckboxInput').is(':checked')){
				ids += (ids ? ',' : '') + $(this).parent().attr('id');
			}
		});
		postRequest('/vef-analysis',
			{ids: ids},
			function(data){
				$('#boxAnalysis').html(data);
			}
		);
	}
</script>
@stop
