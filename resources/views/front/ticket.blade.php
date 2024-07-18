<?php
	$currentSubmenu ='/dc/ticket';
	$tables = [
			'RunTicketFdcValue'		=>['name'=>'TICKET FDC'],
			'RunTicketValue'		=>['name'=>'TICKET VALUE'],
	];
 	//$active = 1;
 	$isAction = true;
 	$useFeatures	= [
 							['name'	=>	"filter_modify",
 							"data"	=>	["isFilterModify"	=> true,
 										"isAction"			=> $isAction]],
							['name'	=>	"delete_constraint",
							"data"	=>	["tab"				=> ['RunTicketFdcValue','RunTicketValue'],
 										"keyColumns"		=> ['TANK_ID','OCCUR_DATE','TICKET_NO']]]
 	];
 	
 	$filterGroups = array(	'productionFilterGroup'	=> [['name'			=>'CodeProductType',
											 			'independent'	=>true,
 														'filterName'	=>'Phase Type',
						 								"defaultEnable"	=> false,
 														'extra'			=> ["Facility","CodeProductType"],
											 			'dependences'	=>["Tank"]],
													 	],
 			'frequenceFilterGroup'	=> [	["name"			=> "Tank",
						 					"defaultEnable"	=> false,
											"getMethod"		=> "loadBy",
 											"source"		=> ['productionFilterGroup'=>["Facility","CodeProductType"]]],
						 			],
 			'enableButton'			=> false,
  			'FacilityDependentMore'	=> ["Tank"],
 			'extra' 				=> ['CodeProductType'],
 			'enableShowAll'		=> true
 	);
 	
 	if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
 	$filterGroups['enableButton']	= false;
?>

@extends('core.pm')
@section('funtionName')
RUN TICKET CAPTURE
@stop

@section('editBoxContentview')
	@include('editfilter.ticket_editfilter',['filters'			=> $filterGroups,
							    			'prefix'			=> "secondary_",
									    	])
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/ticket/load";
	actions.saveUrl 		= "/ticket/save";
	actions.historyUrl 		= "/ticket/history";
	
	actions.type = {
					idName:['TANK_ID','ID','FLOW_PHASE','OCCUR_DATE','TICKET_NO'],
					keyField:'ID',
					saveKeyField : function (model){
										return 'ID';
									},
					};

	var ticketValidating	= actions.validating;
	actions.validating = function (reLoadParams){
		var v = ticketValidating(reLoadParams);
		if(!v) return v;
		
		var validated = true;
		var message	= "";
		if(typeof actions.editedData == "object") {
			if(actions.editedData.RunTicketFdcValue !== undefined) {
				for (var i = 0; i < actions.editedData.RunTicketFdcValue.length; i++) {
					var entry 	= actions.editedData.RunTicketFdcValue[i];
					validated	= validated&&entry.TICKET_NO!==undefined&&entry.TICKET_NO!=null&&entry.TICKET_NO!="";
					if(!validated) {
						message = "Please fill Ticket No. in TICKET FDC tab";
						break;
					}
				}
			}
			if(validated && actions.editedData.RunTicketValue !== undefined){
				for (var i = 0; i < actions.editedData.RunTicketValue.length; i++) {
					var entry 	= actions.editedData.RunTicketValue[i];
					validated	= validated&&entry.TICKET_NO!==undefined&&entry.TICKET_NO!=null&&entry.TICKET_NO!="";
					if(!validated) {
						message = "Please fill Ticket No. in TICKET VALUE tab";
						break;
					}
				}
			}
		}
		if(message!="") alert(message);
		return validated;
	}
	
	addingOptions.keepColumns = ['OCCUR_DATE','TICKET_NO','TICKET_TYPE'];

 	actions.extraDataSetColumns = {/* 'TARGET_TANK': 'PHASE_TYPE', */'FLOW_ID': 'PHASE_TYPE'};
	
// 	source['PHASE_TYPE']	=	{	dependenceColumnName	:	['TARGET_TANK']};
	source['PHASE_TYPE']	=	{	dependenceColumnName	:	['FLOW_ID'],
									url						: 	'/ticket/loadsrc'
								};

	actions['parseChartDate'] = function(datetime){
		date = moment.utc(datetime,configuration.time.DATETIME_FORMAT_UTC);
		y 		= date.year();
		m 		= date.month();
		d 		= date.date();
		hour 	= date.hour();
		minute 	= date.minute();
		day = Date.UTC(y,m,d,hour,minute);
		return {data	: day,
				display	: date.format(configuration.time.DATETIME_FORMAT)};
	};


	var flows	= <?php echo json_encode($flows); ?>;
	var oGetValueTextOfSelect 		= actions.getValueTextOfSelect;
	actions.getValueTextOfSelect	= function(collection,data2,columnName){
		var text 	= oGetValueTextOfSelect(collection,data2,columnName);
		if(columnName == "FLOW_ID" && text == "&nbsp" ) {
			text	= oGetValueTextOfSelect(flows,data2,columnName);
		}
		return text;
	}
	var ogetTableOption = actions.getTableOption;
	actions.getTableOption	= function(data,tab){
		$.each(data.properties, function( index, property ) {
			if(property.data == "TARGET_TANK") {
				property.INPUT_TYPE =  {
						applyEditable	: function(tab,type,td, cellData, rowData, aProperty,collection){
							editBox.addMoreHandle(null,rowData,td,tab,$(td));
							if (cellData==null||cellData=='') 
								cellDataText =  "&nbsp";
							else 
								cellDataText = actions.getValueTextOfSelect(tanks,cellData,property.data);
							$(td).html(cellDataText);
						},
						render			: function (data,cindex ) {
							return function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								return actions.getValueTextOfSelect(tanks,data2,property.data);
							}
						}
				};
			}
	   	});
		return ogetTableOption(data,tab);
	};
	
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.size	= {	height 	: 180,
						width 	: 700,
					};
	editBox.hidenFields = [{name:'Tank',field:'TANK_ID'}];
</script>
@stop



@section('endDdaptData')
@parent
<script>
	var tanks 	= <?php echo json_encode($tanks); ?>;
	editBox.editSelectedObjects	= function(dataStore,resultText){
		if(typeof editBox.currentId != "undefined"){
			var tab 	= getActiveTabID();
        	var table 	= $('#table_'+tab).DataTable();
			var rowData	= table.row( '#'+editBox.currentId).data();
			rowData.TARGET_TANK	= dataStore.Tank;
			/* var success = actions.getEditSuccessfn(null,tab,null,rowData,"TARGET_TANK",[],"select");
			success(null,parseFloat(dataStore.tank)); */

			rowData 	= actions.putModifiedData(tab,"TARGET_TANK",rowData.TARGET_TANK,rowData,"text");
			/* cellDataText = actions.getValueTextOfSelect(tanks,rowData.TARGET_TANK);
			rowData.TARGET_TANK	 = cellDataText; */
			table.row( '#'+editBox.currentId ).data(rowData);

			
			/* rowData.TARGET_TANK	= dataStore.Tank;
			row.data(rowData);
			editBox.currentTable.draw( false );
			actions.putModifiedData(tab,"TARGET_TANK",rowData.TARGET_TANK,rowData,"text"); */
		}
	}

	var orenderFilter = editBox.renderFilter;
	editBox.renderFilter = function(rowData){
		orenderFilter(rowData);
		$('#secondary_CodeProductType').val(rowData.PHASE_TYPE);
		$('#secondary_CodeProductType').change();
//  		$('#secondary_CodeProductType').attr('disabled','disabled');
	};
</script>
@stop


