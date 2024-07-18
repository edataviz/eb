<?php
$currentSubmenu ='/fo/safety';
$tables = ['Safety'	=>['name'=>'SAFETY']];
?>
@extends('core.fo')

@section('funtionName')
SAFETY DATA CAPTURE
@stop


@section('adaptData')
@parent
<script>
	actions.loadUrl = "/safety/load";
	actions.saveUrl = "/safety/save";
	actions.type = {
					idName:['CREATED_DATE','FACILITY_ID','X_CATEGORY_ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'CATEGORY_ID';
					},
				};
</script>
@stop
