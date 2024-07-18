<?php

namespace App\Models;
use App\Models\ExtensionPhaseType;
use App\Trail\ObjectNameLoad;

class PreosPhaseType extends ExtensionPhaseType
{
	use ObjectNameLoad;
	protected static $codes = ['OIL','GAS','COND','CC'];
	
}
