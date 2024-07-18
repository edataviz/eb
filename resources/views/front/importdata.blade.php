<?php
$currentSubmenu = '/importdata';
?>

@extends('core.bsinterface')
@section('title')
<div class="function_title">IMPORT DATA</div>
@stop @section('group')
	<div
		style="width: 640px; height: 40px; margin: 10px 10px; background: #dddddd">
		<table id="tableImpType" cellpadding="0" cellspacing="0"
			style="width: 100%; height: 100%">
			<tr>
				<td><strong style="margin: 20px 20px 20px 10px">Import data using</strong></td>
				<td style="background: #79bbff"><input id="im1" checked="true"
					value="EXCEL" name="importMethod" type="radio"><label for="im1"
					style="margin-right: 20px; cursor: pointer"><b>Excel</b></label></td>
				<td><input id="im2" name="importMethod" value="PI" type="radio"><label
					for="im2" style="margin-right: 20px; cursor: pointer"><b>Network Datasource
							</b></label></td>
				<td></td>
			</tr>
		</table>
	</div>
@stop @section('content')
<script src="/common/js/jquery.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
<link rel="stylesheet" href="/common/css/jquery-ui-timepicker-addon.css"/>

<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})

// 	var d = new Date();
	var dt = moment().add(-1, 'days').format(configuration.time.DATETIME_FORMAT) + "";
	dt = dt.substr(0,dt.indexOf(" "));
	$("#date_begin").val(dt + ' 00:00');
	$("#date_end").val(dt + ' 23:59');
// 	$("#date_begin").val(""+zeroFill(1+d.getMonth(),2)+"/"+zeroFill(d.getDate(),2)+"/"+d.getFullYear()+" 00:00");
// 	$("#date_end").val(""+zeroFill(1+d.getMonth(),2)+"/"+zeroFill(d.getDate(),2)+"/"+d.getFullYear()+" 23:59");
	var jsTimeFormat = configuration['time']['TIME_FORMAT'].replace('A','TT').replace('a','tt');
	
	$( "#date_begin, #date_end" ).datetimepicker({
	    changeMonth	:true,
	    changeYear	:true,
	    //format		:configuration['time']['DATETIME_FORMAT']
 	    dateFormat	: jsFormat,
 		timeFormat	: jsTimeFormat, //"HH:mm",
 		showTimezone	: false,
 		showMicrosec	: null
	});

	$('#cboImportSettings').change();
	//$('input[type=file]').on('change', _importdata.prepareUpload);

	$(".methodElement").hide();
	var importMethodTab = $('input[type=radio][name=importMethod]');
	importMethodTab.change(function() {
		$("#tableImpType td").each(function(){
			$(this).css("background","none");
		});
		$(this).parent().css("background","#79bbff");
        if (this.value == 'EXCEL') {
            $("#impExcel").show();
            $("#impConnection").hide();
            $(".methodElement").hide();
        }
		else
		{
            $(".methodElement").show();
            $("#impExcel").hide();
            $("#impConnection").show();
			$('#cboConnection').html("");

			param = {
				'type' : this.value
			};
			
			sendAjax('/loadintservers', param, function(data){
				_importdata.loadSettings(data, 'cboConnection');
			});
		}
    });
});

var _importdata = {
		importSettingChange : function(){
			param = {
				'id' : $("#cboImportSettings").val()
			};
			
			sendAjax('/getimportsetting', param, function(data){
				$("#tabIndex").val(data.TAB);
				$("#tagColumn").val(data.COL_TAG);
				$("#timeColumn").val(data.COL_TIME);
				$("#valueColumn").val(data.COL_VALUE);
				$("#rowStart").val(data.ROW_START);
				$("#rowFinish").val(data.ROW_FINISH);
			});
		},
		doImport : function(update_db)
		{
			var imptype=$('input[type=radio][name=importMethod]:checked').val();
			if(imptype=="EXCEL")
			{
				if($("#file").val()=="")
				{
					alert("Please select file (.xls) to import");
					$("#file").focus();
					return;
				}
				$("#logContent").html("Running...");
				$("#boxImpLog").fadeIn(function(){				
					var formData = new FormData();
					formData.append('file', $("#file")[0].files[0]);
					formData.append('tabIndex', $('#tabIndex').val());
					formData.append('tagColumn', $('#tagColumn').val());
					formData.append('timeColumn', $('#timeColumn').val());
					formData.append('valueColumn', $('#valueColumn').val());     
					formData.append('rowStart', $('#rowStart').val());
					formData.append('rowFinish', $('#rowFinish').val());
					formData.append('cal_method', $('#cboMethod').val()); 
					formData.append('date_begin', formatDateTimeUTC($('#date_begin').val())); 
					formData.append('date_end', formatDateTimeUTC($('#date_end').val()));
					formData.append('update_db', update_db);

					$.ajax({
						type: "POST",
						url: '/doimport',
						data: formData,
						processData: false,
						contentType: false,
						dataType: 'json',
						cache: false,
						success: function(data){
							$('#logContent').html(data.log);
						},
						error: function(data) {
							console.log("doimport error");
							alert("there are errors in importing");
//								enableSelect(dependentIds,false);
						}
					});
				});
			}
			else
			{
				var s=$("#boxTags").html().trim();
				if(s=="")
				{
					alert("No tag selected");
					return;
				}
				$("#logContent").html("Running...");
				$("#boxImpLog").fadeIn(function(){_importdata.importNetworkData(update_db)});
			}
		},

		importNetworkData : function(update_db)
		{
			if(update_db>0 && $("#cboMethod").val()=="all"){
				$("#logContent").html("<font color='red'>Not allow import data with method '<b>All</b>'</font>");
				alert("Not allow import data with method 'All'");
				return;
			}
			var tags=$("#txtTags").val();

			param = {
					'update_db' : update_db,
					'connection_id' : $('#cboConnection').val(),
					'tagset_id' : $('#cboTagSet').val(),
					'date_begin' : formatDateTimeUTC($('#date_begin').val()) ,
					'date_end' : formatDateTimeUTC($('#date_end').val()),
					'cal_method' : $("#cboMethod").val(),
					'tags' : $("#txtTags").val(),
			};
			
			sendAjax('/importnetworkdata', param, function(data){
				$("#logContent").html(data);
			});
		},
		
		prepareUpload : function(event)
		{
			var file = event.target.files[0];
			if(!file.name.split('.').pop().toLowerCase().startsWith("xls")){
				alert('Only accept xls or xlsx file');
				$("#frmExcel")[0].reset();
			}
		},
		
		loadSettings : function (data, id, value){
			var cbo = '';
			$('#'+id).html(cbo);
			for(var v = 0; v < data.length; v++){
				cbo += ' 		<option value="' + data[v].ID + '">' + data[v].NAME + '</option>';
			}

			$('#'+id).html(cbo);
			$('#'+id).change();
		},
		connectionChange : function(){
			param = {
				'id' : $("#cboConnection").val()
			};
			
			sendAjaxNotMessage('/detailsconnection', param, function(data){
				_importdata.loadSettings(data.int_tag_set, 'cboTagSet');
			});
		},
		loadTagSet : function(v)
		{
			$('#txtTags').val("");

			param = {
				'set_id' : $("#cboTagSet").val()
			};
			
			sendAjax('/loadtagset', param, function(data){
				if(data != ''){
					$('#txtTags').val(data.replace(/;/g, "<br>"));
				}
			});
		}
}
</script>
<body style="margin: 0px">
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
	<div id="container" style="padding: 0px 10px">
		<!-- Content  -->
			<div id="impConnection" style="display: none; margin: 10px 0px">
				<div style="margin: 10px 0px">
					<span style="padding-left: 0px">Connection </span> <select onchange="_importdata.connectionChange();"
						id="cboConnection"></select>
					<button style="margin-left: 10px; display: none">Test connection</button>
					<span style="padding-left: 20px">Tag set </span> <select onchange="_importdata.loadTagSet();"
						id="cboTagSet"></select>
				</div>
				<div id="boxTags"
					style="box-sizing: border-box; width: 640px; height: 250px;overflow: auto; background: #e6e6e6; padding: 6px 0px 0px 6px;">
					<textarea id="txtTags" style="width: 98%; height: 95%;resize: none;"></textarea>
				</div>
			</div>
			<div id="impExcel" style="display:">
				<input type="hidden" name="update_db" value="1">
				<div style="margin-top: 10px; margin-bottom: 10px">
					<b>Select file</b><br> <form id="frmExcel"><input name="file" id="file" style="width: 640px" type="file"></form>
					<br><br> <span style="padding-left: 0px">Select setting</span> 
					<select id="cboImportSettings" onchange="_importdata.importSettingChange();">
					@foreach($int_import_setting as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
					@endforeach
					</select>

					<div id="boxSetting"
						style="box-sizing: border-box; width: 640px; background: #e6e6e6; padding: 10px; margin: 10px 0px;">
						<span
							style="display: block; float: left; width: 80px; margin: 3px">Sheet
							name</span> <input id="tabIndex" name="tabIndex"
							style="width: 300px; margin: 3px;"><br> <span
							style="display: block; float: left; width: 80px; margin: 3px">Tag
							column</span> <select id="tagColumn" name="tagColumn"
							style="width: 50px; margin: 3px;">
							<?php
								foreach ( range ( 'A', 'Z' ) as $i ) {
									echo "<option value='$i'>$i</option>";
								}
							?></select> Time column <select id="timeColumn" name="timeColumn">
							<?php
								foreach ( range ( 'A', 'Z' ) as $i ) {
									echo "<option value='$i'>$i</option>";
								}
							?>
							</select> Value column <select id="valueColumn" name="valueColumn">
							<?php
								foreach ( range ( 'A', 'Z' ) as $i ) {
									echo "<option value='$i'>$i</option>";
								}
							?>
							</select>
							<br> <span
							style="display: block; float: left; width: 80px; margin: 3px">Row
							start</span><input id="rowStart" name="rowStart"
							style="width: 100px; margin: 3px;"> Row finish <input
							id="rowFinish" name="rowFinish" style="width: 100px">
					</div>
				</div>
			</div>
			<!-- end of div excel -->
			<div
				style="background: #dddddd; width: 640px; box-sizing: border-box">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td style="width: 100"><strong>From time</strong></td>
						<td style="width: 100"><strong>To time</strong></td>
						<td style=""><strong class="methodElement">Method</strong></td>
						<td style=""><input style="display: none" name="cbDateAll"
							id="cbDateAll" type="checkbox"><label for="cbDateAll"><strong></strong></label></td>
					</tr>
					<tr>
						<td><input id='date_begin' style='width: 180px;' type='text'
							name='date_begin' size='10'></td>
						<td><input id='date_end' style='width: 180px;' type='text'
							name='date_end' size='10'></td>
						<td width="140"><select id="cboMethod" class="methodElement" style="width: 140px;"
							size="1" name="cboMethod">
								<option value="all">All</option>
								<option value="last">Last</option>
								<option value="first">First</option>
								<option value="max">Max</option>
								<option value="min">Min</option>
								<option value="average">Average</option>
								<option value="interpolation">Interpolation</option>
						</select></td>
						<td></td>
					</tr>
				</table>
				<input type="button"
					value="Simulate" onClick="_importdata.doImport(0)"
					style="width: 120; height: 30; margin: 5px"> 
				<input type="button" value="Import" onClick="_importdata.doImport(1)"
					style="width: 120; height: 30; margin: 5px"> 
				<input type="button"
					value="Show last log" onclick="$('#boxImpLog').fadeIn()"
					style="width: 120; height: 30; margin: 5px">

			</div>
	</div>
</body>
@stop
