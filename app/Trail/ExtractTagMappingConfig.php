<?php

namespace App\Trail;

trait ExtractTagMappingConfig {
	
	public static function receiveDataFromTagMapping($values,$intTagMapping,$carbonDate,$update_db){
		$sSQL				= "not any action";
		if (!$intTagMapping) {
			$sSQL			= "Tag mapping not found";
		}
		else{
			$configs		= $intTagMapping->CONFIGS;
			if ($configs) {
				list($entry, $attributes) = static::initEntryByKeys($configs,$intTagMapping,$carbonDate);
				if ($entry) {
					$values		= $entry->fillFromTagValues($values,$carbonDate);
					if ($entry->exists) {
						$whereText 	= http_build_query($attributes,'',' and ');
						$valueText 	= implode(', ', array_map( function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
													$values,
													array_keys($values)
													));
						$sSQL		= "update {$entry->table} set $valueText where ($whereText)";
					}
					else{
						$eValues 	= array_merge($attributes,$values);
						$columnText = implode(', ',array_keys($eValues));
						$valueText 	= implode(', ',array_values($eValues));
						$sSQL		= "insert into {$entry->table} ( $columnText ) values ($valueText)";
					}
					if ($update_db) $entry->save();
				}
			}
			else
				$sSQL		= "tag mapping configs column is not set";
		}
		return $sSQL;
	}
	
	public function fillFromTagValues(&$values,$carbonDate){
// 		if(isset(self::$dateField)) $values[self::$dateField] = $carbonDate;
		$this->fill($values);
		return $values;
	}
	
	public static function extractAttribute(&$attributes,$configs,$property,$column){
		if (array_key_exists($property, $configs)) {
			$attributes[$column]		= $configs[$property];
			return true;
		}
		return false;
	}

	public static function initEntryByKeys($configs,$intTagMapping,$carbonDate){
		$attributes	= [];
		foreach(self::$keyFields as $field => $referenceEloquent)
			if(!static::extractAttribute($attributes,$configs,$referenceEloquent,$field)) return [null,"$field is not set"];
		if(!$carbonDate) return [null,"date column is invalid"];
		$attributes[self::$dateField] = $carbonDate;
		$entry	= self::firstOrNew($attributes);
		if ($carbonDate instanceof \Carbon\Carbon) $attributes[self::$dateField] = $carbonDate->toDateString();
		return [$entry,$attributes];
	}
}
