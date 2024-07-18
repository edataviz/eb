<?php 
namespace App\Models; 

class CodeDeferTheorMethod extends DynamicModel {

	protected $table = 'CODE_DEFER_THEOR_METHOD';
	
	public static function loadTheorBy($sourceColumnValue,$columns = null){
		$data = [];
		$entry	= static::find($sourceColumnValue);
		if ($entry) {
			$mdl = "\App\Models\\{$entry->CODE}";
			if (class_exists($mdl)){
				$data	= $mdl::loadTheorEntries($columns);
			}
		}
		return $data;
	}
} 
