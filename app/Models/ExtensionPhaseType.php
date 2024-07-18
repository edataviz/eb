<?php

namespace App\Models;
use App\Models\CodeFlowPhase;
use App\Trail\ObjectNameLoad;

class ExtensionPhaseType extends CodeFlowPhase
{
	use ObjectNameLoad;
	protected static $codes = ['OIL','GAS','WTR'];
	
	public static function all($columns = array())
	{
		if (count($columns)>0) {
			return static::whereIn('CODE',static::$codes)->get($columns);
		}
		return static::whereIn('CODE',static::$codes)->get();
	}
	
	public static function getPreosPhaseType(){
		$codes = ['OIL','GAS','COND','CC'];
		$entries = static::whereIn('CODE',$codes)->where("ACTIVE","=",1)->orderBy("ORDER")->get();
		return $entries;
	}
}
