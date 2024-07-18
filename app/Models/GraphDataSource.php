<?php 
namespace App\Models;

class GraphDataSource extends DynamicModel
{ 
	protected $table = 'GRAPH_DATA_SOURCE'; 

	protected static $fieldVALUE = 'SOURCE_NAME';
	protected static $fieldNAME = 'SOURCE_NAME';
	protected static $fieldACTIVE = null;
	protected static $fieldORDER = null;

	public static function getList($condition = [], $all = false, $none = false){
		$list = static :: where($condition)->select('SOURCE_NAME as value');
		$list = $list->orderBy('value')->get()->all();
        if($none){
            array_unshift($list, ['value' => 'none', 'name' => '(None)']);
        }
        if($all){
            array_unshift($list, ['value' => 'all', 'name' => '(All)']);
		}
		foreach($list as $item)
			$item['name'] = \Helper::getModelName($item['value'], false);
		return $list;
	}
} 
