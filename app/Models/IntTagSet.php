<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class IntTagSet extends DynamicModel 
{ 
	protected $table = 'INT_TAG_SET'; 
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'NAME',
			'TAGS',
			'CONNECTION_ID'
	];
} 
