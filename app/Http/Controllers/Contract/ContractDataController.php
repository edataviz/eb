<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\pdCodeContractAttribute;
use App\Models\PdContractData;
use App\Models\PdContractQtyFormula;
use App\Models\PdContractTemplateAttribute;
use App\Models\CfgFieldProps;


class ContractDataController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdContractData";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if ($dcTable==PdContractData::getTableName()) {
    		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
    	}
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.BEGIN_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.BEGIN_DATE",'<=',$date_end)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    public function getDetailData($id,$postData,$properties){
    	$templateId 					= $postData['templateId'];
    	$pdContractData					= PdContractData::getTableName();
    	$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
    	$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
    	$pdContractQtyFormula			= PdContractQtyFormula::getTableName();
    	
    	$contractDataSet = PdContractData::join($pdCodeContractAttribute,
					    			"$pdContractData.ATTRIBUTE_ID",
					    			'=',
					    			"$pdCodeContractAttribute.ID")
				    			->leftJoin($pdContractQtyFormula,
				    					"$pdCodeContractAttribute.FORMULA_ID",
				    					'=',
				    					"$pdContractQtyFormula.ID")
				    			->where("$pdContractData.CONTRACT_ID",'=',$id)
				    			->select(
				    					"$pdContractData.*",
				    					"$pdContractData.CONTRACT_ID as CONTRACT_ID_INDEX",
				    					"$pdContractData.ATTRIBUTE_ID as ATTRIBUTE_ID_INDEX",
				    					"$pdCodeContractAttribute.ID as DT_RowId",
 				    					"$pdCodeContractAttribute.ID as $pdContractData",
				    					"$pdCodeContractAttribute.NAME as CONTRACT_ID",
				    					"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
 				    					"$pdCodeContractAttribute.ID as ID",
				    					"$pdContractQtyFormula.NAME as FORMULA"
				    					)
		    					->get();
		    					
		    					
    	$selects = ["$pdCodeContractAttribute.ID as DT_RowId",
	    			"$pdCodeContractAttribute.ID as $pdContractData",
	    			"$pdCodeContractAttribute.NAME as CONTRACT_ID",
	    			"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
	    			"$pdCodeContractAttribute.ID",
				    "$pdCodeContractAttribute.FORMULA_ID as FORMULA",
    				"$pdCodeContractAttribute.ID as ATTRIBUTE_ID_INDEX",
    				"$pdContractQtyFormula.NAME as FORMULA",
    				\DB::raw("$id as CONTRACT_ID_INDEX"),
    	];
    	
    	foreach($properties["properties"] as $property ){
    		if ($property instanceof CfgFieldProps) {
	    		$columnName = $property->data;
	    		if ($columnName!='CONTRACT_ID'&&$columnName!='ATTRIBUTE_ID'&&$columnName!='ID') {
		    		$selects[] = \DB::raw("null as $columnName");
	    		}
    		}
    	}
    	
    	$templateQuery = PdContractTemplateAttribute::join($pdCodeContractAttribute, 
									    			"$pdContractTemplateAttribute.ATTRIBUTE", 
									    			'=', 
									    			"$pdCodeContractAttribute.ID")
								    			->leftJoin($pdContractQtyFormula,
								    					"$pdCodeContractAttribute.FORMULA_ID",
								    					'=',
								    					"$pdContractQtyFormula.ID")
										    	->where("$pdContractTemplateAttribute.CONTRACT_TEMPLATE",'=',$templateId)
										    	->where("$pdContractTemplateAttribute.ACTIVE",'=',1)
										    	->select($selects);
// 										    	->get();
    	
										    	
    	$field = config('database.default')==='oracle'?'id':'ID';
    	$existAttributes = $contractDataSet->pluck($field);
    	if (count($existAttributes)>0) {
    		$templateQuery->whereNotIn("$pdCodeContractAttribute.ID", $existAttributes);
    	}
    	$templateDataSet = $templateQuery->get();
    	$dataSet = $contractDataSet->union($templateDataSet);
    	return $dataSet;
    }
}
