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
                $columns = \DB::table("USER_TAB_COLUMNS")
                    ->where(['table_name'=>$tableName])
                    ->select('column_name','data_type','data_length')->orderBy('column_name')
                    ->get();

                $constraints = \DB::table("user_constraints")
					->join('user_cons_columns','user_cons_columns.constraint_name','=','user_constraints.constraint_name')
                    ->join('user_cons_columns t1','t1.constraint_name','=','user_constraints.r_constraint_name')
                    ->where(['user_cons_columns.table_name'=>$tableName])
                    ->select('user_cons_columns.column_name','user_constraints.constraint_name','t1.table_name as referenced_table_name','t1.column_name as referenced_column_name')
                    ->get();
                $constraints =collect($constraints)->keyBy('column_name');
            }
	?>
			@if($tableName)
				@foreach ($columns as $column)
					<?php
						$columnName = $column->column_name;
						$constrain = isset($constraints)&&$constraints?$constraints->get($columnName):'';
						$constraintText = $constrain?','.$constrain->constraint_name.','.$constrain->referenced_table_name.','.$constrain->referenced_column_name:'';
					?>
					{{$columnName.','.$column->data_type.','.$column->data_length.$constraintText}}</br>
				@endforeach
			@endif
		</br>
@endforeach