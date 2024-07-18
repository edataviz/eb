<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class CodeDeferCode2 extends DynamicModel 
{ 
	protected $table = 'code_defer_code2';
	
	
	public function CodeDeferCode3($option=null){
		return $this->hasMany('App\Models\CodeDeferCode3', 'PARENT_ID', 'ID');
	}
} 
