<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdVoyage extends EbBussinessModel 
{ 
	protected $table 		= 'PD_VOYAGE';
	protected $dates 		= ['SCHEDULE_DATE'];
	protected $fillable  	= ['CODE', 
								'NAME', 
								'MASTER_NAME', 
								'CARRIER_ID', 
								'CARGO_ID', 
								'LIFTING_ACCOUNT', 
								'STORAGE_ID', 
								'VOYAGE_NO', 
								'INCOTERM', 
								'SCHEDULE_DATE', 
								'ADJUSTABLE_TIME', 
								'SCHEDULE_QTY', 
								'QUANTITY_TYPE', 
								'SCHEDULE_UOM', 
								'BERTH_ID', 
								'CONSIGNER', 
								'CONSIGNEE_1', 
								'CONSIGNEE_2', 
								'LOAD_PORT_SAMPLE_ID1', 
								'LOAD_PORT_SAMPLE_ID2', 
								'NOMINATION_ID'];
	
	public static function getList($condition = [], $all = false, $none = false){
		$list = static :: select(static::$fieldVALUE.' as value', static::$fieldNAME.' as name');
		$list = $list->orderBy('name')->get()->all();
		if($none){
			array_unshift($list, ['value' => 'none', 'name' => '(None)']);
		}
		if($all){
			array_unshift($list, ['value' => 'all', 'name' => '(All)']);
		}
		return $list;
	}
} 
