<?php 
namespace App\Models; 

 class FeatureKeystore extends EbBussinessModel { 
	protected $disableUpdateAudit = false;
	protected static $objectModelName 	= null;
	public static $foreignKeystore 		= null;
	protected $dates 					= ['OCCUR_DATE'];
	
	public static function getEntries($facility_id=null,$product_type = 0){
		$oModel = static::$objectModelName;
		if ($oModel) {
			$oModel 	= 'App\Models\\' . $oModel;
			$wheres 	= [];
			if ($facility_id)	$wheres ['FACILITY_ID']	= $facility_id;
			if ($product_type>0)$wheres ['PRODUCT']		= $product_type;
			$entries = $oModel::where($wheres)->select('ID','NAME')->orderBy('NAME')->get();
			return $entries;
		}
		return null;
	}
	
	public function getKeystoreStorageId(){
		return null;
	}
	
	public function getKeystoreCondition(){
		return [];
	}
	
	public function getKeystoreSelection(){
		return ["END_VOL as BEGIN_VOL"];
	}
	
	public function updateBeginValues(){
		$values = $this->where($this->getKeystoreCondition())
						->whereDate('OCCUR_DATE', '<', $this->OCCUR_DATE)
						->orderBy('OCCUR_DATE','desc')
						->get($this->getKeystoreSelection())
						->first();
		if($values){
			$values = $values->toArray();
			$this->update($values);
		}
	}

	public static function getObjects() {
		$oModel = static::$objectModelName;
		if ($oModel) {
			$oModel 	= 'App\Models\\' . $oModel;
			return $oModel::where("ID",">",0)->orderBy("NAME")->get();
		}
		return null;
	}
} 
