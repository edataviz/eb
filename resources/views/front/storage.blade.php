<?php
	$currentSubmenu ='/dc/storage';
	$tables = ['TankDataFdcValue'		=>['name'=>'TANK FDC'],
				'TankDataValue'			=>['name'=>'TANK VALUE'],
				'TankDataPlan'			=>['name'=>'TANK PLAN'],
				'TankDataForecast'		=>['name'=>'TANK FORECAST'],
				'StorageDataValue'		=>['name'=>'STORAGE VALUE'],
				'StorageDataPlan'		=>['name'=>'STORAGE PLAN'],
				'StorageDataForecast'	=>['name'=>'STORAGE FORECAST'],
	];
?>

@extends('core.pm')
@section('funtionName')
TANK & STORAGE DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	var tankIdColumn = '{{config("constants.tankIdColumn")}}';
	var storageIdColumn = '{{config("constants.storageIdColumn")}}';

	var saveKeyFields =  {TankDataFdcValue	 	: tankIdColumn,
							TankDataValue 		: tankIdColumn,
							TankDataPlan 		: tankIdColumn,
							TankDataForecast 	: tankIdColumn,
							StorageDataValue 	: storageIdColumn,
							StorageDataPlan 	: storageIdColumn,
							StorageDataForecast : storageIdColumn,
						};
	actions.loadUrl 		= "/storage/load";
	actions.saveUrl 		= "/storage/save";
	actions.historyUrl 		= "/storage/history";
	actions.type = {
					idName:['{{config("constants.tankId")}}','{{config("constants.tankFlowPhase")}}'],
					keyField:'{{config("constants.tankId")}}',
					saveKeyField : function (model){
						return saveKeyFields[model];
					},
// 				,xIdName:'X_FL_ID'
					};

	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		$('.CodePlanType  , .CodeForecastType').css('display','none');
		if(activeTabID=='TankDataPlan'||activeTabID=='StorageDataPlan'){
			$('.CodePlanType').css('display','block');
		}
		else if(activeTabID=='TankDataForecast'||activeTabID=='StorageDataForecast'){
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
</script>
@stop
