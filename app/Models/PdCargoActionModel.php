<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdCargoActionModel extends EbBussinessModel 
{ 
	public function Demurrage(){
		return $this->hasMany('App\Models\Demurrage', "CARGO_ID", "CARGO_ID");
	}
	public function PdContractData(){
		return $this->hasMany('App\Models\PdContractData', "ATTRIBUTE_ID", "DEMURRAGE_EBO");
	}
	
	public function TerminalTimesheetData(){
		return $this->hasMany('App\Models\TerminalTimesheetData', "PARENT_ID", "ID");
	}
	
} 
