<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 
use App\Trail\RelationDynamicModel;

class MisMeasurement extends EbBussinessModel { 
	
	use RelationDynamicModel;
	
	protected $table = 'MIS_MEASUREMENT';
	protected $primaryKey = 'ID';
// 	protected $dates = ['END_TIME','BEGIN_TIME'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'FACILITY_ID', 
							'BEGIN_TIME', 
							'END_TIME', 
							'DURATION', 
							'OBJECT_TYPE', 
							'OBJECT_ID', 
							'MMR_CLASS', 
							'MMR_REASON', 
							'MMR_ROOT_CAUSE', 
							'MMR_ACCUM_QTY_TODATE', 
							'MMR_CORRECT_TODATE', 
							'MMR_ACCUM_DIFF', 
							'MMR_ACCUM_QTY_TOTAL', 
							'MMR_CORRECT_TOTAL', 
							'MMR_TOTAL_DIFF', 
							'MMR_QTY_UOM', 
							'MMR_CALC_METHOD_FORMULA', 
							'MMR_ORIGINATED_ID', 
							'MMR_STATUS', 
							'MMR_APPROVED_ID', 
							'MMR_COMMENT', 
							'STATUS_BY', 
							'STATUS_DATE', 
							'RECORD_STATUS'];
	
	public  static  $idField = 'ID';
// 	public  static  $unguarded = true;
	public static function getSourceModel(){
		return "IntObjectType";
	}
	
	public function afterSaving($postData) {
		//Tinh toan lai cac gia tri THEOR, GAS
		$shouldSave = false;
		$hours =  $this->DURATION;
		$rat=$hours/24;
		if ($this->wasRecentlyCreated) {
			$this->FACILITY_ID = $postData['Facility'];
			$shouldSave = true;
		}
		if ($shouldSave) $this->save();
	}
} 
