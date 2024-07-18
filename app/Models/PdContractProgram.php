<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContractProgram extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT_PROGRAM'; 
	protected $dates = ['START_DATE','END_DATE'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'START_DATE', 
							'END_DATE', 
							'CONTRACT_ID', 
							'PROGRAM_TYPE', 
							'RUN_FREQUENCY'];
} 
