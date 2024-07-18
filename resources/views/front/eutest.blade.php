<?php
	$currentSubmenu ='/dc/eutest';
	$tables = ['EuTestDataFdcValue'		=>['name'=>'FDC VALUE'],
			'EuTestDataStdValue'		=>['name'=>'STD VALUE'],
			'EuTestDataValue'			=>['name'=>'DAY VALUE'],
	];
	$isAction 		= true;
	$useFeatures	= [
						['name'	=>	"filter_modify",
						"data"	=>	["isFilterModify"	=> false,
									"isAction"			=> $isAction]],
						['name'	=>	"editable_by_right","data"	=>	[]],
						['name'	=>	"delete_constraint",
						"data"	=>	["tab"				=> ['EuTestDataFdcValue','EuTestDataStdValue','EuTestDataValue'],
									"keyColumns"		=> ['EU_ID','EFFECTIVE_DATE']]]
					];
?>

@extends('core.pm')
@section('funtionName')
WELL TEST DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/eutest/load";
	actions.saveUrl 		= "/eutest/save";
	actions.historyUrl 		= "/eutest/history";
	
	actions.type = {
					idName:['EU_ID','ID','BEGIN_TIME','END_TIME','EFFECTIVE_DATE'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
										return 'ID';
									},
					};
	addingOptions.keepColumns = ['BEGIN_TIME','END_TIME','EFFECTIVE_DATE'];

	actions.specialColumns 	= [{	column		: "TEST_USAGE",
								right		: "_ADMIN_WELLTEST_SET_ALLOC",
								columnValue	: 1,
							}];

</script>
@stop