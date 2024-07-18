<?php 
namespace App\Http\Controllers;

use App\Models\CfgFieldProps;

class CommonController extends EBController {
	
	public function getField($tablename){		
		
		$cfgFieldProps = CfgFieldProps::where('TABLE_NAME', '=', [$tablename])
		->where('USE_FDC', '=', '1')
		->orderBy('FIELD_ORDER', 'asc')
		->select('COLUMN_NAME', 'LABEL' ,'FDC_WIDTH')
		->get();
		
		$column = "";
		$label = "";
		$totalWidth = 0;
		
		foreach ($cfgFieldProps as $str){
			$width = $str->FDC_WIDTH;
			$column.= ",".$str->COLUMN_NAME;
			$label.= ",".($str->LABEL?$str->LABEL:$str->COLUMN_NAME);
			
			if($width > 0){
				$width = $width + 18;
			}else{
				$width = $width + 118;
			}
			
			$totalWidth = $totalWidth + $width;
		}
		
		$column = substr($column, 1, strlen($column));
		$listColumn = explode(',',$column);
		
		$label = substr($label, 1, strlen($label));
		$listLabel = explode(',',$label);

		$obj = array(
			[
				'listColumn' => $listColumn,
				'listLabel' => $cfgFieldProps,
				'totalWidth' => $totalWidth
			]
		);
		
		return $obj;
	}
	
	public function myencrypt($x)
	{
		$cost = 10;
		// Create a random salt
		$salt = strtr(base64_encode(random_bytes(16)), '+', '.');
		// Prefix information about the hash so PHP knows how to verify it later.
		// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
		$salt = sprintf("$2a$%02d$", $cost) . $salt;
		// Hash the password with the salt
		$hash = crypt($x, $salt);
		return $hash;
	}
}
