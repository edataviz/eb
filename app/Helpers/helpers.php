<?php
use App\Models\LockTable;
use App\Models\AuditValidateTable;
use App\Models\AuditApproveTable;
use App\Models\UserDataScope;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Collection;

class Helper {

    public static $colors             = ['#7e6de3',"#aa39c9","#aa1483","#aa003e","#aafed3","#aa3913","#aa6887","#aa4a4e","#aae9b8",'#6fe37d','#39b5db','#de7c47',
        '#C0C0C0',
        '#808080',
        '#000000',
        '#FF0000',
        '#800000',
        '#FFFF00',
        '#808000',
        '#00FF00',
        '#008000',
        '#00FFFF',
        '#008080',
        '#0000FF',
        '#000080',
        '#FF00FF',
        '#800080'];

	public static function getFilterArray($id,$collection=null,$currentUnit=null,$option=null){
		if ($option==null||is_string($option)) {
			$option = array();
		}
		$option['id'] 			= $id;
		$option['modelName'] 	= array_key_exists('modelName', $option)?$option['modelName']:$id;
		$option['collection'] 	= $collection;
		$currentId				= null;
		if ($currentUnit) {
			if (($currentUnit instanceof Model )&& $currentUnit->ID) {
				$currentId		= $currentUnit->ID;
			}
			else if (is_array($currentUnit)&&array_key_exists('ID', $currentUnit)) {
				$currentId		= $currentUnit['ID'];
			}
		}
		$option['currentId'] 	= $currentId;
		$option['current'] 		= $currentUnit;
		return $option;
	}
	
	public static function filter($option=null) {
		if ($option==null) return;
		$collection			= array_key_exists('collection', $option)?$option['collection']:false;
		if (!$collection) {
			$model			='App\\Models\\'.$option['modelName'];
			if ( array_key_exists('getMethod', $option)) {
				$getMethod 	= $option['getMethod'];
				$params		= array_key_exists('filterData', $option)?$option['filterData']:null;
				$collection = call_user_func("$model::$getMethod",$params);
			}
			else 
				$collection = $model::all(['ID', 'NAME']);
		}
		$option['collection'] = $collection;
		Helper::buildFilter($option);
	}
	
	public static function buildFilter($option=null) {
		if ($option == null) return;
		$collection 	= $option['collection'];
		$currentUnit 	= array_key_exists('current', $option)?$option['current']:null;
		
		$default		= (!array_key_exists('defaultEnable', $option)||(array_key_exists('defaultEnable', $option)&&$option['defaultEnable']))
							&&array_key_exists('default', $option)?
							$option['default']:false;
		$id				= array_key_exists('id', $option)?$option['id']:false;
		$name			= array_key_exists('name', $option)?$option['name']:false;
		$filterName 	= array_key_exists('filterName', $option)?$option['filterName']:$name;
		$lang			= session()->get('locale', "en");
		$filterName		= Lang::has("front/site.$filterName", $lang)?trans("front/site.$filterName"):$filterName;
		$enableTitle	= array_key_exists('enableTitle', $option)?$option['enableTitle']:true;
        $attributes     = array_key_exists('attributes', $option)?$option['attributes']:"";

		if ($enableTitle) {
			$htmlFilter 	= "<div  class=\"filter $name\" id='select_container_$id'><div><b id=\"title_$id\">$filterName</b>".
								'</div>
								<select id="'.$id.'" name="'.$name."\" $attributes >";
		}
		else {
			$htmlFilter 	= "<div  class=\"filter $name\" id='select_container_$id'><select id='$id' name='$name' $attributes>";
		}
		if ($default) {
			$htmlFilter .= '<option '.($currentUnit?'':'selected="selected"').' value="'.$default['ID'].'">'.$default['NAME'].'</option>';
		}
	
		$currentId = array_key_exists('currentId', $option)?$option['currentId']:'';
		if ($collection) {
			foreach($collection as $item ){
				if($item){
					$nameValue 	= 	($item instanceof Model)?
									$item->CODE:
									(	isset($item->CODE)?
										$item->CODE:
										(	isset($item->code)?
											$item->code:""));
					$fvalue 	= isset($item->ID)?$item->ID:(isset($item->id)?$item->id:$nameValue);
					$optionName	= isset($item->NAME)?$item->NAME:(isset($item->name)?$item->name:'');
					$optionName	= Lang::has("front/site.$optionName", $lang)?trans("front/site.$optionName"):$optionName;

                    $optionAttributes = array_key_exists('optionAttributes', $option)?$option['optionAttributes']:[];
                    $optionAttributeText = '';
                    foreach ($optionAttributes as $oAttribute){
                        $optionAttributeText.=" $oAttribute=\"{$item->$oAttribute}\" ";
                    }
					$htmlFilter .= '<option name="'.$nameValue
								.'" value="'.$fvalue.'"'.$optionAttributeText.
								($currentUnit&&($currentUnit==$item||(is_array($currentUnit)&&array_key_exists("ID", $currentUnit)&&$currentUnit["ID"]==$fvalue))?
								'selected="selected"':'')
								.'>'.$optionName.'</option>';
				}
			}
		}
		
		$htmlFilter .= '</select></div>';
		if ($id&&array_key_exists('dependences', $option)&&count($option['dependences'])>0) {
			$dependences = [];
			$more = [];
			$originDependences = $option['dependences'];
			foreach($originDependences as $dependence ){
// 				$dependences[] = $dependence;
				if (is_string($dependence) ) {
					$dependences[] = $dependence;
				}
				/* else if (isset($dependence['independent'])&&$dependence['independent']){
//  					$dependences[] = $dependence['name'];
// 					$more[] = $dependence['name'];
				} */
			}
			
			if (count($originDependences)>0
					&&(!array_key_exists('single', $option)
							||!$option['single'])) {
				$extra 	= array_key_exists('extra', $option)&&count($option['extra'])>0?$option['extra']:null;
				$evs 	= $extra?array_values($extra):null;
				$extra 	= is_array($evs)&&count($evs)>0?",'".json_encode($evs)."'":'';
// 				$extra = is_array($extra)&&count($extra)>0?",['".implode("','", $extra)."']":'';
				$htmlFilter.= "<script>registerOnChange('$id',".json_encode($originDependences)."$extra)</script>";
			}
		}
	
		echo $htmlFilter;
	}
	
	
	public static function selectDate($option=null) {
		
		if ($option==null) return;
		$name=array_key_exists('name', $option)?$option['name']:'';
		$value=array_key_exists('value', $option)?$option['value']:'';
		$id=array_key_exists('id', $option)?$option['id']:'';
		$sName=array_key_exists('sName', $option)?$option['sName']:'';
	
		$htmlFilter = '';
		switch ($id) {
    			/* case 'date_begin':
    			case 'date_end':
    			case 'f_date_from':
    			case 'f_date_to':
    			case 'txtCargoDate':
    				break; */
    			case 'cboFilterBy':
    					$htmlFilter = 	"<div class=\"filter\"><div><b>$name</b>".
			    							'</div><select id="'.$id.'" name="'.$name.'">';
    					$htmlFilter .= "<option value = 'SAMPLE_DATE'>Sample Date</option><option value = 'TEST_DATE'>Test Date</option><option value = 'EFFECTIVE_DATE'>Effective Date</option>";
						$htmlFilter .= '</select></div>';
    					break;
    			default:
    				$configuration = auth()->user()->getConfiguration();
    				$format = $configuration['time']['DATE_FORMAT_CARBON'];//'m/d/Y';
    				if ($value&&$value instanceof Carbon) {
						$value	=	$value->format($format);
    				}
    				$jsFormat = $configuration['picker']['DATE_FORMAT_JQUERY'];//'mm/dd/yy';
    				$htmlFilter.= "<div class='date_input'>
    									<div><b>$name</b>
    									<span id='next_$id' class='floatRight ui-icon ui-icon-circle-triangle-e'>Next</span>
    									<span id='pre_$id' class='floatRight ui-icon ui-icon-circle-triangle-w'>Prev</span></div>
    									<input style='width:85%' type='text' id = '$id' name='$sName' size='15' value='$value'>
    							</div>";
					$htmlFilter.= '<script>
											$( "#'.$id.'" ).datepicker({
												changeMonth:true,
												changeYear:true,
												dateFormat:"'.$jsFormat.'"
											});
											if(typeof registerDateNavigation === "function") registerDateNavigation("'.$id.'");
										</script>';
					
					if (array_key_exists('dependences', $option)) {
						$dependences = $option['dependences'];
						$extra = array_key_exists('extra', $option)&&count($option['extra'])>0?$option['extra']:null;
						$extra = is_array($extra)&&count($extra)>0?",['".implode("','", $extra)."']":'';
						$htmlFilter.= "<script>registerOnChange('$id',['".implode("','", $dependences)."']$extra)</script>";
					}
    				break;
    		}
		
	
		echo $htmlFilter;
	}
	
	public static function checkLockedTable($dcTable,$occur_date,$facility_id) {
// 		$mdl = "App\Models\\".$mdlName;
// 		$tableName = $mdl::getTableName();
		$lockTable = LockTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		      					->whereDate('LOCK_DATE', '>=', $occur_date)
								->first();
		return $lockTable;
	}
	
	public static function checkApproveTable($dcTable,$occur_date,$facility_id) {
		$lockTable = AuditApproveTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		->whereDate('DATE_FROM', '<=', $occur_date)
		->whereDate('DATE_TO', '>=', $occur_date)
		->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	public static function checkValidateTable($dcTable,$occur_date,$facility_id) {
		$lockTable = AuditValidateTable::where(['TABLE_NAME'=>$dcTable,'FACILITY_ID'=>$facility_id])
		->whereDate('DATE_FROM', '<=', $occur_date)
		->whereDate('DATE_TO', '>=', $occur_date)
		->first();
		return $lockTable!=null&&$lockTable!=false;
	}
	
	
	public static function camelize($input, $separator = '_')
	{
		return str_replace($separator, '', ucwords($input, $separator));
	}
	
	public static function getRoundValue($value){
		$value = $value?round($value):0;
		return $value;
	}
	
	public static function getModelName($table,$includePackage=true)
	{
		$tableName = strtolower ( trim($table) );
		$mdlName = static::camelize ( $tableName, '_' );
		return $includePackage?'App\Models\\' . $mdlName:$mdlName;
	}
	
	public static function convertDate2CarbonFormat($dateFormat)
	{
		if ($dateFormat) {
			$lowerDateFormat	= 	strtolower($dateFormat);
			$elements			= 	explode('/', $lowerDateFormat);
			$newElements		= 	[];
			foreach ($elements as $element){
	// 			$newElements[] = substr($element, 0, strlen($element)/2);
				if ($element[0]=='y') {
					$newElements[] = 'Y';
				}
				else 
					$newElements[] = $element[0];
			}
			$newFormat = implode('/', $newElements);
			return $newFormat;
		}
		else return null;
	}
	
	public static function convertDate2JqueryFormat($dateFormat)
	{
		if ($dateFormat) {
			$lowerDateFormat	= 	strtolower($dateFormat);
			$elements			= 	explode('/', $lowerDateFormat);
			$newElements		= 	[];
			foreach ($elements as $element){
	 			$newElements[] = substr($element, 0, strlen($element)/2);
			}
			$newFormat = implode('/', $newElements);
			return $newFormat;
		}
		else return null;
	}
	
	public static function convertTime2PickerFormat($timeFormat)
	{
		if ($timeFormat) {
			$newFormat	= \App\Models\DateTimeFormat::$timeFortmatPair;
			return $newFormat[$timeFormat];
		}
		else return null;
	}
	
	public static function parseDate($dateString,$timeFormat=null,$dateFormat=null)
	{
		if (is_null($dateString))  return "";
		if(!$dateFormat){
			$formatSetting 		= 	session('configuration');
			$formatSetting 		= 	$formatSetting?$formatSetting:\App\Models\DateTimeFormat::$defaultFormat;
			$dateFormat 		= 	$formatSetting['DATE_FORMAT'];
			$carbonFormat		= 	\Helper::convertDate2CarbonFormat($dateFormat);
		}
		else
			$carbonFormat = $dateFormat;
        if($timeFormat)
            $carbonFormat   = "$carbonFormat $timeFormat";
        $carbonDate 		= 	Carbon::createFromFormat($carbonFormat, $dateString);
        if(!$timeFormat){
            $carbonDate->hour 	= 0;
            $carbonDate->minute = 0;
            $carbonDate->second = 0;
        }
		return $carbonDate;
	}

	public static function getFacilityIds($data,$facility_id){
		if ($facility_id>0) {
			$facilityIds	= [$facility_id];
		}
		else{
			$areaId 		= $data['LoArea'];
			$facilityIds	= Facility::where("AREA_ID"	,'=', $areaId)->select("ID")->get()->pluck("ID")->toArray();
		}
		return $facilityIds;
	}
	
	public static function logger() {
		$queries = \DB::getQueryLog();
		$query = end($queries);
		$prep = $query['query'];
		foreach( $query['bindings'] as $binding ) : $prep = preg_replace("#\?#", $binding, $prep, 1);
		endforeach;
		return $prep;
	}
	
	public static function getAvailableFacilities($user=null){
		$originAttrCase 		= \Helper::setGetterUpperCase();
    	$user 					= $user?$user:auth()->user();
		$workspace 				= $user->workspace();
		$userDataScope			= UserDataScope::where("USER_ID",$user->ID)->first();
		if (!$userDataScope) return null;
		
		$facilityIds			= null;
		$DATA_SCOPE_PU			=$userDataScope->PU_ID;
		$DATA_SCOPE_AREA		=$userDataScope->AREA_ID;
		$DATA_SCOPE_FACILITY	=$userDataScope->FACILITY_ID;
		
		if(!empty($DATA_SCOPE_FACILITY)){
			$facilityIds		= explode(",", $DATA_SCOPE_FACILITY);
		}
		else if($DATA_SCOPE_AREA&&$DATA_SCOPE_AREA>0){
			$facilities 		= Facility::where("AREA_ID","=",$DATA_SCOPE_AREA)->get();
			$facilityIds		= $facilities->pluck("ID")->toArray();
		}
		else if($DATA_SCOPE_PU&&$DATA_SCOPE_PU>0){
			$facilities 		= Facility::whereHas('Area', function ($query) use ($DATA_SCOPE_PU){
										$query->where('PRODUCTION_UNIT_ID', '=', $DATA_SCOPE_PU);
									})
									->get();
			$facilityIds		= $facilities->pluck("ID")->toArray();
		}
		\Helper::setGetterCase($originAttrCase);
		
		return $facilityIds;
	}
	
	public static function isNullOrEmpty($value){
		return $value==null||$value==''||$value==false;
	}
	
	public static function translateText($lang,$text){
		return Lang::has("front/site.$text", $lang)?trans("front/site.$text"):$text;
	}
	
	public static function getExtraSelects($tableView,$objectType,$objectId,$extraSelect){
		if($tableView=="v_cargo_nomination"||$tableView=="V_CARGO_NOMINATION") return "CARGO_NAME as E";
		return $extraSelect;
	}
	
	public static function correctColumn($column){
		return config('database.default')==='oracle'?strtoupper($column):$column;
	}
	
	public static function setGetterCase($attribute=null){
		if($attribute&&config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
			$dbh->setAttribute (\PDO::ATTR_CASE, $attribute);
		}
	}
	
	public static function setGetterUpperCase(){
		if(config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
			$attribute		= $dbh->getAttribute (\PDO::ATTR_CASE);
	    	$dbh->setAttribute (\PDO::ATTR_CASE, \PDO::CASE_UPPER);
	    	return $attribute;
		}
		return null;
	}
	
	public static function setGetterLowerCase(){
		if(config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
			$dbh->setAttribute (\PDO::ATTR_CASE, \PDO::CASE_LOWER);
		}
	}
	
	public static function setGetterNaturalCase(){
		if(config('database.default')==='oracle'){
			$dbh 			= \DB::connection()->getPdo();
			$dbh->setAttribute (\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
		}
	}
	
	
	public static function extractColumns($columns){
		$results = [];
		foreach($columns as $column ){
			if ($column&&isset($column->column_name)) {
				$results[] = $column->column_name;
			}
		}
		return $results;
	}
	
	public static function getConstantTextOverDbDriver($name){
		if (config('database.default')==='oracle') return '"'.$name.'"';
		return $name;
	}
	
	public static function getSubNameSelectQuery($name){
		if (config('database.default')==='oracle') return $name;
		return "as $name";
	}
	
	public static function getDataType($table,$field){
		$type	= null;
		if (config('database.default')==='oracle') {
			$data_type 	= \DB::table ('all_tab_columns')
							->where ( ['TABLE_NAME' => $table, 'COLUMN_NAME'=>$field] )
							->select ('DATA_TYPE')->first();
			if ($data_type) {
				$type 		= static::getInputType($data_type->data_type);
			}
		}
		else if (config('database.default')==='mysql') {
			$data_type 	= \DB::table ('INFORMATION_SCHEMA.COLUMNS')
							->where ( ['TABLE_NAME' => $table, 'COLUMN_NAME'=>$field] )
							->select ('DATA_TYPE')->first(); 
			if ($data_type) {
				$type 		= static::getInputType($data_type->DATA_TYPE); 
			}
		}
		return $type;
	}
	
	public static function getIdentifierColumn($column){
		return config('database.default')==='oracle'?$column."_":$column;
	}
	
	public static function removeInvalidCharacter($text){
		return config('database.default')==='oracle'||config('database.default')==='sqlsrv'?
				str_replace("`", "", $text):$text;
	}

	public static function getInputType($dataType){
		$dataType	= is_string($dataType)?strtolower($dataType):$dataType;
		switch($dataType){
			case "varchar":
			case "text":
			case "char":
				return 1;				//Text input
			case "int":
			case "decimal":
			case "tinyint":
			case "float":
				return 2;				//Number input
			case "date":
				return 3;				//Date picker
			case "time":
				return 4;				//Date picker
			case "datetime":
				return 4;				//Datetime picker
		}
	}
	
	public static function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
	
	public static function checkCTVavailabe($item){
		return is_array($item)&&array_key_exists("CTV", $item)&&($item["CTV"]==1||$item["CTV"]=="1"||$item["CTV"]==true||$item["CTV"]=="true");
	}
	
	public static function getGraphFilter($options = []){
		$filterGroups	= static :: getCommonGroupFilter($options);
		$item			= ['ID'=>0,'NAME'=>'None'];
		$objectName		= ["ObjectName" => ["default" => $item]];

        /*$level          = [ "name"			=> "DefermentLevel",
                            "getMethod"	    => "getLevel",
                            "filterName"	=> "Level"];*/
		$filterGroups['productionFilterGroup'][0]["extra"][] 		= $objectName;
		$filterGroups['productionFilterGroup'][1]["extra"][] 		= $objectName;
		$filterGroups['frequenceFilterGroup'][0]["default"] 		= $item;
		$filterGroups['frequenceFilterGroup'][0]["defaultEnable"] 	= true;

        $filterGroups['productionFilterGroup'][1]["dependences"][2] 		= "GraphObjectTypeProperty";

        $filterGroups['frequenceFilterGroup'][1]['dependences'] 	= ["GraphObjectTypeProperty"];
        $filterGroups['frequenceFilterGroup'][2]['name'] 	        = "GraphObjectTypeProperty";
		return $filterGroups;
	}
	
	public static function getCommonGroupFilter($options = []){
		$codeFlowPhase	= ["name"		=>	"CodeFlowPhase",
							"source"	=>	"ObjectName" ];
		$filterGroups = array(	'productionFilterGroup'	=> [['name'			=> 'CodeProductType',
															'independent'	=> true,
															"getMethod"		=> "loadActive",
															'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
															'dependences'	=> ["ObjectName",
																				$codeFlowPhase]],
															['name'			=> 'IntObjectType',
															'independent'	=> true,
															"getMethod"		=> "getGraphObjectType",
															'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
															'dependences'	=> ["ObjectName",
																				["name"		=>	"ObjectDataSource"],
																				"ObjectTypeProperty",
																				$codeFlowPhase
																				]
															]],
								'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																"defaultEnable"	=> false,
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","CodeProductType"]]],
																["name"			=> "ObjectDataSource",
																"getMethod"		=> "loadBy",
																"filterName"	=>	"Data source",
																'dependences'	=> ["ObjectTypeProperty"],
																'extra'			=> ["Facility","CodeProductType"],
																"source"		=> ['productionFilterGroup'=>["IntObjectType"]]],
																["name"			=> "ObjectTypeProperty",
																"getMethod"		=> "loadBy",
																"filterName"	=>	"Property",
																"source"		=>  ['frequenceFilterGroup'=>["ObjectDataSource"]]],
																["name"			=> "CodeFlowPhase",
																"getMethod"		=> "loadBy",
																"source"		=>  ['frequenceFilterGroup'=>["ObjectName"]]],
																						  
																						   
																											
																["name"			=>	"CodeEventType",
																"filterName"	=>	"Event type",
																"getMethod"		=> "loadActive"],
																["name"			=>	"CodeAllocType",
																"getMethod"		=> "loadActive",
																"filterName"	=>	"Alloc type",],
																["name"			=>	"CodePlanType",
																"getMethod"		=> "loadActive",
																"filterName"	=>	"Plan type",],
																["name"			=>	"CodeForecastType",
																"getMethod"		=> "loadActive",
																"filterName"	=> "Forecast type",]
														],
								'dateFilterGroup'		=> array(	['id'=>'date_begin','name'=>'From date'],
																	['id'=>'date_end',	'name'=>'To date']),
								'enableButton'			=> false,
								'FacilityDependentMore'	=> ["ObjectName"],
								'extra' 				=> ['IntObjectType','CodeProductType',"ObjectDataSource"]
		);
		
		return $filterGroups;
	}

    public static function getCommonGroupFilterExport($options = []){
        $filterGroups = array(
            'productionFilterGroup'	=> [
                [
                    'name'			=> 'CodeProductType',
                    'independent'	=> false,
                    "getMethod"		=> "loadActive",
                    'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
                    'dependences'	=> ["ObjectName"]
                ],
                [
                    'name'			=> 'IntObjectType',
                    'independent'	=> true,
                    "getMethod"		=> "getGraphObjectTypeExport",
                    'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
                    'dependences'	=> ["ObjectName",["name"		    =>	"ObjectDataSource"],"ObjectTypePropertyExportData"]
                ],
                [
                    "name"			=> "CodeDeferGroupType",
                    "filterName"	=> "Deferment Group"
//                    "default"	    => false
                ]
            ],
            'frequenceFilterGroup'	=> [
                [
                    "name"			=> "ObjectName",
                    //'default'	=> ['ID'=>0,'NAME'=>'All'],
                    "getMethod"		=> "loadBy",
                    "filterName"	=> "Flow",
                    "defaultEnable"	=> false,
                    "source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","CodeProductType"]]
                ],
                [
                    "name"			=> "ObjectDataSource",
                    "getMethod"		=> "loadBy",
                    "filterName"	=>	"Data source",
                    'dependences'	=> ["ObjectTypePropertyExportData"],
                    'extra'			=> ["Facility","CodeProductType"],
                    "source"		=> ['productionFilterGroup'=>["IntObjectType"]]
                ],
                [
                    "name"			=> "ObjectTypePropertyExportData",
                    //'default'	=> ['ID'=>0,'NAME'=>'All'],
                    "getMethod"		=> "loadBy",
                    "filterName"	=>	"Property",
                    "source"		=>  ['frequenceFilterGroup'=>["ObjectDataSource"]]
                ],
				[
					"name"			=> "Keystore",
					//'default'	=> ['ID'=>0,'NAME'=>'All'],
					"filterName"	=>	"Keystore",
					"source"		=>  ['frequenceFilterGroup'=>["ObjectDataSource"]]
				],
                [
                    "name"			=> "CodeFlowPhase",
                    'default'	    =>  false,
                    "getMethod"		=> "loadActive",
                    "filterName"	=> "Flow phase"
                    //"source"		=> ['frequenceFilterGroup'=>["ObjectName"]]
                ],
                [
                    "name"			=>	"CodeEventType",
                    'default'	    =>  false,
                    "getMethod"		=>  "loadActive",
                    "filterName"	=>	"Event type",
                ],
                [
                    "name"			=>	"CodeAllocType",
                    'default'	    =>  false,
                    "getMethod"		=>  "loadActive",
                    "filterName"	=>	"Allocation type",
                ],
                [
                    "name"			=>	"CodeTestingMethod",
                    'default'	    =>  false,
                    "getMethod"		=>  "loadActive",
                    "filterName"	=>	"Testing Method",
                ],
                [
                    "name"			=>	"CodePlanType",
                    'default'	    =>  false,
                    "getMethod"		=>  "loadActive",
                    "filterName"	=>	"Plan Type",
                ]
            ],
            'dateFilterGroup'		=> array(
                                                ['id'=>'date_begin','name'=>'From date'],
                                                ['id'=>'date_end',	'name'=>'To date']
            ),
            'enableButton'			=> false,
            'FacilityDependentMore'	=> ["ObjectName"],
            'extra' 				=> ['IntObjectType',"ObjectDataSource"]
        );

        return $filterGroups;
    }

	static function checkSubMenu(&$smenu, $isAll, &$rights, &$lang) {
		foreach($smenu as  $cindex => $menuItem ){
			if(isset($menuItem["text"])) {
				$right = isset($menuItem["right"]) ? $menuItem["right"] : '';
				if($isAll || in_array($right, $rights) || $right == "PUBLIC"){
					$menuItem["text"]	= Lang::has("front/site.".$menuItem["text"], $lang)? trans("front/site.".$menuItem["text"]):$menuItem["text"];
					$menuItem["desc"]	= Lang::has("front/site.".$menuItem["desc"], $lang)? trans("front/site.".$menuItem["desc"]):$menuItem["desc"];
					$smenu[$cindex]	= $menuItem;
				}else{
					unset($smenu[$cindex]);
				}
			}
			else if(is_array($menuItem)) {
				self::checkSubMenu($menuItem, $isAll, $rights, $lang);
			}
		}
	}

	public static function getUserMenu(){
        if((auth()->user() != null)){
            $rights = auth()->user()->role();
        }else{
            return [];
        }

		$xmenu = [
			[
				"title" => "Production",
				"groups" =>
				[
					[
						"title" => "Production management",
						"items" =>
						[
							["title" => "Flow Data Capture",			"code" => "dc/flow",		"right" => "FDC_FLOW"],
							["title" => "Energy Unit Data Capture",		"code" => "dc/eu",			"right" => "FDC_EU"],
							["title" => "Tank & Storage",				"code" => "dc/storage",		"right" => "FDC_STORAGE"],
							["title" => "Tank Ticket",					"code" => "dc/ticket",		"right" => "FDC_TICKET"],
							["title" => "Well Test",					"code" => "dc/eutest",		"right" => "FDC_EU_TEST"],
							["title" => "Deferment & MMR",				"code" => "dc/deferment",	"right" => "FDC_DEFER"],
							["title" => "Quality Data",					"code" => "dc/quality",		"right" => "FDC_QUALITY"],
							["title" => "Well Status",					"code" => "dc/wellstatus",	"right" => "FDC_WELL_STATUS"],
							["title" => "Scssv",						"code" => "dc/scssv",		"right" => "FDC_SCSSV"],
							["title" => "Sub-Daily",					"code" => "dc/subdaily",	"right" => "FDC_FLOW"],
						]
					],
					[
						"title" => "Field Operations",
						"items" =>
						[
							["title" => "Safety", 			"code" => "fo/safety", 		"right" => "FOP_SAFETY"],
							["title" => "Comments", 		"code" => "fo/comment", 	"right" => "FOP_COMMENT"],
							["title" => "Environmental", 	"code" => "fo/env", 		"right" => "FOP_ENV"],
							["title" => "Equipment", 		"code" => "fo/equipment", 	"right" => "FOP_EQUIP"],
							["title" => "Chemical", 		"code" => "fo/chemical", 	"right" => "FOP_CHEMICAL"],
							["title" => "Personnel", 		"code" => "fo/personnel", 	"right" => "FOP_PERSONNEL"],
							["title" => "Hourly Allocation", 		"code" => "fo/logistic", 	"right" => "FOP_LOGISTIC"],
						]
					],
					[
						"title" => "Allocation",
						"items" =>
						[
							["title" => "Run Allocation", "code" => "allocrun", "right" => "ALLOC_RUN"],
						]
					],
				]
			],
			[
				"title" => "Data Visualization",
				"groups" =>
				[
					[
						"title" => "Data Visualization",
						"items" =>
						[
							["title" => "Diagram", 			"code" => "dv-diagram", 		"right" => "VIS_NETWORK_MODEL"],
							["title" => "Data Views", 		"code" => "dataview", 			"right" => "VIS_DATA_VIEW"],
							["title" => "Reports", 			"code" => "workreport",			"right" => "VIS_REPORT"],
							["title" => "Advanced Graph", 	"code" => "dv-graph", 				"right" => "VIS_ADVGRAPH"],
							["title" => "Workflow", 		"code" => "workflow", 			"right" => "VIS_WORKFLOW"],
							["title" => 'Dashboard',		"code" => "dv-dashboard", 		"right" => "VIS_DASHBOARD"],
							["title" => "Choke Model", 		"code" => "fp/choke", 			"right" => "VIS_CHOKE_MODEL"],
							["title" => "Task Manager", 	"code" => "dv/taskman",			"right" => "VIS_TASKMAN"],
							["title" => "Storage Display", 	"code" => "pd/storagedisplay",	"right" => "VIS_STORAGE_DISPLAY"],
						]
					],
				]
			],
			[
				"title" => "PRODUCT DELIVERY",
				"groups" =>
				[
					[
						"title" => "Contract Admin",
						"items" =>
						[
							["title" => "Contract Data", 		"code" => "pd/contractdata", 		"right" => "PD_CONTRACT_ADMIN"],
							["title" => "Contract Calculation",	"code" => "pd/contractcalculate", 	"right" => "PD_CONTRACT_ADMIN"],
							["title" => "Contract Template", 	"code" => "pd/contracttemplate", 	"right" => "PD_CONTRACT_ADMIN"],
							["title" => "Cargo Program", 		"code" => "pd/contractprogram", 	"right" => "PD_CONTRACT_ADMIN"],
						]
					],
					[
						"title" => "Cargo Admin",
						"items" =>
						[
							["title" => "Cargo Entry", 			"code" => "pd/cargoentry", 			"right" => "PD_CARGO_ADMIN"],
							["title" => "Cargo Nomination",		"code" => "pd/cargonomination", 	"right" => "PD_CARGO_ADMIN"],
							["title" => "Cargo Schedule", 		"code" => "pd/cargoschedule", 		"right" => "PD_CARGO_ADMIN"],
						]
					],
					[
						"title" => "Cargo Action",
						"items" =>
						[
							["title" => "Cargo Voyage", 	"code" => "pd/cargovoyage", 	"right" => "PD_CARGO_ACTION"],
							["title" => "Cargo Load Activities", 		"code" => "pd/cargoload", 		"right" => "PD_CARGO_ACTION"],
							["title" => "Cargo Unload Activities", 	"code" => "pd/cargounload", 	"right" => "PD_CARGO_ACTION"],
							["title" =>	"Cargo Measurement","code" => "pd-cargomeasurement","right" => "PD_CARGO_ACTION"],
							["title" =>	"Transit Carrier VEF","code" => "pd-vef","right" => "PD_CARGO_ACTION"],
							["title" => "Voyage Marine", 	"code" => "pd/voyagemarine", 	"right" => "PD_CARGO_ACTION"],
							["title" => "Voyage Ground", 	"code" => "pd/voyageground", 	"right" => "PD_CARGO_ACTION"],
							["title" => "Voyage Pipeline", 	"code" => "pd/voyagepipeline", 	"right" => "PD_CARGO_ACTION"],
							["title" => "BL/MR", 			"code" => "pd/shipblmr", 		"right" => "PD_CARGO_ACTION"],
						]
					],
					[
						"title" => "Cargo Management",
						"items" =>
						[
							["title" => "Demurrage/EBO", 	"code" => "pd/demurrageebo", 	"right" => "PD_CARGO_MAN"],
							["title" => "Cargo Documents", 	"code" => "pd/cargodocuments", 	"right" => "PD_CARGO_MAN"],
							["title" => "Cargo Ledger", 	"code" => "pd/cargostatus", 	"right" => "PD_CARGO_MAN"],
						]
					],
					[
						"title" => "Cargo Monitoring",
						"items" =>
						[
							["title" => "Lifting Acct Daily Balance", 	"code" => "pd/liftaccdailybalance", 	"right" => "PD_CARGO_MON"],
							["title" => "Lifting Acct Monthly Data", 	"code" => "pd/liftaccmonthlyadjust", 	"right" => "PD_CARGO_MON"],
							["title" => "Cargo Planning", 				"code" => "pd/cargoplanning", 			"right" => "PD_CARGO_MON"],
						]
					],
				]
			],
			[
				"title" => "GHG",
				"groups" =>
				[
					[
						"title" => "Emission Sources",
						"items" =>
						[
							["title" => "Combustion", "code" => "ghg/es/combustion", "right" => "GHG_EMIS_SRC"],
							["title" => "Indirect", "code" => "ghg/es/indirect", "right" => "GHG_EMIS_SRC"],
							["title" => "Events", "code" => "ghg/es/events", "right" => "GHG_EMIS_SRC"],
						]
					],
					[
						"title" => "Emission Entry",
						"items" =>
						[
							["title" => "Combustion", "code" => "ghg/ee/combustion", "right" => "GHG_EMIS_ENTRY"],
							["title" => "Indirect", "code" => "ghg/ee/indirect", "right" => "GHG_EMIS_ENTRY"],
							["title" => "Events", "code" => "ghg/ee/events", "right" => "GHG_EMIS_ENTRY"],
						]
					],
					[
						"title" => "Emission Release",
						"items" =>
						[
							["title" => "Combustion", "code" => "ghg/er/combustion", "right" => "GHG_EMIS_REL"],
							["title" => "Indirect", "code" => "ghg/er/indirect", "right" => "GHG_EMIS_REL"],
							["title" => "Events", "code" => "ghg/er/events", "right" => "GHG_EMIS_REL"],
						]
					],
				]
			],
			[
				"title" => "ADMIN",
				"groups" =>
				[
					[
						"title" => "Transaction Data",
						"items" =>
						[
							["title" => "Validate Data", "code" => "am/validatedata", "right" => "ADMIN_VALIDATE"],
							["title" => "Approve Data", "code" => "am/approvedata", "right" => "ADMIN_APPROVE"],
							["title" => "Data Locking", "code" => "am/lockdata", "right" => "ADMIN_DATA_LOCKING"],
						]
					],
					[
						"title" => "Administrator",
						"items" =>
						[
							["title" => "Users Management", "code" => "am/users", "right" => "ADMIN_USERS"],
							["title" => "Roles Settings", "code" => "am/roles", "right" => "ADMIN_ROLES"],
							["title" => "User Logs", "code" => "am/userlog", "right" => "ADMIN_USER_LOG"],
							["title" => "Audit Trail", "code" => "am/audittrail", "right" => "ADMIN_AUDIT"],
							["title" => "Password & Preferences", "code" => "me/setting", "right" => "PUBLIC"], // Public Right
							["title" => "Help Editor", "code" => "am/helpeditor", "right" => "ADMIN_HELP_EDITOR"],
						]
					],
				]
			],
			[
				"title" => "CONFIG",
				"groups" =>
				[
					[
						"title" => "System Configuration",
						"items" =>
						[
							["title" => "Fields Config", "code" => "fieldsconfig", "right" => "CONFIG_FIELDS"],
							["title" => "Table Data", "code" => "loadtabledata", "right" => "CONFIG_TABLE_DATA"],
							["title" => "TAG MAPPING CONFIG", "code" => "tagsMapping", "right" => "CONFIG_TAGS_MAPPING"],
							["title" => "View Config", "code" => "viewconfig", "right" => "CF_VIEW_CONFIG"],
							["title" => "Formula Editor", "code" => "formula", "right" => "CONFIG_FORMULA"],
							["title"=> "Objects Manager", "code" => "objectsmanager", "right" => "OBJECT_MANAGER"],
							["title" => "Routes Config", "code" => "routeconfig", "right" => "CONFIG_TAGS_MAPPING"],
						]
					],
					[
						"title" => "Interface",
						"items" =>
						[
							["title" => "Source Config", "code" => "sourceconfig", "right" => "INT_SOURCE_CONFIG"],
							["title" => "Export Data", "code" => "exportdata", "right" => "INT_EXPORT_DATA"],
							["title" => "Import Data", "code" => "importdata", "right" => "INT_IMPORT_DATA"],
							["title" => "Data Loader", "code" => "dataloader", "right" => "INT_DATA_LOADER"],
							["title" => "Config Dashboard", "code" => "cf-dashboard", "right" => "CF_DASHBOARD_CONFIG"],
						]
					],
					[
						"title" => "Allocation",
						"items" =>
						[
							["title" => "Config Allocation", "code" => "allocset", "right" => "ALLOC_CONFIG"],
						]
					],
				]
			],
			[
				"title" => "Forecast & Planning",
				"groups" =>
				[
					[
						"title" => "Forecast & Planning",
						"items" =>
						[
							["title" => "Well Forecast", "code" => "fp/forecast", "right" => "FP_WELLFORECAST"],
							["title" => "PREoS", "code" => "fp/preos", "right" => "FP_PREOS"],
							["title" => "Manual Allocate Plan", "code" => "fp/allocateplan", "right" => "FP_ALLOCATE_PLAN"],
							["title" => "Manual Allocate Forecast", "code" => "fp/allocateforecast", "right" => "FP_ALLOCATE_PLAN"],
							["title" => "Load Plan/Forecast", "code" => "fp/loadplanforecast", "right" => "FP_LOAD_PLAN_FORECAST"],
						]
					],
				]
			],
		];
		
		$lang			= session()->get('locale', "en");
		$isAll			= in_array("_ALL_", $rights);
		$userMenu = [];

		foreach($xmenu as $m => $menu){
			$groups = [];
			foreach($menu["groups"] as $g => $group){
				$items = [];
				foreach($group["items"] as $i => $item){
					$right = isset($item["right"]) ? $item["right"] : '';
					if ($isAll || in_array($right, $rights) || $right == "PUBLIC"){
						$item["title"] = static::translateText($lang, $item["title"]);
						$items[] = $item;
					}
				}
				if(count($items))
					$groups[] = ["items" => $items, "title" => static::translateText($lang, $group["title"])];
			}
			if(count($groups))
				$userMenu[] = ["groups" => $groups, "title" => static::translateText($lang, $menu["title"])];
		}

		//$xmenu = array_values($xmenu);
		return $userMenu;
	}


	public static function generateHomeMenu(){

        if((auth()->user() != null)){
            $rights = auth()->user()->role();
        }else{
            $rights= [];
        }

        $xmenu["production"]=[
            "text"  =>"Production Management",
            "display" => 1,
            "className" => ["hex-1","hex-gap"],
            "sub" =>[
				[
					["text"  =>"Flow Stream","desc" => "","url" => "/dc/flow","right" => "FDC_FLOW"],
					["text"  =>"Sub-Daily","desc" => "","url" => "/dc/subdaily","right" => "FDC_FLOW"],
				],
                ["text"  =>"Energy Unit","desc" => "","url" => "/dc/eu", "right" => "FDC_EU"],
                ["text"  =>"Tank & Storage","desc" => "","url" => "/dc/storage", "right" => "FDC_STORAGE"],
                ["text"  =>"Ticket","desc" => "","url" => "/dc/ticket", "right" => "FDC_TICKET"],
                ["text"  =>"Well Test","desc" => "","url" => "/dc/eutest", "right" => "FDC_EU_TEST"],
                ["text"  =>"Deferment & MMR","desc" => "","url" => "/dc/deferment", "right" => "FDC_DEFER"],
                ["text"  =>"Quality","desc" => "","url" => "/dc/quality", "right" => "FDC_QUALITY"],
                ["text"  =>"Well Status","desc" => "","url" => "/dc/wellstatus", "right" => "FDC_WELL_STATUS"],
                ["text"  =>"Scssv","desc" => "","url" => "/dc/scssv", "right" => "FDC_SCSSV"],
            ]
        ];
        $xmenu["operation"]=[
            "text"  =>"Field Operations",
            "display" => 1,
            "className" => ["hex-2"],
            "sub" =>[
                ["text"  =>"Safety","desc" => "","url" => "/fo/safety","right" => "FOP_SAFETY"],
                ["text"  =>"Comments","desc" => "","url" => "/fo/comment","right" => "FOP_COMMENT"],
                ["text"  =>"Equipment","desc" => "","url" => "/fo/equipment","right" => "FOP_EQUIP"],
                ["text"  =>"Chemical","desc" => "","url" => "/fo/chemical","right" => "FOP_CHEMICAL"],
                ["text"  =>"Personnel","desc" => "","url" => "/fo/personnel","right" => "FOP_PERSONNEL"],
                ["text"  =>"Hourly Allocation","desc" => "","url" => "/fo/logistic","right" => "FOP_LOGISTIC"],
                ["text"  =>"Environmental","desc" => "","url" => "/fo/env","right" => "FOP_ENV"],
            ]
        ];
        $xmenu["visual"]=[
            "text"  =>"Data Visualization",
            "display" => 1,
            "className" => ["hex-1","hex-gap"],
            "sub" =>[
                ["text"  =>"Network Model","desc" => "","url" => "/diagram","right" => "VIS_NETWORK_MODEL"],
                ["text"  =>"Data View","desc" => "","url" => "/dataview","right" => "VIS_DATA_VIEW"],
                ["text"  =>"Reports","desc" => "","url" => "/workreport","right" => "VIS_REPORT"],
                ["text"  =>"Advanced Graph","desc" => "","url" => "/graph","right" => "VIS_ADVGRAPH"],
                ["text"  =>"Workflow","desc" => "","url" => "/workflow","right" => "VIS_WORKFLOW"],
                ["text"  =>"Choke Model","desc" => "","url" => "/fp/choke","right" => "VIS_CHOKE_MODEL"],
                ["text"  =>'Dashboard',"desc"=>"","url" => "/dashboard","right" => "VIS_DASHBOARD"],
                ["text"  =>"Task Manager","desc" => "","url" => "/dv/taskman","right" => "VIS_TASKMAN"],
                ["text"  =>"Storage Display","desc" => "","url" => "/pd/storagedisplay","right" => "VIS_STORAGE_DISPLAY"],
            ]
        ];
        $xmenu["allocation"]=[
            "text"  =>"Allocation",
            "display" => 1,
            "className" => ["hex-2"],
            "sub" =>[
                ["text"  =>"Run Allocation","desc" => "","url" => "/allocrun","right" => "ALLOC_RUN"],
                ["text"  =>"Config Allocation","desc" => "","url" => "/allocset","right" => "ALLOC_CONFIG"]
            ]
        ];
        $xmenu["forecast"]=[
            "text"  =>"Forecast & Planning",
            "display" => 1,
            "className" => ["hex-1","hex-gap"],
            "sub" =>[
                ["text"  =>"WELL FORECAST","desc" => "","url" => "/fp/forecast","right" => "FP_WELLFORECAST"],
                ["text"  =>"PREoS","desc" => "","url" => "../fp/preos","right" => "FP_PREOS"],
                ["text"  =>"MANUAL ALLOCATE PLAN","desc" => "","url" => "/fp/allocateplan","right" => "FP_ALLOCATE_PLAN"],
                ["text"  =>"MANUAL ALLOCATE FORECAST","desc" => "","url" => "/fp/allocateforecast","right" => "FP_ALLOCATE_PLAN"],
                ["text"  =>"LOAD PLAN/FORECAST","desc" => "","url" => "/fp/loadplanforecast","right" => "FP_LOAD_PLAN_FORECAST"],
            ]
        ];
        $xmenu["delivery"]=[
            "text"  =>"Product Delivery",
            "display" => 1,
            "className" => ["hex-1"],
            "sub" =>[
                ["text"  =>"CONTRACT ADMIN","desc" => "","url" => "/pd/contractdata","right" => "PD_CONTRACT_ADMIN_DATA"],
                ["text"  =>"CARGO ADMIN","desc" => "","url" => "/pd/cargoentry","right" => "PD_CONTRACT_ADMIN_CALC"],
                ["text"  =>"CARGO ACTION","desc" => "","url" => "/pd/cargovoyage","right" => "PD_CONTRACT_ADMIN_TEMP"],
                ["text"  =>"CARGO MANAGEMENT","desc" => "","url" => "/pd/demurrageebo","right" => "PD_CONTRACT_ADMIN_PROG"],
                ["text"  =>"CARGO MONITORING","desc" => "","url" => "/pd/liftaccdailybalance","right" => "PD_CARGO_MON_DAILY_BAL"],
            ]
        ];
        $xmenu["greenhouse"]=[
            "text"  =>"Greenhouse Gas",
            "display" => 1,
            "className" => ["hex-2"],
            "sub" =>[
                ["text"  =>"EMISSION SOURCES","desc" => "","url" => "../ghg/index.php/emission","right" => "PD_CARGO_ADMIN_ENTRY"],
                ["text"  =>"EMISSION ENTRY","desc" => "","url" => "../ghg/index.php/emissionEntry","right" => "PD_CARGO_ADMIN_ENTRY"],
                ["text"  =>"EMISSION RELEASED","desc" => "","url" => "../ghg/index.php/emissionReleased","right" => "PD_CARGO_ADMIN_ENTRY"],
                ["text"  =>"EMISSION ALLOCATION","desc" => "","url" => "../ghg/index.php/emissionAllocation","right" => ""],
                ["text"  =>"EMISSION REPORT","desc" => "","url" => "../ghg/index.php/emissionReport","right" => ""]
            ]
        ];
        $xmenu["admin"]=[
            "text"  =>"Administrator",
            "display" => 1,
            "className" => ["hex-1","hex-gap"],
            "sub" =>[
                ["text"  =>"VALIDATE DATA","desc" => "","url" => "/am/validatedata","right" => "ADMIN_VALIDATE"],
                ["text"  =>"APPROVE DATA","desc" => "","url" => "/am/approvedata","right" => "ADMIN_APPROVE"],
                ["text"  =>"LOCK DATA","desc" => "","url" => "/am/lockdata","right" => "ADMIN_DATA_LOCKING"],
                ["text"  =>"ROLES","desc" => "","url" => "/am/roles","right" => "ADMIN_ROLES"],
                ["text"  =>"USERS","desc" => "","url" => "/am/users","right" => "ADMIN_USERS"],
                ["text"  =>"Audit Trail","desc" => "","url" => "/am/audittrail","right" => "ADMIN_AUDIT"],
                ["text"  =>"USERS LOG","desc" => "","url" => "/am/userlog", "right" => "ADMIN_USER_LOG"],
                ["text"  =>"HELP EDITOR","desc" => "","url" => "/am/helpeditor", "right" => "ADMIN_HELP_EDITOR"],
                ["text"  =>"PASSWORD & PREFERENCES","desc" => "","url" => "/me/setting", "right" => "PUBLIC"]
            ]
        ];
        $xmenu["config"]=[
            "text"  =>"System Configuration",
            "display" => 1,
            "className" => ["hex-2"],
            "sub" =>[
                ["text"  =>"Fields Config","desc" => "","url" => "/fieldsconfig","right" => "CONFIG_FIELDS"],
                ["text"  =>"Tables Data","desc" => "","url" => "/loadtabledata","right" => "CONFIG_TABLE_DATA"],
                ["text"  =>"Tags Mapping","desc" => "","url" => "/tagsMapping","right" => "CONFIG_TAGS_MAPPING"],
                ["text"  =>"Formula Editor","desc" => "","url" => "/formula","right" => "CONFIG_FORMULA"],
                ["text"  =>"View Config","desc" => "","url" => "/viewconfig","right" => "CF_VIEW_CONFIG"],
                ["text"  =>"Dashboard Config","desc" => "","url" => "/config/dashboard","right" => "CF_DASHBOARD_CONFIG"],
                ["text"  =>"Objects Manager","desc" => "","url" => "/objectsmanager","right" => "OBJECT_MANAGER"],
                ["text"  =>"Routes Config","desc" => "","url" => "/routeconfig","right" => "CONFIG_TAGS_MAPPING"],
            ]
        ];
        $xmenu["interfaces"]=[
            "text"  =>"Interface",
            "display" => 1,
            "sub" =>[
                ["text"  =>"EXPORT DATA","desc" => "Export Tags Spreadsheet","url" => "/exportdata","right" => "INT_EXPORT_DATA"],
                ["text"  =>"IMPORT DATA","desc" => "Import Tags Spreadsheet","url" => "/importdata","right" => "INT_IMPORT_DATA"],
                ["text"  =>"SOURCE CONFIG","desc" => "","url" => "/sourceconfig","right" => "INT_SOURCE_CONFIG"],
                ["text"  =>"OPENSIM RESERVOIR SIMULATION","desc" => "","url" => "","right" => "INT_SOURCE_CONFIG"],
                ["text"  =>"DATA LOADER","desc" => "","url" => "/dataloader","right" => "INT_DATA_LOADER"]
            ]
        ];


        $lang = session()->get('locale', "en");
        $isAll	= in_array("_ALL_", $rights);

        foreach($xmenu as $index => $object ){
            $smenu = $object["sub"];
			self::checkSubMenu($smenu, $isAll, $rights, $lang);

            if (!$isAll) $smenu = array_values($smenu);
            if (count($smenu) > 0){
                $xmenu[$index]["sub"] = $smenu;
                $xmenu[$index]["text"] = \Helper::translateText($lang,$xmenu[$index]["text"]);
            }else{
                unset($xmenu[$index]);
            }
        }
        $xmenu = array_values($xmenu);
        return $xmenu;
    }

    public static function ebFunctionParent ($data, $parent = NULL, $str='--') {
        foreach ($data as $key => $val) {
            $name = $val['NAME'];
            $str2 = "/";
            $code = $val['CODE'];
            $parent_code = $val['PARENT_CODE'];
            if ($parent_code == $parent) {
                echo "<option value='$code'>$str $str2 $name</option>";
                \Helper::ebFunctionParent($data,$code,$str.' --');
            }
        }
    }
    
    public static function sendEmail ($emails,$subjectName,$data,$template = 'front.email.default',$attachedFiles = null) {
    	foreach ($emails as $index=>$aEmail){
    		if (!filter_var($aEmail, FILTER_VALIDATE_EMAIL)) unset ($emails[$index]);
    	}
    	$result = "UNSENT";
	   	if (count($emails)>0) {
	   		try{
				if(!view()->exists($template)){
					$template = 'front.email.default';
					if(!array_key_exists('content',$data))
						$data['content']='(No content)';
				}				
	   			$ret = Mail::send($template,$data, function ($message) use ( $subjectName, $emails, $attachedFiles) {
					$mailFrom = config('mail.from.address');
					$mailFromName = config('mail.from.name');
	   				$message->from($mailFrom, $mailFromName);
	   				$message->to($emails);
	   				$message->subject($subjectName);
					if($attachedFiles){
						if(is_array($attachedFiles)){
							foreach($attachedFiles as $file) 
								if($file)
									$message->attach($file);
						}
						else
							$message->attach($attachedFiles);
					}
	   			});
   				$result =  $ret > 0?"\n<br/>Email sent successfully to $ret recipients":"\n<br/>Sending email return code ".$ret;
   				
	   		}catch (\Exception $e){
	   			\Log::error($e->getMessage());
	   			$result = "\n<br/>Error exception when sending email";
	   		}
	   	}
	   	else 
	   		\Log::info("empty to emails or email invalid");
	   		
	   	\Log::info($result);
	   	return $result;
    }
    
   public static function getRoundDatetimeFunction($datetimeColumn, $sampleInterval,$testMode= true) {
	   	if ($sampleInterval<=30000) return $datetimeColumn;
	   	if ($sampleInterval<3600000){
		   	$minuteInterval	= $sampleInterval/60000;
		   	if ($testMode) return " DATEADD(minute, DATEDIFF(minute, DATEDIFF(minute, 0, $datetimeColumn) % $minuteInterval , $datetimeColumn), 0) ";
		   	return " EXTRACT(year FROM $datetimeColumn)||'-'
		   			||EXTRACT(month FROM $datetimeColumn)||'-'
		   			||EXTRACT(day FROM $datetimeColumn)||' '
		   			||EXTRACT(hour FROM $datetimeColumn)||':'
		   			||EXTRACT(minute FROM $datetimeColumn)/$minuteInterval*$minuteInterval||':00' ";
		   	
	   	}
	   	else if ($sampleInterval<43200000){
	   		$hourInterval	= $sampleInterval/3600000;
	   		if ($testMode) return " DATEADD(hour, DATEDIFF(hour, DATEDIFF(hour, 0, $datetimeColumn) % $hourInterval , $datetimeColumn), 0) ";
	   		return " EXTRACT(year FROM $datetimeColumn)||'-'
		   			||EXTRACT(month FROM $datetimeColumn)||'-'
		   			||EXTRACT(day FROM $datetimeColumn)||' '
		   			||EXTRACT(hour FROM $datetimeColumn)/$hourInterval*$hourInterval||':00:00' ";
	   	}
		else{
			if ($testMode) return " DATEADD(Day, DATEDIFF(Day, 0, $datetimeColumn), 0) ";
			return " EXTRACT(year FROM $datetimeColumn)||'-'
					||EXTRACT(month FROM $datetimeColumn)||'-'
					||EXTRACT(day FROM $datetimeColumn)||' 00:00:00' ";
		}
	}
    
    public static function getIntervals($timebaseArray) {
    	$tCollection			= new Collection;
    	foreach($timebaseArray as $key => $interval ){
    		list($value,$text) 	= \Helper::dateDiff("now", "now +$interval");
    		$tCollection->push((object)['ID' =>	$value,'NAME' => $text]);
    	}
    	return $tCollection;
    }
    // Time format is UNIX timestamp or
    // PHP strtotime compatible strings
   public static function dateDiff($time1, $time2, $precision = 6) {
    	// If not numeric then convert texts to unix timestamps
    	if (!is_int($time1)) {
    		$time1 = strtotime($time1);
    	}
    	if (!is_int($time2)) {
    		$time2 = strtotime($time2);
    	}
    
    	// If time1 is bigger than time2
    	// Then swap time1 and time2
    	if ($time1 > $time2) {
    		$ttime = $time1;
    		$time1 = $time2;
    		$time2 = $ttime;
    	}
    
    	// Set up intervals and diffs arrays
    	$baseDay = 24*60*60*1000;
    	$intervals = array('year','month','week','day','hour','minute','second');
    	$diffValues = array('year'	=> 365*$baseDay, 'month' =>30*$baseDay, 'week' =>7*$baseDay,'day'=>$baseDay,'hour'=>60*60*1000,'minute'=>60*1000,'second'=>1000);
    	$diffs = array();
    	$diffValue	= 0;
    	// Loop thru all intervals
    	foreach ($intervals as $interval) {
    		// Create temp time from time1 and interval
    		$ttime = strtotime('+1 ' . $interval, $time1);
    		// Set initial values
    		$add = 1;
    		$looped = 0;
    		// Loop until temp time is smaller than time2
    		while ($time2 >= $ttime) {
    			// Create new temp time from time1 and interval
    			$add++;
    			$ttime = strtotime("+" . $add . " " . $interval, $time1);
    			$looped++;
    		}
    
    		$time1 = strtotime("+" . $looped . " " . $interval, $time1);
    		$diffs[$interval] = $looped;
    	}
    
    	$count = 0;
    	$times = array();
    	// Loop thru all diffs
    	foreach ($diffs as $interval => $value) {
    		// Break if we have needed precission
    		if ($count >= $precision) {
    			break;
    		}
    		// Add value and interval
    		// if value is bigger than 0
    		$unit	= $interval;
    		if ($value > 0) {
    			// Add s if value is not 1
    			if ($value != 1) {
    				$interval .= "s";
    			}
    			// Add value and interval to times array
    			$times[] = $value . " " . $interval;
    			$count++;
    		}
    		$diffValue+=$value*$diffValues[$unit];
    	}
    
    	// Return string with times
    	return [$diffValue, implode(", ", $times)];
    }
    
    public static function decryptLicenseKey($key){
    	if(!$key||strlen($key)!=32) return false;
    	$map_r = ['1' => '0', 'a' => '1', '2' => '2', 'b' => '3', '3' => '4', 'c' => '5', '4' => '6', 'd' => '7', '5' => '8', 'e' => '9'];
    	$n = hexdec($key[0]);
    	$date = substr($key, $n + 1, 8);
    	$s = "";
    	for($i=0; $i<8; $i++){
//    		$k = $map_r[$date[$i]] - $n;
            $k = $map_r[$date[$i]] - $n - ($i + 3);
    		while($k < 0) $k += 10;
    		$s .= $k;
    	}
    	return substr($s, 0, 4).'-'.substr($s, 4, 2).'-'.substr($s, 6);
    }
    
    public static function getExpireDate($key=null)
    {
    	$expiredDate 	= null;
    	if ($key) {
    		try {
    			$dateString 	= \Helper::decryptLicenseKey($key);
    			if ($dateString){
    				$expiredDate 	= \Carbon\Carbon::parse($dateString);
    				if ($expiredDate) {
    					$year = $expiredDate->year;
    					$expiredDate = $year>=2000&&$year<2099?$expiredDate:null;
    				}
    			}
    		} catch (\Exception $e) {
    			if (!$e) $e = new \Exception("can not get date from license key");
    			\Log::info($e->getMessage());
    			\Log::info($e->getTraceAsString());
    			return null;
    		}
    		return $expiredDate;
    	}
    	return $expiredDate;
    }
}