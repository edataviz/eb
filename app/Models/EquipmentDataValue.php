<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class EquipmentDataValue extends EbBussinessModel 
{ 
	protected $disableUpdateAudit = false;
	protected $table 	= 'EQUIPMENT_DATA_VALUE';
	protected $primaryKey 	= 'ID';
	protected $dates 		= ['OCCUR_DATE'];

	public  static  $idField = 'EQUIPMENT_ID';
	public  static  $typeName = 'EQUIPMENT';
	public  static  $dateField = 'OCCUR_DATE';
	
	protected $fillable  	= ['EQUIPMENT_ID', 
								'OCCUR_DATE', 
								'TEMP', 
								'PRESS', 
								'EQP_ONLINE_HOURS', 
								'EQP_OFFLINE_HOURS', 
								'OFFLINE_REASON_CODE', 
								'EQP_NOTE', 
								'EQP_FUEL_CONS_TYPE', 
								'EQP_CONS_QTY', 
								'EQP_CONS_UOM', 
								'EQP_GHG_REL_TYPE', 
								'EQP_GHG_CONS_QTY', 
								'EQP_GHG_UOM', 
								'EQP_FUEL_CONS_RATE_DAY', 
								'BEGIN_READING_VALUE', 
								'END_READING_VALUE'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		$newData['EQUIPMENT_ID'] = $newData['EQUIPMENT_ID'];
		if ($occur_date&&(!array_key_exists ( 'OCCUR_DATE', $newData )||!$newData['OCCUR_DATE']))
		    $newData['OCCUR_DATE'] = $occur_date;
		else
            $occur_date = $newData['OCCUR_DATE'];
		
		if ( array_key_exists ( 'EQP_FUEL_CONS_TYPE', $newData )&& !$newData['EQP_FUEL_CONS_TYPE']) {
			unset($newData['EQP_FUEL_CONS_TYPE']);
		}
		if ( array_key_exists ( 'EQP_GHG_REL_TYPE', $newData )&& !$newData['EQP_GHG_REL_TYPE']) {
			unset($newData['EQP_GHG_REL_TYPE']);
		}
		return [
				'EQUIPMENT_ID' => $newData['EQUIPMENT_ID'],
				'OCCUR_DATE' => $occur_date
		];
	}
	
	public function updateDependRecords($occur_date,$values,$postData) {
		$object_id = $this->EQUIPMENT_ID;
		if ($this->wasRecentlyCreated) {
// 			$v=getOneValue("select END_READING_VALUE from equipment_data_value where occur_date<'$sql_occur_date' and EQUIPMENT_ID=$object_id order by occur_date desc limit 1");
			$previousRecord = static :: whereDate('OCCUR_DATE','<',$occur_date)
										->where('EQUIPMENT_ID','=',$object_id)
										->select('END_READING_VALUE')
										->orderBy('OCCUR_DATE','desc')
										->first();
			if ($previousRecord!=null&&$previousRecord->END_READING_VALUE) {
				$this->BEGIN_READING_VALUE = $previousRecord->END_READING_VALUE;
				$this->save();
			}
		}
		else{
			$nextRecord = static :: whereDate('OCCUR_DATE','>',$occur_date)
									->where('EQUIPMENT_ID','=',$object_id)
// 									->select('END_READING_VALUE')
									->orderBy('OCCUR_DATE','asc')
									->first();
			if ($nextRecord!=null&&$this->END_READING_VALUE) {
				$nextRecord->BEGIN_READING_VALUE = $this->END_READING_VALUE;
				$nextRecord->save();
				return $nextRecord;
			}
		}
		return null;
	}
	
	public static function getObjects() {
		return Equipment::all();
	}
} 
