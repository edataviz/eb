<?php 
namespace App\Models; 
 

 class KeystoreInjectionPointDay extends FeatureKeystore 
{ 
	protected $table 				= 'KEYSTORE_INJECTION_POINT_DAY';
	public static $foreignKeystore 	= "INJECTION_POINT_ID";
	protected $dates 				= ['OCCUR_DATE'];
	protected $disableUpdateAudit	= true;
	protected static $objectModelName = "KeystoreInjectionPoint";// 'INJECTION_POINT_ID'

	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (!array_key_exists("OCCUR_DATE",$newData)|| !$newData["OCCUR_DATE"]||$newData["OCCUR_DATE"]==''){
			$newData["OCCUR_DATE"] 		= $occur_date;
		}
		return ["INJECTION_POINT_ID" 	=> $newData["INJECTION_POINT_ID"],
				"KEYSTORE_ID" 			=> $newData["KEYSTORE_ID"],
				"OCCUR_DATE" 			=> $occur_date,
		];
	}
	
	public static function getEntries($facility_id=null,$product_type = 0){
		return KeystoreInjectionPoint::select('ID','NAME')->get();
	}
	
	
	public function updateBeginValues(){}
	
	
	public static function buildQueryBy($facilityIds,$columns=null,$dateFrom=null,$dateTo=null,$values=null){
		$objectTypeTables				= CodeInjectPoint::loadActive();
		$queries						= null;
		$objectTypeTables->each(function ($item, $key) use ($facilityIds,&$queries,$columns,$values,$dateFrom,$dateTo){
			$objectTypeTable			= $item->CODE;
			$q							= static::buildQueryByCode($facilityIds,$objectTypeTable,$columns,$dateFrom,$dateTo,$values);
			$queries 					= $queries&&!$values?$queries->unionAll($q):$q;
		});
		return $queries; 
	}
	
	public static function buildQueryByCode($facilityIds,$objectTypeTable,$columns=null,$dateFrom=null,$dateTo=null,$values=null){
		$keystore						= Keystore::getTableName();
		$keystoreInjectionPointDay		= KeystoreInjectionPointDay::getTableName();
		$keystoreInjectionPoint 		= KeystoreInjectionPoint::getTableName();
		$query 							= KeystoreInjectionPointDay::join($keystore,"$keystoreInjectionPointDay.KEYSTORE_ID","=","$keystore.ID")
											->join($keystoreInjectionPoint, "$keystoreInjectionPoint.ID", '=', "$keystoreInjectionPointDay.INJECTION_POINT_ID")
											->join($objectTypeTable, function($join) use ($objectTypeTable,$facilityIds,$keystoreInjectionPoint){
												$join->on("$keystoreInjectionPoint.OBJECT_ID", '=', "$objectTypeTable.ID");
												$fname = Facility::getTableName();
												if ($objectTypeTable==$fname){
													if (is_array($facilityIds)) $join->whereIn("$objectTypeTable.ID",$facilityIds);
													else $join->where("$objectTypeTable.ID","=",$facilityIds);
												}
												else{
													if (is_array($facilityIds)) $join->whereIn("$objectTypeTable.FACILITY_ID",$facilityIds);
													else $join->where("$objectTypeTable.FACILITY_ID","=",$facilityIds);
												}
											});
		if ($dateFrom) $query->whereDate("$keystoreInjectionPointDay.OCCUR_DATE" ,">=", $dateFrom);
		if ($dateTo) $query->whereDate("$keystoreInjectionPointDay.OCCUR_DATE" ,"<=", $dateTo);
		if ($columns) $query->select($columns);
		if ($values) {
// 			\DB::enableQueryLog();
			$query->chunk(100, function($rows) use ($values){
							$rows->each(function ($item, $key) use ($values){
								$item->fill($values)->save();
							});
	    				}); 
// 			$query->update($values);
// 			\Log::info(\DB::getQueryLog());
		}
		return $query;
	}
} 
