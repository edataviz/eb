<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

class CodeDeferCode1 extends DynamicModel { 
	protected $table = 'CODE_DEFER_CODE1'; 
	
	public function CodeDeferCode2($option=null){
		return $this->hasMany('App\Models\CodeDeferCode2', 'PARENT_ID', 'ID');
	}
} 
