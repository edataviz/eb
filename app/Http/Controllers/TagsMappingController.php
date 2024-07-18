<?php

namespace App\Http\Controllers;
use App\Models\IntMapTable;
use App\Models\IntTagMapping;
use App\Models\IntObjectType;
use Illuminate\Http\Request;

class TagsMappingController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	
										'TABLE_NAME'		=>	[	'column'	=>'COLUMN_NAME',
																	'model'		=>'CodeDeferCode2'],
									];
	}

	private $objectType;
	private $objectType_ID = -1;
	function getObjectType($objectType_ID){
		if($this->objectType_ID == $objectType_ID)
			return $this->objectType;
		$this->objectType_ID = $objectType_ID;
		if (is_numeric($this->objectType_ID)&&$this->objectType_ID>0)
			$this->objectType=IntObjectType::find($this->objectType_ID);
		else
			$this->objectType=IntObjectType::where("CODE",'=',$this->objectType_ID)->first();
		if (!$this->objectType)
			abort(501,"ObjectType unknown");
		return $this->objectType;
	}
	
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$objectTable = $this->getObjectType($postData['IntObjectType'])->CODE;
		$props = [
			(object)['data' => 'ID', 'title' => '', 'width' => 50],
			(object)['data' => 'TAG_ID', 'title' => 'Tag', 'width' => 300, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'FIELD_ORDER'=>1],
			(object)['data' => 'OBJECT_ID', 'title' => 'Object Name', 'width' => 180, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'FIELD_ORDER'=>2],
			(object)['data' => 'TABLE_NAME', 'title' => 'Table', 'width' => 230, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'FIELD_ORDER'=>3],
			(object)['data' => 'COLUMN_NAME', 'title' => 'Column', 'width' => 180, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'FIELD_ORDER'=>4],
		];
		if($objectTable == 'ENERGY_UNIT'){
			$props=array_merge($props, [
				(object)['data' => 'FLOW_PHASE', 'title' => 'Flow Phase', 'width' => 0, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'COLUMN_NAME' => "FLOW_PHASE", 'FIELD_ORDER'=>6],
				(object)['data' => 'EVENT_TYPE', 'title' => 'Event Type', 'width' => 0, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'COLUMN_NAME' => "EVENT_TYPE", 'FIELD_ORDER'=>7],
			]);
		}
		else if($objectTable == 'KEYSTORE'){
			$props=array_merge($props, [
				(object)['data' => 'INJECTION_POINT_ID', 'title' => 'Injection Point', 'width' => 150, 'INPUT_TYPE'=>1, 'DATA_METHOD'=>1, 'COLUMN_NAME' => "INJECTION_POINT_ID", 'FIELD_ORDER'=>6],
			]);
		}
        $properties = collect($props);
    	$uoms = $this->getUoms($properties,$facility_id,$dcTable,$locked = false,$postData);
    	
    	$results = ['properties'	=>$properties,
					'uoms'			=>$uoms,
			];
        return $results;
    }

	public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$object_id = $postData['ObjectName'];
    	$mdlName = $postData[config("constants.tabTable")];
		$mdl = "App\\Models\\$mdlName";
		
		$objectType = $this->getObjectType($postData['IntObjectType']);
		$xtable = $objectType->CODE;
		$object_type = $objectType->ID;
    	
	    $where = ["$dcTable.OBJECT_TYPE" 	=> $object_type];
    	
    	$xMdl 			= \Helper::getModelName($xtable);
    	$fillable		= $xMdl::getInstance()->getTableColumns();
    	$hasFacility	= in_array('FACILITY_ID', $fillable);
    	
    	if ($hasFacility)
    		$where["$xtable.FACILITY_ID"] 	=  $facility_id;
    	
	    
	    if ($object_id>0) $where["$dcTable.OBJECT_ID"]= $object_id;
	    
	    $aQuery =  $xtable!="QLTY_DATA"?
	    			$mdl::join($xtable,"$dcTable.OBJECT_ID",'=',"$xtable.ID")->where($where)
	    			:$mdl::where($where);
	    $dataSet = $aQuery->select(
								"$dcTable.ID as DT_RowId",
								"$dcTable.*"
								)
		 				->get();
	    
    	$bunde = ['OBJECT_TYPE' => $object_type];
    	$extraDataSet 	= $this->getExtraDataSet($dataSet, $bunde);
    	
    	$data = IntMapTable::where("OBJECT_TYPE",'=',$object_type)//->orWhereNull('OBJECT_TYPE')
    	->orderBy('NAME')
    	->get([
    			'TABLE_NAME as NAME',
    			'TABLE_NAME as '.\Helper::getConstantTextOverDbDriver("value"),
    			'TABLE_NAME as '.\Helper::getConstantTextOverDbDriver("text")]);
    	
     	$extraDataSet['TABLE_NAME']['TABLE_NAME'] = $data;
     	
		 $objects = \DB::table($xtable)
		 ->where($hasFacility?["FACILITY_ID" => $facility_id]:[])
		 ->orderBy('NAME')
		 ->get(['ID','NAME',
				 'ID as '.\Helper::getConstantTextOverDbDriver("value"),
				 'NAME as '.\Helper::getConstantTextOverDbDriver("text")]);

     	$extraDataSet['OBJECT_ID']['OBJECT_ID'] = $objects;
     	
    	return ['dataSet'=>$dataSet,
     			'extraDataSet'=>$extraDataSet
    	];
    }
    
    
    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'TABLE_NAME':
    			//note for multi db
    			if (config('database.default')==='oracle'){
					//        			\DB::enableQueryLog();
    				$query 		= \DB::table('user_tab_cols')
				    				->where('TABLE_NAME','=',$sourceColumnValue)
				    				->whereIn('DATA_TYPE',['NUMBER','FLOAT','DOUBLE']);
//  		     	\Log::info(\DB::getQueryLog());
    			}
    			else{
					$db_schema = (config('database.default')==='sqlsrv'?config('database.connections.sqlsrv.schema'):ENV('DB_DATABASE'));
					$query = \DB::table('INFORMATION_SCHEMA.COLUMNS')
			    				->where('TABLE_SCHEMA','=',$db_schema)
			    				->where('TABLE_NAME','=',$sourceColumnValue);
			    				//->whereIn('DATA_TYPE',['decimal','float','double','varchar','nvarchar']);
    			}
    			$data	= $query->select(
						    			"COLUMN_NAME as ID",
						    			"COLUMN_NAME as NAME",
	    								"COLUMN_NAME as ".\Helper::getConstantTextOverDbDriver("value"),
						    			"COLUMN_NAME as ".\Helper::getConstantTextOverDbDriver("text")
						    			)
								->orderBy("COLUMN_NAME")
    							->get();
    			break;
    	}
    	return $data;
    }
    
    
    public function loadsrc(Request $request){
    	$postData = $request->all();
    	$sourceColumn = $postData['name'];
    	$sourceColumnValue = $postData['value'];
    	$bunde = [];
    	$extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
    	$targetColumn = $extraDataSetColumn['column'];
    	$data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
    	$dataSet = [
    				$targetColumn	=>[	'data'			=>	$data,
								    	'ofId'			=>	$sourceColumnValue,
								    	'sourceColumn'	=>	$sourceColumn]
    				];
    	 
    	return response()->json(['dataSet'=>$dataSet,
    							'postData'=>$postData]);
    }
    
    public function all(Request $request){
    	\Helper::setGetterUpperCase();
    	
/*     	$intObjectType 		= IntObjectType::getTableName();
    	$query 				= null;
    	$rquery 			= null;
    	$objectInfos 		= IntObjectType::where("ACTIVE","=",1)->whereNotIn("CODE",["QUALITY"])->get();
    	foreach($objectInfos as $key => $objectInfo ){
    		$objectType 	= $objectInfo->CODE;
    		$mdl 			= \Helper::getModelName($objectType);
    		$tableName		= $mdl::getTableName();
    		$query 			= $mdl::join($intObjectType,function($join) use ($intObjectType,$objectType){
					    			$join->on("$intObjectType.ID","=","$intObjectType.ID");
					    			$join->where("$intObjectType.CODE",'=',$objectType);
					    		})
					    		->select("$tableName.ID as OBJECT_ID",
					    				"$intObjectType.ID as OBJECT_TYPE",
					    				"$tableName.NAME as OBJECT_NAME");
			if ($rquery&&$query) 
				$rquery->union($query);
			else if($query)	
				$rquery = $query;
    	}
    	$subtext 		= "sub";
    	$subName		= \Helper::getSubNameSelectQuery($subtext);
    	$intTagMapping	= \App\Models\IntTagMapping::getTableName();
//     	\DB::enableQueryLog();
    	$tags 			= \App\Models\IntTagMapping::join( \DB::raw("({$rquery->toSql()}) $subName"),
    		function($join) use ($intTagMapping,$subtext){
    		$join->on("$intTagMapping.OBJECT_TYPE","=","$subtext.OBJECT_TYPE");
    		$join->on("$intTagMapping.OBJECT_ID","=","$subtext.OBJECT_ID");
    	})
    	->mergeBindings($rquery->getQuery()) // you need to get underlying Query Builder
    	->select("$intTagMapping.TAG_ID","$subtext.OBJECT_NAME")
    	->orderBy("$subtext.OBJECT_NAME")
    	->get();
//     	\Log::info(\DB::getQueryLog());
 */
    	$tags	= IntTagMapping::select("TAG_ID","TAG_NAME as OBJECT_NAME")->orderBy("TAG_ID")->get();
    	return response()->json($tags);
    }
}
