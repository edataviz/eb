<?php 
namespace App\Models; 

 class ExtensionEbFunctions extends EbFunctions { 
 	protected $primaryKey = null;
 	
	public static function loadBy($sourceData){
		$entries				= null;
		if ($sourceData!=null&&is_array($sourceData)) {
			$objectType 	= $sourceData['EbFunctions'];
			$code 			= $objectType->CODE;
			if($code!=='workflow-fun'){
				$where	= ['PARENT_CODE'=>$code];
				$entries 	= static ::where('USE_FOR','like',"%TASK_FUNC%")
									->where($where)
									->select("CODE",
											"ID",
											"PATH as FUNCTION_URL",
											"CODE as value",
											"NAME as text",
											"NAME")
									->get();
			}else{
				$entries 	= TmWorkflow ::select("ID","NAME")->get();
			}
			
		}
		
		return $entries;
	}
	
} 
