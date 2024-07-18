<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdContractYear extends DynamicModel 
{ 
	protected $table = 'PD_CONTRACT_YEAR'; 
	
	protected $fillable  = ['CONTRACT_ID', 
							'CALCULATION_ID', 
							'YEAR', 
							'FORMULA_VALUE'];
} 
