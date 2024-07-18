<?php

namespace App\Models;
use App\Models\DynamicModel;

class IntMapTable extends DynamicModel
{
	protected $primaryKey = 'ID';
	protected $table = 'INT_MAP_TABLE';
	
}