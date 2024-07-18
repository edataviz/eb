<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\pdCodeContractAttribute;
use App\Models\PdContractTemplateAttribute;

class ContractTemplateController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdContractTemplateAttribute";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if ($dcTable==PdContractTemplateAttribute::getTableName()) {
    		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
    	}
    		
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	$pdCodeContractAttribute= PdCodeContractAttribute::getTableName();
    	 
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.EFFECTIVE_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.EFFECTIVE_DATE",'<=',$date_end)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    
    public function getDetailData($id,$postData,$properties){
    	$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
    	$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
    	
    	$dataSet 						= PdContractTemplateAttribute::join($pdCodeContractAttribute,
									    			"$pdContractTemplateAttribute.ATTRIBUTE",
									    			'=',
									    			"$pdCodeContractAttribute.ID")
							    			->where("$pdContractTemplateAttribute.CONTRACT_TEMPLATE",'=',$id)
							    			->where("$pdContractTemplateAttribute.ACTIVE",'=',1)
							    			->select(
							    					"$pdContractTemplateAttribute.ID as DT_RowId",
			 				    					"$pdContractTemplateAttribute.ID",
			 				    					"$pdContractTemplateAttribute.ID as $pdContractTemplateAttribute",
			 				    					"$pdContractTemplateAttribute.ATTRIBUTE"
							    					)
					    					->get();
    	return $dataSet;
    }
}
