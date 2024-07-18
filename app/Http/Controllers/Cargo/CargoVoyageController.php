<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargo;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use App\Models\PdTransitCarrier;
use App\Models\PdShipPortInformation;
use App\Models\PdTransportPipelineDetail;
use App\Models\PdTransportShipDetail;
use App\Models\PdTransportGroundDetail;
use Illuminate\Http\Request;

class CargoVoyageController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdVoyageDetail";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	}
	
	public function loadDetail(Request $request){
		$postData 				= $request->all();
		$id 					= $postData['id'];
		$tab					= isset($postData['tab'])?$postData['tab']:$this->detailModel;
		$detailModel			= "App\Models\\$tab";
		$detailTable	 		= $detailModel::getTableName();
		$results 				= $this->getProperties($detailTable);
		$originAttrCase = \Helper::setGetterUpperCase();
		$dataSet 				= $this->getDetailData($id,$postData,$results['properties']);
		\Helper::setGetterCase($originAttrCase);
		
		$results['dataSet'] 	= $dataSet;
	
		return response()->json([$this->detailModel => $results]);
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if (!array_key_exists("Storage", $postData)) {
    		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
    	}
    	$storage_id			= $postData['Storage'];
    	$date_end 			= $postData['date_end'];
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
    	$mdlName 			= $postData[config("constants.tabTable")];
    	$mdl 				= "App\Models\\$mdlName";
    	$pdCargo 			= PdCargo::getTableName();
    	 
    	$dataSet = $mdl::join($pdCargo,
			    			"$dcTable.CARGO_ID",
			    			'=',
			    			"$pdCargo.ID")
		    			->whereDate("$dcTable.SCHEDULE_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.SCHEDULE_DATE",'<=',$date_end)
    					->where("$pdCargo.STORAGE_ID",'=',$storage_id)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    public function getDetailData($id,$postData,$properties){
    	$facility	 			= $postData['Facility'];
		$pdVoyage				= PdVoyage::getTableName();
    	$pdVoyageDetail			= PdVoyageDetail::getTableName();
    	$dataSet 				= PdVoyageDetail::join($pdVoyage,
						    			"$pdVoyageDetail.VOYAGE_ID",
						    			'=',
						    			"$pdVoyage.ID")
				    			->where("$pdVoyage.ID",'=',$id)
				    			->select(
				    					"$pdVoyageDetail.*",
				    					"$pdVoyage.SCHEDULE_UOM as VOYAGE_SCHEDULE_UOM",
				    					"$pdVoyageDetail.ID as DT_RowId",
										"$pdVoyageDetail.ID as $pdVoyageDetail"
				    					)
		    					->get();
    	return $dataSet;
    }
    
    public function gentransport(Request $request){
    	$postData 			= $request->all();
    	$voyage_id			= $postData['VOYAGE_ID'];

    	$pdVoyageDetail		= PdVoyageDetail::getTableName();
    	$pdVoyage			= PdVoyage::getTableName();
    	$pdTransitCarrier	= PdTransitCarrier::getTableName();
    	$dataSet = PdVoyageDetail::join($pdVoyage,
						    			"$pdVoyageDetail.VOYAGE_ID",
						    			'=',
						    			"$pdVoyage.ID")
				    			->join($pdTransitCarrier,
						    			"$pdVoyage.CARRIER_ID",
						    			'=',
						    			"$pdTransitCarrier.ID")
				    			->where("$pdVoyage.ID",'=',$voyage_id)
				    			->orderBy("$pdVoyageDetail.ID")
				    			->select(
				    					"$pdVoyage.CODE as VOYAGE_CODE",
				    					"$pdVoyage.NAME as VOYAGE_NAME",
				    					"$pdVoyage.QUANTITY_TYPE as VOYAGE_QTY_TYPE",
				    					"$pdTransitCarrier.TRANSIT_TYPE",
				    					"$pdVoyageDetail.*")
		    					->get();
    	
    	try
    	{
    		$resultTransaction = \DB::transaction(function () use ($dataSet,$voyage_id){
    			$attributes = ['VOYAGE_ID'	=> $voyage_id];
    			foreach($dataSet as $ro){
    				$attributes['PARCEL_NO']	= $ro->PARCEL_NO;
    				switch ($ro->TRANSIT_TYPE) {
    					case 3:
    						$pdTransportShipDetail			= PdTransportShipDetail::where($attributes)->first();
    						if (!$pdTransportShipDetail) {
    							$values						= ['VOYAGE_ID'	=> $voyage_id];
    							$values['CODE']				= "SH_$ro->VOYAGE_CODE"."_$ro->PARCEL_NO";
    							$values['NAME']	 			= "SH_$ro->VOYAGE_NAME"."_$ro->PARCEL_NO";
    							$values['CARGO_ID']			= $ro->CARGO_ID;
    							$values['PARCEL_NO']		= $ro->PARCEL_NO;
    							$values['RECEIPT_QTY']		= $ro->LOAD_QTY;
    							$values['QTY_TYPE']			= $ro->VOYAGE_QTY_TYPE;
    							$values['QTY_UOM']			= $ro->LOAD_UOM;
    				    
    							$pdTransportShipDetail		= PdTransportShipDetail::insert($values);
    							$pdShipPortInformation		= PdShipPortInformation::insert($attributes);
    						}
    						break;
    			
    					case 4:
    						$pdTransportPipelineDetail		= PdTransportPipelineDetail::where($attributes)->first();
    						if (!$pdTransportPipelineDetail) {
    							$values						= ['VOYAGE_ID'	=> $voyage_id];
    							$values['CODE']				= "PP_$ro->VOYAGE_CODE"."_$ro->PARCEL_NO";
    							$values['NAME']	 			= "PP_$ro->VOYAGE_NAME"."_$ro->PARCEL_NO";
    							$values['CARGO_ID']			= $ro->CARGO_ID;
    							$values['PARCEL_NO']		= $ro->PARCEL_NO;
    							$values['QUANTITY']			= $ro->LOAD_QTY;
    							$values['QUANTITY_UOM']		= $ro->LOAD_UOM;
    			
    							$pdTransportPipelineDetail	= PdTransportPipelineDetail::insert($values);
    						}
    						break;
    			
    					default:
    						$pdTransportGroundDetail		= PdTransportGroundDetail::where($attributes)->first();
    						if (!$pdTransportGroundDetail) {
    							$values						= ['VOYAGE_ID'	=> $voyage_id];
    							$values['CODE']				= "GR_$ro->VOYAGE_CODE"."_$ro->PARCEL_NO";
    							$values['NAME']	 			= "GR_$ro->VOYAGE_NAME"."_$ro->PARCEL_NO";
    							$values['CARGO_ID']			= $ro->CARGO_ID;
    							$values['PARCEL_NO']		= $ro->PARCEL_NO;
    							$values['QUANTITY']			= $ro->LOAD_QTY;
    							$values['QUANTITY_UOM']		= $ro->LOAD_UOM;
    			
    							$pdTransportGroundDetail	= PdTransportGroundDetail::insert($values);
    						}
    						break;
    				}
    			}
    		});
    	}
	    catch (\Exception $e)
	    {
    		return response()->json('error when insert data');
	    }
	    return response()->json('success');
    }
}
