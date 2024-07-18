<?php
$currentSubmenu = '/sourceconfig';
?>

@extends('core.bsinterface')
@section('title')
<div style="margin: 0px 10px;" class="function_title">SOURCE CONFIGURATION</div>
@stop @section('group')
<div
	style="width: 660px; height: 40px; margin: 10px 10px; background: #dddddd">
	<table id="tableImpType" cellpadding="0" cellspacing="0"
		style="width: 100%; height: 100%">
		<tr>
			<td><strong style="margin: 20px 20px 20px 10px">Import data using</strong></td>
			<td style="background: #79bbff">
				<input id="im1" checked="true" value="EXCEL" name="importMethod" type="radio">
					<label for="im1" style="margin-right: 20px; cursor: pointer"><b>Excel</b></label>
			</td>
			<td><input id="im2" name="importMethod" value="PI" type="radio">
				<label for="im2" style="margin-right: 20px; cursor: pointer"><b>Network Datasource</b></label>
			</td>
			<td></td>
		</tr>
	</table>
</div>
@stop @section('content')
<script src="/common/js/jquery.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/jquery-ui-timepicker-addon.js"></script>

<link rel="stylesheet" href="/common/css/jquery-ui.css" />
<link rel="stylesheet" href="/common/css/style.css" />
<script type="text/javascript">

$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})
		
	$('input[type=radio][name=importMethod]').change(function() {
		$("#tableImpType td").each(function(){
			$(this).css("background","none");
		});
		$(this).parent().css("background","#79bbff");		
		_sourceconfig.currentType=this.value;
        if (_sourceconfig.currentType == 'EXCEL') {
            $("#impExcel").show();
            $("#impConnection").hide();
        }
		else
		{
            $("#impExcel").hide();
            $("#impConnection").show();
            _sourceconfig.loadConnections();
			if (_sourceconfig.currentType == 'PI') {
			}
			else if (_sourceconfig.currentType == 'SCADA') {				
			}
		}
    });

	$('#cboImportSettings').change();
	if(_sourceconfig.currentType === "PI"){
		$('#cboConnection').change();
		$('#cboTagSet').change();
	}
	
});

var _sourceconfig = {
		currentType : "EXCEL",
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
		save : function(x)
		{
			var id=0;
			var sname="";
			if(x==0)
			{
				id=$("#cboImportSettings").val();
				sname=$("#cboImportSettings option:selected").text();
			}
			else
			{
				var xname=$("#cboImportSettings option:selected").text();
				sname=prompt("Input setting's name",xname);
				if(sname.trim()=="") 
					return;
			}

			param = {
				'id' : id,
				'name' : sname,
				'tab' : $("#tabIndex").val(),
				'col_tag' : $("#tagColumn").val(),
				'col_time' : $("#timeColumn").val(),
				'col_value' : $("#valueColumn").val(),
				'row_start' : $("#rowStart").val(),
				'row_finish' : $("#rowFinish").val()
			};
			
			sendAjax('/saveimportsetting', param, function(data){
					alert("Setting saved successfully");
					var _data = data.int_import_setting;
					_sourceconfig.loadSettings(_data, 'cboImportSettings');
					$('#cboImportSettings').val(data.id);
			});			
		},
		loadSettings : function (data, id, value){
			var cbo = '';
			$('#'+id).html(cbo);
			for(var v = 0; v < data.length; v++){
				cbo += ' 		<option value="' + data[v].ID + '" ' + (data[v].ID == value?" selected":"") + '>' + data[v].NAME + '</option>';
			}

			$('#'+id).html(cbo);
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
				_sourceconfig.loadSettings(data, 'cboImportSettings');
				alert("Delete successfully");
			});
		},
		rename : function()
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
				_sourceconfig.loadSettings(_data, 'cboImportSettings');
				$('#cboImportSettings').val(data.id);
			});
		},
		loadConnections : function(){
			var cv="";
			if(typeof v !== 'undefined'){
				cv=v;
			};

			param = {
				'type' : _sourceconfig.currentType
			};
			
			sendAjax('/loadintservers', param, function(data){
			    if(data.length > 0 ){
                    _sourceconfig.loadSettings(data, 'cboConnection');
                    $('#cboConnection').change();
				}else{
					$("#txtConnServer,#txtConnUsername,#txtConnPassword").val('');
					$("#cboConnSystem").val('');
				}
			});
		},
		connectionChange : function(){
			param = {
				'id' : $("#cboConnection").val()
			};
			
			sendAjax('/detailsconnection', param, function(data){
                if(data.int_tag_set.length == 0) $("#txtTags").val('');
				if(data.dt.length > 0){
					var dts = data.dt[0];
					$("#txtConnServer").val(dts.SERVER);
					$("#txtConnUsername").val(dts.USER_NAME);
					$("#txtConnPassword").val(dts.PASSWORD);
					$("#cboConnSystem").val(dts.SYSTEM);
					if(data.int_tag_set.length > 0){
						_sourceconfig.loadSettings(data.int_tag_set, 'cboTagSet');
						$('#cboTagSet').change();	
					}else{
						$('#cboTagSet').html('');
					}
				}
			});
		},
		saveConn : function(x)
		{
			if($("#txtConnServer").val().trim()=="")
			{
				alert("Please input server IP address or server name.");
				$("#txtConnServer").focus();
				return;
			}
			if($("#txtConnUsername").val().trim()=="")
			{
				alert("Please input username.");
				$("#txtConnUsername").focus();
				return;
			}
			var id=0;
			var sname="";
			if(x==0)
			{
                id=$("#cboConnection").val();
                if(id == null){
                    var xname=$("#cboConnection option:selected").text();
                    sname=prompt("Input connection's name",xname);
                    if(sname.trim()=="")
                        return;
				}else{
                    sname=$("#cboConnection option:selected").text();
                    if(sname.trim()=="") return;
				}
			}
			else
			{
				var xname=$("#cboConnection option:selected").text();
				sname=prompt("Input connection's name",xname);
				if(sname.trim()=="")
					return;
			}

			param = {
				'id' : id,
				'name' : sname,
				'server' : $("#txtConnServer").val(),
				'system' : $("#cboConnSystem").val(),
				'username' : $("#txtConnUsername").val(),
				'password' : $("#txtConnPassword").val(),
				'type' : _sourceconfig.currentType
			};
			
			sendAjax('/saveconn', param, function(data){
				alert("Connection information saved successfully");
				_sourceconfig.loadSettings(data.conn, 'cboConnection');
				$('#cboConnection').val(data.id);
				$('#cboConnection').change();
			});
		},
		renameConn : function()
		{
			var id=$("#cboConnection").val();
			if(id>0)
			{
				var xname=$("#cboConnection option:selected").text();
				sname=prompt("Input connection's name",xname);
				if(sname.trim()=="") 
					return;
			}
			else
				return;

			param = {
				'id' : id,
				'name' : sname,
				'type' : _sourceconfig.currentType
			};
			
			sendAjax('/renameconn', param, function(data){
			    alert("Rename Connection Successfully");
				_sourceconfig.loadSettings(data.conn, 'cboConnection');
				$('#cboConnection').val(data.id);
				$('#cboConnection').change();
			});
		},
		deleteConn : function()
		{
			var id=$("#cboConnection").val();
			if(!(id>0)) return;
			if(!confirm("Do you want to delete this connection?")) return;
			
			param = {
				'id' : id,
				'type' : _sourceconfig.currentType
			};
			
			sendAjax('/deleteconn', param, function(data){
                alert("Delete Connection Successfully");
				_sourceconfig.loadSettings(data.conn, 'cboConnection');
				$('#cboConnection').change();
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
		},
		saveTagSet : function(x)
		{
			if($("#txtTags").val().trim()=="")
			{
				alert("Please input tags.");
				$("#txtTags").focus();
				return;
			}
			var id=0;
			var sname="";
			if(x==0)
			{
				id=$("#cboTagSet").val();
				if(id == null){
                    var xname=$("#cboTagSet option:selected").text();
                    sname=prompt("Input Tag set's name",xname);
                    if(sname.trim()=="")
                        return;
				}else{
                    sname = $("#cboTagSet option:selected").text();
                    if(sname.trim()=="") return;
				}
			}
			else
			{
				var xname=$("#cboTagSet option:selected").text();
				sname=prompt("Input Tag set's name",xname);
				if(sname.trim()=="")
					return;
			}

			param = {
				'id' : id,
				'name' : sname,
				'tags' : $("#txtTags").val(),
				'conn_id' : $("#cboConnection").val()
			};
			
			sendAjax('/savetagset', param, function(data){
				if(data.substr(0,3)=="ok:")
				{
					id=data.substr(3);
					alert("Tag set saved successfully");
					_sourceconfig.loadTagSets(id);
					$('#cboTagSet').val(id);
					$('#cboTagSet').change();
				}
				else alert(data);
			});
		},
		loadTagSets : function(v)
		{
			var cv="";
			if(typeof v !== 'undefined'){
				cv=v;
			};
			param = {
				'connection_id' : $("#cboConnection").val()
			};
			
			sendAjax('/loadtagsets', param, function(data){
				_sourceconfig.loadSettings(data, 'cboTagSet', cv);

				$('#cboTagSet').change();
			});
			
		},
		renameTagSet : function()
		{
			var id=$("#cboTagSet").val();
			if(id>0)
			{
				var xname=$("#cboTagSet option:selected").text();
				sname=prompt("Input Tag set's name",xname);
				if(sname.trim()=="") 
					return;
			}
			else
				return;

			param = {
				'id' : id,
				'name' : sname
			};
			
			sendAjax('/renametagset', param, function(data){
                alert("Rename Tag Successfully");
				_sourceconfig.loadTagSets(data);
			});
		},
		deleteTagSet : function()
		{
			var id=$("#cboTagSet").val();
			if(!(id>0)) return;
			if(!confirm("Do you want to delete this Tag set?")) return;

			param = {
				'id' : id
			};
			
			sendAjax('/deletetagset', param, function(data){
                alert("Delete Tag Successfully");
				_sourceconfig.loadTagSets();
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
				style="width: 970px; height: 470px; overflow: auto"></div>
		</div>
	</div>
	<div id="container" style="padding: 0px 10px">
		
		<!-- Content  -->
		<div id="impConnection" style="display: none; margin: 10px 0px">
			<div style="margin: 10px 0px">
				<span style="padding-left: 0px">Connection </span> <select onchange="_sourceconfig.connectionChange();"
					id="cboConnection"></select>
				<button style="margin-left: 10px; display: none">Test connection</button>
			</div>
			<div id="boxConnectionInfo"
				style="box-sizing: border-box; width: 600px; overflow: auto; background: #e6e6e6; padding: 10px;">
				<table>
					<tr>
						<td>Server</td>
						<td><input type="text" style="width: 200px" id="txtConnServer"
							name="txtConnServer"></td>
						<td>System</td>
						<td><select id="cboConnSystem">
							<option value="">(Unknown)</option>
							@foreach($int_system as $re)
								<option value="{!!$re['CODE']!!}">{!!$re['NAME']!!}</option>
							@endforeach
							</select></td>
					</tr>
					<tr>
						<td>Username</td>
						<td><input type="text" style="width: 200px" id="txtConnUsername"
							name="txtConnUsername"></td>
						<td>Password</td>
						<td><input type="password" style="width: 200px"
							id="txtConnPassword" name="txtConnPassword"></td>
					</tr>
				</table>
				<br> <input type="button" onclick="_sourceconfig.saveConn(0)" value="Save"
					style="width: 100px"> <input type="button" onclick="_sourceconfig.saveConn(1)"
					value="Save as" style="width: 100px; margin-left: 10px"> <input
					type="button" onclick="_sourceconfig.renameConn()" value="Rename"
					style="width: 100px; margin-left: 10px"> <input type="button"
					onclick="_sourceconfig.deleteConn()" value="Delete"
					style="width: 100px; margin-left: 10px">
			</div>
			<div style="margin: 10px 0px">
				<span style="padding-left: 0px">Tag set </span> <select onchange = "_sourceconfig.loadTagSet();"
					id="cboTagSet"></select>
			</div>
			<div id="boxTags"
				style="box-sizing: border-box; width: 600px; overflow: auto; background: #e6e6e6; padding: 10px;">
				<textarea id="txtTags" style="width: 100%; height: 170px">
</textarea>
				<br>
				<br> <input type="button" onclick="_sourceconfig.saveTagSet(0)" value="Save"
					style="width: 100px"> <input type="button" onclick="_sourceconfig.saveTagSet(1)"
					value="Save as" style="width: 100px; margin-left: 10px"> <input
					type="button" onclick="_sourceconfig.renameTagSet()" value="Rename"
					style="width: 100px; margin-left: 10px"> <input type="button"
					onclick="_sourceconfig.deleteTagSet()" value="Delete"
					style="width: 100px; margin-left: 10px">

			</div>
		</div>

		<div id="impExcel" style="display:">
			<span style="padding-left: 0px">Select setting</span> <select id="cboImportSettings" onchange="_sourceconfig.importSettingChange();">
				@foreach($int_import_setting as $re)
					<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
				@endforeach
				</select>
			<div id="boxSetting"
				style="width: 640px; background: #e0e0e0; padding: 10px; margin: 10px 0px;">
				<span style="display: block; float: left; width: 80px; margin: 3px">Tab</span>
				<input id="tabIndex" name="tabIndex"
					style="width: 300px; margin: 3px;"><br> <span
					style="display: block; float: left; width: 80px; margin: 3px">Tag
					column</span> 
					<select id="tagColumn" name="tagColumn"
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
					?></select> Value column <select id="valueColumn" name="valueColumn">
					<?php
						foreach ( range ( 'A', 'Z' ) as $i ) {
							echo "<option value='$i'>$i</option>";
						}
					?></select><br> <span
					style="display: block; float: left; width: 80px; margin: 3px">Row
					start</span><input id="rowStart" name="rowStart"
					style="width: 100px; margin: 3px;"> Row finish <input
					id="rowFinish" name="rowFinish" style="width: 100px"> <br>
				<br> <input type="button" onclick="_sourceconfig.save(0)" value="Save"
					style="width: 100px"> <input type="button" onclick="_sourceconfig.save(1)"
					value="Save as" style="width: 100px; margin-left: 10px"> <input
					type="button" onclick="_sourceconfig.rename()" value="Rename"
					style="width: 100px; margin-left: 10px"> <input type="button"
					onclick="_sourceconfig.deleteSetting()" value="Delete"
					style="width: 100px; margin-left: 10px">
			</div>
		</div>
	</div>
	<!-- end of div excel -->
	</div>
</body>
@stop
