<?php

namespace App\Models;
use App\Models\DynamicModel;

class DateTimeFormat extends DynamicModel
{
	public static $sample = array(
									'DATE_FORMAT'	=>[	'DD/MM/YYYY'	=>	'23/11/2016',
														'MM/DD/YYYY'	=>	'11/23/2016',
														'YYYY/MM/DD'	=>	'2016/11/23'],
			
									'TIME_FORMAT'	=>[	'HH:mm'			=>	'20:30',
														'hh:mm A'		=>	'08:30 PM'],
									'DECIMAL_MARK'	=>[	'dot'			=>	'1,245.38',
														'comma'			=>	'1.245,38'],
			);
	
	public static $defaultFormat = ['DATE_FORMAT'	=>'DD/MM/YYYY',
									'TIME_FORMAT'	=>'HH:mm',
									'DECIMAL_MARK'	=>'dot'
	];
	
	public static $timeFortmatPair =	['hh:mm A'	=>	'HH:ii P',
										'HH:mm'		=>	'hh:ii',
										];
	
	
	public static function getSample($formats){
		$sample = [];
		$samples = static ::$sample;
		foreach($formats as $key => $format ){
			$sample[$key]	= $samples[$key][$format];
		}
		return $sample;
	}
	
	public function getFormat($type){
		$samples = static ::$sample;
		$data = [];
		
		foreach($samples[$type] as $format => $text ){
			$data[]	= (object)['value' =>$format	,'text' => $text    , 'name' => $type 	];
		}
		return  collect($data);
	}
}
