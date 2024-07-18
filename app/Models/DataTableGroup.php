<?php

namespace App\Models;
use App\Models\DynamicModel;

class DataTableGroup extends DynamicModel
{
	protected $table = 'data_table_group';
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'NAME', 'TABLES'];
}
