<?php 
namespace App\Models; 
use App\Models\KeystoreTank;

class KeystoreTankDataValue extends FeatureKeystore 
{ 
	protected $table = 'KEYSTORE_TANK_DATA_VALUE'; 
	public static $objectModelName = "KeystoreTank";
	public static $foreignKeystore = "KEYSTORE_TANK_ID";
	public static $idField = "KEYSTORE_TANK_ID";
	protected $dates = ['OCCUR_DATE'];
	public  static  $relationStatusField = 'KEYSTORE_TANK_ID';
	
	/* protected $fillable  = [
			"KEYSTORE_TANK_ID"
		      ,"OCCUR_DATE"
		      ,"BEGIN_LEVEL"
		      ,"END_LEVEL"
		      ,"BEGIN_VOL"
		      ,"END_VOL"
		      ,"FILLED_VOL"
		      ,"INJECTED_VOL"
		      ,"CONSUMED_VOL"
		      ,"CONCENTRATION"
		      ,"REMAIN"
		      ,"TARGET"
		      ,"NUMBER_1"
		      ,"NUMBER_2"
		      ,"TEXT_1"
		      ,"TEXT_2"
		      ,"COMMENTS"
		      ,"NUMBER_3"
		      ,"NUMBER_4"
		      ,"NUMBER_5"
		      ,"STATUS_BY"
		      ,"STATUS_DATE"
		      ,"RECORD_STATUS" ]; */
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if (!array_key_exists("OCCUR_DATE",$newData)|| !$newData["OCCUR_DATE"]||$newData["OCCUR_DATE"]==''){
			$newData["OCCUR_DATE"] 		= $occur_date;
		}
		return ["KEYSTORE_TANK_ID" 		=> $newData["KEYSTORE_TANK_ID"],
				"OCCUR_DATE" 			=> $newData["OCCUR_DATE"],
		];
	}
	
	public static function findManyWithConfig($updatedIds){
		
		$keystoreTankDataValue			= KeystoreTankDataValue::getTableName();
		$keystoreTank					= KeystoreTank::getTableName();
		$codeProductType 				= CodeProductType::getTableName();
		
		$dataSet 						= KeystoreTankDataValue::/* join($codeProductType,"$keystoreTank.PRODUCT",'=',"$codeProductType.ID")
											-> */join($keystoreTank,
													"$keystoreTankDataValue.KEYSTORE_TANK_ID",
													'=',
													"$keystoreTank.ID")
										->whereIn("$keystoreTankDataValue.ID",$updatedIds)
										->select(
											"$keystoreTankDataValue.*",
											"$keystoreTankDataValue.ID as DT_RowId",
											"$keystoreTank.NAME as $keystoreTankDataValue",
											"$keystoreTank.PRODUCT as FL_FLOW_PHASE"
// 											"$codeProductType.NAME as PHASE_NAME"
										)
										->orderBy("$keystoreTank.PRODUCT")
										->get();
		return $dataSet;
	}
	
	public function getKeystoreCondition(){
		return ["KEYSTORE_TANK_ID"	=> $this->KEYSTORE_TANK_ID];
	}
	
	public function getKeystoreSelection(){
		return ["END_VOL as BEGIN_VOL", "END_LEVEL as BEGIN_LEVEL"];
	}
	
	public function getKeystoreStorageId(){
		//$keyStoreTank = $this->belongsTo('App\Models\KeystoreTank', 'KEYSTORE_TANK_ID', 'ID')->first();
		$keyStoreTank = KeystoreTank::where('ID', '=', $this->KEYSTORE_TANK_ID)
					    	->select("STORAGE_ID") 
	  		    			->first();
		return $keyStoreTank!=null?$keyStoreTank->STORAGE_ID:null;
	}
	
	public function afterSaving($postData) { 
		if(config('constants.systemName') == "Santos"){
			$occur_date = $this->OCCUR_DATE;
			$first_day = date("Y-m-01", strtotime($occur_date));
			$v = KeystoreTankDataValue::where('KEYSTORE_TANK_ID','=',$this->KEYSTORE_TANK_ID)
				->whereDate('OCCUR_DATE','>=',$first_day)
				->whereDate('OCCUR_DATE','<=',$occur_date)
				->select(\DB::raw("SUM(CONSUMED_VOL) AS total"))
				->first()->total;
			$this->NUMBER_1	= $v;
			$this->save();
			//\Log::info("MTD=".$v);
		}
		return $this;
	}
}