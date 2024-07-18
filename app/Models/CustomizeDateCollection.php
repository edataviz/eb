<?php

namespace App\Models;

use App\Models\PdContract;

class CustomizeDateCollection {
	protected $date_begin;
	protected $date_end;
	public function __construct($field, $value) {
		$this->$field = $value;
	}
	public function PdContract($option = null) {
		if ($option != null && is_array ( $option )) {
			if (array_key_exists ( 'date_begin', $option )) {
				$beginBundle 	= $option ['date_begin'];
				$date_begin 	= $beginBundle ['id'];
			} else
				$date_begin 	= $this->date_begin;
			
			$date_begin 	= \Helper::parseDate($date_begin);
				
			if (array_key_exists ( 'date_end', $option )) {
				$endBundle 		= $option ['date_end'];
				$date_end 		= $endBundle ['id'];
			} else
				$date_end 		= $this->date_end;
		
			$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
				
			$sourceData = [ 
					'date_begin' 	=> $date_begin,
					'date_end' 		=> $date_end 
			];
			return PdContract::getByDateRange ( $sourceData );
		}
		return null;
	}
}
