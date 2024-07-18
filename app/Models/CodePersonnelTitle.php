<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class CodePersonnelTitle extends DynamicModel 
{ 
	protected $table = 'CODE_PERSONNEL_TITLE';
	protected $primaryKey = 'ID';
	
	
 	/* public function Personnel(){
		return $this->hasMany('App\Models\Personnel','TITLE', $this->primaryKey);
	} */
	
} 
