<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdShipPortInformation extends  EbBussinessModel 
{ 
	protected $table = 'PD_SHIP_PORT_INFORMATION';
	protected $fillable  = ['CARRIER_ID', 
							'VOYAGE_ID', 
							'PORT_ID', 
							'PARCEL_NO', 
							'PILOT_NAME', 
							'ULLAGE_PORT', 
							'ARRIVAL_TUGS_NO', 
							'DEPART_TUGS_NO', 
							'NITROGEN_FILLED', 
							'FORWARD_ARRIVAL', 
							'FORWARD_DEPART', 
							'AMIDSHIP_ARRIVAL', 
							'AMIDSHIP_DEPART', 
							'AFTER_ARRIVAL', 
							'AFTER_DEPART', 
							'DRAFT_ARRIVAL', 
							'DRAFT_DEPART', 
							'HFO_ARRIVAL', 
							'HFO_RECEIVED', 
							'HFO_DEPART', 
							'DIESEL_ARRIVAL', 
							'DIESEL_RECEIVED', 
							'DIESEL_DEPART', 
							'DIESEL_CONSUMED', 
							'LIGHT_OIL_ARRIVAL', 
							'LIGHT_OIL_RECEIVED', 
							'LIGHT_OIL_DEPART', 
							'LIGHT_OIL_CONSUMED', 
							'WATER_ARRIVAL', 
							'WATER_RECEIVED', 
							'WATER_DEPART', 
							'WATER_CONSUMED'];
} 
