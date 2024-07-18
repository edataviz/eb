<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdTransportPipelineDetail extends EbBussinessModel 
{ 
	protected $table = 'PD_TRANSPORT_PIPELINE_DETAIL'; 
	protected $dates = ['BEGIN_TRANSIT_TIME',
						'END_TRANSIT_TIME'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'VOYAGE_ID', 
							'PARCEL_NO', 
							'CARGO_ID', 
							'PARCEL_ID', 
							'BEGIN_TRANSIT_TIME', 
							'END_TRANSIT_TIME', 
							'ESTIMATED_TIME_TRANSIT', 
							'PRODUCT_TYPE', 
							'QUANTITY', 
							'QUANTITY_UOM', 
							'ORIGIN_PORT', 
							'CONNECTING_CARRIER', 
							'DESTINATION_PORT', 
							'ADJUSTED_QUANTITY', 
							'FLOW_ID', 
							'TICKET_NUMBER'];
} 
