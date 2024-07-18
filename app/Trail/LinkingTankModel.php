<?php

namespace App\Trail;


trait LinkingTankModel{
	
public function getStorageId(){
		$tank = $this->Tank()->first();
		return $tank!=null?$tank->STORAGE_ID:null;
	}
}
