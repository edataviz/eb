<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdCargo extends EbBussinessModel 
{ 
	protected $table = 'PD_CARGO';
	protected $dates = ['REQUEST_DATE'];
	protected $fillable  = ['CODE', 
							'NAME', 
							'OTHER_PARTY_CODE', 
							'LIFTING_ACCT', 
							'STORAGE_ID', 
							'REQUEST_DATE', 
							'REQUEST_QTY', 
							'REQUEST_UOM', 
							'PRIORITY', 
							'QUANTITY_TYPE', 
							'CONTRACT_ID'];
} 
