<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdTransportGroundDetail extends EbBussinessModel 
{ 
	protected $table 		= 'PD_TRANSPORT_GROUND_DETAIL'; 
	protected $dates 		= ['BEGIN_LOADING_TIME','END_LOADING_TIME'];
	protected $fillable  	= [	'CODE', 
								'NAME', 
								'VOYAGE_ID', 
								'CARGO_ID', 
								'PARCEL_NO', 
								'PARCEL_ID', 
								'BEGIN_LOADING_TIME', 
								'END_LOADING_TIME', 
								'PRODUCT_TYPE', 
								'QUANTITY', 
								'QUANTITY_UOM', 
								'ORIGIN_PORT', 
								'DESTINATION_PORT', 
								'ADJUSTED_QUANTITY', 
								'FLOW_ID', 
								'TICKET_NUMBER'];
} 
