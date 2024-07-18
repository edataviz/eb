<?php 
namespace App\Models; 
use App\Models\FeatureTicketModel;
use App\Models\Tank;

 class RunTicketValue extends FeatureTicketModel 
{ 
	protected $table 		= 'RUN_TICKET_VALUE';
	protected $primaryKey 	= 'ID';
// 	protected $dates 		= [/* 'OCCUR_DATE', */'REPORT_DATE'];
	protected $fillable  = [
							'OCCUR_DATE',
							'TICKET_NO',
							'TICKET_TYPE',
							'TANK_ID',
							'CARRIER_ID',
							'BEGIN_LEVEL',
							'END_LEVEL',
							'BEGIN_VOL',
							'END_VOL',
							'SW',
							'TICKET_GRS_VOL',
							'TICKET_NET_VOL',
							'TICKET_DENSITY',
							'TICKET_GRS_MASS',
							'TICKET_NET_MASS',
							'TICKET_WTR_VOL',
							'LOADING_TIME',
							'REPORT_DATE',
							'PHASE_TYPE',
							'FLOW_ID',
							'TARGET_TANK',
			
	];
	
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		if ( array_key_exists ( 'auto', $newData )) {
			$attributes = $newData;
		}
		else {
			$attributes = parent:: getKeyColumns($newData,$occur_date,$postData);
			if ( !array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'ID', $newData )) {
				return $attributes;
			}
		}
		if ( array_key_exists ( 'FLOW_PHASE', $newData )) {
			$attributes['PHASE_TYPE'] = $newData['FLOW_PHASE'];
		}
		if ( array_key_exists ( 'TANK_ID', $newData )) {
			$attributes['TANK_ID'] = $newData['TANK_ID'];
		}
		if ( !array_key_exists ( 'TICKET_NO', $newData )) $attributes['TICKET_NO'] = "NO_NAME";
// 		if (!array_key_exists ( 'LOADING_TIME', $newData )) $newData['LOADING_TIME'] = $occur_date;
		return $attributes;
	}
	
	public static function  getFdcValues($attributes){
		/* if (strpos($attributes['ID'], 'NEW_RECORD') !== false) {
			unset($attributes['ID']);
		} */
		if (!array_key_exists ( 'ID', $attributes )||strpos($attributes['ID'], 'NEW_RECORD') !== false) {
			/* if ( array_key_exists ( 'auto', $attributes )) unset($attributes['auto']);
// 			if ( array_key_exists ( 'isAdding', $attributes )) unset($attributes['isAdding']);
			if ( array_key_exists ( 'ID', $attributes )) unset($attributes['ID']);
			if ( array_key_exists ( 'FLOW_PHASE', $attributes )) unset($attributes['FLOW_PHASE']); */
			$newAttributes = [	'OCCUR_DATE'=>$attributes['OCCUR_DATE'],
								'TICKET_NO'=>$attributes['TICKET_NO'],
								'TANK_ID'=>$attributes['TANK_ID'],
			];
		}
		else{
			$newAttributes = [
								'ID'=>$attributes['ID'],
			];
		}
		$fdcValues = RunTicketFdcValue::where($newAttributes)->first();
		return $fdcValues;
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
		if(array_key_exists('auto', $attributes)&&$attributes['auto']){
			if ((array_key_exists('ID', $attributes) && strpos($attributes['ID'], 'NEW_RECORD') !== false)&&
					(!array_key_exists ( 'OCCUR_DATE', $attributes )||
							!array_key_exists ( 'TICKET_NO', $attributes )||
							!array_key_exists ( 'TANK_ID', $attributes ))) return null;
					
			$fields = [	"BEGIN_VOL",
						"END_VOL",
						"TICKET_GRS_VOL",
						"TICKET_NET_VOL",
						config("constants.keyField") 	=>	'TANK_ID'];
			if(!array_key_exists('FLOW_PHASE', $attributes)) {
				$tank = Tank::where('ID','=',$attributes['TANK_ID'])->select('PRODUCT')->first();
				$attributes['FLOW_PHASE'] = $tank?$tank->PRODUCT:null;
			}
			static::updateValues($attributes,$values,'TANK',$fields);
			$originAttrCase = \Helper::setGetterUpperCase();
			$fdcValues = static::getFdcValues ( $attributes );
			\Helper::setGetterCase($originAttrCase);
			if ($fdcValues) {
				$newValues = $fdcValues->toArray();
				foreach ( $newValues 	as $column => $vl ) if (!$vl||$column=="ID") unset($newValues[$column]);
				foreach ( $values 		as $column => $vl ) if (!$vl||$column=="ID") unset($values[$column]);
				$values = array_merge($newValues, $values);
			}
			$attributes = ['OCCUR_DATE'=>$values['OCCUR_DATE'],
					'TICKET_NO'=>$values['TICKET_NO'],
					'TANK_ID'=>$values['TANK_ID'],
			];
		}
		
		if(array_key_exists('FLOW_PHASE', $attributes)) unset($attributes['FLOW_PHASE']);
		if(array_key_exists('ID', $values)) 		unset($values['ID']);
		return $values;
	}
} 
