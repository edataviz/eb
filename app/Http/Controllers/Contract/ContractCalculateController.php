<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\PdContractCalculation;
use App\Models\PdContractQtyFormula;
use App\Models\PdContractYear;
use Illuminate\Http\Request;

class ContractCalculateController extends CodeController {
    
	public function load(Request $request){
		$postData 	= $request->all();
		$contractId = $postData['PdContract'];
		$originAttrCase = \Helper::setGetterUpperCase();
		$results 	= $this->loadData($contractId,$postData);
		\Helper::setGetterCase($originAttrCase);

		return response()->json($results);
	}
	
	public function loadData($contractId,$postData){
		$pdContractYear			= PdContractYear::getTableName();
		$pdContractCalculation	= PdContractCalculation::getTableName();
		
		$contractYears = PdContractYear::join($pdContractCalculation,"$pdContractYear.CALCULATION_ID", '=', "$pdContractCalculation.ID")
										->where("$pdContractYear.CONTRACT_ID", '=', $contractId)
										->select("$pdContractYear.FORMULA_VALUE",
												"$pdContractYear.YEAR",
												"$pdContractCalculation.FORMULA_ID")
												->orderBy("$pdContractYear.YEAR",'desc')
												->get();
		
				 
		$propertiesArray = [(object)['data' =>	'NAME'			,'title' => 'Contract Quantity' ,'width' => 150    ],
							(object)['data' =>	'DESCRIPTION'	,'title' => 'Description'		,'width' => 400    ]];


		$pdContractQtyFormula			= PdContractQtyFormula::getTableName();
		$data 							= PdContractQtyFormula::select(
																	"ID as DT_RowId",
																	"$pdContractQtyFormula.*")
																->get();

		$years = $contractYears->groupBy("YEAR");
		if($years->count()>0){
			foreach($years as $year => $set ){
				$yearField = "year$year";
				$propertiesArray[] = (object)['data' =>	$yearField		,'title' => "$year",'DATA_METHOD'=>0,'INPUT_TYPE'=>2];
	
				$formulaGroups = $set->groupBy("FORMULA_ID");
	
				$data = $data->each(function ($item, $key) use ($yearField,$formulaGroups){
					$item->{$yearField} = $formulaGroups[$item->ID][0]->FORMULA_VALUE;
				});
			}
		}
		$properties = collect($propertiesArray);
		$results['properties']	= $properties;
		$results['dataSet'] 	= $data;
		$results['postData'] 	= $postData;
		return $results;
	}
	
	public function addyear(Request $request){
    	$postData 								= $request->all();
    	$contractId								= $postData['PdContract'];
     	$year		 							= $postData['year'];
     	
     	$qltyFormulas		 					= PdContractQtyFormula::all();
     	$formulaValues  						= \FormulaHelpers::getDataFormulaContract($qltyFormulas,$contractId,$year);
     	
     	$resultTransaction 						= \DB::transaction(function () use ($qltyFormulas,$formulaValues,$contractId,$year){
	     	$attributes 						= ['CONTRACT_ID'	=> $contractId];
	     	$yAttributes 						= ['CONTRACT_ID'	=> $contractId,
	     											'YEAR'			=> $year
	     	];
	     	$yValues	 						= ['CONTRACT_ID'	=> $contractId,
	     											'YEAR'			=> $year
	     	];
	     	 
// 	     	PdContractYear::where($yAttributes)->delete();
	     	foreach($qltyFormulas 	as 	$key 	=> $qltyFormula) {
	     		$attributes['FORMULA_ID'] 		= $qltyFormula->ID;
	     		$values 						= $attributes;
	     		$calculation					= PdContractCalculation::updateOrCreate($attributes,$values);
	     		
	     		/* $sql = "INSERT INTO pd_contract_calculation(FORMULA_ID,CONTRACT_ID) "
	     				. "VALUE(".$aryRequest['FORMULA_ID'.$id].",".$aryRequest['CONTRACT_ID'].")"; */
	     		
	     		$formulaValue					= (int) $formulaValues[$qltyFormula->ID];
	     		$yAttributes['CALCULATION_ID'] 	= $calculation->ID;
	     		$yValues['CALCULATION_ID'] 		= $calculation->ID;
	     		$yValues['FORMULA_VALUE'] 		= $formulaValue;
	     		$contractYear					= PdContractYear::updateOrCreate($yAttributes,$yValues);
	     		 
	     				
	     	
	     		/* $val = (int) $aryValue[$formulaId[$key]]; // abc($id,$contractId)  se thay bang cong thuc
	     		$sql2 = "INSERT INTO pd_contract_year(CALCULATION_ID,YEAR,FORMULA_VALUE,CONTRACT_ID) VALUE($id,$year,'$val',$contractId)";
	     		$sql2=str_replace("''", "NULL", $sql2); */
	     	}
     	});

     	$originAttrCase = \Helper::setGetterUpperCase();
     	$results 	= $this->loadData($contractId,$postData);
		\Helper::setGetterCase($originAttrCase);
     	
     	return response()->json($results);
	}
}
