<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class ShipCargoBlmr extends EbBussinessModel 
{ 
	protected $table 		= 'SHIP_CARGO_BLMR';
	protected $dates 		= ['DATE_TIME'];
	protected $fillable  	= [	'CARRIER_ID', 
								'VOYAGE_ID', 
								'PORT_ID', 
								'CARGO_ID', 
								'PARCEL_NO', 
								'LIFTING_ACCOUNT', 
								'PRODUCT_TYPE', 
								'MEASURED_ITEM', 
								'ITEM_VALUE', 
								'ITEM_UOM', 
								'DATE_TIME', 
								'COMMENT', 
								'PARENT_ID', 
								'PARENT_TYPE'];
} 
