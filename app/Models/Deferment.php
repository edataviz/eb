<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 
use App\Trail\RelationDynamicModel;
use App\Models\EnergyUnit;
use App\Models\DefermentGroup;
use Carbon\Carbon;

class Deferment extends EbBussinessModel { 
    public function __construct(array $attributes = []) {
    	parent::__construct();
    }

	use RelationDynamicModel {
		RelationDynamicModel::getForeignColumn as public getBaseForeignColumn;
	}
	
	public function CodeDeferTheorMethod(){
		return $this->belongsTo('App\Models\CodeDeferTheorMethod',"THEOR_METHOD", "ID");
	}
		
	protected $table 	= 'DEFERMENT';
// 	protected $dates 	= ['END_TIME','BEGIN_TIME'];
	public  static  $idField = 'ID';
// 	public  static  $unguarded = true;
	public  static  $dateField = 'BEGIN_TIME';
	
	public  static  $relateColumns = ['id'	=> "DEFER_TARGET",'type'	=> "DEFER_GROUP_TYPE"];
	
	public  static  $dependenceColumns = [
			"CODE2" 			=>["COLUMN"	=> "CODE1",			"MODEL"	=> "CodeDeferCode2" ],
			"CODE3" 			=>["COLUMN"	=> "CODE2",			"MODEL"	=> "CodeDeferCode3" ],
			"DEFER_REASON2" 	=>["COLUMN"	=> "DEFER_REASON",	"MODEL"	=> "CodeDeferReason2" ],
			"THEOR_METHOD_TYPE" =>["COLUMN"	=> "THEOR_METHOD",	"MODEL"	=> "CodeDeferTheorMethod" ],
	];
	public  static  $relationStatusField = 'DEFER_GROUP_TYPE';
	
	public static function getObjectTypeCode() {
		return "DEFER_TARGET";
	}
	public static function addExtraQueryCondition(&$where,$object,$objectType){
		$where['DEFER_GROUP_TYPE'] = $objectType;
	}
	
	public static function getTagetColumns($columnName,$sourceIdColumn){
		switch ($columnName) {
			case "CODE1":
				return ["CODE1" => "CODE2", "CODE2"	=>"CODE3"];
				break;
			case "CODE2":
				return ["CODE2"	=> "CODE3"];
				break;
			case "DEFER_REASON":
				return ["DEFER_REASON"	=> "DEFER_REASON2"];
				break;
			case "DEFER_GROUP_TYPE":
				return ["DEFER_GROUP_TYPE"	=> "DEFER_TARGET"];
				break;
			case "THEOR_METHOD":
				return ["THEOR_METHOD"	=> "THEOR_METHOD_TYPE"];
				break;
		}
		return [];
	}
	
	public static function getTargetSourceColumn($columnName,$row){
		switch ($columnName) {
			case "CODE1":
				return "CODE2";
				break;
			case "CODE2":
				return "CODE3";
			case "DEFER_REASON":
				return "DEFER_REASON2";
				break;
			case "DEFER_TARGET":
				return "DEFER_TARGET";
				break;
		}
		return null;
	}
	
	public static function getSourceTargetColumn($columnName,$row){
		switch ($columnName) {
			case "CODE1":
			case "CODE2":
			case "DEFER_REASON":
			case "DEFER_GROUP_TYPE":
			case "THEOR_METHOD":
				return $columnName;
		}
		return null;
	}
	
	public static function getSourceModel($columnName){
		switch ($columnName) {
			case "CODE1":
				return "CodeDeferCode1";
			case "CODE2":
				return "CodeDeferCode2";
			case "CODE3":
				return "CodeDeferCode3";
			case "DEFER_REASON":
				return "CodeDeferReason";
			case "DEFER_REASON2":
				return "CodeDeferReason2";
			case "DEFER_GROUP_TYPE":
				return "CodeDeferGroupType";
			case "DEFER_TARGET":
				return "DefermentGroup";
			case "THEOR_METHOD_TYPE":
				return "CodeDeferTheorMethod";
            case "DEFERMENT_GROUP_SUB1":
                return "DefermentGroupSub1";
            case "DEFERMENT_GROUP_SUB2":
                return "DefermentGroupSub2";
		}
		return null;
	}
	
	public static function getTargetModel($columnName){
		return static::getSourceModel($columnName);
	}
	
	public static function getForeignColumn($row,$originCommand,$columnName){
		$command 			= $originCommand;
		$columnName 		= strtoupper($columnName);
		if ($columnName=="DEFER_TARGET") {
			$splitCharacter = config('database.default')==='oracle'?"":";";
			$s_where	= "";
			$s_order	= "";
			$namefield	= "NAME";
			$sourceTypeColumn = "DEFER_GROUP_TYPE";
			$sourceTypeColumn 	= config('database.default')==='oracle'?strtolower($sourceTypeColumn):$sourceTypeColumn;
			$id			= $row&&array_key_exists($sourceTypeColumn, $row)?$row[$sourceTypeColumn]:0;
			if ($id==3) {
				$ref_table 	= EnergyUnit::getTableName();
			}
			else{
				$ref_table 	= DefermentGroup::getTableName();
				if($id) $s_where = "where DEFER_GROUP_TYPE = $id";
			}
			$command 	= "select ID, $namefield from $ref_table $s_where $s_order $splitCharacter --select";
			return $command;
		}
		else if (in_array($columnName, array_keys(static::$dependenceColumns))) {
			$source				= static::$dependenceColumns[$columnName];
			$sourceTypeColumn	= $source["COLUMN"];
			$sourceTypeColumn 	= config('database.default')==='oracle'?strtolower($sourceTypeColumn):$sourceTypeColumn;
			$splitCharacter 	= config('database.default')==='oracle'?"":";";
			$s_order			= "";
			$namefield			= "NAME";
			$id					= $row&&array_key_exists($sourceTypeColumn, $row)?$row[$sourceTypeColumn]:1;
			if($id&&$id!=""){
				$s_where			= "where PARENT_ID=$id";
				$sourceModel		= $source["MODEL"];
				if($columnName=="THEOR_METHOD_TYPE"){
					$s_where		= "";
					switch ($id) {
						case 2:
							$sourceModel = "CodeForecastType";
						break;
						case 3:
							$sourceModel = "CodePlanType";
							break;
						default:
							$sourceModel	= null;
							break;
					}
				}
				if ($sourceModel) {
					$sourceModel	= 'App\Models\\' .$sourceModel;
					$ref_table		= $sourceModel::getTableName();
					$command 		= "select ID, $namefield from $ref_table $s_where $s_order $splitCharacter --select";
				}
			}
		}
		return $command;
	}

	public function preSaving(&$values){
		//\Log::info($values);
		$shouldCalculate = false;
		$beginTime = null;
		if(isset($values["BEGIN_TIME"])){
			if($values["BEGIN_TIME"]){
				$beginTime = Carbon::parse($values["BEGIN_TIME"]);
				$shouldCalculate = true;
			}
		}
		else if($this->BEGIN_TIME)
			$beginTime = Carbon::parse($this->BEGIN_TIME);
		
		$endTime = null;
		if(isset($values["END_TIME"])){
			if($values["END_TIME"]){
				$endTime = Carbon::parse($values["END_TIME"]);
				$shouldCalculate = true;
			}
		}
		else if($this->END_TIME)
			$endTime = Carbon::parse($this->END_TIME);
		
		// Xu ly rieng cho Mazarine
		if(config('constants.systemName') == "Mazarine"){
			$comment = 'Deferred quantities calculated based on end date';
			if(!$endTime){
				$endTime = Carbon::parse(date("Y-m-d 07:00:00"));//(calculate to 7am)
				$values["COMMENT"] = $comment.' '.$endTime;
				$shouldCalculate = true;
			}
			else if(strpos($this->COMMENT, $comment) !== false && !isset($values["COMMENT"]))
				$values["COMMENT"] = '';
		}
		
		if($shouldCalculate && $endTime && $beginTime){
			$hours = $endTime->diffInHours($beginTime);
			$values["DURATION"] = $hours;
		}
		//\Log::info($values);
	}
	
	public function afterSaving($postData) {
		//Tinh toan lai cac gia tri THEOR, GAS
		$shouldSave 		= false;
		$hours 				= $this->DURATION;
		$rat				= $hours/24;
		$detailEntries		= [];
		$defer_group_code = \App\Models\CodeDeferGroupType::where('ID',$this->DEFER_GROUP_TYPE)->first()->CODE;
		if($defer_group_code=='WELL'){
			$eu_id		= $this->DEFER_TARGET;
			if($eu_id>0){
				$rowTest	= $this->getTheorEntry($eu_id);
				if($rowTest){
					//-----------THEOR------------
					$this->THEOR_OIL_PERDAY			=$rowTest->EU_TEST_LIQ_HC_VOL;
					$this->THEOR_OIL_MASS_PERDAY	=$rowTest->EU_TEST_LIQ_HC_MASS;
					$this->THEOR_GAS_PERDAY			=$rowTest->EU_TEST_GAS_HC_VOL;
					$this->THEOR_WATER_PERDAY		=$rowTest->EU_TEST_WTR_VOL;
					//-----------CALC------------
					$this->CALC_DEFER_OIL_VOL		=$rat*$rowTest->EU_TEST_LIQ_HC_VOL;
					$this->CALC_DEFER_OIL_MASS		=$rat*$rowTest->EU_TEST_LIQ_HC_MASS;
					$this->CALC_DEFER_GAS_VOL		=$rat*$rowTest->EU_TEST_GAS_HC_VOL;
					$this->CALC_DEFER_WATER_VOL		=$rat*$rowTest->EU_TEST_WTR_VOL;
					$shouldSave = true;
				}
				DefermentDetail::where("DEFERMENT_ID", $this->ID)->delete();
			}
		}
		else {
			$defermentTarget 		= (config('constants.systemName')=='Mazarine'?$this->DEFER_TARGET:$this->DEFERMENT_GROUP_SUB2);
			$defermentGroupIDField 	= (config('constants.systemName')=='Mazarine'?'DEFERMENT_GROUP_ID':'DEFERMENT_GROUP_SUB2_ID');
			if($defermentTarget > 0){
				$defermentGroupEu 		= DefermentGroupEu::getTableName();
				$energyUnit 			= EnergyUnit::getTableName();
				$deferment				= $this;
				$total_theor			= array(0, 0, 0, 0);
				$total_calc				= array(0, 0, 0, 0);
				$total_ovr				= array(0, 0, 0, 0);
				$defermentGroupEuIds	= DefermentGroupEu::join($energyUnit, "$defermentGroupEu.EU_ID", '=', "$energyUnit.ID")
														->where("$defermentGroupEu.$defermentGroupIDField",'=',$defermentTarget)
														->select("$defermentGroupEu.EU_ID")
														->get();
				$defermentGroupEuIds->each(function ($item, $key) use ($deferment,$rat,&$total_theor,&$total_calc,$postData,&$detailEntries){
					$eu_id				= $item->EU_ID;
					$rowTest			= $deferment->getTheorEntry($eu_id);
	// 				$rowTest 			= static ::getEUTest($eu_id,$deferment->BEGIN_TIME);
					if($rowTest){
						//THEOR
						$total_theor[0]		+=$theor_oil		=$rowTest->EU_TEST_LIQ_HC_VOL;
						$total_theor[1]		+=$theor_gas		=$rowTest->EU_TEST_GAS_HC_VOL;
						$total_theor[2]		+=$theor_water		=$rowTest->EU_TEST_WTR_VOL;
						$total_theor[3]		+=$theor_oil_mass	=$rowTest->EU_TEST_LIQ_HC_MASS;
						//CALC
						$total_calc[0]		+=$calc_oil			=$rat*$theor_oil;
						$total_calc[1]		+=$calc_gas			=$rat*$theor_gas;
						$total_calc[2]		+=$calc_water		=$rat*$theor_water;
						$total_calc[3]		+=$calc_oil_mass	=$rat*$theor_oil_mass;
						
						$defermentId					= $deferment->ID;
						$attributes						= [];
						$attributes['EU_ID'] 			= $eu_id;
						$attributes['DEFERMENT_ID'] 	= $defermentId;
						$values							= [];
						$values['DEFERMENT_ID'] 		= $defermentId;
						$values['EU_ID'] 				= $eu_id;
						$values['THEOR_OIL_PERDAY']		= $theor_oil;
						$values['THEOR_OIL_MASS_PERDAY']= $theor_oil_mass;
						$values['THEOR_GAS_PERDAY']		= $theor_gas;
						$values['THEOR_WATER_PERDAY']	= $theor_water;
						$values['CALC_DEFER_OIL_VOL']	= $calc_oil;
						$values['CALC_DEFER_OIL_MASS']	= $calc_oil_mass;
						$values['CALC_DEFER_GAS_VOL']	= $calc_gas;
						$values['CALC_DEFER_WATER_VOL']	= $calc_water;
						$returnRecord					= DefermentDetail::updateOrCreate($attributes, $values);
						if($returnRecord)$detailEntries[] = $returnRecord;
	// 						if($returnRecord) $returnRecord->afterSaving($postData);
					}
				});
				$values	= [];
				$this->THEOR_OIL_PERDAY		= $total_theor[0];
				$this->THEOR_GAS_PERDAY		= $total_theor[1];
				$this->THEOR_WATER_PERDAY	= $total_theor[2];
				$this->THEOR_OIL_MASS_PERDAY= $total_theor[3];
				$this->CALC_DEFER_OIL_VOL	= $total_calc[0];
				$this->CALC_DEFER_GAS_VOL	= $total_calc[1];
				$this->CALC_DEFER_WATER_VOL	= $total_calc[2];
				$this->CALC_DEFER_OIL_MASS	= $total_calc[3];
				$shouldSave = true;
			}
		}

		if (!$this->FACILITY_ID&&$postData['Facility']>0) {
			$this->FACILITY_ID = $postData['Facility'];
			$shouldSave = true;
		}
		if ($shouldSave) $this->save();
		if (count($detailEntries)>0) {
			foreach ($detailEntries as $key => $returnRecord){
				$returnRecord->afterSaving($postData);
			}
		}
		return $this;
	}
	
	public function getTheorEntry($eu_id,$method = null) {
		$beginTime			= $this->BEGIN_TIME;
		$theorMethod		= $this->THEOR_METHOD;
		if (!$method) {
			$codeTheorMethod= $this->CodeDeferTheorMethod;
			if($codeTheorMethod){
				$method		= $codeTheorMethod->CODE;
			}
		}
		switch ($method) {
			case "EnergyUnitDataForecast":
			case "EnergyUnitDataPlan":
			case "EnergyUnitDataAlloc":
				$entryData				= (object)[
											"EU_TEST_LIQ_HC_VOL"		=> 0,
											"EU_TEST_LIQ_HC_MASS"		=> 0,
											"EU_TEST_GAS_HC_VOL"		=> 0,
											"EU_TEST_WTR_VOL"			=> 0];
				$mdl					= "App\Models\\$method";
				$theorMethodType		= $this->THEOR_METHOD_TYPE;
				if (is_string($beginTime)) $beginTime 					= Carbon::parse($beginTime)->startOfDay();
				$theorEntries			= $mdl::getVolMassValues($eu_id,$beginTime,$theorMethodType);
				if ($theorEntries) {
					$entry				= $theorEntries->get("OIL");
					if ($entry) {
						$entryData->EU_TEST_LIQ_HC_VOL 		= $entry->GRS_VOL;
						$entryData->EU_TEST_LIQ_HC_MASS 	= $entry->GRS_MASS;
					}
					$entry				= $theorEntries->get("GAS");
					if ($entry) $entryData->EU_TEST_GAS_HC_VOL 		= $entry->GRS_VOL;
					$entry				= $theorEntries->get("WTR");
					if ($entry) $entryData->EU_TEST_WTR_VOL 		= $entry->GRS_VOL;
				}
				return $entryData;
				break;
			default:
				break;
		}
		return static ::getEUTest($eu_id,$beginTime);
	}
	
	public static function buildLoadQuery($objectId,$object) {
		return static::where(["DEFER_TARGET"	=> $objectId,"DEFER_GROUP_TYPE"	=> 3]);//3 is well
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_values($mdlData);
		DefermentDetail::whereIn("DEFERMENT_ID", $valuesIds)->delete();
		static::whereIn('ID', $valuesIds)->delete();
	}

    public static function getEntries($facility_id=null,$product_type = 0){
        return DefermentLevel ::getLevel($facility_id);
    }
} 
