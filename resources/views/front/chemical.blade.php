<?php
$currentSubmenu ='/fo/chemical';
$tables = ['KeystoreTankDataValue'			=>['name'=>'CHEMICAL TANK VALUE'],
		'KeystoreStorageDataValue'			=>['name'=>'CHEMICAL STORAGE VALUE'],
		'KeystoreInjectionPointDay'			=>['name'=>'CHEMICAL INJECTION POINT']
];

?>
@extends('core.fo')

@section('funtionName')
CHEMICAL DATA
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/chemical/load";
	actions.saveUrl = "/chemical/save";

	actions.type = {
			idName:['ID'/* ,'KEYSTORE_TANK_ID' */,'OCCUR_DATE'],
			keyField:'DT_RowId',
			saveKeyField : function (model){
// 				if(model=="KeystoreInjectionPointDay") return "DT_RowId";
				return 'DT_RowId';
				},
			};
	var aLoadNeighbor = actions.loadNeighbor;
	actions.loadNeighbor = function() {
		var activeTabID = getActiveTabID();
		$('#filterFrequence').css('display','none');
		if(activeTabID=='KeystoreInjectionPointDay'){
			$('#filterFrequence').css('display','block');
		}
		aLoadNeighbor();
	}

	/* var osaveSuccess = actions.saveSuccess;
	actions.saveSuccess = function (data,noDelete){
		osaveSuccess(data,noDelete);
		actions.doLoad(true);
	}; */
</script>
@stop
