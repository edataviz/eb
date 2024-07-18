<?php
$currentSubmenu = '/dataloader';
?>

@extends('core.bsinterface')
@section('group')
@stop @section('content')
<script src="/common/js/jquery.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/jquery-ui-timepicker-addon.js"></script>

<link rel="stylesheet" href="/common/css/jquery-ui.css" />
<link rel="stylesheet" href="/common/css/style.css" />

<style>
table td {
	font-size: 9pt
}

.readonlytext {
	border: 0px solid #fff;
	background: none
}

input {
	border: 1px solid #888
}

#boxInputData td {
	text-align: right;
	min-width: 40px
}

#boxInputData th {
	text-align: right;
	min-width: 40px;
	font-weight: bold;
	border-bottom: 1px solid black
}

input[type="text"] {
	padding: 2px 3px
}
</style>

<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})

	$('#cboImportSettings').change();
	$('input[type=file]').on('change', _dataloader.prepareUpload);
});

var _dataloader = {
		filesToUpload : [],
		
		prepareUpload : function(event)
		{
		  	var files = event.target.files || event.originalEvent.dataTransfer.files;
		    $.each(files, function(key, value) {
		    	_dataloader.filesToUpload.push(value);
		    });
		},
		
		importSettingChange : function(){
			param = {
				'id' : $("#cboImportSettings").val()
			};
			
			sendAjax('/getimportsetting', param, function(data){
				//console.log(data);
				$("#tabIndex").val(data.TAB);
				$("#tagColumn").val(data.COL_TAG);
				$("#timeColumn").val(data.COL_TIME);
				$("#valueColumn").val(data.COL_VALUE);
				$("#rowStart").val(data.ROW_START);
				$("#rowFinish").val(data.ROW_FINISH);
				$("#txtTable").val(data.TABLE_NAME);
				$("#txtMapping").val(data.COLS_MAPPING);
				$("#txt_fo_obj_col").val(data.COL_OBJECT_ID);
				$("#txt_fo_date_col").val(data.COL_DATE);
				$("#chk_auto_apply_formula").attr('checked', data.AUTO_FORMULA=="1");
			});
		},
		doImport : function(update_db)
		{
			if($("#file").val()=="")
			{
				alert("Please select file (.xls) to import");
				$("#file").focus();
				return;
			}
			$("#frmImpExcel input[name='update_db']").val(update_db);
			$("#logContent").html("Running...");
			$("#boxImpLog").fadeIn(function(){
				//$("#frmImpExcel").submit()
				if(_dataloader.filesToUpload){
				    var formData = new FormData();
				    if (_dataloader.filesToUpload) {
				        $.each(_dataloader.filesToUpload, function(key, value){
				            formData.append('file', value);
				        });   

				        formData.append('tabIndex', $('#tabIndex').val());
				        formData.append('tagColumn', $('#tagColumn').val());
				        formData.append('timeColumn', $('#timeColumn').val());
				        formData.append('valueColumn', $('#valueColumn').val());     
				        formData.append('rowStart', $('#rowStart').val());
				        formData.append('rowFinish', $('#rowFinish').val());
				        formData.append('cal_method', $('#cal_method').val()); 
				        formData.append('date_begin', $('#date_begin').val()); 
				        formData.append('date_end', $('#date_end').val());
				        formData.append('update_db', update_db); 
				        formData.append('cboOveride', $('#cboOveride').val());  
				        formData.append('txtTable', $('#txtTable').val());  
				        formData.append('txtMapping', $('#txtMapping').val());
						
				        formData.append('apply_formula', $('#chk_auto_apply_formula').is(":checked")?"1":"");
				        formData.append('fo_obj_id_col', $('#txt_fo_obj_col').val().trim());
				        formData.append('fo_date_col', $('#txt_fo_date_col').val().trim());
				    }

					$.ajax({
				        type: "POST",
				        url: '/doimportdataloader',
				        data: formData,
				        processData: false,
				        contentType: false,
				        dataType: 'json',
				        cache: false,
				        success: function(data){
				        	$('#logContent').html(data.log);
						},
						error: function(data) {
							console.log (data.responseText);
				        	$('#logContent').html(data.responseText);
							if (typeof(actions) == "object"&&typeof(actions.loadError) == "function") {
								actions.loadError(data);
							}
						}
				    });
				}
			});
		},
		saveSetting : function(isSaveAs)
		{
			var id=0;
			var sname="";
			if(isSaveAs===true)
			{
				var xname=$("#cboImportSettings option:selected").text();
				sname=prompt("Input setting's name",xname);
				if(sname.trim()=="") 
					return;
			}
			else
			{
				id=$("#cboImportSettings").val();
				sname=$("#cboImportSettings option:selected").text();
			}

			param = {
					'id' : id,
					'name' : sname,
					'tab' : $("#tabIndex").val(),
					'col_tag' : $("#tagColumn").val(),
					'col_time' : $("#timeColumn").val(),
					'col_value' : $("#valueColumn").val(),
					'row_start' : $("#rowStart").val(),
					'row_finish' : $("#rowFinish").val(),
					'cols_mapping' : $("#txtMapping").val(),
					'table' : $("#txtTable").val(),
					'col_object' : $("#txt_fo_obj_col").val(),
					'col_date' : $("#txt_fo_date_col").val(),
					'auto_formula' : $('#chk_auto_apply_formula').is(":checked")?1:0,
				};
				sendAjax('/saveimportsetting', param, function(data){
						alert("Setting saved successfully");
						if(isSaveAs){
							var _data = data.int_import_setting;
							_dataloader.loadSettings(_data, 'cboImportSettings', data.id);
						}
				});	
		},
		loadFields : function(){
		    $('#cboFields').html('');

		    param = {
				'table' : $("#txtTable").val()
			};
			
			sendAjax('/gettablefieldsall', param, function(data){
				_dataloader.showField(data);
			});
		}, 
		showField : function(data){
			var cbo = '';
			$('#cboFields').html(cbo);
			for(var v = 0; v < data.length; v++){
				cbo += ' 		<option value="' + data[v] + '">' + data[v] + '</option>';
			}

			$('#cboFields').html(cbo);
		},
		addMapping : function(){
			if($("#txtValue").val().trim()!="" && $("#cboFields").val()!=""){
				var s=$("#txtMapping").val();
				var f="";
				if($("#txtFormat").val().trim()!="")
					f="{"+$("#txtFormat").val()+"}";
				$("#txtMapping").val(s+(s==""?"":"\r\n")+$("#cboFields").val()+"="+$("#txtValue").val()+f);
			}
		},
		renameSetting : function()
		{
			var id=$("#cboImportSettings").val();
			if(id>0)
			{
				var xname=$("#cboImportSettings option:selected").text();
				sname=prompt("Input setting's name",xname);
				if(sname.trim()=="") 
					return;
			}
			else
				return;

			param = {
				'id' : id,
				'name' : sname
			};
			
			sendAjax('/renamesetting', param, function(data){
				var _data = data.int_import_setting;
				_dataloader.loadSettings(_data, 'cboImportSettings',data.id);
			});
		},
		loadSettings : function (data, id, value){
			var cbo = '';
			$('#'+id).html(cbo);
			for(var v = 0; v < data.length; v++){
				cbo += ' 		<option value="' + data[v].ID + '" ' + (data[v].ID == value?" selected":"") + '>' + data[v].NAME + '</option>';
			}

			$('#'+id).html(cbo);
			$('#'+id).change();
		},
		deleteSetting : function()
		{
			var id=$("#cboImportSettings").val();
			if(!(id>0)) return;
			if(!confirm("Do you want to delete this setting?")) return;

			param = {
				'id' : id
			};
			
			sendAjax('/deletesetting', param, function(data){
				_dataloader.loadSettings(data, 'cboImportSettings');
				alert("Delete successfully");
			});
		},
}
</script>
<body style="margin: 0; overeu-x: hidden">
	<div id="boxImpLog"
		style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 2">
		<div
			style="position: absolute; box-sizing: border-box; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.5); left: 10%; top: 10%; padding: 15px; width: 80%; height: 80%; z-index: 1; border: 1px solid #999999; background: white">
			<input type="button" value="Close"
				onclick="$('#boxImpLog').fadeOut()"
				style="position: absolute; width: 80px; height: 30px; right: 0px; top: -30px">
			<div id="logContent"
				style="width: 100%; height: 100%; overflow: auto"></div>
		</div>
	</div>
	<div id="container" style="padding: 0px 10px 10px 10px">
		<form enctype="multipart/form-data" method="post" name="frmImpExcel"
			id="frmImpExcel">
			<input type="hidden" name="update_db" value="0">
			<div id="impExcel" style="display:">
				<div style="margin-top: 10px; margin-bottom: 10px">
					<b>Select file</b><br> <input name="file" id="file"
						style="width: 600px" type="file"><br>
					<br> <span style="padding-left: 0px">Select setting</span> 
					
					<select id="cboImportSettings" onchange="_dataloader.importSettingChange();">
					@foreach($int_import_setting as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
					@endforeach
					</select>
						
						<input type="button" value="Save"
						onclick="_dataloader.saveSetting()"> <input type="button" value="Save As"
						onclick="_dataloader.saveSetting(true)"> <input type="button" value="Rename"
						onclick="_dataloader.renameSetting()"> <input type="button" value="Delete"
						onclick="_dataloader.deleteSetting()">

					<div id="boxSetting"
						style="box-sizing: border-box; width: 600px; background: #e6e6e6; padding: 10px; margin: 10px 0px;">
						<span
							style="display: block; float: left; width: 80px; margin: 3px">Table</span>
						<input id="txtTable" name="txtTable"
							style="width: 300px; margin: 3px;"> <input type="button"
							value="Load Fields" onclick="_dataloader.loadFields()"><br> <span
							style="display: block; float: left; width: 80px; margin: 3px">Sheet
							name</span> <input id="tabIndex" name="tabIndex"
							style="width: 300px; margin: 3px;"><br><input type="hidden" id="tagColumn" name="tagColumn">
							<input type="hidden" id="timeColumn" name="timeColumn">
							<input type="hidden" id="valueColumn" name="valueColumn"><span
							style="display: block; float: left; width: 80px; margin: 3px">Row
							start</span><input id="rowStart" name="rowStart"
							style="width: 100px; margin: 3px;"> Row finish <input
							id="rowFinish" name="rowFinish" style="width: 100px"><br> <span
							style="display: block; float: left; width: 80px; margin: 3px">Field</span>
						<select id="cboFields" name="cboFields" style="margin: 3px;"></select>
						Mapping <input id="txtValue" name="txtValue"> Format <input
							id="txtFormat" name="txtFormat" style="width: 100px"> <input
							type="button" value="Add" onclick="_dataloader.addMapping()"><br> <span
							style="display: block; float: left; width: 80px; margin: 3px">Columns
							mapping</span>
						<textarea id="txtMapping" name="txtMapping"
							style="width: 480px; height: 150px; margin: 3px;"></textarea>
					</div>
				</div>
			</div>
			<fieldset style="width:200px;height:140px;position:absolute;top:170px;left:620px;">
				<legend>Apply formula</legend>
				<table>
				<tr><td colspan="2"><input type="checkbox" checked id="chk_auto_apply_formula"> <label for="chk_auto_apply_formula">Auto apply formula</label></td></tr>
				<tr><td>Object ID column</td><td><input type="text" id="txt_fo_obj_col" style="width:50px"></td></tr>
				<tr><td>Occur Date column</td><td><input type="text" id="txt_fo_date_col" style="width:50px"></td></tr>
				</table>
			</fieldset>
			<table cellpadding="2" cellspacing="0" id="table1"
				style="margin-top: 10px">
				<tr id="_rh" style="background: #E6E6E6;">
					<td><b>Overide?</b></td>
					<td align="right"><a href="javascript:$('#boxImpLog').show()">Show last log</a></td>
				</tr>
				<tr style="background: #E6E6E6; height: 40px">
					<td><select id="cboOveride" name="cboOveride" style="width: 100%"><option
								value="1">Yes</option>
							<option value="0">No</option></select></td>
					<td style="padding: 3px; width: 260"><input type="button"
						onClick="_dataloader.doImport(0)" style="width: 100px; height: 30px;"
						value="Simulate"> <input type="button" onClick="_dataloader.doImport(1)"
						style="width: 100px; height: 30px;" value="Load data"></td>
				</tr>
			</table>
		</form>
	</div>
</body>
@stop
