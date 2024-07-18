<?php
$currentSubmenu = '';

$listControls = [
		'DataTableGroup' => array (
				'label' => 'DataTableGroup',
				'ID' => 'DataTableGroup',
				'default' => 'All'
		)
];

?>

@extends('core.am', ['listControls' => $listControls])

@section('title')
<div class="title">EDIT DATA TABLE GROUP</div>
@stop @section('group')
<div id="controlSearch">
	<div class="role_title">
		<b>Group </b>
	</div>
	<div class="div_roleselect">
		<select id="cboGroup" onchange="_group.onChangeCbo()"> @foreach($datas
			as $data)
			<option value="{!! $data->ID !!}">{!! $data->NAME !!}</option>
			@endforeach
		</select>
	</div>

</div>
<br>
@stop @section('content')

<script type="text/javascript">

var ebtoken = $('meta[name="_token"]').attr('content');

$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

$(function(){
	$('#txt_group_name').val($("#cboGroup option:selected").text());

	$('#btnSave').click(function(){
		param = {
				'GROUP_ID' : $('#cboGroup').val(),
				'NAME' : $('#txt_group_name').val(),
				'TABLES' : $('#txtTables').val()
		}

		_group.saveGroup(param);
	});

	$('#btnAddSave').click(function(){
		param = {
				'NAME' : $('#txt_group_name').val(),
				'TABLES' : $('#txtTables').val(),
				'GROUP_ID' : -1,
		}

		_group.saveGroup(param);
	});

	$('#btnDelete').click(function(){
		_group.deleteGroup();
	});

});

var _group = {
		onChangeCbo : function(){
			_group.loadData();
		},

		loadData : function(){
			param = {
				'GROUP_ID' : $('#cboGroup').val(),
			}

			sendAjax('/am/loadGroup', param, function(data){
				_group.loadTables(data);
			});
		},

		loadTables : function(_data){
			$('#txt_group_name').val($("#cboGroup option:selected").text());
			$('#txtTables').val(_data.result.TABLES);
		},

		loadCbo : function(data){
			var strCbo = '';
			var value = data.datatablegroup;
			$('#cboGroup').html(strCbo);

			for(var i = 0; i < value.length; i++){
				strCbo += '<option value="'+value[i].ID+'">'+value[i].NAME+'</option>';
			}
			$('#cboGroup').html(strCbo);
		},

		saveGroup : function(param){
			sendAjax('/am/saveGroup', param, function(data){
				_group.loadCbo(data);
	    		_group.loadTables(data);
			});
		},

		deleteGroup : function(){
			param = {
				'GROUP_ID' : $('#cboGroup').val(),
			}

			sendAjax('/am/deleteGroup', param, function(data){
				_group.loadCbo(data);
	    		_group.loadTables(data);
			});
		}
}
</script>

<div>
	<b>Group name</b><br><input type="text" name="txt_group_name" id="txt_group_name"><br><br>
<b>Tables</b> <br><textarea type="text" name="txtTables" id="txtTables">{!! $datatablegroup->TABLES !!}</textarea>
<br><br>
<button class = "btnGroupName" id="btnSave">Save</button>
<button class = "btnGroupName" id="btnAddSave">Save as New group</button>
<button class = "btnGroupName" id="btnDelete">Delete</button>
</div>
@stop
