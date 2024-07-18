<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 
use App\Exceptions\DataInputException;

 class PdCargoNomination extends EbBussinessModel 
{ 
	public static $dateField = 'REQUEST_DATE';
	protected $table = 'PD_CARGO_NOMINATION'; 
	protected $dates = ['REQUEST_DATE'];
	protected $fillable  = ['NAME', 
							'CARGO_ID', 
							'IS_IMPORT', 
							'INCOTERM', 
							'REQUEST_DATE', 
							'REQUEST_QTY', 
							'REQUEST_QTY_UOM', 
							'ADJUSTABLE_TIME', 
							'REQUEST_TOLERANCE', 
							'PRIORITY', 
							'NOMINATION_DATE', 
							'NOMINATION_QTY', 
							'NOMINATION_UOM', 
							'NOMINATION_ADJ_TIME', 
							'TRANSIT_TYPE', 
							'PD_TRANSIT_CARRIER_ID', 
							'CARGO_STATUS', 
							'LNG_PURGE_QTY', 
							'LNG_GASUP_QTY', 
							'LNG_COOLDOWN_QTY', 
							'LAST_DEST_PORT', 
							'TOTAL_LADEN_TIME', 
							'EXCESS_LADEN_TIME', 
							'TOTAL_BALLAST_TIME', 
							'EXCESS_BALLAST_TIME', 
							'LNG_PRELOAD_TIME', 
							'TOTAL_LOAD_UNLOAD_TIME', 
							'BUNKERING_TIME', 
							'ROUND_TRIP_TIME', 
							'BL_DATE', 
							'BL_QTY', 
							'DEMURRAGE_CHARGE', 
							'PARCEL_QTY'];
	
	public function checkAndSave(&$values) {
		$cargo_id 			= $values['CARGO_ID'];
		$pdCargo			= PdCargo::getTableName();
		$pdCargoNomination 	= PdCargoNomination::getTableName();
		$row				= static::join($pdCargo,"$pdCargoNomination.CARGO_ID",'=',"$pdCargo.ID")
									->where("$pdCargoNomination.CARGO_ID",'=',$cargo_id)
									->select("$pdCargo.NAME")
									->first();
		
		 
		if ($row==null) {
			$this->fill($values)->save();
			return $this;
		}
		throw new DataInputException ( "Cargo $row->NAME nominated already");
	}
} 
