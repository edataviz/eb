<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class SqlList extends DynamicModel 
{ 
	protected $table = 'SQL_LIST';
	
	public static function getCommonSql(){
		return static::where( ["ENABLE"=>1, "TYPE" => 2 ])->orWhere(["ENABLE"=>1, "TYPE" => 0 ])->select("ID","NAME")->get();
	}
} 
