<?php

namespace App\Models;

use App\Exceptions\DataInputException;
use Carbon\Carbon;
use App\Models\DynamicModel;
use App\Models\AuditTrail;
use App\Models\EuTestDataValue;
// use App\Services\EBSchema;

class EbBussinessModel extends DynamicModel {
	
	protected $objectModel = null;
	protected static $enableCheckCondition = false;
	protected $disableUpdateAudit = true;
	public  static  $idField = 'ID';
	
	protected $excludeColumns = [];
	public  static $ignorePostData = false;
	protected $oldValues = null;
	protected $isAuto = false;
	protected $guarded = [];
	
	public static function isAllowFormula($formula){
		return true;
	}
	
	public function setAttribute($key, $value){
		if (is_scalar($value)) {
			$value = $this->emptyStringToNull($value);
		}
        if(strtoupper($key)=='ID'&& is_numeric($value)==false){
            return 0;
        }
		return parent::setAttribute($key, $value);
	}
	
	function emptyStringToNull($value){
		return (!isset($value) || trim($value)==='')?null:$value;
	}
	
	public static function getKeyColumns(&$newData,$occur_date,$postData)
	{
		return [static::$idField => $newData[static::$idField]];
	}
	
	public static function getObjectTypeCode()
	{
		return static::$idField;
	}
	
	public static function findManyWithConfig($updatedIds) {
		return parent::findMany ( $updatedIds );
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_values($mdlData);
		foreach($valuesIds as $key => $value)
			if(!is_numeric($value['ID'])){
				unset($valuesIds[$key]);
			}
		static::whereIn('ID', $valuesIds)->delete();
	}
	
	public static function updateValues(array $attributes, array &$values = [], $type, $fields) {
		if (!array_key_exists ( "auto", $values )||!$values[ "auto"]) return ;
		$unnecessary = true;
		foreach ( $fields as $field ) {
			$unnecessary = $unnecessary && array_key_exists ( $field, $values ) && $values [$field] != null && $values [$field] != '';
		}
		
		if ($unnecessary) return;
		if( !array_key_exists ( config ( "constants.flowPhase" ), $attributes )) return ;
		$flow_phase = $attributes [config ( "constants.flowPhase" )];
		// OIL or GAS
		if (($flow_phase == 1 || $flow_phase == 2 || $flow_phase == 21)) {
			$fdcValues = static::getFdcValues ( $attributes );
			if (!$fdcValues) return ;
			
			$object_id = $attributes [$fields [config ( "constants.keyField" )]];
			$occur_date = $fdcValues->OCCUR_DATE;
			
			$T_obs = $fdcValues->OBS_TEMP;
			$P_obs = $fdcValues->OBS_PRESS;
			$API_obs = $fdcValues->OBS_API;
			
			$_Bg = \FormulaHelpers::calculateBg ( $flow_phase, $T_obs, $P_obs, $API_obs, $occur_date, $object_id, $type );
			
			foreach ( $fields as $field ) {
				if (config ( "constants.keyField" ) == $field) {
					continue;
				}
				// if($ctv==1){
				if (array_key_exists ( $field, $values )) {
					break;
				}
				$_vFDC = $fdcValues->$field;
				if (static::$enableCheckCondition && $_Bg == null && $_vFDC != '') {
					throw new DataInputException ( "Can not calculate conversion for $type ID: $object_id (check API, Temprature, Pressure value)" );
					return;
				}
				$values [$field] = $_vFDC;
				switch ($flow_phase) {
					case 1 :
						$_v = null;
						if ($_vFDC && $_Bg != null)
							$_v = $_vFDC * $_Bg;
						$values [$field] = $_v;
						break;
					case 2 :
					case 21 :
						if ($_Bg == null) {
							$values [$field] = null;
						} else {
							if ($_Bg == 0) {
								if ((($values [$field] != null && $values [$field] != ''))) {
									throw new DataInputException ( "Wrong gas conversion number (zero) for $type ID: $object_id" );
								}
							} else {
								$values [$field] = $values [$field] / $_Bg;
							}
						}
						break;
					default :
						break;
				}
			}
		}
	}
	
	public static function updateOrCreateWithCalculating(array $attributes, array $values = []) {
	    if(isset($attributes['id'])){
            if(is_numeric($attributes['id'])==false){
                $attributes['id']="";
            }
        }
        if(isset($attributes['ID'])){
            if(is_numeric($attributes['ID'])==false){
                $attributes['ID']="";
            }
        }
		$auto = array_key_exists("auto", $values)&&$values["auto"];
		if (array_key_exists("isAdding", $values)&&$values["isAdding"]&&!$auto) {
			 if (array_key_exists ( 'ID', $values ))unset($values["ID"]);
			 if (array_key_exists ( 'id', $values ))unset($values["id"]);
			$instance = new static();
	        $instance->exists = false;
	        foreach ( $values as $column => $value ) {
	        	if ($column=="ID"||!$instance->isFillable($column)) {
	        		unset($values[$column]);
	        	}
	        }
			$instance->preSaving($values);
	        if (count($values)>0) {
				$instance = $instance->checkAndSave($values);
		        return $instance;
	        }
	        return null;
		}
		$values = static::calculateBeforeUpdateOrCreate ( $attributes, $values );
		$instance = null;
		if ($values&&is_array($values)&&count($values)>0&&count($attributes)>0) {
			if ( array_key_exists("auto", $attributes)) unset($attributes["auto"]);
			$instance = static::firstOrNew($attributes);
			if($instance->isNotAvailable($attributes)) return null;
					
			$instance->isAuto = $auto;
			$oldValues = [];
// 			$attributeColumns	= array_keys($attributes);
			foreach ( $values as $column => $value ) {
				$oldValues[$column]= $instance->$column;
				if (!$instance->isFillable($column)) {
					unset($values[$column]);
				}
			}
		//\Log::info($instance);
			$instance->preSaving($values);
			if (count($values)>0) {
				$instance->fill($values)->save();
				$instance->oldValues = $oldValues;
			}
			else
				return null;
			
		}
		return $instance; 
	}
	
	public function refresh(){
		$instance				= $this->fresh();
		if($instance){
			$instance->isAuto 		= $this->isAuto;
			$instance->oldValues 	= $this->oldValues;
		}
		return $instance;
	}
	
	public static function calculateBeforeUpdateOrCreate(array &$attributes, array $values = []){
		return $values;
	}
	
	public function isNotAvailable($attributes){
		return false;
	}
	
	public static function updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase,$event_type) {
		$newData = [static::$idField=>$object_id];
		$attributes = static::getKeyColumns($newData,$occur_date,null);
		$values = array_merge($values,$newData);
		return parent::updateOrCreate($attributes,$values);
	}
	
	public static function findWith($object_id,$occur_date,$flow_phase,$event_type) {
		$newData = [static::$idField=>$object_id];
		$attributes = static::getKeyColumns($newData,$occur_date,null);
		return parent::where($attributes)->first();
	}
	
	public static function updateWithQuality($record,$occur_date) {
		return false;
	}
	
	public function getObjectDesc($rowID) {
	    if ($this->objectModel){
            $mdl = 'App\Models\\'.$this->objectModel;
            return $mdl::find($rowID);
        }
//        $referenceColumn = isset(static::$previewNameColumn)?static::$previewNameColumn:"NAME";

//        $this->NAME = $this->$referenceColumn;
        return $this;
	}
	
	public function afterSaving($postData) {
	}
	
	public function preSaving(&$values) {
	}
	
	public function checkAndSave(&$values) {
		$this->fill($values)->save();
		return $this;
	}
	
	public function updateDependRecords($occur_date,$values,$postData) {
		return null;
	}
	
	public function getKeyAttributes($mdlName,$column){
		return null;
	}
	
	public function getLastValueOfColumn($mdlName,$column,$occur_date,$postData=null,$returnQuery=true,$take_config='1') {
		//sample of range: '1', ':1','avg:10', 'max:4', 'min:10'
		$s = explode(':',$take_config);
		$func=(count($s)==1?'':strtolower($s[0]));
		$take=($func===''?1:end($s));
		if(!is_numeric($take)|| !($func==='' || $func=='min' || $func=='max' || $func=='avg')){
			throw new \Exception("Wrong range config: $take_config");
			return null;
		}
		if (!$occur_date) return null;
		if(!is_numeric($this->DT_RowId)) return null;
		$mdlName	= strpos($mdlName, 'App\Models\\')===false?'App\Models\\'.$mdlName:$mdlName;
		$newData	= $this->getKeyAttributes($mdlName,$column);
		if (!$newData) return null;
		
		$where		= $mdlName::getKeyColumns($newData,$occur_date,$postData);
		unset($where[$mdlName::$dateField]);
		$sub		= $mdlName :: where($where)
								->whereNotNull($column)
								->where($column,">",0)
								->whereDate($mdlName::$dateField,"<",$occur_date)
								->select($column)
								->orderBy($mdlName::$dateField,"desc")
								->take($take);
		$query = \DB::table( \DB::raw("({$sub->toSql()}) as a") )
		->mergeBindings($sub->getQuery())
		->select(\DB::raw("$func(a.$column) as $column"), \DB::raw("'$this->DT_RowId' as DT_RowId"));
		//\Log::info($query->toSql());
		if ($returnQuery) return $query;
		else return $query->get();
	}
	
	
	public function updateAudit($attributes,$values,$postData) {
		if ($this->disableUpdateAudit)  return;
		$current 			= Carbon::now();
		$currentUser 		= auth()->user();
		$current_username 	= $currentUser?$currentUser->username:"unauthenticated (dcsave ...)";
		$rowID 				= array_key_exists(static::$idField, $attributes)? $attributes[static::$idField]: $values[static::$idField];
		$facility_id 		= array_key_exists("Facility", $postData)? $postData['Facility']: 0;
		$objectDesc 		= $this->getObjectDesc($rowID);
        $recordID           = is_numeric($rowID)?$rowID:$objectDesc->ID;
		$oldValue 			= null;
		$newValue 			= null;
		$records 			= array();
		$shouldInsertAudit 	= true;
		$columns 			= $values;
		$keyColumns			= array_keys($attributes);
		$action 			= $this->wasRecentlyCreated?"New record":"Update value";
		$occurDate 			= isset(static::$dateField)?$this->{static::$dateField}:(isset($this->OCCUR_DATE)?$this->OCCUR_DATE:null);

		if (\Schema::hasColumn($this->getTable(), "RECORD_STATUS")) {
			$recordStatus 	= $this->RECORD_STATUS;
			if ($this->wasRecentlyCreated || !$recordStatus) {
				$this->RECORD_STATUS = "P";
				$this->save();
			}
		}
		
		foreach ( $columns as $column => $columnValue ) {
			$newValue 		= $this->$column;
			if (!$this->wasRecentlyCreated) {
				$shouldInsertAudit = false;
				if (!in_array($column, $this->excludeColumns)) {
					if(isset($this->oldValues)) {
						$original = $this->oldValues;
						if (array_key_exists($column, $original)){
							$oldValue = $original[$column];
							$shouldInsertAudit = $oldValue!=$newValue;
						}
					}
				}
			}
					
			if ($shouldInsertAudit
					&&(in_array($column,$this->fillable))&&!in_array($column,$keyColumns)){
// 					&&($action=="New record" || (!in_array($column,$keyColumns)))){
				
				$auditNote = null;
				if(isset($values["AUDIT_NOTE-$column"])) $auditNote = $values["AUDIT_NOTE-$column"];
				else if(isset($values["COMMENT"])) $auditNote = $values["COMMENT"];
				else if(isset($values["COMMENT_"])) $auditNote = $values["COMMENT_"];
				else if(isset($values["COMMENTS"])) $auditNote = $values["COMMENTS"];
			
				$records[] 	= array('ACTION'	=>$action,
								'FACILITY_ID'	=>$facility_id,
								'WHO'			=>$current_username,
								'WHEN'			=>$current, 
								'TABLE_NAME'	=>$this->table,
								'COLUMN_NAME'	=>$column,
								'RECORD_ID'		=>$recordID,
								'OBJECT_DESC'	=>$objectDesc->NAME,
								'REASON'		=>1,
								'OLD_VALUE'		=>$oldValue,
								'NEW_VALUE'		=>$newValue,
								'AUDIT_NOTE'	=>$auditNote,
								'OCCUR_DATE'	=>$occurDate,
				);
			}
		}
		
		if (count($records)>0) {
			AuditTrail::insert($records);
		}
	}

	public static function getEUTest($object_id,$occur_date) {
		$originAttrCase = \Helper::setGetterUpperCase();
		$rowTest=EuTestDataValue::where([['EU_ID',$object_id] ,['TEST_USAGE',1]
		])
				->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				->orderBy('EFFECTIVE_DATE','desc')
				->first();
		\Helper::setGetterCase($originAttrCase);
		return $rowTest;
	}
	
	public static function getEUTestAlloc($object_id,$occur_date,$attr) {
		$rowTest=EuTestDataValue::where([
			['EU_ID',$object_id],
			['TEST_USAGE',1]
		])
				->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				->orderBy('EFFECTIVE_DATE','desc')
				->select($attr)
				->first();
		//\Log::info ($rowTest);
		return $rowTest[$attr];
	}
	
	public static function getCalculateFields() {
		return null;
	}

	public static function getObjectTargets() {
		return  collect([
				(object)['value' =>	'KEEP_DISPLAY_VALUE','text' => 'Display origin value'      	],
// 				(object)['value' =>	'NO_FORMAT'			,'text' => 'No format'    ],
				(object)['value' =>	'TBD'				,'text' => 'To be defined'    ],
		]);
	}
	
	public static function buildLoadQuery($objectId,$object) {
		return static::where(static::$idField,$objectId);
	}
	
	public static function isInFacilities($facilityIds,$objectId){
		$object = static::where("ID","=",$objectId)->whereIn("FACILITY_ID",$facilityIds)->first();
		return $object!=null;
	}
}
