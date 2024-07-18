<?php

namespace App\Models;
use App\Models\DynamicModel;
use App\Trail\ObjectNameLoad;

class IntObjectType extends DynamicModel
{
	use ObjectNameLoad;
	
	protected $table = 'INT_OBJECT_TYPE';
	
	public function __construct(array $param = [],$custom = false) {
		if (is_array($param)&&$custom) {
			parent::__construct();
			if(array_key_exists("ID", $param)&&
					array_key_exists("CODE", $param)&&
					array_key_exists("NAME", $param)
					){
				$this->ID 		= $param["ID"];
				$this->CODE 	= $param["CODE"];
				$this->NAME 	= $param["NAME"];
				$this->keyType 	= "string";
			}
		}
		else parent::__construct($param);
		$this->primaryKey = $this->isOracleModel?strtoupper($this->primaryKey):$this->primaryKey;
	}
	
	public static function find($id){
		if (!is_numeric($id)) {
			//$objects = static ::getGraphObjectType();
			$objects = ($id == "EQUIPMENT" || $id == "DEFERMENT" || $id == "COMMENTS" || $id == "ENVIRONMENTAL" || $id == "LOGISTIC") ? static ::getGraphObjectTypeExport() : static ::getGraphObjectType();
			$instance = $objects->where('CODE',$id)->first();
			return $instance;
		}
		else  return static ::where('ID',$id)->first();
	}
	
	public static function getPreosObjectType(){
		$entries = static ::whereIn('CODE',['FLOW','ENERGY_UNIT','TANK','STORAGE'])->get();
		return $entries;
	}
	
	public static function loadBasic(){
		return IntObjectType::whereIn("CODE",["FLOW","ENERGY_UNIT","TANK","EQUIPMENT"])
                   			->select("CODE as ID","NAME","CODE")
                   			->orderBy("ORDER")->get();
	}
	
	public static function getGraphObjectType($columns = array()){
		return  collect([
				new IntObjectType(['ID' =>	'FLOW'			,'CODE' =>	'FLOW'			,'NAME' => 'Flow'    		],true),
				new IntObjectType(['ID' =>	'ENERGY_UNIT'	,'CODE' =>	'ENERGY_UNIT'	,'NAME' => 'Energy unit'	],true),
				new IntObjectType(['ID' =>	'TANK'			,'CODE' =>	'TANK'			,'NAME' => 'Tank'    		],true),
				new IntObjectType(['ID' =>	'STORAGE' 		,'CODE' =>	'STORAGE'		,'NAME' => 'Storage'    	],true),
				new IntObjectType(['ID' =>	'EQUIPMENT'		,'CODE' =>	'EQUIPMENT'		,'NAME' => 'Equipment'    	],true),
				new IntObjectType(['ID' =>	'EU_TEST'		,'CODE' =>	'EU_TEST'		,'NAME' => 'Well test'    	],true),
				new IntObjectType(['ID' =>	'KEYSTORE'		,'CODE' =>	'KEYSTORE'		,'NAME' => 'Keystore'    	],true),
                new IntObjectType(['ID' =>	'DEFERMENT'		,'CODE' =>	'DEFERMENT'		,'NAME' => 'Deferment'    	],true),
		]);
	}

    public static function getGraphObjectTypeExport($columns = array()){
        return  collect([
            new IntObjectType(['ID' =>	'FLOW'			,'CODE' =>	'FLOW'			,'NAME' => 'Flow'    		],true),
            new IntObjectType(['ID' =>	'ENERGY_UNIT'	,'CODE' =>	'ENERGY_UNIT'	,'NAME' => 'Energy unit'	],true),
            new IntObjectType(['ID' =>	'TANK'			,'CODE' =>	'TANK'			,'NAME' => 'Tank'    		],true),
            new IntObjectType(['ID' =>	'STORAGE' 		,'CODE' =>	'STORAGE'		,'NAME' => 'Storage'    	],true),
            new IntObjectType(['ID' =>	'COMMENTS' 		,'CODE' =>	'COMMENTS'		,'NAME' => 'Comments'    	],true),
            new IntObjectType(['ID' =>	'ENVIRONMENTAL'	,'CODE' =>	'ENVIRONMENTAL'	,'NAME' => 'Environmental' 	],true),
            new IntObjectType(['ID' =>	'LOGISTIC' 		,'CODE' =>	'LOGISTIC'		,'NAME' => 'Logistic'    	],true),
            new IntObjectType(['ID' =>	'EU_TEST'		,'CODE' =>	'EU_TEST'		,'NAME' => 'Well test'    	],true),
            new IntObjectType(['ID' =>	'KEYSTORE'		,'CODE' =>	'KEYSTORE'		,'NAME' => 'Chemical & Quality'],true),
            new IntObjectType(['ID' =>	'EQUIPMENT'		,'CODE' =>	'EQUIPMENT'		,'NAME' => 'Equipment'    	],true),
            new IntObjectType(['ID' =>	'DEFERMENT'		,'CODE' =>	'DEFERMENT'		,'NAME' => 'Deferment'    	],true),
        ]);
    }
	
	public function ObjectDataSource($option=null){
		if ($option!=null&&is_array($option)) {
            $option["IntObjectType"]    = (object)[
                                        'CODE'	=>	$option['IntObjectType']["name"],
                                        'ID'	=>	$option['IntObjectType']["id"]];
			$mdl 			= \Helper::getModelName ("ObjectDataSource");
			return $mdl::loadBy($option);
		}
		return null;
	}
}
