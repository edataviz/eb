<?php
namespace App\Http\Controllers\Contract;

use App\Http\Controllers\CodeController;
use App\Models\PdContractCalculation;
use App\Models\PdContractData;
use App\Models\PdContractQtyFormula;
use App\Models\PdContractYear;
use App\Models\PdCargo;
use Illuminate\Http\Request;

class ContractProgramController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdContractData";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'Actions','width'=> 50];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    		
    	$date_end 		= $postData['date_end'];
    	$date_end 		= \Helper::parseDate($date_end);
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::whereDate("$dcTable.START_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.START_DATE",'<=',$date_end)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
//  		\Log::info(\DB::getQueryLog());
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    public function open(Request $request){
    	$postData 				= $request->all();
    	$code 					= $postData['code'];
    	$contract_id 			= $postData['contract_id'];
    	$storage_id 			= $postData['storage_id'];
    	 
		return view ( "front.contract.$code",
				['contract_id'		=> $contract_id,
				'storage_id'		=> $storage_id
		]);
    }
    public function calculate(Request $request){
    	$postData 				= $request->all();
    	$contract_id 			= $postData['contract_id'];
    	$formula_id 			= $postData['PdContractQtyFormula'];
    	$pdContractYear 		= PdContractYear::getTableName();
    	$pdContractCalculation 	= PdContractCalculation::getTableName();
    	
    	$value = PdContractYear::join($pdContractCalculation,function ($query) use ($pdContractYear,$pdContractCalculation,$formula_id) {
     												$query->on("$pdContractCalculation.ID",'=',"$pdContractYear.CALCULATION_ID" );
													$query->where("$pdContractCalculation.FORMULA_ID",'=',$formula_id );
											})
					->where("$pdContractYear.CONTRACT_ID",$contract_id)
					->sum("$pdContractYear.FORMULA_VALUE");
    	return response()->json($value);
    }
    
    public function gen(Request $request){
    	try {
	    	$postData 			= $request->all();
    		$result 			= \DB::transaction(function () use ($postData){
		    	$contract_id 	= $postData['contract_id'];
		    	$storage_id 	= $postData['PdContractQtyFormula'];
		    	
		    	$code1st		= array_key_exists("code1st" 				, $postData)?$postData["code1st"]			:0;
		    	$year1			= array_key_exists("year1"   				, $postData)?$postData["year1"]				:0;
		    	$code2nd		= array_key_exists("code2nd" 				, $postData)?$postData["code2nd"]			:0;
		    	$year			= array_key_exists("year"    				, $postData)?$postData["year"]				:0;
		    	$month			= array_key_exists("month"   				, $postData)?$postData["month"]				:0;
		    	$day			= array_key_exists("day"     				, $postData)?$postData["day"]				:"";
		    	$seq			= array_key_exists("seq"     				, $postData)?$postData["seq"]				:0;
		    	$liftacc		= array_key_exists("PdLiftingAccount" 		, $postData)?$postData["PdLiftingAccount"]	:0;
		    	$priority		= array_key_exists("PdCodeCargoPriority"	, $postData)?$postData["PdCodeCargoPriority"]:0;
		    	$qtytype		= array_key_exists("PdCodeCargoQtyType" 	, $postData)?$postData["PdCodeCargoQtyType"]:0;
		    	$date1st		= array_key_exists("date1st" 				, $postData)?$postData["date1st"]			:0;
		    	$avgqty			= array_key_exists("avgqty"  				, $postData)?$postData["avgqty"]			:0;
		    	$uom			= array_key_exists("PdCodeMeasUom"     		, $postData)?$postData["PdCodeMeasUom"]		:0;
		    	$adjtime		= array_key_exists("PdCodeTimeAdj" 			, $postData)?$postData["PdCodeTimeAdj"]		:0;
		    	$tolerance		= array_key_exists("PdCodeQtyAdj"			, $postData)?$postData["PdCodeQtyAdj"]		:0;
		    	$qty			= array_key_exists("qty"     				, $postData)?$postData["qty"]				:0;
		    	
		    	$n=strlen($seq);
		    	$num=$seq+1-1;
		    	$count=0;
		    	$x_qty=0;
		    	if($qty<=0){
		    		return "Quantity value must be greater than zero";
		    	}
		    	if($avgqty<=0){
		    		return "Average quantity value must be greater than zero";
		    	}
	    	
		    	$date1st 		= \Helper::parseDate($date1st);
		    	$requestDate	= $date1st;
		    	while(true){
		    		$x_qty		+=$avgqty;
		    		$exit		= ($x_qty>=$qty);
		    		$code		= $code1st.$year1.$code2nd.$year.$month.$day.str_pad($num, $n, '0', STR_PAD_LEFT);
		    	
		    		PdCargo::where("CODE",$code)->delete();
		    		PdCargo::insert([
		    				"CODE"          => $code,
		    				"NAME"          => $code,
		    				"LIFTING_ACCT"  => $liftacc,
		    				"STORAGE_ID"    => $storage_id,
		    				"REQUEST_DATE"  => $requestDate,
		    				"REQUEST_QTY"   => $avgqty,
		    				"REQUEST_UOM"   => $uom,
		    				"PRIORITY"      => $priority,
		    				"QUANTITY_TYPE" => $qtytype,
		    				"CONTRACT_ID"   => $contract_id,
		    		]);
		    		
		    		$num++;
		    		$requestDate = $requestDate->addMonths(6);
		    		if($exit) break;
		    	}
		    	return "Sucess";
    		});
    	}
    	catch (\Exception $e){
    		$result = "could not generate cargo";
    		\Log::info("\n---gen cargo--\nException wher run transation\n ");
    		\Log::info($e->getMessage());
    		\Log::info($e->getTraceAsString());
    		// 			return response($e->getMessage(), 400);
    	}
    	
    	return response()->json($result);
    }
}
