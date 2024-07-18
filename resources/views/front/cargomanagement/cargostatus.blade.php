<?php
	$currentSubmenu ='/pd/cargostatus';
	$tables 		= ['PdCargo'	=>['name'=>'Load']];
	$detailTableTabs= 	[
							[	"name"	=> 'PdCargoEntry',
								"title"	=> 'Cargo Entry'],
							[	"name"	=> 'PdCargoNomination',
								"title"	=> 'Cargo Nomination'],
							[	"name"	=> 'PdVoyageDetail',
								"title"	=> 'Voyage Parcel'],
							[	"name"	=> 'PdCargoLoad',
								"title"	=> 'Cargo Load Activities'],
							[	"name"	=> 'PdCargoUnload',
								"title"	=> 'Cargo UnLoad Activities'],
							[	"name"	=> 'PdTransportShipDetail',
								"title"	=> 'Voyage Marine'], 
							[	"name"	=> 'PdTransportGroundDetail',
								"title"	=> 'Voyage Ground'],
							[	"name"	=> 'PdTransportPipelineDetail',
								"title"	=> 'Voyage Pipeline'],
							[	"name"	=> 'ShipCargoBlmr',
								"title"	=> 'BLMR'],
							/* 
								*/
	];
	$isLoad 		= 0;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	var detailTableTabs = <?php echo json_encode(array_column($detailTableTabs, 'name')); ?>;

	$( document ).ready(function() {
	    var onChangeFunction = function() {
		    if($('#Facility option').size()>0 ) actions.doLoad(true);
	    };
	    
	    $( "#Facility" ).change(onChangeFunction);
// 		actions.doLoad(true);
	});

	actions.loadUrl = "/cargostatus/load";
// 	actions.saveUrl = "/voyagemarine/save";
// 	actions['idNameOfDetail'] = ['ID'];
	
	actions.isDisableAddingButton	= function (tab,table) {
		return true;
	};

	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Select</a>';
		return html;
	};

	editBox.initExtraPostData = function (id,rowData){
		templateId = id;
	 		return 	{
		 			id			: id,
		 			tabs		: detailTableTabs,
		 		};
 	}
	editBox.editGroupSuccess = function(tabs,id){
		for (var tab in tabs) {
			options = editBox.getEditTableOption(tab);
			renderTable(tab,tabs[tab],options,actions.createdFirstCellColumn);
		}
	}

	editBox['getEditTableOption'] = function(tab){
		return {
		 			tableOption :	{
		 					searching	: false,
							autoWidth	: true,
							scrollX		: true,
							scrollY		: false,
							info		: false
					}
				};
	};
 	
	actions.renderFirsEditColumn = null;
</script>
@stop
@section('editBoxParams')
@parent
<script>
 	editBox.loadUrl = "/cargostatus/detail";
//  	editBox.saveUrl = "/shipport/save";
	editBox['size'] = {	height : 500,
						width : 1100,
			};
 	
</script>
@stop

@section('editBoxContentview')
	<table border='0' cellpadding='0' style='width:100%;height:100%'>
		@if(is_array($detailTableTabs))
			@foreach($detailTableTabs as $key => $table )
				<tr style='height:30px'>
					<td colspan="1" style='padding-top: 15px;' ><b>{{$table['title']}}</b></td>
				</tr>
				<tr>
					<td valign='top'>
						<div id="table_{{$table['name']}}_containerdiv" style='height:100%;overflow: hidden;'>
							<table id="table_{{$table['name']}}" class="fixedtable nowrap display">
							</table>
						</div>
					</td>
				</tr>
	 		@endforeach
		@endif
	</table>
@stop