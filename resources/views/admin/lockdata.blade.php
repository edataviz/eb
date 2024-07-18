<?php
$currentSubmenu = '/am/lockdata';
$configuration	= isset($configuration)?$configuration:auth()->user()->getConfiguration();

$listControls = [
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit'
		),

		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id'
		),

		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'fkey' => 'area_id'
		),

		'DataTableGroup' => array (
				'label' => 'DataTableGroup',
				'ID' => 'DataTableGroup',
				'default' => 'All'
		),

		'IntObjectType' => array (
				'label' => 'Object Type',
				'ID' => 'IntObjectType',
				'default' => 'All'
		),

		'loadData' => array(
				'label' => 'Load Data',
				'ID' => 'loadData',
				'TYPE' => 'BUTTON',
				'onclick' => '_lock.loadData()'
		),

		'begin_date' => array (
				'label' => 'Lock Date (<span style="font-size:10px; color: red;">for lock All</span>)',
				'ID' => 'begin_date',
				'TYPE' => 'DATE',
				'FORMAT'	=> $configuration['time']['DATE_FORMAT_CARBON'],
		),

		'lockData' => array(
				'label' => 'Lock All',
				'ID' => 'Lock Data All',
				'TYPE' => 'BUTTON',
				'onclick' => '_lock.lockAll()'
		)
];

?>

@extends('core.am', ['listControls' => $listControls])

@section('content')

<script type="text/javascript">
$(function(){
	$("#checkAll").click(function () {
        $('.chckbox').prop('checked', this.checked);
	});

	$('#listValidate').css('width', 754);
});

var _lock = {
		loadData : function (){
			param = {
				'FACILITY_ID' : $('#Facility').val(),
				'GROUP_ID' : $('#DataTableGroup').val(),
				'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/loadLockData', param, function(data){
				_lock.listData(data.result);
			});
		},

		listData : function(data){
			var str = '';
			$('#bodyList').html('');

			for(var i = 0; i< data.length; i++){
				var id = data[i].ID;
				var k = i+"abc"+data[i].TABLE_NAME;
				var cssClass = "row1";
				if(i%2 == 0){
					cssClass = "row2";
				}
				str += '<tr class='+ cssClass +'>';
				str += '	<td class="vcolumn35"><input class="chckbox" table_name = '+data[i].TABLE_NAME+' name='+data[i].ID +' type="checkbox" ' + (data[i].T_ID) + '></td>';
				str += '	<td class="vcolumn205" id="table_name_'+i+'">'+ checkValue(data[i].TABLE_NAME,'') +'</td>';
				str += '	<td class="vcolumn205">'+ checkValue(data[i].FRIENDLY_NAME,'') +'</td>';
				str += '	<td class="vcolumn165"><input type="text" id="txtDateFrom_'+i+'" value="'+formatDate(checkValue(data[i].LOCK_DATE,''))+'"/></td>';
				str += '	<td class="vcolumn105"><input type="button" onclick="_lock.lockData('+i+')" value="Lock" class="btnValidate"/></td>';
				str += '</tr>';
			}

			$('#bodyList').html(str);

			 $( "input[type='text']" ).datepicker({
				changeMonth:true,
				changeYear:true,
				dateFormat	:jsFormat
			});


		},

		lockData : function(index){
			var tableName = $('#table_name_'+index).text();
			var dateFrom = $('#txtDateFrom_'+index).val();

			if(dateFrom == ""){
				alert("Please select date range to validate data");
				return;
			}

			param = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'TABLE_NAMES' : tableName,
					'USER_ID' : $('#textUsername').text(),
					'GROUP_ID' : $('#DataTableGroup').val(),
					'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/lockData', param, function(data){
				_lock.listData(data.result);
				alert("Complete");
			});
		},

		lockAll : function(){
			var tableName="";
			$('.chckbox').each(function(){
				if(this.checked) tableName+=(tableName==""?"":",")+$(this).attr("table_name");
			});
			if(tableName=="")
			{
				alert("Please select tables that you want to lock data");
				return;
			}

			if(!confirm("Are you sure to lock all data of facility "+$("#Facility option:selected").text()+"?")) return;

			var dateFrom = $('#begin_date').val();//.replace('-','/');
			param = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'TABLE_NAMES' : tableName,
					'USER_ID' : $('#textUsername').text(),
					'GROUP_ID' : $('#DataTableGroup').val(),
					'OBJECTTYPE' : $('#ObjectType').val()
			}

			sendAjax('/am/lockData', param, function(data){
				_lock.listData(data.result);
				alert("Complete");
			});
		}
}
</script>

<div id="dialog" style="display: none; height: 35px">
	<div id="chart_change">
		<table>
			<tr>
				<td>Group name:</td>
				<td><input type="text" size="" value="" id="d_group_name"
					style="width: 250px"></td>
			</tr>
		</table>
	</div>
</div>

<div>
	<table style="table-layout: fixed;">
		<thead id="table5">
			<tr>
				<td class="column30"><input class="chckAll" type="checkbox"
					id="checkAll"></td>
				<td class="column200">Data table</td>
				<td class="column200">Name</td>
				<td class="column160">Last locked date</td>
				<td class="column100">Action</td>
			</tr>
		</thead>
	</table>

	<div id="listValidate">
		<table style="table-layout: fixed;">
			<tbody id="bodyList">
			</tbody>
		</table>
	</div>
</div>
@stop
