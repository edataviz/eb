<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargoNomination;
use App\Models\PdCargo;
use App\Models\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CargoEntryController extends CodeController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>90];
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	
    	$date_end 		= array_key_exists('date_end',  $postData)?$postData['date_end']:null;
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
    	$mdlName = $postData[config("constants.tabTable")];
    	$mdl = "App\Models\\$mdlName";
    	
    	$storage = Storage::getTableName();
    	$pdCargoNomination = PdCargoNomination::getTableName();
    	 
//     	\DB::enableQueryLog();
		$originAttrCase = \Helper::setGetterUpperCase();
    	$query 	= $mdl::join($storage,"$dcTable.STORAGE_ID", '=', "$storage.ID")
    					->leftJoin($pdCargoNomination,"$pdCargoNomination.CARGO_ID", '=', "$dcTable.ID")
    					->where(["$storage.FACILITY_ID" => $facility_id])
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$pdCargoNomination.ID as IS_NOMINATED",
				    			"$dcTable.*");
//   		    			->orderBy('EFFECTIVE_DATE')
//   		    			->get();
  		if ($date_end) 		$query->whereDate("$dcTable.REQUEST_DATE",'<=',$date_end);
  		if ($occur_date) 	$query->whereDate("$dcTable.REQUEST_DATE",'>=',$occur_date);
  		$dataSet = $query->get();
		\Helper::setGetterCase($originAttrCase);
//  		\Log::info(\DB::getQueryLog());
  		return ['dataSet'=>$dataSet,
//     			'objectIds'=>$objectIds
    	];
    }
    
    public function nominate(Request $request){
    	$postData 			= $request->all();
    	$cargo_id 			= $postData['cargoId'];
    	$pdCargo			= PdCargo::getTableName();
    	$pdCargoNomination 	= PdCargoNomination::getTableName();
    	/* $checkNominated		= PdCargo::join($pdCargoNomination,"$pdCargoNomination.CARGO_ID", '=', "$pdCargo.ID")
								    	->where("$pdCargo.ID",'=',$cargo_id)
								    	->select("$pdCargo.NAME")
								    	->first(); */
    	 
    	$pdCargoNomination		= PdCargoNomination::firstOrNew(["CARGO_ID"=>$cargo_id]);
    	if($pdCargoNomination){
    		if($pdCargoNomination->exists){
    			$result = 	['code'		=> 'EXIST',
    						'message'	=> "Cargo id $cargo_id has been nominated already."];
    		}
    		else{
    			$cargo		= PdCargo::find($cargo_id);
    			if ($cargo) {
	    			$values = [
	    					"CARGO_ID"			=> $cargo_id,
	    					"REQUEST_DATE"		=> $cargo->REQUEST_DATE,
	    					"REQUEST_QTY"		=> $cargo->REQUEST_QTY,
	    					"REQUEST_QTY_UOM"	=> $cargo->REQUEST_UOM,
	    			];
	    			$insertId = $pdCargoNomination->fill($values)->save();
		    		if ($insertId>0) {
			    		$result = 	['code'		=> 'DONE',
			    					'message'	=> "Cargo id $cargo_id is nominated successfully!"];
		    		}
		    		else $result = 	['code'		=> 'ERROR',
			    					'message'	=> "unsuccessfully!"];
    			}
    			else $result = 	['code'		=> 'NOT_EXIST',
			    				 'message'	=> "Cargo id $cargo_id not exist!"];
    		}
    	}
    	else $result = 	['code'		=> 'ERROR',
    					'message'	=> "unsuccessfully!"];
    	
    	return response()->json($result);
    }
}
