@extends('partials.editfilter')
@section($prefix.'action_extra')
@parent
	<table border="0" class="clearBoth" style="">
		<tr>
			<td>
				<b>Y axis: Position </b>
				<select id="edit_cboYPos" style="width: auto">
					<option value="L">Left</option>
					<option value="R">Right</option>
				</select>
			</td>
			<td>
				<b> Text </b>
				<input name="txt_y_unit" id="edit_txt_y_unit" value="">
			</td>
			<td><span style="margin-left:20px">
				<input type="checkbox" name="graph_cummulative" id="graph_cummulative" value="">
				Cummulative</span>
			</td>
		</tr>
	</table>
@stop

@section($prefix.'filter_extra')
@parent
	<script type='text/javascript'>
		var oBuildFilterData		= editBox.buildFilterData;
		editBox.buildFilterData 	= function(){
			var dataStore 			= oBuildFilterData();
			dataStore.cboYPos 		= $("#edit_cboYPos").val();
			dataStore.txt_y_unit 	= $("#edit_txt_y_unit").val();
			dataStore.graph_cummulative	= $("#graph_cummulative").is(':checked')?1:0;
			return dataStore;
		}

		editBox.updateExtraFilterData = function(dataStore){
			$("#edit_cboYPos").val(dataStore.cboYPos);
			$("#edit_txt_y_unit").val(dataStore.txt_y_unit);
			$("#graph_cummulative").prop('checked', dataStore.graph_cummulative==1 || dataStore.ObjectName.endsWith("(*)"));
		}
	</script>
@stop
