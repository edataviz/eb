<?php
	$currentSubmenu ='/pd-cargomeasurement';
	$tables = ['PdVoyage'	=>['name'=>'Load', 'readonly' => true]];
	$detailTableTab = 'PdShipOilLpgTankData';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/cargomeasurement/load";
	actions.saveUrl = "";//"/cargomeasurement/save";

	actions['idNameOfDetail'] = ['VOYAGE_ID', 'ID','STORAGE_ID','CARGO_ID'];

	addingOptions.keepColumns = ['LIFTING_ACCOUNT','LOAD_UOM','BERTH_ID'];

	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="PdVoyage";
	};

	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="edit_row_'+id+'" class="actionLink">&nbsp;Select</a>';
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a id="gen_row_'+id+'" class="actionLink">Gen. VEF</a>';
		}
		return html;
	};

	actions['addMoreHandle']  = function ( table,rowData,td,tab) {
		var id = rowData['DT_RowId'];
		var moreFunction = function(e){
			showWaiting();
		    postData = {id:id};
		    $.ajax({
				url: '/cargomeasurement/gen-vef',
				type: "post",
				data: postData,
				success:function(data){
					alert(JSON.stringify(data));
					hideWaiting();
				},
				error: function(data) {
					alert("error");
					console.log ("gen-vef error");
					console.log (data);
					hideWaiting();
				}
			});
		};
//		$(td).find('#edit_row_'+id).click(editFunction);
		table.$('#gen_row_'+id).click(moreFunction);
	};
	
	actions.getAddButtonHandler = actions.getDefaultAddButtonHandler;

	editBox.editRow = function(id,rowData){
		editBox.rowData = rowData;
		$('#editPopupDetail content').html('Loading...');
		$('#editPopup').dialog({
			height: 400,
			width: 900,
			position: { my: 'top', at: 'top+150' },
			modal: true,
			title: "Cargo measurement details",
		});

		postRequest('/cargomeasurement_loaddetail',
			{
				voyageId		: editBox.rowData.VOYAGE_ID,
				carrierId		: editBox.rowData.CARRIER_ID,
				date_begin		: moment($('#date_begin').datepicker('getDate')).format('YYYY-MM-DD'),
				date_end		: moment($('#date_end').datepicker('getDate')).format('YYYY-MM-DD'),
			},
			function(data){
				$('#editPopupDetail content').html(data);
				$("#editPopupDetail ._datetimepicker" ).datetimepicker({
					dateFormat: 'yy-mm-dd',
					timeFormat: 'HH:mm:ss',
					//maxDate: $('#tdate').val()?$('#tdate').val():false,
					//minDate: $('#tdate').val()?$('#tdate').val():false
				});
				$('#box_add_all_object_type #add_all_object_type').html($('#tableMeas select[name="MEAS_MEASURE_TYPE_NEW0"]').html());
				$('#box_add_all_object_type #add_all_object_port').html($('#tableMeas select[name="MEAS_PORT_ID_NEW0"]').html());
				//$('#box_add_all_object_type #add_all_object_product').html($('#tableMeas select[name="MEAS_PRODUCT_NAME_NEW0"]').html());
			}
		);
	}
	
var newRowInd=0;
function addMeas(){
	newRowInd++;
	var $tr = $('#tableMeas').append($('#tableMeas tr[rid="_NEW0"]')[0].outerHTML.replace(/_NEW0/g, '_NEW'+newRowInd));
	$tr.find('._datetimepicker' ).datetimepicker({
		dateFormat: 'yy-mm-dd',
		timeFormat: 'HH:mm:ss',
	});
	var ids = $('input[name="delete_ids"]').val();
	$('input[name="delete_ids"]').val(ids + (ids ? "," : "") + rid);
	return $tr;
}
var disableLoadData = false;
function addAllMeas(){
	$('#box_add_all_object_type').dialog({
		width: 300,
		modal: true,
		title: "Add all objects",
		buttons: {
			'OK': function(){
				$(this).dialog('close');
				var measureType = $('#add_all_object_type').val();
				var portId = $('#add_all_object_port').val();
				var productType = $('#add_all_object_product').val();
				if(measureType == 1 && editBox.rowData.CARRIER_ID || ((measureType == 2 || measureType == 4) && portId) || (measureType == 3)){
					postRequest('/cargomeasurement_addallobjects', {
						measureType: measureType,
						carrierId: editBox.rowData.CARRIER_ID,
						productType: productType,
						portId: portId,
						newRowInd: newRowInd,
					},
					function(data){
						//newRowInd = data.newRowInd;
						//$('#tableMeas').append(data.rows);
						disableLoadData = true;
						var arr = JSON.parse(data);
						var opts = "";
						arr.forEach(function(item){
							opts += "<option value='"+item[0]+"'>"+item[1]+"</option>";
						});
						arr.forEach(function(item){
							var $tr = addMeas();
							var rid = '_NEW'+newRowInd;
							$tr.find('select[name="MEAS_MEASURE_TYPE'+rid+'"]').val(measureType);
							$tr.find('select[name="MEAS_PORT_ID'+rid+'"]').val(portId);
							$tr.find('select[name="MEAS_COMPARTMENT_ID'+rid+'"]').html(opts.replace("value='"+item[0]+"'", "value='"+item[0]+"' selected"));
						});
						disableLoadData = false;
					});
				}
			},
			'Cancel': function(){
				$(this).dialog('close');
			},
		}
	});
}
function onPortChanged(rid){
	var measureType = $('select[name="MEAS_MEASURE_TYPE'+rid+'"]').val();
	if(measureType == 2 || measureType == 3 || measureType == 4)
		loadMeasObject(rid);
}
function onProductChanged(rid){
	/*
	var measureType = $('select[name="MEAS_MEASURE_TYPE'+rid+'"]').val();
	if(measureType == 3)
		loadMeasObject(rid);
	*/
}
function loadMeasObject(rid){
	if(disableLoadData) return;
	var measureType = $('select[name="MEAS_MEASURE_TYPE'+rid+'"]').val();
	var productType = $('select[name="MEAS_PRODUCT_NAME'+rid+'"]').val();
	var portId = $('select[name="MEAS_PORT_ID'+rid+'"]').val();
	if(measureType == 1 && editBox.rowData.CARRIER_ID || ((measureType == 2 || measureType == 4) && portId) || (measureType == 3)){
		postRequest('/cargomeasurement_loadobjects', {
				measureType: measureType,
				carrierId: editBox.rowData.CARRIER_ID,
				productType: productType,
				portId: portId,
			},
			function(data){
				$('select[name="MEAS_COMPARTMENT_ID'+rid+'"]').html(data);
			}
		);
	}
}

function deleteMeas(rid)
{
	$("#tableMeas tr[rid='"+rid+"']").remove();
	if(Number(rid)>0){
		var ids = $('input[name="delete_ids"]').val();
		$('input[name="delete_ids"]').val(ids + (ids ? "," : "") + rid);
	}
}

function saveMeas()
{
	$('input[name="new_ind"]').val(newRowInd);
	postRequest('/cargomeasurement_savedetail', $('form#form_fdc').serialize(),
		function(data){
			if(data!="")
			{
				//alert(data);
				$('#editPopupDetail content').html(data);
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
#editPopup button {
	margin: 2px;
	float: left;
}
#editPopupDetail {
	width: 100%;
	height: calc(100% - 56px);
    overflow: auto;
    margin-bottom: 10px;
    margin-top: 5px;
    border-bottom: 1px solid #e0e0e0;
}
#editPopupButtons {
	height: 40px;
	position: relative;
}
#editPopupDetail select {
	width: unset;
	margin: 1px;
	font-size: 12px!important;
}
#editPopupDetail table th {
	padding-right: 10px;
}
#editPopupDetail table thead tr {
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
#tableMeas td input {
	margin-bottom: 0;
}
#tableMeas td ._readonly {
	pointer-events: none;
	background: #eeeeee;
}
#tableMeas tr[rid="_NEW0"] {
	display: none;
}
#box_add_all_object_type .input-title{
	display: block;
	margin: 5px 0 2px 0;
	font-weight: bold;
}
#box_add_all_object_type {
	padding-left: 20px;
	padding-top: 20px;
}
</style>
<div id="editPopup" class="dialog-box">
	<div id="editPopupDetail">
		<form name="form_fdc" id="form_fdc" method="POST">
			<input type="hidden" name="new_ind" value="0">
			<input type="hidden" name="delete_ids" value="">
			<content></content>
		</form>
	</div>
	<div id="editPopupButtons">
		<button onclick="addMeas()">Add</button>
		<button onclick="addAllMeas()">Add all objects</button>
		<button onclick="$('#editPopup').dialog('close')" style="float: right;">Cancel</button>
		<button onclick="saveMeas()" style="float: right;">Save</button>
	</div>
</div>
<div id="box_add_all_object_type" class="dialog-box">
<div class="input-title">Object type</div><select id="add_all_object_type"></select><br>
<div class="input-title">Port</div><select id="add_all_object_port"></select><br>
<!-- <div class="input-title">Product</div><select id="add_all_object_product"></select><br> -->
</div>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = '/cargomeasurement/loaddetail';
	editBox.saveUrl = '/cargomeasurement/savedetail';

	editBox['size'] = {	height : 380,
						width : 900,
			};
</script>
@stop