<?php
namespace App\Http\Controllers\Cargo;
use App\Http\Controllers\Cargo\VoyageController;
use App\Models\PdVoyage;
use App\Models\ShipCargoBlmrData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CargoShipblmrController extends VoyageController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> $dcTable==ShipCargoBlmrData::getTableName()?100:60];
	}
	
	public function __construct() {
		parent::__construct();
		$this->detailModel = "ShipCargoBlmrData";
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if (!array_key_exists("Storage", $postData)) {
    		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
    	}
    	$storage_id		= $postData['Storage'];
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	$mdlName 		= $postData[config("constants.tabTable")];
    	$mdl 			= "App\Models\\$mdlName";
    	$pdVoyage 		= PdVoyage::getTableName();
//     	\DB::enableQueryLog();
    	$dataSet = $mdl::join($pdVoyage,
    			"$dcTable.VOYAGE_ID",
    			'=',
    			"$pdVoyage.ID")
    			->whereDate("$pdVoyage.SCHEDULE_DATE",'>=',$occur_date)
    			->whereDate("$pdVoyage.SCHEDULE_DATE",'<=',$date_end)
    			->where("$pdVoyage.STORAGE_ID",'=',$storage_id)
    			->select(
    					"$dcTable.ID as $dcTable",
    					"$dcTable.ID as DT_RowId",
    					"$dcTable.*")
    					->get();
//     					\Log::info(\DB::getQueryLog());
    	return ['dataSet'=>$dataSet];
    }
	
    public function getDetailData($id,$postData,$properties){
    	$detailTable	 		= ShipCargoBlmrData::getTableName();
		$originAttrCase = \Helper::setGetterUpperCase();
    	$dataSet 				= ShipCargoBlmrData::where("BLMR_ID",'=',$id)
				    			->select(
				    					"$detailTable.*",
				    					"$detailTable.ID as DT_RowId",
 				    					"$detailTable.ID as $detailTable"
				    					)
		    					->get();
		\Helper::setGetterCase($originAttrCase);
    	return $dataSet;
    }
    
    public function cal(Request $request){
    	$postData 		= $request->all();
    	$id 			= $postData['id'];
    	$isAll			= isset($postData['isAll'])?$postData['isAll']:false;
//     		$sql="select ID, FORMULA_ID from SHIP_CARGO_BLMR_DATA where BLMR_ID=$vid";
    	$where 			= $isAll?["BLMR_ID" => $id]:["ID" => $id];
		$originAttrCase = \Helper::setGetterUpperCase();
    	$blmrData 		= ShipCargoBlmrData::where($where)->select(["ID","FORMULA_ID"])->get();
		\Helper::setGetterCase($originAttrCase);
    	
    	try {
	    	$ids = \DB::transaction(function () use ($blmrData){
	    		$ids = [];
	    		foreach($blmrData as $shipCargoBlmrData ){
		    		$val								= \FormulaHelpers::doEvalFormula($shipCargoBlmrData->FORMULA_ID);
		    		$shipCargoBlmrData->LAST_CALC_TIME 	= Carbon::now();
		    		$shipCargoBlmrData->ITEM_VALUE 		= $val;
		    		$shipCargoBlmrData->save();
		    		$ids[] 								= $shipCargoBlmrData->ID;
	    		}
	    		return $ids;
	    		/* $row=getOneRow("select ID, FORMULA_ID from SHIP_CARGO_BLMR_DATA where ID=$vid");
	    		$val=evalFormula($row[FORMULA_ID],false,$vid);
	    		$sql="update SHIP_CARGO_BLMR_DATA set LAST_CALC_TIME=now(),ITEM_VALUE='$val' where ID=$vid"; */
	    	
	    	});
    	}
    	catch (\Exception $e) {
//     		$results = "error";
			$msg = $e->getMessage();
    		return response()->json($msg, 500);
    	}

    	$originAttrCase = \Helper::setGetterUpperCase();
    	$updatedData 	= ["ShipCargoBlmrData" 	=> ShipCargoBlmrData::findManyWithConfig($ids)];
		\Helper::setGetterCase($originAttrCase);
    	$results 		= ['updatedData'		=> $updatedData,
    						'postData'			=> $postData];
    	
    	return response()->json($results);
    }
}
