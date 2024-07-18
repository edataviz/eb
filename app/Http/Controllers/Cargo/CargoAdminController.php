<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;

class CargoAdminController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	'TRANSIT_TYPE'	=>	[	'column'	=>'PD_TRANSIT_CARRIER_ID',
																'model'		=>'PdTransitCarrier'],
		];
	}
	
	public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde = null){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'TRANSIT_TYPE':
		    	$targetModel = $extraDataSetColumn['model'];
		    	$targetEloquent = "App\Models\\$targetModel";
		    	$data = $targetEloquent::where('TRANSIT_TYPE','=',$sourceColumnValue)
		    							->select("ID as value",
									    		"NAME as text",
		    									"ID",
		    									"NAME",
		    									"CODE"
		    									)
		    							->get();
    			break;
    	}
    	return $data;
    }
}
