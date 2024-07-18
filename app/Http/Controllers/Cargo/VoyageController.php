<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdVoyage;
use App\Models\ShipCargoBlmr;
use App\Models\Storage;
use Illuminate\Http\Request;

class VoyageController extends CodeController {
    protected $modelName;
    protected $parentType;
    
    public function getFirstProperty($dcTable){
    	return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> 60];
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
    	$dataSet 		= $mdl::join($pdVoyage,"$dcTable.VOYAGE_ID",'=',"$pdVoyage.ID")
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
    
    public function getShipCargoBlmr($pid){
    	return ShipCargoBlmr::where("PARENT_ID",'=',$pid)->where("PARENT_TYPE",'=',$this->parentType)->first();
    }
    
    public function getUpdateFields($shipCargoBlmr,$transportType){
    	return array(
					"$shipCargoBlmr.ITEM_VALUE" => \DB::raw("$transportType.RECEIPT_QTY"),
					"$shipCargoBlmr.ITEM_UOM" 	=> \DB::raw("$transportType.QTY_UOM"),
					"$shipCargoBlmr.DATE_TIME" 	=> \DB::raw("$transportType.ARRIVAL_TIME"),
				);
    }
    
    public function getInsertFields($pid,$pdVoyage,$storage,$transportType){
    	return [
				\DB::raw("$pid as PARENT_ID") ,
				\DB::raw("'$this->parentType' as PARENT_TYPE"),
				"$pdVoyage.CARRIER_ID"              ,
				"$transportType.VOYAGE_ID"               ,
				"$transportType.DEPART_PORT as PORT_ID" ,
				"$transportType.CARGO_ID"                ,
				"$transportType.PARCEL_NO"               ,
				"$pdVoyage.LIFTING_ACCOUNT"         ,
				"$storage.PRODUCT as PRODUCT_TYPE"    ,
				"$transportType.QTY_TYPE as MEASURED_ITEM"  ,
				"$transportType.RECEIPT_QTY as ITEM_VALUE" ,
				"$transportType.QTY_UOM as ITEM_UOM"       ,
				"$transportType.ARRIVAL_TIME as DATE_TIME"  ,
// 				'COMMENT'        => 'null 'COMMENT'             ,
			];
    }
    
    public function genBLMR(Request $request){
    	$postData 				= $request->all();
    	$pid					= $postData['id'];
    	
    	$shipCargoBlmr 			= $this->getShipCargoBlmr($pid);
    	$xid 					= $shipCargoBlmr?$shipCargoBlmr->ID:0;
    	 
    	$results = \DB::transaction(function () use ($xid,$pid){
    				$mdl 							= $this->modelName;
    				$transportType 					= $mdl::getTableName();
    				$shipCargoBlmr 					= ShipCargoBlmr::getTableName();
    				
			    	if($xid>0){
// 			    			\DB::enableQueryLog();
			    		if (config('database.default')==='mysql') {
			    			$values 	= $this->getUpdateFields($shipCargoBlmr,$transportType);
							ShipCargoBlmr::join($transportType,
							   					"$transportType.ID",
								    			'=',
								    			"$shipCargoBlmr.PARENT_ID")
							    			->where("$shipCargoBlmr.ID",$xid)
							    			->update($values);
			    		} elseif (config('database.default')==='oracle') {
			    			$values = array(
								"t.ITEM_VALUE" => \DB::raw("t.RECEIPT_QTY"),
								"t.ITEM_UOM" 	=> \DB::raw("t.QTY_UOM"),
								"t.DATE_TIME" 	=> \DB::raw("t.ARRIVAL_TIME")
			    			);
							\DB::table( \DB::raw("(select * from SHIP_CARGO_BLMR join PD_TRANSPORT_SHIP_DETAIL on(PD_TRANSPORT_SHIP_DETAIL.ID = SHIP_CARGO_BLMR.PARENT_ID ) where SHIP_CARGO_BLMR.ID = 1) t") )
						    			->update($values);
			    		}
// 						   \Log::info(\DB::getQueryLog());
				    	return 'Success(only update exist rows)';
			    	}
			    	else{
			    		$pdVoyage 				= PdVoyage::getTableName();
			    		$storage 				= Storage::getTableName();
			    		$values 				= $this->getInsertFields($pid,$pdVoyage,$storage,$transportType);
			    		 
			    		$mdl::join($pdVoyage,
		    				"$pdVoyage.ID",
		    				'=',
		    				"$transportType.VOYAGE_ID")
	    				->join($storage,
    						"$storage.ID",
    						'=',
    						"$pdVoyage.STORAGE_ID")
	    				->where("$transportType.ID",$pid)
	    				->select($values)
	    				->chunk(10, function($rows) {
	    					$valueInsert = $rows->toArray();
	    					if (config('database.default')==='oracle') {
	    						unset($valueInsert[0]['rn']);
	    					}
	    					ShipCargoBlmr::insert($valueInsert);
	    				});
				    	return 'Success!';
			    	}
			    	
    	});
    	return response()->json($results);
    }
}
