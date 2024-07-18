<?php

namespace App\Http\Controllers;
use App\Models\CodeInjectPoint;
use App\Models\CodeProductType;
use App\Models\CodeVolUom;
use App\Models\Keystore;
use App\Models\KeystoreInjectionPoint;
use App\Models\KeystoreInjPointChemical;
use App\Models\KeystoreInjectionPointDay;
use App\Models\KeystoreStorage;
use App\Models\KeystoreStorageDataValue;
use App\Models\KeystoreTank;
use App\Models\KeystoreTankDataValue;
use App\Models\Facility;
use Carbon\Carbon;

class ChemicalController extends CodeController {
    
	public function __construct() {
		parent::__construct();
	}
	
	public function enableBatchRun($dataSet,$mdlName,$postData){
		return true;
	}

	protected function enableReformular($mdlName,$records) {
		return true;
	}
	
	public function getObjectIds($dataSet,$postData,$properties){
		$dcTable 		= $this->getWorkingTable($postData);
		switch ($dcTable) {
			case KeystoreInjectionPointDay::getTableName():
				$objectIds = $dataSet->map(function ($item, $key) {
					$odate = $item->OCCUR_DATE;
					$odate	= $odate instanceof Carbon ?$odate->toDateString():$odate;
					return ["DT_RowId"			=> $item->DT_RowId,
							"INJECTION_POINT_ID"=> $item->INJECTION_POINT_ID,
							"KEYSTORE_ID"		=> $item->KEYSTORE_ID,
							"OCCUR_DATE"		=> $odate,
					];
				});
				break;
			case KeystoreTankDataValue::getTableName():
				$objectIds = $dataSet->map(function ($item, $key) {
					$odate = $item->OCCUR_DATE;
					$odate	= $odate instanceof Carbon ?$odate->toDateString():$odate;
					return ["DT_RowId"			=> $item->DT_RowId,
							"KEYSTORE_TANK_ID"	=> $item->KEYSTORE_TANK_ID,
							"OCCUR_DATE"		=> $odate,
					];
				});
				break;
			case KeystoreStorageDataValue::getTableName():
				$objectIds = $dataSet->map(function ($item, $key) {
					$odate = $item->OCCUR_DATE;
					$odate	= $odate instanceof Carbon ?$odate->toDateString():$odate;
					return ["DT_RowId"				=> $item->DT_RowId,
							"KEYSTORE_STORAGE_ID"	=> $item->KEYSTORE_STORAGE_ID,
							"OCCUR_DATE"			=> $odate,
					];
				});
				break;
			default:
				$objectIds	= [];
				break;
		}
		
		return $objectIds;
	}
	
	public function getFirstProperty($dcTable){
		if ($dcTable==KeystoreInjectionPointDay::getTableName())
			return null;
		return  ['data'=>$dcTable,'title'=>'Keystore Tank','width'=>230];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$object_type 					= array_key_exists('CodeInjectPoint', $postData)?$postData['CodeInjectPoint']:1;
    	$product_type 					= 0;
    	$keystoreStorage 				= KeystoreStorage::getTableName();
    	$codeProductType 				= CodeProductType::getTableName();
    	$keystoreInjectionPointDay		= KeystoreInjectionPointDay::getTableName();
    	$columns						= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns 		= [];
    	
    	if ($dcTable==$keystoreInjectionPointDay) {
    		$keystoreInjectionPointChemical	= KeystoreInjPointChemical::getTableName();
    		$objectTypeName				= CodeInjectPoint::find($object_type)->CODE;
    		$objectTypeModel			= \Helper::getModelName ( $objectTypeName, '_' );
    		$objectTypeTable			= $objectTypeModel::getTableName();
    		$keystoreInjectionPoint 	= KeystoreInjectionPoint::getTableName();
    		$codeVolUom					= CodeVolUom::getTableName();
    		$keystore					= Keystore::getTableName();
    		array_push($columns,"$keystoreInjectionPointDay.ID as DT_RowId",
				    			"$keystoreInjectionPointDay.OCCUR_DATE",
	    						"$keystoreInjectionPointChemical.ID as $dcTable",
				    			"$keystoreInjectionPointChemical.INJECTION_POINT_ID",
				    			"$keystoreInjectionPointChemical.KEYSTORE_ID",
				    			"$keystoreInjectionPointChemical.MIN_QTY_DAY",
				    			"$keystoreInjectionPointChemical.MAX_QTY_DAY",
				    			"$keystoreInjectionPointChemical.RECOMMEND_QTY_DAY",
				    			"$keystoreInjectionPointChemical.QTY_UOM as VOL_UOM",
		    					"$objectTypeTable.NAME as OBJECT_NAME",
				    			"$keystoreInjectionPoint.OBJECT_ID",
				    			"$keystore.NAME as KEYSTORE_NAME",
				    			"$keystoreInjectionPointDay.INJECTED_VOL");
    		
    		$dataSet	= KeystoreInjPointChemical::join($keystoreInjectionPoint, function($join) use ($keystoreInjectionPoint,$object_type,$keystoreInjectionPointChemical){
									    		$join->on("$keystoreInjectionPoint.ID", '=', "$keystoreInjectionPointChemical.INJECTION_POINT_ID");
									    		$join->where("$keystoreInjectionPoint.OBJECT_TYPE",'=',$object_type);
    										})
    										->join($objectTypeTable, function($join) use ($objectTypeTable,$facility_id,$keystoreInjectionPoint){
									    		$join->on("$keystoreInjectionPoint.OBJECT_ID", '=', "$objectTypeTable.ID");
									    		$fname = Facility::getTableName();
									    		if ($objectTypeTable==$fname) 
									    			$join->where("$objectTypeTable.ID",'=',$facility_id);
									    		else 
									    			$join->where("$objectTypeTable.FACILITY_ID",'=',$facility_id);
    										})
						    				->join($keystore,"$keystoreInjectionPointChemical.KEYSTORE_ID","=","$keystore.ID")
						    				->leftJoin($codeVolUom,"$keystoreInjectionPointChemical.QTY_UOM","=","$codeVolUom.ID")
						    				->leftJoin($keystoreInjectionPointDay, function($join) use ($keystoreInjectionPointDay,$keystoreInjectionPointChemical,$occur_date){
						    					$join->on("$keystoreInjectionPointChemical.INJECTION_POINT_ID", '=', "$keystoreInjectionPointDay.INJECTION_POINT_ID");
						    					$join->on("$keystoreInjectionPointChemical.KEYSTORE_ID", '=', "$keystoreInjectionPointDay.KEYSTORE_ID");
						    					$join->where("$keystoreInjectionPointDay.OCCUR_DATE",'=',$occur_date);
						    				})
						    				->select($columns)
								    		->orderBy("OBJECT_NAME")
								    		->get();
    	}
    	else{
	    	switch ($dcTable) {
	    		case KeystoreTankDataValue::getTableName():
	    			$mainModel		= "\App\Models\KeystoreTank";
	    			$joinByColumn	= "KEYSTORE_TANK_ID";
	    		break;
	    		case KeystoreStorageDataValue::getTableName():
	    			$mainModel		= "\App\Models\KeystoreStorage";
	    			$joinByColumn	= "KEYSTORE_STORAGE_ID";
    			break;
	    		default:
	    		break;
	    	}
	    	if ($mainModel&&$joinByColumn) {
	    		$keystoreStorage	= $mainModel::getTableName();
	    		$where = [
	    				"$keystoreStorage.FACILITY_ID"=>$facility_id,
	    				"$keystoreStorage.FDC_DISPLAY"=>1,
	    		];
	    		if ($product_type>0) $where["$keystoreStorage.PRODUCT"] = $product_type;
	    		array_push($columns,"$keystoreStorage.ID as $joinByColumn",
	    							"$keystoreStorage.ID as ID",
					    			"$dcTable.ID as DT_RowId",
					    			"$keystoreStorage.NAME as $dcTable",
					    			"$keystoreStorage.PRODUCT as FL_FLOW_PHASE",
					    			"$codeProductType.NAME as PHASE_NAME");
	    		
	    		$query 	= $this->buildQuery($mainModel,$occur_date,$facility_id,$postData);
	    		$dataSet = $query->join($codeProductType,"$keystoreStorage.PRODUCT",'=',"$codeProductType.ID")
						    	->where($where)
						    	->whereDate("$keystoreStorage.START_DATE",'<=',$occur_date)
						    	->leftJoin($dcTable, function($join) use ($keystoreStorage,$dcTable,$occur_date,$joinByColumn){
						    		$join->on("$keystoreStorage.ID", '=', "$dcTable.$joinByColumn");
						    		$join->where("$dcTable.OCCUR_DATE",'=',$occur_date);
						    	})
						    	->select($columns)
		 		    			->orderBy("$dcTable")
		 		    			->orderBy("$keystoreStorage.PRODUCT")
		 		    			->get();
	    	}
    	}
    	//      	\DB::enableQueryLog();
    	//  		\Log::info(\DB::getQueryLog());
    	
    	return ['dataSet'=>$dataSet];
    }
    protected function afterSave($resultRecords,$occur_date) {
		//Update keystorage value
//     	\DB::enableQueryLog();
    	$tankDataValue = KeystoreTankDataValue::getTableName();
    	$keyStoreTank = KeystoreTank::getTableName();
    	$columns = [ \DB::raw("sum(BEGIN_VOL) 	as	BEGIN_VOL"),
	    			\DB::raw("sum(END_VOL) 			as	END_VOL"),
	    			\DB::raw("sum(FILLED_VOL) 		as	FILLED_VOL"),
	    			\DB::raw("sum(INJECTED_VOL) 		as	INJECTED_VOL"),
	    			\DB::raw("sum(CONSUMED_VOL) 		as	CONSUMED_VOL"),
					];
    	$attributes = ['OCCUR_DATE'=>$occur_date];
    	$storage_ids = [];
    	$originAttrCase = \Helper::setGetterUpperCase();
    	foreach($resultRecords as $mdlName => $records ){
    		foreach($records as $mdlRecord ){
    			if (method_exists($mdlRecord,"updateBeginValues")) {
    				$mdlRecord->updateBeginValues();
    			}
	    		$storageID = $mdlRecord->getKeystoreStorageId();
	    		if ($storageID) $storage_ids[] = $storageID;
    		}
    	}
    	$storage_ids = array_unique($storage_ids);
    	 
    	foreach($storage_ids as $storage_id){
	    	$values = KeystoreTankDataValue::join($keyStoreTank,function ($query) use ($tankDataValue,$keyStoreTank,$storage_id) {
						    					$query->on("$keyStoreTank.ID",'=',"$tankDataValue.KEYSTORE_TANK_ID")
								    					->where("$keyStoreTank.STORAGE_ID",'=',$storage_id);
							})
					    	->whereDate('OCCUR_DATE', '=', $occur_date)
					    	->select($columns) 
	  		    			->first();
			
	  		$attributes['KEYSTORE_STORAGE_ID'] = $storage_id;
	  		$values = $values->toArray();
	  		$values['KEYSTORE_STORAGE_ID'] = $storage_id;
	  		$values['OCCUR_DATE'] = $occur_date;
		//\Log::info($attributes);
		//\Log::info($values);
	  		KeystoreStorageDataValue::updateOrCreate($attributes,$values);
    	}
    	\Helper::setGetterCase($originAttrCase);
//     	\Log::info(\DB::getQueryLog());
    }
}
