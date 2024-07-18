<?php 
namespace App\Models; 

class GraphCfgFieldProps extends CfgFieldProps { 
 	protected $primaryKey = 'ID2';

 	public function getSecondaryKeyName(){
 		return "COLUMN_NAME";
 	}
} 
