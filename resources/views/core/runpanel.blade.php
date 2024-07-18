@section('adaptData')
@parent
<script>
	actions.renderFirsColumn = null;
	var showExtensionFields = function(data){};
	var showResultFields	= function(data){};
	
	$("#result").css("display","none");
	actions.saveSuccess =  function(data){
		actions.editedData = {};
		actions.deleteData = {};
		if( typeof data.data === 'string' ) {
			$("#result_data").html(data.data);
		}
		else if (Object.prototype.toString.call( data.data ) === '[object Array]' ) {
			$("#result_data").html(data.data.join("<br/>"));
		}
		showExtensionFields(data);
		$("#result_warning").html(data.warning);
		$("#result_error").html(data.error);

		showResultFields(data);
 	};
</script>
@stop

@section('actionPanel')
	<div id="selected_objects" style="padding:5px"></div>
	<br>
	<button onClick="actions.doLoad(true)" style="width:100%;height:30px;margin:0px 0px">Load PREoS input data</button>
	<br>
	<br>
	<input type="checkbox" name="chk_update_db" id="chk_update_db"> Update database<br>
	<button onClick="actions.doSave(true)" style="width:120px;height:30px;margin:20px 0px">Run PREoS</button>
@stop

@section('actionInputTable')
	<div id="container_{{$key}}" style="overflow-x:hidden">
		<table border="0" cellpadding="3" id="table_{{$key}}" class="fixedtable nowrap display"></table>
	</div>
@stop

@section('actionOutputView')
	<div id="result">
		<div id="result_chart_container" style="width:600px;height:300px"><br><br></div>
		<b>@yield('logName')</b><br>
		<b>Input data:</b> <div id="result_data"></div><br>
		@yield('extensionFields')
		<span id="result_warning" style='background:orange;color:black'><b>Warning: </b></span><br>
		<b>Result:</b><br> <div id="result_result"></div><br><br>
		<span id="result_error" style='background:red;color:white'><b></b></span>
	</div>
@stop


@section('content')
<table cellspacing="5" cellpadding="0" style="background:#e0e0e0;margin-top:10px">
<tr>
	<td valign="top" style="padding:5px">
		<table>
		<tr>
			<td valign="top" style="background:#e0e0e0;padding:5px">
				@yield('actionPanel')
			</td>
		</tr>
		</table>
	</td>
	<td id="box_load_input_data" valign="top" style="padding:5px">
		@yield('actionInputTable')
	</td>
	<td valign="top" style="background:#e0e0e0;padding:5px">
		<div id="boxOutputData" style="">
			@yield('actionOutputView')
		</div>
	</td>
</tr>
</table>
@stop
 