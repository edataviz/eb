<?php 
namespace App\Models; 
use App\Models\DynamicModel; 
use App\Trail\ObjectNameLoad;

 class CodeSampleType extends DynamicModel {
	use ObjectNameLoad;
	protected $table = 'CODE_SAMPLE_TYPE'; 

	public static function find($id){
		if ($id==0) {
			$instance = new CodeSampleType;
			return $instance;
		}
		else  return static ::where('ID',$id)->first();
	}
} 
