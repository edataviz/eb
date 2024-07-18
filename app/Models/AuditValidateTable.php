<?php

namespace App\Models;
use App\Models\DynamicModel;

class AuditValidateTable extends DynamicModel
{
	protected $table = 'audit_validate_table';
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'TABLE_NAME', 'DATE_FROM', 'DATE_TO', 'USER_ID', 'FACILITY_ID'];
	
}
