<?php

namespace App\Models;
use App\Models\DynamicModel;

class UomModel extends DynamicModel
{
	public function CodePressUom()
	{
		return $this->hasMany('App\Models\CodePressUom', 'UOM_TYPE', 'UOM_TYPE');
	}
	
	public function CodeTempUom()
	{
		return $this->hasMany('App\Models\CodeTempUom', 'UOM_TYPE', 'UOM_TYPE');
	}
	
	public function CodeVolUom()
	{
		return $this->hasMany('App\Models\CodeVolUom', 'UOM_TYPE', 'UOM_TYPE');
	}
	
	
	public function CodePowerUom()
	{
		return $this->hasMany('App\Models\CodePowerUom', 'UOM_TYPE', 'UOM_TYPE');
	}
	
	public function CodeEnergyUom()
	{
		return $this->hasMany('App\Models\CodeEnergyUom', 'UOM_TYPE', 'UOM_TYPE');
	}
	
	public function CodeMassUom()
	{
		return $this->hasMany('App\Models\CodeMassUom', 'UOM_TYPE', 'UOM_TYPE');
	}
	public function CodeAllocType()
	{
		return $this->hasMany('App\Models\CodeAllocType', 'UOM_TYPE', 'UOM_TYPE');
	}
}
