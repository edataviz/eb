<?php

namespace App\Http\Controllers;


class AllocatePlanController extends CodeController {
	
	public function getWorkingTable($postData){
		return null;
	}
	
	public function getQueryCondition($where,$postData){
		$planType 		= array_key_exists('CodePlanType', $postData)?$postData['CodePlanType']:0;
		if ($planType>0) $where["PLAN_TYPE" ] 	= $planType;
		return $where;
	}
	
	public function getPreFix($source_type){
		$obj_id_prefix	=	$source_type;
		$field_prefix	=	$source_type;
		if($source_type=="ENERGY_UNIT"){
			$obj_id_prefix	="EU";
		}
		else if($source_type=="FLOW") {
			$obj_id_prefix="FL";
		}
		
		if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
			$field_prefix	= $obj_id_prefix."_DATA";
		}
		return $field_prefix.'_';
	}
	
	public function getObjectTable($src,$data_source){
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
 		$mdl 			= 	"App\Models\\".$mdl;
		return $mdl;
	}
	
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$source_type 	= 	$postData['IntObjectTypeName'];
		$prefix 		= 	$this->getPreFix($source_type);
		$par = [
            (object)['data' =>	'OCCUR_DATE',		'title' => 'Occur Date',	'width'	=>	100,'INPUT_TYPE'=>3,	'DATA_METHOD'=>2,'FIELD_ORDER'=>1],
            (object)['data' =>	$prefix."NET_VOL"	,'title' => 'Net Vol'	,   'width'=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>3],
            (object)['data' =>	$prefix."GRS_ENGY"	,'title' => 'Gross Energy',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>6],
		];
		if(config('constants.systemName')!='Santos') {
			$par[] = (object)['data' =>	$prefix."GRS_VOL"	,'title' => 'Gross Vol'	,	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>2];
			$par[] = (object)['data' =>	$prefix."GRS_MASS"	,'title' => 'Gross Mass',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>4];
			$par[] = (object)['data' =>	$prefix."NET_MASS"	,'title' => 'Net Mass', 	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>5];
			$par[] = (object)['data' =>	$prefix."GRS_PWR"	,'title' => 'Gross Power',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>7];
		}
		$properties = collect($par);
		return ['properties'	=>$properties];
	}
	
	public function getModelName($mdlName,$postData) {
		$source_type 	= 	$postData['IntObjectTypeName'];
		$table			=	$source_type."_DATA_PLAN";
		$tableName		= 	strtolower ( $table );
		$mdlName 		= 	\Helper::camelize ( $tableName, '_' );
		return $mdlName;
	}
	
	protected function deleteData($postData) {
		if (array_key_exists ('deleteData', $postData )) {
			$deleteData 	= $postData['deleteData'];
			$flow_phase 	= 	$postData['ExtensionPhaseType'];
			$object_id 		= 	$postData['ObjectName'];
			$source_type 	= 	$postData['IntObjectTypeName'];
			$occur_date 	= 	$postData['date_begin'];
    		$occur_date		= 	\Helper::parseDate($occur_date);
			$date_from		=	$occur_date;
			$date_to 		= 	$postData['date_end'];
			$date_to		= 	\Helper::parseDate($date_to);
				
			$obj_id_prefix	=	$source_type;
			$field_prefix	=	$source_type;
			$idField		= 	$source_type;
			
			foreach($deleteData as $mdlName => $mdlData ){
				if ($mdlData) {
					$modelName = $this->getModelName($mdlName,$postData);
					$mdl = "App\Models\\".$modelName;
					
					$where = [];
					if($source_type=="ENERGY_UNIT"){
						$obj_id_prefix				="EU";
						$idField 					= $obj_id_prefix;
						$where["FLOW_PHASE" ] 		= $flow_phase;
					}
					else if($source_type=="FLOW") {
						$obj_id_prefix="FL";
					}
					
					if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
						$field_prefix=$obj_id_prefix."_DATA";
					}
					$where["$idField"."_ID" ] 	= $object_id;
					$where						= $this->getQueryCondition($where,$postData);
						
					$mdl::where($where)
						->whereBetween('OCCUR_DATE', [$date_from,$date_to])
						->delete();
				}
			}
		}
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){

    	$flow_phase 	= 	$postData['ExtensionPhaseType'];
    	$object_id 		= 	$postData['ObjectName'];
		$source_type 	= 	$postData['IntObjectTypeName'];
    	$date_from		=	$occur_date;
    	$date_to 		= 	$postData['date_end'];
	    $date_to 		= 	\Helper::parseDate($date_to);
	     
    	if(!$object_id||$object_id<=0){
//     		return "";
    		$message 	= "Object Name $object_id invalid. Please input valid Object Name";
    		$e			= new \Exception($message);
    		\Log::info($e->getMessage());
    		\Log::info($e->getTraceAsString());
//     		throw $e;
    		return $message;
    	}
    	$obj_id_prefix	=	$source_type;
    	$field_prefix	=	$source_type;
    	$idField		= 	$source_type;
    	$modelName 		= 	$this->getModelName($source_type,$postData);
 		$mdl 			= 	"App\Models\\".$modelName;
    	 
    	$selects = ["ID as DT_RowId","OCCUR_DATE"];
    	$where = [];
    	if($source_type=="ENERGY_UNIT"){
			$obj_id_prefix				="EU";
			$idField 					= $obj_id_prefix;
			$where["FLOW_PHASE" ] 		= $flow_phase;
			$selects[] 					= "FLOW_PHASE as EU_FLOW_PHASE";
			$selects[] 					= "EU_ID";
    	}
		else if($source_type=="FLOW") {
			$obj_id_prefix="FL";
			$selects[] 		= "FLOW_ID";
		}
		else $selects[] 		= "$idField"."_ID";
		
		if($source_type=="FLOW"||$source_type=="ENERGY_UNIT"){
			$field_prefix=$obj_id_prefix."_DATA";
		}
		$fillable					= $mdl::getInstance()->getTableColumns();
		$column						= "$field_prefix"."_GRS_VOL";
		if (in_array($column, $fillable)) $selects[] 					= $column;
		$column						= "$field_prefix"."_NET_VOL";
		if (in_array($column, $fillable)) $selects[] 					= $column;
		$column						= "$field_prefix"."_GRS_MASS";
		if (in_array($column, $fillable)) $selects[] 					= $column;
		$column						= "$field_prefix"."_NET_MASS";
		if (in_array($column, $fillable)) $selects[] 					= $column;
		$column						= "$field_prefix"."_GRS_ENGY";
		if (in_array($column, $fillable)) $selects[] 					= $column;
		$column						= "$field_prefix"."_GRS_PWR";
		if (in_array($column, $fillable)) $selects[] 					= $column;
		
		$where["$idField"."_ID" ] 	= $object_id;
		$where						= $this->getQueryCondition($where,$postData);

		//     	\DB::enableQueryLog();
		$dataSet = $mdl::where($where)
// 						->whereDate('OCCUR_DATE',">=",$date_from)
// 						->whereDate('OCCUR_DATE',"<=",$date_to)
						->whereBetween('OCCUR_DATE', [$date_from,$date_to])
						->select($selects)
						->orderBy('OCCUR_DATE')
						->get();
				//  		\Log::info(\DB::getQueryLog());
    	return ['dataSet'=>$dataSet];
    }
}
