<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdTransportShipDetail extends EbBussinessModel 
{ 
	protected $table = 'PD_TRANSPORT_SHIP_DETAIL';
	protected $dates = ['ARRIVAL_TIME',
						'DEPARTURE_TIME',
						'UNLOAD_TIME',
						'BUNKERING_TIME'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'VOYAGE_ID', 
							'CARGO_ID', 
							'PARCEL_NO', 
							'PARCEL_ID', 
							'ARRIVAL_TIME', 
							'DEPARTURE_TIME', 
							'RECEIPT_QTY', 
							'ADJUSTED_QUANTITY', 
							'QTY_UOM', 
							'QTY_TYPE', 
							'DEPART_PORT', 
							'NEXT_DESTINATION_PORT', 
							'UNLOAD_TIME', 
							'BUNKERING_TIME', 
							'UNLOAD_PORT_SAMPLE_ID1', 
							'UNLOAD_PORT_SAMPLE_ID2'];
} 
