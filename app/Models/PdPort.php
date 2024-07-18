<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdPort extends DynamicModel 
{ 
	protected $table = 'PD_PORT'; 
	public static function getList($condition = [], $all = false, $none = false){
		$list = static :: select(static::$fieldVALUE.' as value', static::$fieldNAME.' as name');
		$list = $list->orderBy('name')->get()->all();
		if($none){
			array_unshift($list, ['value' => 'none', 'name' => '(None)']);
		}
		if($all){
			array_unshift($list, ['value' => 'all', 'name' => '(All)']);
		}
		return $list;
	}
} 
