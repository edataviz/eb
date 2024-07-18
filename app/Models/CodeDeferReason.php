<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class CodeDeferReason extends DynamicModel 
{ 
	protected $table = 'CODE_DEFER_REASON'; 
	
	public function CodeDeferReason2($option=null){
		return $this->hasMany('App\Models\CodeDeferReason2', 'PARENT_ID', 'ID');
	}
} 
