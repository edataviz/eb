<?php

namespace App\Models;
use App\Models\IntObjectType;

class PreosIntObjectType extends IntObjectType {
	protected static $codes = ['FLOW','ENERGY_UNIT','TANK','STORAGE'];
	
	public static function all($columns = array()){
		if (count($columns)>0) {
			return static::whereIn('CODE',static::$codes)->get($columns);
		}
		return static::whereIn('CODE',static::$codes)->get();
	}
}
