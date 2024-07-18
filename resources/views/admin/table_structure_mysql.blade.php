DATABASE {{$driver}}:{{$database}}
</br>
</br>
@foreach( $tables as  $tkey => $table )
		{{--@if ($tkey>5) @break @endif--}}
        <?php $tableName = null; ?>
		@foreach ($table as $key => $value)
			<?php $tableName = $value; ?>
		@endforeach
	{{$tableName}}:</br>
	<?php
			if ($tableName){
                $columns = \DB::table("INFORMATION_SCHEMA.COLUMNS")
                    ->where(['TABLE_NAME'=>$tableName,
                        'TABLE_SCHEMA'=>$database,])
                    ->select('COLUMN_NAME','COLUMN_TYPE')
                    ->get();
                $constraints = \DB::table("INFORMATION_SCHEMA.KEY_COLUMN_USAGE")
                    ->where(['TABLE_NAME'=>$tableName,
                        'REFERENCED_TABLE_SCHEMA'=>$database,])
                    ->select('COLUMN_NAME','CONSTRAINT_NAME','REFERENCED_TABLE_NAME','REFERENCED_COLUMN_NAME')
                    ->get();
                $constraints =collect($constraints)->keyBy('COLUMN_NAME');
            }
	?>
			@if($tableName)
				@foreach ($columns as $column)
					<?php
						$columnName = $column->COLUMN_NAME;
						$constrain = $constraints->get($columnName);
						$constraintText = $constrain?','.$constrain->CONSTRAINT_NAME.','.$constrain->REFERENCED_TABLE_NAME.','.$constrain->REFERENCED_COLUMN_NAME:'';
					?>
					{{$columnName.','.$column->COLUMN_TYPE.$constraintText}}</br>
				@endforeach
			@endif
		</br>
@endforeach