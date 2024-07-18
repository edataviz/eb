<?php

namespace App\Trail;

trait RelationDynamicModel 
{
	
	public static function getSourceModel($columnName){
		return null;
	}
	
	public static function getTargetModel($columnName){
		return "ObjectName";
	}
	
	public static function getTargetSourceColumn($columnName,$row){
		$ret = isset(static :: $relateColumns)&&array_key_exists("id", static :: $relateColumns)?
							static :: $relateColumns["id"]:"OBJECT_ID";
		if(config('database.default')==='oracle')
			$ret = strtolower($ret);
		return $ret;
	}
	
	public static function getSourceTargetColumn($columnName,$row){
		$ret = isset(static :: $relateColumns)&&array_key_exists("type", static :: $relateColumns)?
							static :: $relateColumns["type"]:"OBJECT_TYPE";
		if(config('database.default')==='oracle')
			$ret = strtolower($ret);
		return $ret;
	}
	
	public static function getTagetColumns($columnName,$sourceIdColumn){
		return [$sourceIdColumn	=> $sourceIdColumn];
	}
	
	public static function getForeignColumn($row,$originCommand,$columnName){
		$command 			= $originCommand;
		$columnName 		= strtoupper($columnName);
		$sourceIdColumn		= static :: getTargetSourceColumn($columnName,$row);
		$sourceTypeColumn	= static :: getSourceTargetColumn($columnName,$row);
		$sourceTypeColumn 	= config('database.default')==='oracle'?strtolower($sourceTypeColumn):$sourceTypeColumn;
		if ($columnName==$sourceIdColumn) {
			$splitCharacter 	= config('database.default')==='oracle'?"":";";
			$s_where	= "";
			$s_order	= "";
			$namefield	= "NAME";
			$id			= $row&&array_key_exists($sourceTypeColumn, $row)?$row[$sourceTypeColumn]:1;
			$sourceModel= static::getSourceModel($columnName);
			if ($sourceModel) {
				$sourceModel= 'App\Models\\' .$sourceModel;
				$inject		= $sourceModel::find($id);
				if($inject&&$inject->CODE) {
					if (method_exists($inject, "getReferenceTable")) {
						$ref_table	= $inject->getReferenceTable($inject->CODE);
					}
					else
						$ref_table	= $inject->CODE;
					$command 	= "select ID, $namefield from $ref_table $s_where $s_order $splitCharacter --select";
				}
			}
		}
		return $command;
	}
	
	public static function getDependences($columnName,$idValue){
		$columnName 		= strtoupper($columnName);
		$option 			= null;
		$sourceIdColumn		= static :: getTargetSourceColumn($columnName,$row);
		$sourceTypeColumn	= static :: getSourceTargetColumn($columnName,$row);
		if ($columnName==$sourceTypeColumn) {
			$dependences 	= static::getTagetColumns($columnName,$sourceIdColumn);
			$sourceModel	= static::getSourceModel($columnName);
			$targets		= array_values($dependences);
			if (config('database.default')==='oracle') {
				foreach($targets as $index => $target ){
					$targets[$index] = strtolower($target);
				}
			}
			$option = ["dependences"	=> [],
					"sourceModel"		=> $sourceModel,
					"targets"			=> $targets ,
					'extra'				=> [$sourceTypeColumn],
			];
			foreach($dependences as $sourceColumn => $dependence ){
				$sourceModel			= static::getSourceModel($sourceColumn);
				$dependenceModel		= static::getTargetModel($dependence);
				$option["dependences"][] = ["name"		=> $dependenceModel,
											"elementId"	=> config('database.default')==='oracle'?strtolower($dependence):$dependence,
											"source"	=> $sourceModel,
				];
			}
		}
		return $option;
	}
}
