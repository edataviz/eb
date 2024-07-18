<?php

namespace App\Models;


class QltyDataDetail extends QltyData
{
	protected $table = 'QLTY_DATA_DETAIL';
	protected $fillable  = ['QLTY_DATA_ID',
							 'ELEMENT_TYPE',
							 'VALUE',
							 'UOM',
							 'GAMMA_C7',
							 'MOLE_FACTION',
							 'MASS_FRACTION',
							 'NORMALIZATION',
							 'MOLE_FACTION2',
							 'MASS_FRACTION2'];
	
	
	
	public function QltyProductElementType(){
		return $this->hasOne('App\Models\QltyProductElementType','ID','ELEMENT_TYPE');
	}
	
	public static function initEntryByKeys($configs,$intTagMapping,$carbonDate){
		list($entry, $attributes) = QltyData::initEntryByKeys($configs,$intTagMapping,$carbonDate);
		if ($entry) {
			if (!$entry->exists) $entry->save();
			$dAttributes	= ["QLTY_DATA_ID"	=> $entry->ID];
			if(!self::extractAttribute($dAttributes,$configs,"QltyProductElementType","ELEMENT_TYPE"))	return [null,"ELEMENT_TYPE is not set"];
			$dEntry	= self::firstOrNew($dAttributes);
			return [$dEntry,$dAttributes];
		}
		else{
			$conditions = http_build_query($attributes,'',' and ');
			return [null,"Can not create new QltyData ! [ $conditions ]"];
		}
	}
}
