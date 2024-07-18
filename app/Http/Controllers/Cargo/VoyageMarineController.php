<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Cargo\VoyageController;
use App\Models\PdShipPortInformation;
use App\Models\PdTransportShipDetail;

class VoyageMarineController extends VoyageController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdShipPortInformation";
		$this->modelName = "App\Models\PdTransportShipDetail";
		$this->parentType = "S";
	}
	
    public function getFirstProperty($dcTable){
    	if ($dcTable==PdTransportShipDetail::getTableName()) {
			return  ['data'=>$dcTable,'title'=>'BL/MR','width'=> 120];
    	}
    	return null;
	}
	
    
    public function getDetailData($id,$postData,$properties){
    	$pdShipPortInformation 			= PdShipPortInformation::getTableName();
    	$pdTransportShipDetail 			= PdTransportShipDetail::getTableName();

    	$originAttrCase = \Helper::setGetterUpperCase();
    	$dataSet 						= PdShipPortInformation::join($pdTransportShipDetail,function ($query) 
    											use ($id,$pdShipPortInformation,$pdTransportShipDetail) {
							    					$query->on("$pdShipPortInformation.VOYAGE_ID",'=',"$pdTransportShipDetail.VOYAGE_ID");
							    					$query->on("$pdShipPortInformation.PARCEL_NO",'=',"$pdTransportShipDetail.PARCEL_NO");
										    		$query->where("$pdTransportShipDetail.ID",'=',$id) ;
												})
						    			->select(
						    					"$pdShipPortInformation.*",
						    					"$pdShipPortInformation.ID as DT_RowId",
		 				    					"$pdShipPortInformation.ID as $pdShipPortInformation"
						    					)
				    					->get();
		\Helper::setGetterCase($originAttrCase);
    	return $dataSet;
    }
}
