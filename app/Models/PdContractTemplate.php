<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContractTemplate extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT_TEMPLATE';
	
	protected $fillable  = ['CODE', 
							'NAME', 
							'EFFECTIVE_DATE', 
							'END_DATE', 
							'CONTACT_TYPE'];
	
} 
