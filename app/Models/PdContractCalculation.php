<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class PdContractCalculation extends DynamicModel 
{ 
	protected $table = 'PD_CONTRACT_CALCULATION'; 
	
	protected $fillable  = ['FORMULA_ID', 
							'CONTRACT_ID', 
							'COMMENTS'];
 }
	
