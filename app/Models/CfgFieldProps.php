<?php

namespace App\Models;

class CfgFieldProps extends EbBussinessModel
{
    protected $table = 'cfg_field_props';
    
    public function LockTable(){
    	return $this->hasMany('App\Models\LockTable', 'TABLE_NAME', 'TABLE_NAME');
    }
    
    public function shouldLoadLastValueOf($object){
    	$should	= !$object||$this->RANGE_PERCENT&&$this->RANGE_PERCENT>0;
   	 	if (!$should) {
   	 		$objectExtension = isset ( $this->OBJECT_EXTENSION)?json_decode ($this->OBJECT_EXTENSION,true):[];
   	 		if($objectExtension&&is_array($objectExtension)){
	   	 		$found	= current(array_filter(array_keys($objectExtension), function($key) use($object,$objectExtension) { 
				    			$result	= $object->{$object::$idField}==$key;
				    			if ($result) {
				    				$overwrite 	= array_key_exists("OVERWRITE", $objectExtension[$key])?$objectExtension[$key]["OVERWRITE"]:false;
				    				$basic 		= array_key_exists("basic", $objectExtension[$key])?$objectExtension[$key]["basic"]:[];
				    				$result		= ($overwrite==true||$overwrite=="true")
				    								&&array_key_exists("RANGE_PERCENT", $basic)
				    								&&$basic["RANGE_PERCENT"]>0;
				    			}
				    			return $result?$basic["RANGE_PERCENT"]:false;
				    	}));
	   	 		$should =	$found!==FALSE;
   	 		}
    	}
    	return $should?$this->RANGE_PERCENT:false;
    }
    
    
    public static function getOriginProperties($where,$runQuery= true){
    	$originAttrCase 	= \Helper::setGetterUpperCase();
    	$cfgFieldProps		= CfgFieldProps::getTableName();
    	$selects = ["$cfgFieldProps.COLUMN_NAME as data",
	    			"$cfgFieldProps.COLUMN_NAME as name",
	    			"$cfgFieldProps.FDC_WIDTH as width",
	    			"$cfgFieldProps.LABEL as title",
	    			"$cfgFieldProps.COLUMN_NAME",
	    			"$cfgFieldProps.TABLE_NAME",
	    			"$cfgFieldProps.DATA_METHOD",
	    			"$cfgFieldProps.IS_MANDATORY",
    				"$cfgFieldProps.INPUT_ENABLE",
	    			"$cfgFieldProps.INPUT_TYPE",
	    			"$cfgFieldProps.VALUE_MAX",
	    			"$cfgFieldProps.VALUE_MIN",
	    			"$cfgFieldProps.VALUE_FORMAT",
	    			"$cfgFieldProps.VALUE_WARNING_MAX",
	    			"$cfgFieldProps.VALUE_WARNING_MIN",
	    			"$cfgFieldProps.RANGE_PERCENT",
	    			"$cfgFieldProps.ID",
	    			"$cfgFieldProps.FIELD_ORDER",
	    			"$cfgFieldProps.OBJECT_EXTENSION"];
    	
    	$properties = CfgFieldProps::where($where)
							    	->orderBy("$cfgFieldProps.FIELD_ORDER")
							    	->select($selects);
    	if ($runQuery) $properties = $properties->get();
    	
    	if ($properties&&config('database.default')==='oracle'){
    		foreach($properties as $property ){
    			$property->data		= $property->DATA;
    			$property->name		= $property->NAME;
    			$property->width	= $property->WIDTH;
    			$property->title	= $property->TITLE;
    			$property->id		= $property->ID;
    		}
    	}
    	\Helper::setGetterCase($originAttrCase);
    	return $properties;
    }
    
    public static function getConfigFields($tableName){
    	$originAttrCase = \Helper::setGetterUpperCase();
    	$rs	= static ::where('TABLE_NAME', '=', $tableName)
									->where('USE_FDC', '=', 1)
									->orderBy('FIELD_ORDER')
									->select('COLUMN_NAME')
    								->get();
    	\Helper::setGetterCase($originAttrCase);
    	return $rs;
    }
    
    public static function getNonCTVConfigFields($tableName){
    	$originAttrCase = \Helper::setGetterUpperCase();
    	$rs	= static ::where('TABLE_NAME', '=', $tableName)
				    	->where('USE_FDC', '=', 1)
						->where('COLUMN_NAME', '!=','CTV')
				    	->orderBy('FIELD_ORDER')
				    	->select('COLUMN_NAME')
				    	->get();
    	\Helper::setGetterCase($originAttrCase);
    	return $rs;
    }

     public static function getFieldProperties($table,$field,$configId=null){
     	$re_prop 				= CfgFieldProps::where(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field,'CONFIG_ID'=>$configId])->select('*')->get();
     	$mdl					= \Helper::getModelName($table);
     	$objectExtension 		= method_exists($mdl,"getObjects")?$mdl::getObjects():[];
     	$objectExtensionTarget 	= method_exists($mdl,"getObjectTargets")?$mdl::getObjectTargets():[];
     	$facilities             = Facility::all();
     	return ["data"					=> $re_prop,
     			"objectExtension"		=> $objectExtension,
     			'objectExtensionTarget'	=> $objectExtensionTarget,
                'facility'              => $facilities,
     	];
     }





     public static function getDataProp($table, $configId=null){
         $originAttrCase = \Helper::setGetterUpperCase();
         $rs	= CfgFieldProps ::where('TABLE_NAME', '=', $table)
             ->where(['CONFIG_ID' => $configId])
             ->orderBy('FIELD_ORDER')
             ->get();
         \Helper::setGetterCase($originAttrCase);
         return $rs;
	 }
	 
	 public static function getList($condition = [], $selectedValue = null, $all = false, $none = false){
		$originAttrCase = \Helper::setGetterUpperCase();
		$rs	= CfgFieldProps ::where($condition)
			->whereNull('CONFIG_ID')
			->select('COLUMN_NAME as value', \DB::raw("case when LABEL is null or LABEL='' then COLUMN_NAME else LABEL end as name"))
			->orderBy('FIELD_ORDER')
			->get()->all();
		if($selectedValue != null){
			foreach($rs as $r)
				if($r['value'] == $selectedValue)
					$r['selected'] = true;
		}
		else if(count($rs))
			$rs[0]['selected'] = true;
		\Helper::setGetterCase($originAttrCase);
		return $rs;
	}
}
