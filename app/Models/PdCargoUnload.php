<?php 
namespace App\Models; 
use App\Models\PdCargoActionModel; 

 class PdCargoUnload extends PdCargoActionModel 
{ 
	protected $table 		= 'PD_CARGO_UNLOAD'; 
	protected $dates 		= ['DATE_UNLOAD'];
	protected $fillable  	= ['CARGO_ID', 
								'DATE_UNLOAD', 
								'LOAD_QTY', 
								'LOAD_UOM', 
								'TRANSIT_TYPE', 
								'PD_TRANSIT_CARRIER_ID', 
								'TIME_LAPSE', 
								'DEMURRAGE_EBO', 
								'BERTH_ID', 
								'CARGO_STATUS', 
								'NOMINATION_QTY2', 
								'NOMINATION_UOM2', 
								'NOMINATION_QTY3', 
								'NOMINATION_UOM3', 
								'SURVEYOR_BA_ID', 
								'WITNESS_BA_ID1', 
								'WITNESS_BA_ID2', 
								'COMMENT', 
								'NOMINATION_ID'];
	
} 
