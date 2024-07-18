<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdTransitCarrierVefData extends EbBussinessModel 
{ 
	public static $dateField = 'DATE_TIME';
	protected $table = 'PD_TRANSIT_CARRIER_VEF_DATA'; 
	protected $disableUpdateAudit = false;

} 
