<?php
$subMenus = [array('title' => 'CONTRACT ADMIN', 	'link' => ''),
	array('title' => 'CARGO ADMIN', 		'link' => 'cargoentry'),
	array('title' => 'CARGO NOMINATION', 	'link' => 'cargonomination'),
	array('title' => 'CARGO SCHEDULE', 		'link' => 'cargoschedule'),
	array('title' => 'CARGO ACTION', 		'link' => ''),
	array('title' => 'DEMURRAGE/EBO', 	 	'link' => 'demurrageebo'),
	array('title' => 'CARGO DOCUMENTS', 	'link' => 'cargodocuments'),
	array('title' => 'CARGO MONITORING', 	'link' => '')
];
?>
@extends('core.bscontent',['subMenus' => $subMenus])


@section('adaptData')
@parent
<script>
	$( document ).ready(function() {
	    console.log( "ready!" );
 		actions.doLoad(true);
	});
</script>	
@stop