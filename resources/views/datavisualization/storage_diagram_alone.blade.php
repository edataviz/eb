<?php
	$tableTab		= "StorageDisplayChart";
	$enableFilter	= false;
	$enableFooter	= false;
	$enableHeader	= false;
 ?>


@extends('core.fp')

@section('content')
	@include('datavisualization.storage_diagram')
	<script type="text/javascript">
		$( document ).ready(function() {
		    console.log( "ready! for render chart" );
		    var sumaryData		= <?php echo json_encode($summaryData); ?>;
		    var container		= $('#diagramContainer');
		    editBox.genDiagram(sumaryData.diagram,container);
		});
	</script>
@stop
