<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 
use App\Models\PdVoyage;

 class PdVoyageDetail extends EbBussinessModel 
{ 
	protected $table 		= 'PD_VOYAGE_DETAIL';
	protected $dates 		= ['LOAD_DATE'];
	protected $fillable  	= ['VOYAGE_ID', 
								'CARGO_ID', 
								'PARCEL_NO', 
								'LIFTING_ACCOUNT', 
								'STORAGE_ID', 
								'QUANTITY_TYPE', 
								'LOAD_DATE', 
								'LOAD_QTY', 
								'LOAD_UOM', 
								'BERTH_ID'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData) {
		foreach ( $newData as $column => $value ) {
			if (!$value) {
				unset($newData[$column]);
			}
		}
		return ['ID'				=> $newData['ID']];
	}
	
	public static function findManyWithConfig($updatedIds){
		$pdVoyage						= PdVoyage::getTableName();
		$pdVoyageDetail					= static::getTableName();
		$dataSet = static ::join($pdVoyage,
								"$pdVoyageDetail.VOYAGE_ID",
								'=',
								"$pdVoyage.ID")
							->whereIn("$pdVoyageDetail.ID",$updatedIds)
							->select(
									"$pdVoyageDetail.*",
									"$pdVoyage.SCHEDULE_UOM as VOYAGE_SCHEDULE_UOM",
									"$pdVoyageDetail.ID as DT_RowId",
									"$pdVoyageDetail.ID as $pdVoyageDetail"
									)
							->get();
		return $dataSet;
	}
	
} 
