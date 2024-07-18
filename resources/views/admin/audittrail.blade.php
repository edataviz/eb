<?php
	$currentSubmenu = '/am/audittrail';
	$tables = ['Audittrail'	=>['name'=>'Load']];
?>

@extends('core.admin')

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/am/loadAudittrail";
	actions.getTableOption	= function(data){
		return {tableOption :	{searching: true,
								autoWidth	: false},
				invisible	: [],
            	resetTableHtml : function(tabName) { return true}
		};

	};
</script>
@stop
