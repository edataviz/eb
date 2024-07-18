<?php

namespace App\Models;
use App\Models\DynamicModel;

class LockTable extends DynamicModel
{
	protected $table = 'LOCK_TABLE';
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'TABLE_NAME', 'LOCK_DATE', 'USER_ID', 'FACILITY_ID'];
	
}
