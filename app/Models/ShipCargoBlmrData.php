<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class ShipCargoBlmrData extends EbBussinessModel 
{ 
	protected $table 		= 'SHIP_CARGO_BLMR_DATA'; 
	protected $dates 		= ['LAST_CALC_TIME'];
	protected $fillable  	= [	'BLMR_ID', 
								'MEASURED_ITEM', 
								'FORMULA_ID', 
								'ITEM_VALUE', 
								'ITEM_UOM', 
								'LAST_CALC_TIME', 
								'CALC_MESSAGE', 
								'COMMENT'];
	
} 
