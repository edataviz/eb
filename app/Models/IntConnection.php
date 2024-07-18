<?php

namespace App\Models;
use App\Models\DynamicModel;

class IntConnection extends DynamicModel
{
	protected $table = 'INT_CONNECTION';
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = [
			'ID',
			'NAME',
			'SERVER',
			'SYSTEM',
			'USER_NAME',
			'PASSWORD',
			'TYPE'
	];
	
	public function IntTagSet($option=null){
		return IntTagSet::where(['CONNECTION_ID'=>$this->ID])->get(['ID', 'NAME']);
	}
	
	public static function getDefaultConnection(){
		return self::orderBy('IS_DEFAULT','DESC')->first();
	}
}
