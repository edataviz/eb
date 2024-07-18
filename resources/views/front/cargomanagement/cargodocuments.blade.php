<?php
	$currentSubmenu ='/pd/cargodocuments';
	$tables 		= ['PdCargoDocument'	=>['name'=>'Load']];
	$detailTableTab ='PdDocumentSetData';
	$isLoad 		= 1;
	$mdl			= "App\Models\PdDocumentSetContactData";
	$secondaryField = $mdl::getTableName();
	$pageMain = 'PdCargoDocuments';
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargodocuments/load";
	actions.saveUrl = "/cargodocuments/save";
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdCargoDocument";
	};
	
	actions['idNameOfDetail'] 	= ['ID','VOYAGE_ID', 'DOCUMENT_ID','CARGO_ID','PARCEL_NO','LIFTING_ACCOUNT'];
	actions.type.keyField 		= 'DT_RowId';

	editBox.editRow = function(id,rowData){
		$('#editDocDetail content').html('Loading...');
		$('#editDoc').dialog({
			height: 400,
			width: 900,
			position: { my: 'top', at: 'top+150' },
			modal: true,
			title: "Document details",
		});

		postRequest('/cargodocument_loaddetail',
			{
				voyageId		: rowData.VOYAGE_ID,
				cargoId			: rowData.CARGO_ID,
				parcelNo		: rowData.MASTER_NAME,
				lifftingAcount	: rowData.LIFTING_ACCOUNT,
			},
			function(data){
				$('#editDocDetail content').html(data);
			}
		);
	}
	
	editBox['moreActionTitle'] 	= 'Add report set';
	var currentRowData;
	editBox.initExtraPostData = function (id,rowData){
		currentRowData =  	{
 					id				: id,
 					voyageId		: rowData.VOYAGE_ID,
		 			cargoId			: rowData.CARGO_ID,
		 			parcelNo		: rowData.MASTER_NAME,
		 			lifftingAcount	: rowData.LIFTING_ACCOUNT,
					tabTable		: '{{$detailTableTab}}',
	 		};
		return currentRowData;
 		
 	};

	editBox['filterField'] = 'CODE';
	
	var addContactData = function(addingRow){
		addingRow['{{$secondaryField}}'] = [];
     	var table 	= $('#table_PdDocumentSetData').DataTable();
		var columns = table.settings()[0].aoColumns;
		$.each(columns, function( index, column ) {
			var key = column.data;
			if(key.startsWith("ORGINAL_ID")||key.startsWith("NUMBER_COPY")){
				addingRow[key] =  " ";
				if(key.startsWith("ORGINAL_ID")){
			 		addingRow = pushContactEntry(addingRow);
				}
			}
	   	});
		
		return addingRow;
	};

	var pushContactEntry =  function(entry){
		if(typeof(entry['{{$secondaryField}}']) === "undefined") entry['{{$secondaryField}}'] = [];
		var dtid = 'NEW_RECORD_DT_RowId_'+(index++);
		entry['{{$secondaryField}}'].push({
				DOCUMENT_SET_DATA_ID	: entry.ID,
				CONTACT_ID				: 0,	
				DT_RowId				: dtid,
				ID						: dtid,
				NUMBER_COPY				: null,
				ORGINAL_ID				: null
			});
		return entry;
	}

		
	var addContactHandle =  function(e){
			var table 	= $('#table_PdDocumentSetData').DataTable();
			var ddataSet = [];
			$.each(table.data(), function( dIndex, dEntry ) {
				dEntry = pushContactEntry(dEntry);
				ddataSet.push(dEntry);
		   	});

// 			var editedData = actions.editedData["PdDocumentSetContactData"];
			
			var ddata = {
							PdDocumentSetData : {
												properties 	: dproperties,
												dataSet 	: ddataSet,
												selects		: oselects,
												suoms		: osuoms}
						};
			editBox.editGroupSuccessAddContact(ddata,id);
	};
	
	editBox.addMoreActionButton = function (table,tab){
		jQuery('<button/>', {
			    id		: 'more_contact_'+tab,
			    title	: 'Add contact',
			    text	: 'Add contact'
		}).on( 'click',addContactHandle)
		.appendTo("#toolbar_"+tab);
// 			box.appendTo($("#toolbar_"+tab));
	};
	
	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['CODE'] 				= selectRow.CODE;
		addingRow['NAME'] 				= selectRow.NAME;
		addingRow['VOYAGE_ID'] 			= currentRowData.voyageId;
		addingRow['CARGO_ID'] 			= currentRowData.cargoId;
		addingRow['PARCEL_NO'] 			= currentRowData.parcelNo;
		addingRow['LIFTING_ACCOUNT'] 	= currentRowData.lifftingAcount;
		addingRow['DOCUMENT_ID'] 		= selectRow.ID;
		addingRow 						= addContactData(addingRow);
		return addingRow;
	};

	actions['initDeleteObject']  = function (tab,id, rowData) {
		if(tab=='PdDocumentSetContactData') 
			return {'ID'	:id,
					index	:rowData.index
					};
		return {'ID':id};
	};

	var dproperties = [];
	var oselects = [];
	var osuoms = [];
	var dataSet = [];
	
	contractOnchage = function(select,originContractId,oindex){
		var tab = "PdDocumentSetContactData";
		var secondaryField = '{{$secondaryField}}';
	   	var recordData = actions.deleteData;
	   	
		if(select.value==0){
	   		if (!(tab in recordData)) {
	    		recordData[tab] = [];
	    	}
	    	//remove in postdata
        	var eData = recordData[tab];
//         	var editedData = actions.editedData[tab];
        	
        	$.each(dataSet, function( dIndex, dEntry ) {
				$.each(dEntry[secondaryField], function( index, value ) {
					if(oindex==index){
		        		var deleteObject = actions.initDeleteObject(tab,value.ID,{index:oindex});
				    	eData.push(deleteObject);
					}
			   	});
				dEntry["CONTACT_ID-"+oindex]	=  null;
		   	});
		}
		else{
			if ((tab in recordData)) {
				var eData = recordData[tab];
	        	$.each(eData, function( index, value ) {
	        		if(typeof(value) !== "undefined" && oindex==value.index) delete eData[index];
			   	});
	    	}
	    	
	     	/* var table 	= $('#table_PdDocumentSetData').DataTable();
			var dtst = table.data(); */
			
			$.each(dataSet, function( dIndex, dEntry ) {
				dEntry["CONTACT_ID-"+oindex]	=  select.value;
				var isAdding = typeof( dEntry['DT_RowId']) === 'string' && dEntry.DT_RowId.indexOf('NEW_RECORD_DT_RowId') > -1;
				if(isAdding){
		            actions.putModifiedData("PdDocumentSetData","CONTACT_ID-"+oindex,	select.value,dEntry);
		            actions.putModifiedData("PdDocumentSetData","ORGINAL_ID-"+oindex,	dEntry["ORGINAL_ID-"+oindex],	dEntry);
		            actions.putModifiedData("PdDocumentSetData","NUMBER_COPY-"+oindex,	dEntry["NUMBER_COPY-"+oindex],	dEntry);
				}
				else{
					$.each(dEntry[secondaryField], function( vindex, value ) {
						if(oindex==vindex){
				            actions.putModifiedData(tab,'CONTACT_ID',select.value,value);
	 			            actions.putModifiedData(tab,'DOCUMENT_SET_DATA_ID',dEntry.ID,value);
						}
				   	});
				}
		   	});
		}
	};
	editBox.editGroupSuccess = function(data,id){
		actions.loadSuccess(data);
	};

	editBox.editGroupSuccessAddContact = function(data,id){
		tab 			= '{{$detailTableTab}}';
		options 		= editBox.getEditTableOption(tab);
		subData 		= data[tab];
		$("#table_"+tab+"_containerdiv").html("<table id=\"table_"+tab+"\" class=\"fixedtable nowrap display\">");
		dataSet 		= subData.dataSet;
		oselects 		= subData.selects;
		osuoms 			= typeof(subData.suoms) !== "undefined"?subData.suoms:[];
		dproperties 	= $.extend(true, [], subData.properties); 
		
		if(dataSet.length>0){
			var set = dataSet[0];
			var secondaryField = '{{$secondaryField}}';
			var set2 = set[secondaryField];
			if(set2.length>0){
				var properties 	= subData.properties;
				var suoms		= [];
				subData['uoms'] = suoms;
				
				var selects 	= (typeof(subData.selects) !== "undefined" && typeof(subData.selects['BaAddress']) !== "undefined")?subData.selects['BaAddress']:[];
				$.each(set2, function( index, entry ) {
					var contractId = entry.CONTACT_ID;
					var sel = $('<select id="select_contact_'+index+'" onchange="contractOnchage(this,'+contractId+','+index+');">');
					sel.addClass("withAuto");
					sel.append($("<option>").attr('value',0).text(''));
					$.each(selects, function( si, se ) {
						var sOption = $("<option>").attr('value',se.ID).text(se.NAME);
						if(contractId==se.ID){
							sOption.attr('selected', true);
						}
						sel.append(sOption);
					});
  					sel.val(contractId);
					var th 					= $('<div>').append(sel);
					var title 				= th.html();
					var originColumn 		= "ORGINAL_ID-"+index;
					var numberCopyColumn 	= "NUMBER_COPY-"+index;
					var column 				= {	'data' 			: 	originColumn,
												'title' 		:  	title,
												'width'			: 	90,
												'INPUT_TYPE'	: 	1,
												'DATA_METHOD'	: 	1,
			 									'FIELD_ORDER'	: 	index*2+3
											};
					properties.push(column);
					properties.push({	'data' 			: 	numberCopyColumn,
										'title' 		:  	" ",
										'width'			: 	30,
										'INPUT_TYPE'	: 	1,
										'DATA_METHOD'	: 	1,
	 									'FIELD_ORDER'	: 	index*2+4
					});
					var sData 				= osuoms[0];
					suoms.push({	COLUMN_NAME : originColumn,
										data 	: sData,
										id 		: originColumn,
										targets	: index*2+2
									});

					sData 					= subData.suoms[1];
					suoms.push({	COLUMN_NAME : numberCopyColumn,
										data 	: sData,
										id 		: numberCopyColumn,
										targets	: index*2+3
									});
	            });

	            
				$.each(dataSet, function( dIndex, dEntry ) {
					$.each(dEntry[secondaryField], function( index, value ) {
						var originColumn 			= "ORGINAL_ID-"+index;
						var numberCopyColumn 		= "NUMBER_COPY-"+index;
						dEntry[originColumn] 		= 	(typeof(dEntry[originColumn]) !== "undefined"
														&&dEntry[originColumn] !== ""
														&&dEntry[originColumn] !== null)?
														dEntry[originColumn]:value.ORGINAL_ID;
						dEntry[numberCopyColumn] 	= 	(typeof(dEntry[numberCopyColumn]) !== "undefined"
															&&dEntry[numberCopyColumn] !== ""
															&&dEntry[numberCopyColumn] !== null)?
															dEntry[numberCopyColumn]:value.NUMBER_COPY;
				   	});
			   	});
			}
		}
		var etbl = renderTable(tab,subData,options,actions.createdFirstCellColumn);
		if(etbl!=null) actions.afterDataTable(etbl,tab);
	}

	oInitSavingDetailData = editBox['initSavingDetailData'];
	editBox['initSavingDetailData'] = function(editId,saveUrl) {
		var pdDocumentSetData 		= actions.editedData["PdDocumentSetData"];
		if(typeof(pdDocumentSetData) !== "undefined"){
			var tab;
			var rowData;
			var vl;
			
			$.each(pdDocumentSetData, function( index, value ) {
				if(typeof(value) !== "undefined" ){
					var isAdding = typeof( value['DT_RowId']) === 'string' && value.DT_RowId.indexOf('NEW_RECORD_DT_RowId') > -1;
					for (var key in value) {
						if(key.startsWith("ORGINAL_ID")||key.startsWith("NUMBER_COPY")){
							var parts = key.split("-");
							if(parts.length>=2){
								var dindex 	= parts[1];
								var column 	= parts[0];
								if(!isAdding){
									tab		= "PdDocumentSetContactData";
									rowData	= getPdDocumentSetContractData(dindex,value.DT_RowId);
									if(rowData==null) break;
									vl		= value[key];
								}
								else if(key.startsWith("ORGINAL_ID")){
									tab		= "PdDocumentSetData";
									column  = "CONTACT_ID-"+dindex;
									rowData	= value;
									vl 		=  $("#select_contact_"+dindex).val();
								}
								actions.putModifiedData(tab,column,vl,rowData);
							}
						}
						else if(key=="ID" && typeof value.ID === 'undefined' )  value.ID = value.DT_RowId;
					}
				}
	   		});
//   	  		delete actions.editedData["PdDocumentSetData"];
		}
		return oInitSavingDetailData(editId,saveUrl);
	};

	getPdDocumentSetContractData = function(index,DT_RowId){
		var secondaryField = '{{$secondaryField}}';
     	var table 	= $('#table_PdDocumentSetData').DataTable();
		var rowData = table.row('#'+DT_RowId).data();
		if(typeof(rowData) === "undefined" ) return null;
		var entry 	= rowData[secondaryField];
		return entry[index];
	}

	editBox['saveFloatDialogSucess'] = function(data,saveUrl){
// 		if(typeof(actions.editedData["PdDocumentSetContactData"]) !== "undefined" ){
		showWaiting();
	  	delete actions.editedData["PdDocumentSetData"];
	  	delete actions.editedData["PdDocumentSetContactData"];
		$.ajax({
			url: editBox.loadUrl,
			type: "post",
			data: currentRowData,
			success:function(data){
				hideWaiting();
				console.log ( "send "+editBox.loadUrl+"  success : "/* +JSON.stringify(data) */);
				editBox.editGroupSuccess(data,currentRowData.id);
			},
			error: function(data) {
				hideWaiting();
				console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
				$("#box_loading").html("reopen the dialog see updates");
			}
		});
// 		}
	}


	editBox['putFieldsData'] = function(value){
		var rid 						= 'NEW_RECORD_DT_RowId_'+(index++);
		var addingRow 					= value;
		addingRow['DOCUMENT_ID'] 		= value['ID'];
		addingRow['DT_RowId'] 			= rid;
 		addingRow['ID'] 				= rid;
		addingRow['VOYAGE_ID'] 			= currentRowData.voyageId;       
		addingRow['CARGO_ID'] 			= currentRowData.cargoId;        
		addingRow['PARCEL_NO'] 			= currentRowData.parcelNo;       
		addingRow['LIFTING_ACCOUNT'] 	= currentRowData.lifftingAcount; 
		addingRow 						= addContactData(addingRow);

		var table 	= $('#table_PdDocumentSetData').DataTable();
		var columns = table.settings()[0].aoColumns;
		$.each(columns, function( i, cvalue ) {
			if(addingRow[cvalue.data]!='') actions.putModifiedData("PdDocumentSetData",cvalue.data,addingRow[cvalue.data],addingRow);
        });
		
		return addingRow;
	}

    editBox['buildExtraSaveDetailData'] = function(editId,saveUrl) {
  		return {
  				voyageId		: currentRowData.voyageId,
	 			cargoId			: currentRowData.cargoId,
	 			parcelNo		: currentRowData.parcelNo,
	 			lifftingAcount	: currentRowData.lifftingAcount,
	 	};
	};
	
    actions.getRenderFirsColumnFn = function (tab) {
        if(tab=='{{$detailTableTab}}') return actions.renderFirsEditColumn;
		return actions.renderFirsColumn;
	}
	
    actions.enableUpdateView = function(tab,postData){
		return tab=='PdVoyage';
	};
</script>
@stop


@section('editBoxParams')
@parent
<script>
var globalTotalContact = 0;    
function buildContact()
{
    globalTotalContact ++;
    $('[id^="DETAIL_NUMBER_COPY_ORGINAL_ID'+globalTotalContact+'_"]').css("display", "");
    $('[id="DETAIL_BA_ADDRESS_TH'+globalTotalContact+'"]').css("display", "");
}

function applyReportSet(id)
{
	$("#box_select_activity_set").dialog("close");
    postRequest('/cargodocument_applyreportset', {id:id},
            function(data) {
				var ds=data.split(';');
				var i=0;
				for(i=0;i<ds.length;i++) {
					var s2=ds[i].split(':');
					if(s2.length==2)
						addActivityRow(s2[0],s2[1]);
				}
            }
    );
}

function loadReportList(){
	$('#box_select_activity').dialog({
		width: 300,
		modal: true,
		title: "Select report",
	});
}
function loadReportSet(){
	$('#box_select_activity_set').dialog({
		width: 300,
		modal: true,
		title: "Select report set",
	});
}

//XOA DU LIEU
function deleteRow(a, isOldRow)
{
	if(isOldRow)
	{
		$("#RD_" + a).remove();
	}
	else if(a>0) // GIU LAI MOT ROW MAU
	{
		$("#newRow"+a).remove();
	}
}

var newRowInd=0;
function addActivityRow(id,text) {
	try{$('#box_select_activity').dialog('close');}catch{}
	var o=$("#input_activity_"+id);
	if(o.val()>0)
	{
		o.parent().parent().effect("highlight", {}, 5000);
		return;
	}
	//$("#container_QUALITY").html(original_table);
	newRowInd++;	
	var sRowHTML="<tr class='row_activity' id='newRow"+newRowInd+"'>"+$("#newRow0").html()+"</tr>";	//LAY NEW ROW 0

	sRowHTML=sRowHTML.replace(/-000/g,id);
	sRowHTML=sRowHTML.replace("activity_text_holder",text);

	sRowHTML=sRowHTML.replace("loadSrcID_NEW(0)","loadSrcID_NEW("+newRowInd+")");
	sRowHTML=sRowHTML.replace("deleteRow(0, false)","deleteRow("+newRowInd+", false)");
	sRowHTML=sRowHTML.replace("editRow(0, false)","editRow("+newRowInd+", false)");
	sRowHTML=sRowHTML.replace(/_NEW0/g,"_NEW"+newRowInd);			
	//for(var n=1;n<=10;n++) {
	sRowHTML=sRowHTML.replace(/_NEW1_0/g,"_NEW1_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW2_0/g,"_NEW2_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW3_0/g,"_NEW3_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW4_0/g,"_NEW4_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW5_0/g,"_NEW5_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW6_0/g,"_NEW6_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW7_0/g,"_NEW7_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW8_0/g,"_NEW8_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW9_0/g,"_NEW9_"+newRowInd);				
	sRowHTML=sRowHTML.replace(/_NEW10_0/g,"_NEW10_"+newRowInd);				
	//}
	$('#body_data_detail').append(sRowHTML);
	
	//Element not delegation
	$("#body_data_detail ._datetimepicker").removeClass('hasDatepicker').datetimepicker();
	$("#body_data_detail ._datepicker").removeClass('hasDatepicker').datepicker();
	//$("#body_data_detail ._numeric").numericInput({ allowFloat: true, allowNegative: true });
	//original_table=$("#container_QUALITY").html();
	
	//freeze();

}

function save()
{
	$('input[name="new_ind"]').val(newRowInd);
	postRequest('/cargodocument_savedetail', $('form#form_fdc').serialize(),
		function(data){
			if(data!="")
			{
				alert(data);
			}
			else
			{
				alert("Save successfully");
			}
		}
	);
	newRowInd=0;
}
</script>
<style>
.dialog-box {
	display: none;
}
#editDoc button {
	margin: 2px;
	float: left;
}
#editDocDetail {
	width: 100%;
	height: calc(100% - 56px);
    overflow: auto;
    margin-bottom: 10px;
    margin-top: 5px;
    border-bottom: 1px solid #e0e0e0;
}
#editDocButtons {
	height: 40px;
	position: relative;
}
#editDocDetail select {
	width: unset;
	margin: 1px;
	font-size: 12px!important;
}
#editDocDetail table th {
	padding-right: 10px;
}
#editDocDetail select[name^="DETAIL_BA_ADDRESS"] {
	width: 190px;
	font-weight: bold;
    color: #528ecc;
}
#editDocDetail select[name^="DETAIL_ORGINAL"] {
	width: calc(100% - 66px);
}
#editDocDetail select[name^="DETAIL_NUMBER"] {
	width: 50px;
}
#editDocDetail table thead tr {
	color: #528ecc;
}
#box_select_activity_set {
	display:none;
	border:unset!important;
	position:unset!important;
	width:unset!important;
	bottom:unset!important;
	left:unset!important;
	background:unset!important;
}
#form_fdc {
	display: initial;
}
</style>
<div id="editDoc" class="dialog-box">
	<div id="editDocDetail">
		<form name="form_fdc" id="form_fdc" method="POST">
			<input type="hidden" name="new_ind" value="0">
			<content></content>
		</form>
	</div>
	<div id="editDocButtons">
		<button onclick="loadReportList()">Add report</button>
		<button onclick="loadReportSet()">Add report set</button>
		<button onclick="buildContact()">Add contact</button>
		<button onclick="$('#editDoc').dialog('close')" style="float: right;">Cancel</button>
		<button onclick="save()" style="float: right;">Save</button>
	</div>
</div>
<div id="box_select_activity" class="dialog-box">
		<table border='0' style='width:100%' cellpadding='5' cellspacing='0'>
<?php
	$sql="select * from PD_REPORT_LIST order by `ORDER`";
	$re=\DB::select($sql);
	foreach($re as $row){
		echo "<tr class='row_activity' style='cursor:pointer' onclick=\"addActivityRow($row->ID,'$row->NAME')\"><td>$row->NAME</td></tr>";
	}
?>
		</table>
</div>
<div id="box_select_activity_set" class="dialog-box">
	<table border='0' style='width:100%' cellpadding='5' cellspacing='0'>
		@foreach($activities as $activity )
			<tr class='row_activity' style='cursor:pointer' onclick="applyReportSet({{$activity->SET_ID}})">
			<td>{{$activity->SET_NAME}}</td>
			</tr>
		@endforeach
	</table>
</div>
@stop
