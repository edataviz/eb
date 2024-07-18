<?php
	$currentSubmenu ='/fp/loadplanforecast';
	$key 			= 'loadplanforecast';
	$dateFormatSql 	= config('database.default')==='oracle'?"dd/mm/yyyy":"%d/%m/%Y";
 ?>

@extends('core.fp')
@section('funtionName')
LOAD PLAN/FORECAST DATA
@stop

@section('adaptData')
@section('content')
<script type="text/javascript">
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})

	$('#buttonSave').css('display', 'none');
	$('#buttonLoadData').css('display', 'none');
	
	$('#cboImportSettings').change();

	$('input[type=file]').on('change', _loadplan.prepareUpload);
});

var _loadplan = {
		filesToUpload : [],
		COLS_MAPPING	: '',
		importSettingChange : function(){
			param = {
				'id' : $("#cboImportSettings").val()
			};
			
			sendAjax('/getimportsetting', param, function(data){
				$("#tabIndex").val(data.TAB);
				$("#timeColumn").val(data.COL_TIME);
				$("#valueColumn").val(data.COL_VALUE);
				$("#rowStart").val(data.ROW_START);
				$("#rowFinish").val(data.ROW_FINISH);
				_loadplan.COLS_MAPPING = data.COLS_MAPPING;
			});
		},
		prepareUpload : function(event)
		{
		  	var files = event.target.files || event.originalEvent.dataTransfer.files;
		    $.each(files, function(key, value) {
		    	_loadplan.filesToUpload.push(value);
		    });
		},

		joinTableName : function(load_type){
		  	var table = $("#IntObjectType").find(":selected").attr( "name")+"_DATA_"+load_type; 
			return table;
		},
		doImport : function(load_type, update_db)
		{
			if($("#file").val()=="")
			{
				alert("Please select file (.xls) to import");
				$("#file").focus();
				return;
			}
			$("#frmImpExcel input[name='update_db']").val(update_db);
			var source_type		= $("#IntObjectType").find(":selected").attr( "name");
			var value_type		= $("#cboValueType").val();
			var fieldTypeColumn	= source_type+"_ID";
			var field_name		= source_type+"_"+value_type;
			if(source_type=="ENERGY_UNIT"){
				field_name		= "EU_DATA_"+value_type;
				fieldTypeColumn	= "EU_ID";
			}
			else if(source_type=="FLOW"){
				field_name		= "FL_DATA_"+value_type;
			}
			var codeType = load_type+"_TYPE";
			$("#frmImpExcel input[name='cboTableName']").val(source_type+"_DATA_"+load_type);
			$("#frmImpExcel input[name='cboFieldName']").val(field_name);
			$("#logContent").html("Running...");
			$("#boxImpLog").fadeIn(function(){
				if(_loadplan.filesToUpload){
				    var formData = new FormData();
				    if (_loadplan.filesToUpload) {
				        $.each(_loadplan.filesToUpload, function(key, value){
				            formData.append('file', value);
				        });   
				        _loadplan.COLS_MAPPING 	= 	"OCCUR_DATE="+$("#timeColumn").val()+"*{"+'{{$dateFormatSql}}'+"}\r\n"+
				        							field_name+"="+$("#valueColumn").val()+"\r\n"+
				        							fieldTypeColumn+"="+$("#ObjectName").val()+"\r\n"+
				        							codeType+"="+$("#"+codeType).val()
				        							;
						
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
				        formData.append('txtTable', _loadplan.joinTableName(load_type)); 
				        formData.append('txtMapping', _loadplan.COLS_MAPPING);  
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
				        	$('#logContent').html(data.columns.str);
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
		}
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
	&nbsp;
	</div>
	<div id="container" style="padding: 10px">
		<form enctype="multipart/form-data" method="post" name="frmImpExcel"
			id="frmImpExcel">
			<input type="hidden" name="update_db" value="0"> <input type="hidden"
				name="cboTableName" value="0"> <input type="hidden"
				name="cboFieldName" value="0">
			<div id="impExcel" style="display:">
				<div style="margin-top: 10px; margin-bottom: 10px">
				<br>
					<b>Select file</b><br> <input name="file" id="file"  type="file"><br>
					<br> <span style="padding-left: 0px">Select setting</span> 
					<select	id="cboImportSettings" style="margin-left: 20px;" onchange="_loadplan.importSettingChange()">
					@foreach($int_import_setting as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
					@endforeach
					</select>

					<div id="boxSetting"
						style="box-sizing: border-box; width: 600px; background: #e6e6e6; padding: 10px;">
						<span style="display: block; float: left; width: 80px; margin: 3px">Tab</span>
						<input id="tabIndex" name="tabIndex" style="width: 300px; margin: 3px;">
						<br>
						<div style = "margin-top: 10px;">
						<span style="width: 80px;">Time column</span> 
						<select id="timeColumn" name="timeColumn" style="width: 50px; margin-left: 12px;">
						<?php
							foreach ( range ( 'A', 'Z' ) as $i ) {
								echo "<option value='$i'>$i</option>";
							}
						?>
						</select> 
						
						<span> Value column</span> 
						<select id="valueColumn" name="valueColumn" style="width : 50px">
						<?php
							foreach ( range ( 'A', 'Z' ) as $i ) {
								echo "<option value='$i'>$i</option>";
							}
							?>
						</select>
						</div> 
						<span	style="display: block; float: left; width: 80px; margin: 3px">Row start</span>
						<input id="rowStart" name="rowStart" style="width: 100px; margin: 3px;"> Row finish 
						<input id="rowFinish" name="rowFinish" style="width: 100px">
					</div>
				</div>
			</div>
			<table cellpadding="2" cellspacing="0" id="table1"
				style="margin-top: 10px;">
				<tr id="_rh" style="background: #E6E6E6;">
					<td style="width: 120px"><b>Property</b></td>
					<td></td>
				</tr>
				<tr style="background: #E6E6E6; height: 40px">
					<td><select id="cboValueType" size="1" 
						name="cboValueType">
							<option value='GRS_VOL'>Gross Volume</option>
							<option value='NET_VOL'>Net Volume</option>
							<option value='GRS_MASS'>Gross Mass</option>
							<option value='NET_MASS'>Net Mass</option>
							<option value='GRS_ENGY'>Gross Energy</option>
							<option value='GRS_PWR'>Gross Power</option>
					</select></td>
					<td style="padding:3px">
						<a href="javascript:$('#boxImpLog').show()">Show last log</a>
					</td>
				</tr>
				<tr style="background: #E6E6E6; height: 40px">
					<td>{{ \Helper::filter(["modelName"=>"CodePlanType",'id' =>'PLAN_TYPE','filterName'=>'Plan Type']) }}</td>
					<td style="padding:3px">
						<input type="button" onClick="_loadplan.doImport('PLAN',0)" style="width:150px;height:30px;" value="Simulate Load Plan">
						<input type="button" onClick="_loadplan.doImport('PLAN',1)" style="width:100px;height:30px;" value="Load Plan">
					</td>
				</tr>
				<tr style="background: #E6E6E6; height: 40px">
					<td>{{ \Helper::filter(["modelName"=>"CodeForecastType",'id' =>'FORECAST_TYPE','filterName'=>'Forecast Type']) }}</td>
					<td style="padding:3px">
						<input type="button" onClick="_loadplan.doImport('FORECAST',0)" style="width:150px;height:30px;" value="Simulate Load Forecast">
						<input type="button" onClick="_loadplan.doImport('FORECAST',1)" style="width:100px;height:30px;" value="Load Forecast">
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>
@stop