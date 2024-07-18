<table border="0" cellpadding="3" bgcolor="#E6E6E6" cellspacing="0" style="display: inline-table;">
	<tr>
		@foreach( $filterGroup as $filter )
			<td><b>{{$filter['name']}}</b></td>
		@endforeach
		<td bgcolor="#FFFFFF">&nbsp;</td>
	</tr>
	<tr>
		@foreach( $filterGroup as $filter )
			@if($filter['type'] == 'date picker')
				<td width='80'>
					<input onChange='reloadData()' 
					readonly style='width:100%' 
					type='text' 
					id = 'date_begin' 
					name='date_begin' 
					size='15'
					value="{{ $filter['type']}}">
				</td>
			@elseif($filter['type'] == 'options')
				<td width="140">
						<select style="width:100%;" id="{{ $filter['id']}}" size="1" name="{{ $filter['selectName']}}">
							@foreach($filter['options'] as $option )
								<option value='{{ $option->ID }}'>{{ $option->NAME }}</option>
							@endforeach
						</select>
				</td>
			@endif
		@endforeach
		<td bgcolor="#FFFFFF">&nbsp;</td>
	</tr>
</table>