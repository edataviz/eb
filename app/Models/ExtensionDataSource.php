<?php

namespace App\Models;
use App\Models\DynamicModel;

class ExtensionDataSource extends DynamicModel
{
	public static function all($columns = array())
	{
		return  collect([
							(object)['ID' =>	'VALUE'	,'CODE' =>	'VALUE'	,'NAME' => 'Standard'      	],
							(object)['ID' =>	'THEOR'	,'CODE' =>	'THEOR'	,'NAME' => 'Theoretical'    ],
							(object)['ID' =>	'ALLOC'	,'CODE' =>	'ALLOC'	,'NAME' => 'Allocation'    ],
						]);
	}
	
	public static function getPreosObjectType(){
		return  collect([
							(object)['ID' =>	'FDC_VALUE'	,'CODE' =>	'FDC_VALUE'	,'NAME' => 'FDC'      	],
						]);
	}
}
