<?php
use App\Models\CfgFieldProps;
use App\Models\Formula;
use App\Models\Fovar;
use App\Models\CodeQltySrcType;
use App\Models\QltyDataDetail;
use App\Models\QltyData;
use App\Models\QltyProductElementType;
use App\Models\StrappingTableData;
use App\Models\PdContractYear;
use App\Models\PdContractCalculation;
use App\Models\PdContractData;
use App\Models\PdCodeContractAttribute;
use App\Models\EbBussinessModel;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FatalErrorException;

function div($a, $b){
	return ($b==0 || $b==null)?0:$a/$b;
}

function sum(){
	$args = func_get_args();
	$s=0;
	foreach ($args as $arg)
	{
		if(is_array($arg))
		{
			foreach($arg as $value)
			{
				if(is_array($value))
					$s += sum($value);
					else $s += $value;
			}
		}
		else $s += $arg;
	}
	return $s;
}

function fnc($n,$tem = 0) {
	global $aryMstCalcu;
	if($tem != 0) {
		$aryMstCalcu = $tem;
	}
	$value = array_key_exists('fnc('.$n.')', $aryMstCalcu)?(int) $aryMstCalcu['fnc('.$n.')']:0;
	return $value;

}

function contract_attr($formulaId,$code,$year = '') {
 	global $contractIdGlobal;
 	global $yearGlobal;
	
	if($year != '') {
		$year  = $yearGlobal - 1;
		$sSQL  =" SELECT a.FORMULA_VALUE FROM pd_contract_year a ,pd_contract_calculation b WHERE b.ID = a.CALCULATION_ID AND"
				. "  a.CONTRACT_ID =  ".$contractIdGlobal ." AND  a.YEAR =  '".$year."'"." AND  b.FORMULA_ID =  '".$formulaId."'";
		
		$pdContractYear			= PdContractYear::getTableName();
		$pdContractCalculation	= PdContractCalculation::getTableName();
		$contractYear			= PdContractYear::join($pdContractCalculation,"$pdContractYear.CALCULATION_ID", '=', "$pdContractCalculation.ID")
									->where("$pdContractYear.CONTRACT_ID", '=', $contractIdGlobal)
									->where("$pdContractYear.YEAR", '=', $year)
									->where("$pdContractCalculation.FORMULA_ID", '=', $formulaId)
									->select("$pdContractYear.FORMULA_VALUE as ATTRIBUTE_VALUE")
									->first();
	} else {
		$sSQL  =" SELECT ATTRIBUTE_VALUE 
				FROM pd_code_contract_attribute a
				,pd_contract_data b
				WHERE b.ATTRIBUTE_ID = a.ID AND"
				. "  b.CONTRACT_ID =  ".$contractIdGlobal ." 
						AND  a.CODE =  '".$code."'";
		
		$pdContractData				= PdContractData::getTableName();
		$pdCodeContractAttribute	= PdCodeContractAttribute::getTableName();
		$contractYear				= PdCodeContractAttribute::join($pdContractData,"$pdContractData.ATTRIBUTE_ID", '=', "$pdCodeContractAttribute.ID")
												->where("$pdCodeContractAttribute.CODE", '=', $code)
												->where("$pdContractData.CONTRACT_ID", '=', $contractIdGlobal)
												->select("ATTRIBUTE_VALUE")
												->first();
	}
	/* $result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	while($row=mysql_fetch_array($result)) {
		return $row['ATTRIBUTE_VALUE'];
	} */
	if ($contractYear) {
		return $contractYear->ATTRIBUTE_VALUE;
	}
	return 0;
}


function evalErrorHandler($errno, $errstr, $errfile, $errline){
    \Log::info("$errstr at errno $errno file $errfile line $errline");
	if ($errstr == 'Division by zero') {
		//return null;
	}
	elseif ($errstr == 'Undefined variable: rho_o_obs'){
		return null;
	}
	
	throw new Exception("$errstr at errno $errno file $errfile line $errline");
}
class FormulaHelpers {
	
	public static function doFormula($tName,$keyfield,$keyvalues,$echo_only=false,$facility_id=false){
		//\Log::info($tName);
		//\Log::info($keyfield);
		//\Log::info($keyvalues);
    	if(!$keyfield || !$keyvalues) return false;
    	
    	$mdl 			= "App\Models\\$tName";
    	$tablename 		= $mdl::getTableName();
    	$isOracle		= config('database.default')==='oracle';
//     	\DB::enableQueryLog();
    	$fquery 		= CfgFieldProps::where('TABLE_NAME', $tablename)
					    	->whereNotNull('FORMULA')
					    	->select("COLUMN_NAME", "FORMULA");
		$config_id = false;
		$sql = "";
		if($facility_id){
			$sql = "select a.ID from CFG_CONFIG a where a.TABLE_NAME='$tablename' and a.FACILITY_ID=$facility_id";
		}
		else if(is_array($keyvalues)){
			$cTable = "";
			$cID = "";
			if(substr($tName, 0, 14)=='EnergyUnitData' || substr($tName, 0, 10)=='EuTestData'){
				$cTable = "ENERGY_UNIT"; $cID = "EU_ID";
			}
			else if(substr($tName, 0, 8)=='FlowData'){
				$cTable = "FLOW"; $cID = "FLOW_ID";
			}
			else if(substr($tName, 0, 8)=='TankData' || $tName=='RunTicketFdcValue' || $tName=='RunTicketValue'){
				$cTable = "TANK"; $cID = "TANK_ID";
			}
			else if(substr($tName, 0, 8)=='StorageData'){
				$cTable = "STORAGE"; $cID = "STORAGE_ID";
			}
			else if(substr($tName, 0, 13)=='EquipmentData'){
				$cTable = "EQUIPMENT"; $cID = "EQUIPMENT_ID";
			}
			if($cTable)
				$sql = "select a.ID from CFG_CONFIG a, $cTable b, $tablename c where a.TABLE_NAME='$tablename' and a.FACILITY_ID=b.FACILITY_ID and b.ID=c.{$cID} and c.ID={$keyvalues[0]}";
		}
		if($sql){
			\Log::info($sql);
			$re = \DB::select($sql);
			if(isset($re[0]))
				$config_id=$re[0]->id;
		}
		if($config_id){
    		$fquery->where('CONFIG_ID', $config_id);
		}
		else{
    		$fquery->whereNull('CONFIG_ID');
		}
    	if ($isOracle) 
    		$fquery->whereNotNull('trim(FORMULA)');
    	else 
    		$fquery->where('FORMULA','<>', '');
    	$formulas = $fquery->get();
//     	\Log::info(\DB::getQueryLog());
//     	$sSet="";
    	$values = [];
    	foreach($formulas as $row ){
    		if($row->FORMULA)
    		{
    			$ss=trim($row->FORMULA);
    			$sWhere="";
    			if (strpos($ss,'{') !== false){
    				$k1=strpos($ss,'{');
    				$k2=strpos($ss,'}',$k1);
    				if($k2>$k1+1){
    					$sWhere=substr($ss,$k1+1,$k2-$k1-1);
    				}
    			}
    			if(strpos(strtoupper($ss), "HOURS")!== false&&strpos(strtoupper($ss), "HOURS")==0)
    			{
    				if ($isOracle)
    					$ss= str_replace(",", "-", substr($ss,5))." * 24";
    				elseif(config('database.default')==='mysql')
    					$ss="time_to_sec(timediff".substr($ss,5).") / 3600";
    				elseif(config('database.default')==='sqlsrv')
    					$ss="-DATEDIFF(second,".substr($ss,6)."/3600.0";
    			}
    			else if(substr($ss,0,1)==="[") //table
    			{
    		
    				$k2=0;
    				$x_ss="";
    				$i=0;
    				while(true)
    				{
    					$i++;
    					if($i>100) break;
    		
    					$k1=strpos($ss,'[',$k2);
    					if($k1===false)
    						break;
    						$kx=$k2;
    						$k2=strpos($ss,']',$k1);
    						if($k2===false)
    							break;
    							if($kx>0 && $k1>$kx)
    								$x_ss.=substr($ss,$kx+1,$k1-$kx-1);
    		
    								$i1=strpos($ss,'(',$k1);
    								if($i1===false)
    									break;
    									$i2=strpos($ss,')',$i1);
    									if($i2===false)
    										break;
    		
    										$s_table=substr($ss,$k1+1,$i1-$k1-1);
    										$x_where_fields=explode(',',substr($ss,$i1+1,$i2-$i1-1));
    		
    										$s_where="";
    		
    										foreach($x_where_fields as $x_where_field)
    										{
    											$x_where_field_parts=explode('=',trim($x_where_field));
    											$is_raw_value = false;
    											if(count($x_where_field_parts)>1)
    											{
    												$v_val=$x_where_field_parts[1];
    												if(is_numeric($v_val) || substr($v_val,0,1)=="'")
    													$is_raw_value=true;
    											}
    											if($is_raw_value)
    												$s_where.=($s_where?" and ":"")."`$x_where_field_parts[0]`=$v_val";
    												else
    													$s_where.=($s_where?" and ":"")."`$x_where_field_parts[0]`=`$tablename`.`".$x_where_field_parts[count($x_where_field_parts)-1]."`";
    										}
    										$s_select=substr($ss,$i2+1,$k2-$i2-1);
    										if (config('database.default')==='sqlsrv') 
   												$x_ss .= "(select top 1 $s_select from $s_table where $s_where)";
    										else
   												$x_ss .= "(select $s_select from $s_table where $s_where".($isOracle?" and ROWNUM = 1":" limit 1").")";
    				}
    				$ss=$x_ss;
    			}
    			if($sWhere) {
    				$rawSql	= "(case when $sWhere then $ss else `$row->COLUMN_NAME` end)";
    				$rawSql	= \Helper::removeInvalidCharacter($rawSql);
    				$values[$row->COLUMN_NAME]=\DB::raw($rawSql);
    			}
    			else {
    				$ss	= \Helper::removeInvalidCharacter($ss);
    				$values[$row->COLUMN_NAME]=\DB::raw($ss);
    			}
    		}
    	}
    		 
//     	if($sSet)
    	if(count($values)>0)
    	{
     		if($echo_only) echo "SQL formular: $values";
     		else {
	    		/* $ids = implode("','",$keyvalues);
				$sSQL="update $tablename set $sSet where `$keyfield` in ($ids)";
	 			$result = \DB::update($sSQL); */
				//     		$result = \DB::update('update ? set ? where ? in ?', [$tablename,$sSet,$keyfield,$keyvalues]);
	    		//     		error_log("<br>sSQL: $sSQL</br>", 3, "C:/xampp/htdocs/eb/log/hung.log");
//      			$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
// 		      	\DB::enableQueryLog();
				$updateRecords = $mdl::whereIn($keyfield,$keyvalues)->update($values);
// 				\Log::info(\DB::getQueryLog());
	    		if ($updateRecords) {
					return array_keys($values);
	    		}
     		}
    	}
    	return true;
    }
    
    public static function applyFormula($mdlName,$objectIds,$occur_date,$returnAffectedIds=false){
    	
//     	global $object_id,$flow_phase,$occur_date,$facility_id;
    	$mdl = "App\Models\\$mdlName";
    	$object_type = $mdl::$typeName;
    	 
    	$result = [];
    	foreach($objectIds as $object_id){
	    	$formulas = self::getFormulatedFields($mdl::getTableName(),$object_id,$object_type,$occur_date);
	    	foreach($formulas as $formula){
		    	$values = [];
				$v=self::evalFormula($formula,$occur_date);
	    		if ($v!==null) $values[$formula->VALUE_COLUMN]=$v;
	    		$flow_phase= $formula->FLOW_PHASE;
	    		$event_type= $formula->EVENT_TYPE;
		    	if (count($values)>0) {
		    		$updateRecords = $mdl::updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase,$event_type);
		    		if ($updateRecords && $returnAffectedIds) {
		    			$result[] = $updateRecords;
		    		};
		    	}
	    	}
	    	
    	}
    	return $result;
    }
    
    public static function getFormulatedFields($tableName,$object_id,$object_type,$occur_date,$flow_phase=false,$event_type=false){
    	$where = ['OBJECT_TYPE'		=>	$object_type,
    			'OBJECT_ID'			=>	$object_id,
    			'TABLE_NAME'		=>	$tableName];
    	if ($flow_phase) $where['FLOW_PHASE'] = $flow_phase;
    	if ($event_type) $where['EVENT_TYPE'] = $event_type;
//     	\DB::enableQueryLog();
    	$fields = Formula::where($where)
							->where(function ($query) use ($occur_date){
					                $query->where(['BEGIN_DATE'=>	null,'END_DATE'=>null])
					                	  ->orWhere(function ($query) use ($occur_date) {
									                $query->where('BEGIN_DATE','<=',$occur_date)
									                	  ->where('END_DATE',null);
									        		})
					                      ->orWhere(function ($query) use ($occur_date){
									                $query->where('BEGIN_DATE','<=',$occur_date)
									                	  ->where('END_DATE','>=',$occur_date);
									        		});
					        		})
					        ->get();
//     	\Log::info(\DB::getQueryLog());
    	return $fields;
    }
    
    public static function getAffects($mdlName,$columns,$objectId,$occur_date,$flow_phase=false){
    	
    	$mdl = "App\Models\\$mdlName";
		$objectType = $mdl::$typeName;
    	$where = ['OBJECT_TYPE'		=>	$objectType,
    			'OBJECT_ID'			=>	$objectId,
    			'TABLE_NAME'		=>	$mdl::getTableName()];
    	
    	if ($flow_phase) $where['FLOW_PHASE']=$flow_phase;
    	$affectedFormulas = [];
//TUNG commented -> hung uncomment 
//    	     	\DB::enableQueryLog();
    	$foVars = FoVar::with('Formula')
    						->whereHas('Formula', function ($whquery) use ($occur_date){
								   $whquery->where(function ($query) use ($occur_date) {
											$query->where('BEGIN_DATE','<=',$occur_date)
											  	->orWhere('BEGIN_DATE',null);
										})
										->where(function ($query) use ($occur_date){
											$query->where('END_DATE','>=',$occur_date)
									  				->orWhere('END_DATE',null);
									});
							})
    						->where($where)
//     						->whereIn('VALUE_COLUMN',$columns)
    						->get();
//    	     	\Log::info(\DB::getQueryLog());
	    foreach($foVars as $foVar ){
	    	$fml = $foVar->Formula;
	    	if ($fml) {
		    	$affectedFormulas[] = $fml;
	    	}
	    }

    	$fos = Formula::where($where)
			->where(function ($query) use ($occur_date) {
						$query->where('BEGIN_DATE','<=',$occur_date)
							  ->orWhere('BEGIN_DATE',null);
						})
			->where(function ($query) use ($occur_date){
						$query->where('END_DATE','>=',$occur_date)
							  ->orWhere('END_DATE',null);
						})
// 			->whereNotIn('VALUE_COLUMN',$columns)
			->get();
						
    	if ($fos) foreach($fos as $fml ) if ($fml) $affectedFormulas[] = $fml;
    	
    	$where['APPLY_EMPTY'] = 'Y'; 
    	$fos = Formula::where($where)
				    	->where(function ($query) use ($occur_date) {
				    		$query->where('BEGIN_DATE','<=',$occur_date)
				    		->orWhere('BEGIN_DATE',null);
				    	})
				    	->where(function ($query) use ($occur_date){
				    		$query->where('END_DATE','>=',$occur_date)
				    		->orWhere('END_DATE',null);
				    	})
				    	->whereIn('VALUE_COLUMN',$columns)
				    	->get();
	    
 	    $ignoreFormulas = [];
 	    if ($fos) foreach($fos as $fml ) if ($fml) $ignoreFormulas[] = $fml;
 	    
 	    $affectedFormulas = array_unique($affectedFormulas);
    	return [$affectedFormulas,$ignoreFormulas];
    }
    
    public static function applyAffectedFormula($objectWithformulas,$occur_date){	
    	if (!$objectWithformulas) return false;
		$order = config('database.default')==='oracle'?'ORDER_':'ORDER';
		usort($objectWithformulas, function($a, $b) use ($order){
			return $a->$order==$b->$order?$a->ID-$b->ID:$a->$order-$b->$order;
		});
//	\Log::info($objectWithformulas);
    	$result = [];
		foreach($objectWithformulas as $formula){
			$values = [];
			$tableName = strtolower ( $formula->TABLE_NAME);
			$mdlName = \Helper::camelize($tableName,'_');
			if (!$mdlName)  continue;
			$mdl = "App\Models\\$mdlName";
			$object_type = $mdl::$typeName;
			$object_id = $formula->OBJECT_ID;
			$flow_phase= $formula->FLOW_PHASE;
			$event_type= $formula->EVENT_TYPE;
			if($mdl::isAllowFormula($formula)){
				$v=self::evalFormula($formula,$occur_date);
				$shouldUseCalculatedValue = false;
				if ($v!==null&&$formula->APPLY_EMPTY!='Y') $shouldUseCalculatedValue = true;
				elseif ($v!==null){
					$record = $mdl::findWith($object_id,$occur_date,$flow_phase,$event_type);
					$shouldUseCalculatedValue = $record?$record->{$formula->VALUE_COLUMN}==null:true;
				}
				
				if($shouldUseCalculatedValue) $values[$formula->VALUE_COLUMN]=$v;
				if (count($values)>0) {
					$updateRecord = $mdl::updateWithFormularedValues($values,$object_id,$occur_date,$flow_phase,$event_type);
					if ($updateRecord) {
						$updateRecord->{"modelName"} = $mdlName;
						$result[] = $updateRecord;
					};
				}
			}
		}
    	return $result;
    }
    
    public static function doEvalFormula($formulaId){
		//\Log::info("doEvalFormula($formulaId)");
    	$formula 	= Formula::find($formulaId);
    	return 		self::evalFormula($formula);
    }
	
	private static function replaceVariable($str, &$vars){
		$words = preg_split("/[*\/+-]/", $str);
		$ret = "";
		$i = 0;
		$k1 = 0;
		while($i<count($words)){
			$key = trim($words[$i]);
			if(strlen($key)>0){
				if(isset($vars[$key]))
					$ret .= "\$vars['$key']";
				else
					$ret .= $words[$i];
				
				if($i<count($words) - 1){
					if(strlen($words[$i+1])>0){
						$k1 = strpos($str, $words[$i], $k1) + strlen($words[$i]);
						$k2 = strpos($str, $words[$i+1], $k1);
						if($k2 > $k1){
							$ss = substr($str,$k1,$k2-$k1);
							$ret .= $ss;
						}
					}
				}
			}
			$i++;
		}
		return $ret;
	}
	
	public static function getQueryObject(&$rowVar, &$vars, &$takeRowsCount, $show_echo){
		$isOK = false;
		if($rowVar->TABLE_NAME && $rowVar->VALUE_COLUMN)
		{
			$j=strpos($rowVar->STATIC_VALUE,"(");
			$k=self::findClosedSign(")",$rowVar->STATIC_VALUE,$j);

			if($k>$j && $j>0)
			{
				$isOK = true;
			}
		}
		if(!$isOK){
			return null;
		}
		
		$isSelectLastDate = false;
		$table=$rowVar->TABLE_NAME;
		$field=$rowVar->VALUE_COLUMN;
		$sql	="select $field from `$table` where 1";
		$sql	= \Helper::removeInvalidCharacter($sql);
		
		//echo "field: $field<br>";
		$params=explode(",",substr($rowVar->STATIC_VALUE,$j+1,$k-$j-1));
		$where = [];
		$whereDate	= [];
		$whereMonth	= [];
		$whereYear	= [];
		$swhere = false;
		foreach ($params as $param)
		{
			$deli="";
			if (strpos($param,'>=') !== false) {
				$deli=">=";
			}
			else if (strpos($param,'<=') !== false) {
				$deli="<=";
			}
			else if (strpos($param,'>') !== false) {
				$deli=">";
			}
			else if (strpos($param,'<') !== false) {
				$deli="<";
			}
			else if (strpos($param,'=') !== false) {
				$deli="=";
			}
			if($deli!=="")
			{
				$ps=array_map('trim', explode($deli,$param));
				if($ps[1]=="@DATE"){
					$pp = "$ps[0] $deli $CURRENT_DATE";
					$whereItem = [$ps[0],$deli,$CURRENT_DATE];
				}
				else if (isset($vars[$ps[1]])&&is_numeric($vars[$ps[1]])){
					$pp = "$ps[0] $deli ".$vars[$ps[1]]."";
					$whereItem = [$ps[0],$deli,$vars[$ps[1]]];
				}
				else{
					if(isset($vars[$ps[1]])){
						$pp = "$ps[0] $deli '".$vars[$ps[1]]."'";
						$whereItem = [$ps[0],$deli,$vars[$ps[1]]];
					}
					else{
						$pp = "$ps[0] $deli $ps[1]";
						$whereItem = [$ps[0],$deli,$ps[1]];
					}
				}
				$sql.=" and $pp";
//     								$swhere.=" and $pp";
				//if($whereItem[0]=="OCCUR_DATE"||$whereItem[0]=="EFFECTIVE_DATE"){
				if( (substr($whereItem[0], -5) === '_DATE')){
					//process INTERVAL x DAY
					$ds = explode("'",$whereItem[2]);
					$ds = explode(' ',$ds[count($ds)-1]);
					if(count($ds) >= 4 && ($ds[1]=='+' || $ds[1]=='-') && $ds[2] === 'INTERVAL' && is_numeric($ds[3])){
						$sign = $ds[1];
						$qty = (int)$ds[3];
						$unit = $ds[4];
						$whereItem[2] = date ( "Y-m-d", strtotime ( "{$sign}{$qty} {$unit}", strtotime ( $occur_date ) ) );
					}
					else {
						//check first/last day function
						if(substr( $whereItem[2], 0, 9 ) === "FIRST_DAY"){
							$whereItem[2]=date('Y-m-01', strtotime($occur_date));
						}
						else if(substr( $whereItem[2], 0, 8 ) === "LAST_DAY"){
							$whereItem[2]=date('Y-m-t', strtotime($occur_date));
						}
						else if(substr($whereItem[2],0,10) === "LAST_VALUE"){
							$isSelectLastDate = $whereItem[0];
							$dateCondition = trim(substr($whereItem[2],10));
						}
						else
							$whereItem[2] 	= str_replace("'","",$whereItem[2]);
					}

					if($isSelectLastDate === false) $whereDate[]	= $whereItem;
				}
				else if (strpos($whereItem[0], 'month') !== false || strpos($whereItem[0], 'year') !== false) {
					$swhere = $swhere?"$swhere and $pp":$pp;
					/* if (strpos($whereItem[0], 'month') !== false) {
						$whereItem[2] 	= $occur_date->month;
						$whereMonth[] 	= $whereItem;
					}
					else {
						if (strpos($whereItem[0], 'year') !== false) $whereItem[2] = $occur_date->year;
						$whereYear[] 	= $whereItem;
					} */
				}
				else {
					$where[]=$whereItem;
				}
				//echo "param: $pp<br>";
			}
		}
		$sql .= " limit 1000";
		if($show_echo) $sqlLog=  ["content" 	=> $sql,	"type" 		=> "sql"];
		$queryField = DB::table($table);
		$rWhere = [];
		foreach ($where as $wKey => $whereItem){
			if (count($whereItem)>=3&&(!$whereItem[2]||$whereItem[2]=="null"||$whereItem[2]=="NULL")) {
				$queryField->whereNull($whereItem[0]);
			}
			else
				$rWhere[] = $whereItem;
		}
		$queryField->where($rWhere);
		if ($swhere) {
			$queryField->whereRaw($swhere);
		}
		
		foreach ($whereDate as $dkey => $dvalue){
			$queryField->whereDate($dvalue[0],$dvalue[1],$dvalue[2]);
		}
		
		foreach ($whereMonth as $mkey => $mvalue){
			$queryField->whereMonth($mvalue[0],$mvalue[1],$mvalue[2]);
		}
		
		foreach ($whereYear as $ykey => $yvalue){
			$queryField->whereYear($yvalue[0],$yvalue[1],$yvalue[2]);
		}
		$takeRowsCount = 1000;
		if($isSelectLastDate){
			$queryField->whereNotNull($field);
			$queryField->orderBy($isSelectLastDate, 'DESC');
			if($dateCondition)
				$queryField->whereDate($isSelectLastDate,'<',str_replace("'","",$dateCondition));
			$takeRowsCount = 1;
		}
		return $queryField;
	}
	
	public static function isFunction($str, $func){
		return strtolower(substr($str,0,strlen($func)))==strtolower($func);
	}
    
    public static function evalFormula($formulaRow, $occur_date = null, $show_echo = false, $extVars = null){
		$occur_date = explode(" ",$occur_date)[0];
    	$logs = false;
    	if(!$formulaRow){
    		$logs = $show_echo?["error"		=> true,"reason"	=> "Formula is out of date range"]:false;
			return $logs;
    	}
    	 
    	$fid = $formulaRow->ID;
    	$flow_phase = $formulaRow->FLOW_PHASE;
    	$object_id = $formulaRow->OBJECT_ID;
    	$formula 	= $formulaRow->FORMULA;
    	$fDisplay	= $formulaRow->FORMULA;
    	if (!$formula) 	{
    		$logs = $show_echo?["error"		=> true,
    				"reason"	=> "formula $formulaRow->NAME was empty. Please set it in formula table"]:false;
    		if($show_echo)  return $logs;
    		else throw new Exception("formula $formulaRow->NAME was empty. Please set it in formula table");
    	}
    		
    	$foVars = $formulaRow->FoVar()->get();
    	 
    	$CURRENT_DATE=date("Y-m-d");
		$first_day = date("Y-m-01", strtotime($occur_date));
		$last_day = date("Y-m-t", strtotime($occur_date));
		$prev_day = date('Y-m-d', strtotime($occur_date .' -1 day'));
		$next_day = date('Y-m-d', strtotime($occur_date .' +1 day'));
    	$s="";
    	$i=0;
    	$vars	= array();
    	$vvv	= array();
    	$logs	= [	"error"		=> false,
    				"variables"	=> []
    	];
    	
    	foreach($foVars as $row ){
    		array_push($vvv,$row->NAME);
			//replece STATIC_VALUE to prevent dangerous injected php code
    		$row->STATIC_VALUE=str_replace("{","#",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("}","#",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace(";","#",$row->STATIC_VALUE);
			
    		$row->STATIC_VALUE=str_replace("FIRST_DAY(@OCCUR_DATE)","'$first_day'",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("LAST_DAY(@OCCUR_DATE)","'$last_day'",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("PREV_DAY(@OCCUR_DATE)","'$prev_day'",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("NEXT_DAY(@OCCUR_DATE)","'$next_day'",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@OCCUR_DATE","'$occur_date'",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@OBJECT_ID",$object_id,$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@FLOW_PHASE",$flow_phase,$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("@VAR_OBJECT_ID",$row->OBJECT_ID,$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("#OIL#","1",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("#GAS#","2",$row->STATIC_VALUE);
    		$row->STATIC_VALUE=str_replace("#WATER#","3",$row->STATIC_VALUE);
			
    		if(strpos($row->STATIC_VALUE,"#CODE_")!==false) $row->STATIC_VALUE=processFormulaCodeConstant($row->STATIC_VALUE);

    		if($show_echo) {
    			$logs["variables"][] =  ["content" => "Processing $row->NAME = $row->STATIC_VALUE ...",	"type" => "source"];
    			$sqlLog 	=  null;
    			$valueLog 	=  null;
    		}
    		
			//replace calculated variables for current variable
			$row->STATIC_VALUE = self::replaceVariable($row->STATIC_VALUE, $vars);
			/*
			foreach($vvv as $key => $v)
			{
				if(isset($vars[$v]) && !is_array($vars[$v])){
					$row->STATIC_VALUE = str_replace("[$v]",$vars[$v],$row->STATIC_VALUE);
				}
			}
			*/

    		/* if($row->IS_DATE>0){
    				$s='$'.$row->NAME."='$row->STATIC_VALUE';\$vs=\$$row->NAME;";
					\Log::info($s);
    				eval($s);
    				$vars[$row->NAME]=$vs;
    				if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
    		}
    		else */
			if(is_numeric($row->STATIC_VALUE)){
    				$s='$'.$row->NAME."=$row->STATIC_VALUE;\$vs=\$$row->NAME;";
    				eval($s);
    				$vars[$row->NAME]=$vs;
    				if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
    		}
    		else if(strpos($row->STATIC_VALUE,"#[")>0){
    				$i=strpos($row->STATIC_VALUE,"#[");
    				$j=strpos($row->STATIC_VALUE,"]",$i);
    				if($j>$i){
    					$ms=substr($row->STATIC_VALUE,0,$i);
    					$key=substr($row->STATIC_VALUE,$i+1,$j-$i-2);
    					$vs=explode("\r",$vars[$ms]);
    					$vl="";
    					foreach($vs as $v){
    						$vx=explode("=",$v);
    						if(trim($vx[0])==$key){
    							$vl=$vx[1];
    							break;
    						}
    					}
    					if($vl){
    						$s='$'.$row->NAME."=$vl;\$vs=\$$row->NAME;";
    						eval($s);
    						$vars[$row->NAME]=$vs;
    						if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
    					}
    				}
    		}
    		else if (self::isFunction($row->STATIC_VALUE, "matlab")) {
				$i = strpos ( $row->STATIC_VALUE, "(" );
				$j = strpos ( $row->STATIC_VALUE, ")", $i );
				if ($j > $i) {
					$ms = explode ( ",", substr ( $row->STATIC_VALUE, $i + 1, $j - $i - 1 ) );
					$args = "";
					$matlab_code = $ms [0];
					for($i = 1; $i < sizeof ( $ms ); $i ++) {
						$args .= ($args == "" ? "" : "%20") . $vars [$ms [$i]];
					}
					$s = "\$vs = file_get_contents('http://energybuilder.co/eb/matlab/$matlab_code/$matlab_code.php?act=get&a=" . $args . "', true);";
					// echo "xxxxx".$s;
					eval ( $s );
					$vars [$row->NAME] = $vs;
					if ($show_echo)
						$valueLog = [ 
								"content" => "$row->NAME = $vs",
								"type" => "value" 
						];
				}
				// $s="$m = file_get_contents('http://energybuilder.co/eb/matlab/test.php?act=get&a=1%204%202', true);
			} 
    		else if(self::isFunction($row->STATIC_VALUE, "getEUTestAlloc")){
				$j=strpos($row->STATIC_VALUE,"(");
				$k=self::findClosedSign(")",$row->STATIC_VALUE,$j);
				if($k>$j && $j>0){
					$params=explode(",",substr($row->STATIC_VALUE,$j+1,$k-$j-1));
					//\Log::info ($params);
					if(count($params)>=3){
						$t_field=$params[2];
						$occur_date = str_replace("'","",$params[1]);
						$object_id = $params[0];
						$t_value = EbBussinessModel :: getEUTestAlloc($object_id,$occur_date,$t_field);
						$s='$'.$row->NAME."='$t_value';\$vs=\$$row->NAME;";
						eval($s);
						$vars[$row->NAME]=$vs;
						$sql = "select $t_field from EU_TEST_DATA_VALUE where EU_ID=$object_id and EFFECTIVE_DATE<='$occur_date' order by EFFECTIVE_DATE desc limit 1";
						if($show_echo) {
							$sqlLog		=  ["content" 	=> $sql,				"type" 		=> "sql"];
							$valueLog	=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
						}
					}
				}
			}
    		else if(self::isFunction($row->STATIC_VALUE, "getEUTest")){
				$j=strpos($row->STATIC_VALUE,"(");
				$k=self::findClosedSign(")",$row->STATIC_VALUE,$j);
				if($k>$j && $j>0){
					$params=explode(",",substr($row->STATIC_VALUE,$j+1,$k-$j-1));
					//\Log::info ($params);
					if(count($params)>=3){
						$t_field=$params[2];
						$occur_date = str_replace("'","",$params[1]);
						$object_id = $params[0];
						$t_value = EbBussinessModel :: getEUTest($object_id,$occur_date)[$t_field];
						$s='$'.$row->NAME."='$t_value';\$vs=\$$row->NAME;";
						eval($s);
						$vars[$row->NAME]=$vs;
						$sql = "select $t_field from EU_TEST_DATA_VALUE where EU_ID=$object_id and EFFECTIVE_DATE<='$occur_date' order by EFFECTIVE_DATE desc limit 1";
						if($show_echo) {
							$sqlLog		=  ["content" 	=> $sql,				"type" 		=> "sql"];
							$valueLog	=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
						}
					}
				}
			}
    		else if(self::isFunction($row->STATIC_VALUE, "calculateMTD")){
				$takeRowsCount = 1;
				$vars[$row->NAME]='null';
				$queryField = self::getQueryObject($row, $vars, $takeRowsCount, $show_echo);
				if($queryField != null){
					$vs = $queryField
						->whereDate('OCCUR_DATE','>=',$first_day)
						->whereDate('OCCUR_DATE','<=',$occur_date)
						->select(\DB::raw("SUM({$row->VALUE_COLUMN}) AS total"))
						->first()->total;
						//\Log::info($getDataResult);
					$vars[$row->NAME]=$vs?$vs:0;
					unset($getDataResult);
				}
				if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = {$vars[$row->NAME]}",	"type" 		=> "value"];
			}
    		else if(self::isFunction($row->STATIC_VALUE, "getData")){
				$field=$row->VALUE_COLUMN;
				$takeRowsCount = 1000;
				$queryField = self::getQueryObject($row, $vars, $takeRowsCount, $show_echo);
				if($queryField != null){
					$originAttrCase = \Helper::setGetterUpperCase();
					$getDataResult = $queryField->select($field)->skip(0)->take($takeRowsCount)->get();
					\Helper::setGetterCase($originAttrCase);
					unset($where);
					unset($params);
					$num_rows = count($getDataResult);
					if($num_rows==0)
					{
						$s='$'.$row->NAME."='null';\$vs=\$$row->NAME;";
						eval($s);
						if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
					}
					else if($num_rows==1)
					{
						$sqlvalue= (is_array ( $getDataResult )?$getDataResult[0]:$getDataResult->toArray()[0]);
						$stdvl = $sqlvalue->$field;
						$s='$'.$row->NAME."='$stdvl';\$vs=\$$row->NAME;";
						eval($s);
						if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
					}
					else
					{
						
						$sqlvalue= is_array ( $getDataResult )?$getDataResult:$getDataResult->toArray();
						$sqlarray=array();
						for($k=0;$k<$num_rows;$k++)
						{
							foreach ($sqlvalue[$k] as $key => $value)
							if($key!=='rn') //skip Oracle rownum column added
							{
//     									if(is_numeric($key))
									$sqlarray[$k][$key]=$value;
							}
						}
						$s='$'.$row->NAME.'=$sqlarray;'."\$vs=\$$row->NAME;";
						eval($s);
						if($show_echo) $valueLog=  ["content" 	=> "$row->NAME is an array with $num_rows rows",	"type" 		=> "value"];
					}

					$vars[$row->NAME]=$vs;
					unset($field);
					unset($getDataResult);
					unset($s);
				}
			}
			else{
				$v=$row->STATIC_VALUE;
				$i=strpos($v,".");
				if($i>0)
				{
					$table=substr($v,0,$i);
					//echo "table: $table<br>";
					$j=strpos($v,"(",$i);
					$k=strpos($v,")",$i);
					if($j>$i && $k>$j)
					{
						$field=substr($v,$i+1,$j-$i-1);
						$sql	="select `$field` from `$table` where 1";
						
						//echo "field: $field<br>";
						$params=explode(",",substr($v,$j+1,$k-$j-1));
						foreach ($params as $param)
						{
							$ps=explode("=",$param);
							if ($vars[$ps[1]])
								$pp = "$ps[0] = '".$vars[$ps[1]]."'";
								else
									$pp = "$ps[0] = '$ps[1]'";
									$sql.=" and $pp";
									//echo "param: $pp<br>";
						}
						$sqlvalue=getOneValue($sql);
						$s='$'.$row->NAME."='$sqlvalue';\$vs=\$$row->NAME;";
						eval($s);
						$vars[$row->NAME]=$vs;
						$sql	= \Helper::removeInvalidCharacter($sql);
						
						if($show_echo) {
							$sqlLog		=  ["content" 	=> $sql,				"type" 		=> "sql"];
							$valueLog	=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
						}
					}
				}
				else{
					$s='$'.$row->NAME."=$row->STATIC_VALUE;\$vs=\$$row->NAME;";
					//\Log::info($s);
					eval($s);
					$vars[$row->NAME]=$vs;
					if($show_echo) $valueLog=  ["content" 	=> "$row->NAME = $vs",	"type" 		=> "value"];
				}
			}
			if($show_echo) {
				//if($sqlLog) 	$logs["variables"][] 	= $sqlLog;
				if($valueLog) 	$logs["variables"][] 	= $valueLog;
			}
    	}
    
    	if($show_echo) $logs["variables"][] =  ["content" => "Processing final expression ...",	"type" => "source"];
    	
    	$f=$formula;
    	$varsKey = [];
    	foreach($vvv as $key => $v)
    	{
    		//$f=str_replace($v,$vars[$v],$f);
    		if(!isset($vars[$v])||!$vars[$v]) $f=str_replace($v,"0",$f);
    		
    		else if(is_array($vars[$v])) {
				$f=str_replace($v,"\$vars['$v']",$f);
    		}
    		else $f=str_replace($v,$vars[$v],$f);
    				//if() echo "$f<br>";
    	}
    	$f=str_replace("@DATE",$CURRENT_DATE,$f);
		$f=str_replace("--","+",$f);
		
		if($extVars){
			foreach($extVars as $var=>$value){
				$f=str_replace($var,$value,$f);
			}
		}
		
    	$s='$vf = '.$f.";";
    	
    	if($show_echo) $logs["variables"][] =  ["content" => "$fDisplay = $f",	"type" => "sql"];
    	
    	if(!(self::php_syntax_error($s)))
    	{
      		set_error_handler("evalErrorHandler");
	    	try {
				//\Log::info($varsKey);
    			eval($s);
    			if($show_echo) $logs["variables"][] =  ["content" => "Final result: $vf",	"type" => "result"];
	    	} catch(Exception $e ){
    			\Log::info("Exception with eval $s ".$e->getMessage());
	    		$vf=null;
    			if($show_echo) $logs["variables"][] =  ["content" => "Syntax error in final expression : $s",	"type" => "error"];
	    	}
	    		
  	    	restore_error_handler();
    	}
    	else
    	{
    		$vf=null;
    		if($show_echo) $logs["variables"][] =  ["content" => "Syntax error in final expression: $s",	"type" => "error"];
    	}
    	
    	if($show_echo) return $logs;
    	return $vf;
    }

    public static function findClosedSign($sign,$s,$from){
    	$s1="";
    	if($sign===")") $s1="(";
    	else if($sign==="]") $s1="[";
    	else if($sign==="}") $s1="{";
    	if(!$s1)
    		return false;
    		if (strpos($s,$sign,$from) === false)
    			return false;
    			$i=$from;
    			$k=0;
    			while($i<strlen($s)){
    				if($s[$i]===$s1)
    					$k++;
    					else if($s[$i]===$sign)
    						$k--;
    						if($k==0)
    							return $i;
    							$i++;
    			}
    			return null;
    }
    
     public static function php_syntax_error($code){
    	$braces=0;
    	$inString=0;
    	foreach (token_get_all('<?php ' . $code) as $token) {
    		if (is_array($token)) {
    			switch ($token[0]) {
    				case T_CURLY_OPEN:
    				case T_DOLLAR_OPEN_CURLY_BRACES:
    				case T_START_HEREDOC: ++$inString; break;
    				case T_END_HEREDOC:   --$inString; break;
    			}
    		} else if ($inString & 1) {
    			switch ($token) {
    				case '`': case '\'':
    				case '"': --$inString; break;
    			}
    		} else {
    			switch ($token) {
    				case '`': case '\'':
    				case '"': ++$inString; break;
    				case '{': ++$braces; break;
    				case '}':
    					if ($inString) {
    						--$inString;
    					} else {
    						--$braces;
    						if ($braces < 0) break 2;
    					}
    					break;
    			}
    		}
    	}
    	$inString = @ini_set('log_errors', false);
    	$token = @ini_set('display_errors', true);
    	ob_start();
    	$braces || $code = "if(0){{$code}\n}";
    	if (eval($code) === false)
    	{
    		ob_end_clean();
    		$code = true;
    		/*
    		 if ($braces) {
    		 $braces = PHP_INT_MAX;
    		 } else {
    		 false !== strpos($code,CR) && $code = strtr(str_replace(CRLF,LF,$code),CR,LF);
    		 $braces = substr_count($code,LF);
    		 }
    		 $code = ob_get_clean();
    		 $code = strip_tags($code);
    		 if (preg_match("'syntax error, (.+) in .+ on line \d+)$'s", $code, $code)) {
    		 $code[2] = (int) $code[2];
    		 $code = $code[2] <= $braces
    		 ? array($code[1], $code[2])
    		 : array('unexpected $end' . substr($code[1], 14), $braces);
    		 }
    		 else $code = array('syntax error', 0);
    		 */
    	}
    	else
    	{
    		ob_end_clean();
    		$code = false;
    	}
    	@ini_set('display_errors', $token);
    	@ini_set('log_errors', $inString);
    	return $code;
    }
    
    
    public static function calculateBg($flow_phase,$T_obs,$P_obs,$API_obs,$occur_date,$object_id,$object_type_code)
    {
    	return 1;
    	$_Bg=null;
    	set_error_handler("evalErrorHandler");
    	if($flow_phase==1)//OIL
    	{
    		try {
	    		if($T_obs && $P_obs && $API_obs)
	    			$_Bg=self::calculateCrudeOil($T_obs, $P_obs, $API_obs);
    		} catch( Exception $e ){
    			\Log::info($e->getTraceAsString());
    		}
    	}
    	else if($flow_phase==2 || $flow_phase==21)//GAS
    	{
    		$cqst = CodeQltySrcType::getTableName();
    		$qdata = QltyData::getTableName();
    		
    		$row= QltyData::getQualityRow($object_id,$object_type_code,$occur_date);
    		if($row)
    		{
    			$dataID=$row->ID;
//     			\DB::enableQueryLog();
    			$querys = [
    					'C1' =>QltyDataDetail::whereHas('QltyProductElementType',function ($query) {$query->where("CODE",'C1' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C2' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C2' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C3' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C3' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C4I'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'IC4');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C4N'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'NC4');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C5I'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'IC5');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C5N'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'NC5');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C6' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C6' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'C7' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C7+');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'H2S'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'H2S');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'CO2'=>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'CO2');})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'N2' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'N2' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(MOLE_FACTION)")),
    					'M_C7' =>QltyProductElementType::where('CODE','C7+')->select(\DB::raw("max(MOL_WEIGHT)")),
    					'G_C7' =>QltyDataDetail::whereHas('QltyProductElementType' , function ($query) {$query->where("CODE",'C7+' );})->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(GAMMA_C7)")),
    					 ];
	
    			$qr = \DB::table(null);
    			foreach($querys as $key => $query ){
    				$qr = $qr->selectSub($query->getQuery(),$key);
    			}
    			$qdltDatas = $qr->first();
//      			\Log::info("qdltDatas C1 ".$qdltDatas->C1);
    								
// 				\Log::info(\DB::getQueryLog());
				
    			if($qdltDatas)
    			{
    				$MolWt_C7	=$qdltDatas->M_C7;
    				$gamma_C7	=$qdltDatas->G_C7;
    				$M_C1	= $qdltDatas->C1;
    				$M_C2	= $qdltDatas->C2;
    				$M_C3	= $qdltDatas->C3;
    				$M_C4n	= $qdltDatas->C4N;
    				$M_C4i	= $qdltDatas->C4I;
    				$M_C5n	= $qdltDatas->C5N;
    				$M_C5i	= $qdltDatas->C5I;
    				$M_C6n	= $qdltDatas->C6;
    				$M_C7	= $qdltDatas->C7;
    				$M_H2S	= $qdltDatas->H2S;
    				$M_CO2	= $qdltDatas->CO2;
    				$M_N2	= $qdltDatas->N2;
    
    				if($T_obs && $P_obs){
    					try {
    						$_Bg=self::calculateGas($T_obs, $P_obs,$MolWt_C7,$gamma_C7,$M_C1,$M_C2,$M_C3,$M_C4n,$M_C4i,$M_C5n,$M_C5i,$M_C6n,$M_C7,$M_H2S,$M_CO2,$M_N2);
    					} catch( Exception $e ){
    						\Log::info($e->getTraceAsString());
    					}
    				}
    			}
    		}
    	}
    	restore_error_handler();
    	return $_Bg;
    }
    
    

    public static function calculateGasWithImp(
    		$T_obs,
    		$P_obs,
    
    		$M_C1,
    		$M_C2,
    		$M_C3,
    		$M_C4n,
    		$M_C4i,
    		$M_C5n,
    		$M_C5i,
    		$M_C6n,
    		$M_C7,
    		$M_H2S,
    		$M_CO2,
    		$M_N2,
    
    		$MolWt_C7
    		)
    {
    
    	$al_0=0.05207300;
    	$al_1=1.01600000;
    	$al_2=0.86961000;
    	$al_3=0.72646000;
    	$al_4=0.85101000;
    	$al_5=0.00000000;
    	$al_6=0.02081800;
    	$al_7=-0.00015060;
    
    	$b_0=-0.39741000;
    	$b_1=1.05030000;
    	$b_2=0.96592000;
    	$b_3=0.78569000;
    	$b_4=0.98211000;
    	$b_5=0.00000000;
    	$b_6=0.45536000;
    	$b_7=-0.00376840;
    
    	$A_1=0.32650;
    	$A_2=-1.07000;
    	$A_3=-0.53390;
    	$A_4=0.01569;
    	$A_5=-0.05165;
    	$A_6=0.54750;
    	$A_7=-0.73610;
    	$A_8=0.18440;
    	$A_9=0.10560;
    	$A_10=0.61340;
    	$A_11=0.72100;
    
    	$yi1	=$M_C1/1;
    	$yi2	=$M_C2/1;
    	$yi3	=$M_C3/1;
    	$yi4	=$M_C4n/1;
    	$yi5	=$M_C4i/1;
    	$yi6	=$M_C5n/1;
    	$yi7	=$M_C5i/1;
    	$yi8	=$M_C6n/1;
    	$yi9	=$M_C7/1;
    	$yi10	=$M_H2S/1;
    	$yi11	=$M_CO2/1;
    	$yi12	=$M_N2/1;
    
    	$Tc1	=(-116.67+460);
    	$Tc2	=(89.92+460);
    	$Tc3	=(206.06+460);
    	$Tc4	=(305.62+460);
    	$Tc5	=(274.46+460);
    	$Tc6	=(385.8+460);
    	$Tc7	=(369.1+460);
    	$Tc8	=(453.6+460);
    	$Tc9	=(512.7+460);
    	$Tc10	=(212.45+460);
    	$Tc11	=(87.91+460);
    	$Tc12	=(-232.51+460);
    
    	$Mw1	=$yi1*16.04;
    	$Mw2	=$yi2*30.07;
    	$Mw3	=$yi3*44.1;
    	$Mw4	=$yi4*58.12;
    	$Mw5	=$yi5*58.12;
    	$Mw6	=$yi6*72.15;
    	$Mw7	=$yi7*72.15;
    	$Mw8	=$yi8*86.18;
    	$Mw9	=$yi9*$MolWt_C7;
    	$Mw10	=$yi9*34.08;
    	$Mw11	=$yi9*44.01;
    	$Mw12	=$yi9*28.01;
    
    	$Pc1	=666.4;
    	$Pc2	=706.5;
    	$Pc3	=616;
    	$Pc4	=550.6;
    	$Pc5	=527.9;
    	$Pc6	=488.6;
    	$Pc7	=490.4;
    	$Pc8	=436.9;
    	$Pc9	=396.8;
    	$Pc10	=1300;
    	$Pc11	=1071;
    	$Pc12	=493.1;
    
    	$I30 = $Mw1+$Mw2+$Mw3+$Mw4+$Mw5+$Mw6+$Mw7+$Mw8+$Mw9+$Mw10+$Mw11+$Mw12;
    	$I31 = $I30/29;
    
    	$J = $al_0+
    	($al_1*$yi10*$Tc10/$Pc10+
    			$al_2*$yi11*$Tc11/$Pc11+
    			$al_3*$yi12*$Tc12/$Pc12)+
    			$al_4*(
    					$yi1*$Tc1/$Pc1+
    					$yi2*$Tc2/$Pc2+
    					$yi3*$Tc3/$Pc3+
    					$yi4*$Tc4/$Pc4+
    					$yi5*$Tc5/$Pc5+
    					$yi6*$Tc6/$Pc6+
    					$yi7*$Tc7/$Pc7+
    					$yi8*$Tc8/$Pc8)+
    					$al_6*$yi9*$MolWt_C7+$al_7*pow(($yi9*$MolWt_C7),2);
    
    					$K = $b_0+(
    							$b_1*$yi10*$Tc10/sqrt($Pc10)+
    							$b_2*$yi11*$Tc11/sqrt($Pc11)+
    							$b_3*$yi12*$Tc12/sqrt($Pc12)
    							)+
    							$b_4*(
    									$yi1*$Tc1/sqrt($Pc1)+
    									$yi2*$Tc2/sqrt($Pc2)+
    									$yi3*$Tc3/sqrt($Pc3)+
    									$yi4*$Tc4/sqrt($Pc4)+
    									$yi5*$Tc5/sqrt($Pc5)+
    									$yi6*$Tc6/sqrt($Pc6)+
    									$yi7*$Tc7/sqrt($Pc7)+
    									$yi8*$Tc8/sqrt($Pc8)
    									)+
    									$b_6*$yi9*$MolWt_C7+$b_7*pow(($yi9*$MolWt_C7),2);
    
    									$T_pc 	= $K*$K/$J;
    									$P_pc 	= $T_pc/$J;
    
    									$T_pr	= ($T_obs+460)/$T_pc;
    									$P_pr	= $P_obs/$P_pc;
    
    									$C_1	=$A_1+ $A_2/$T_pr+$A_3/pow($T_pr,3)+$A_4/pow($T_pr,4)+$A_5/pow($T_pr,5);
    									$C_2 	=$A_6+ $A_7/$T_pr+$A_8/pow($T_pr,2);
    									$C_3	=$A_9*($A_7/$T_pr+$A_8/pow($T_pr,2));
    
    									$z=1;
    									$ii=0;
    									while(true)
    									{
    										$ii++;
    										if($ii>100)
    											break;
    											$rho=0.27*$P_pr/($z*$T_pr);
    											$C_4=$A_10*(1+$A_11*pow($rho,2))*(pow($rho,2)/pow($T_pr,3))*exp(-$A_11*pow($rho,2));
    											$Fx=$z-(1+$C_1*$rho+$C_2*pow($rho,2)-$C_3*pow($rho,5)+$C_4);
    											$dFx=1+($C_1*$rho/$z)+(2*$C_2*pow($rho,2)/$z)-(5*$C_3*pow($rho,5)/$z)+(2*$A_10*pow($P_pr,2)*pow(0.27,2))*exp(-$A_11*pow(($P_pr*0.27/($T_pr*$z)),2))*(-1*pow($A_11,2)*pow($P_pr,4)*pow(0.27,4)+$A_11*pow($P_pr,2)*pow($T_pr,2)*pow(0.27,2)*pow($z,2)+pow($T_pr,4)*pow($z,4))/(pow($T_pr,9)*pow($z,7));
    
    											if(abs($Fx)<0.0000000000001)
    												break;
    
    												$z=$z-$Fx/$dFx;
    									}
    
    									if($ii>100)
    										return -1;
    										else
    										{
    											if($P_obs!=0)
    											{
    												$Bg=0.02827*$z*($T_obs+460)/$P_obs;
    												return $Bg;
    											}
    											else
    												return -2;
    										}
    }
    //**********************************************************
    
    public static function calculateGasNoImp(
    		$T_obs,
    		$P_obs,
    		$M_C1,
    		$M_C2,
    		$M_C3,
    		$M_C4n,
    		$M_C4i,
    		$M_C5n,
    		$M_C5i,
    		$M_C6n,
    		$M_C7,
    		$MolWt_C7,
    		$gamma_C7
    		)
    {
    	$T_b	= pow(4.5579*pow($MolWt_C7,0.15178)*pow($gamma_C7,0.15427),3);
    	$Tc_C7  = 341.7+811*$gamma_C7+(0.4244+0.1174*$gamma_C7)*$T_b+(0.4669-3.2623*$gamma_C7)*pow(10,5)/$T_b;
    	$Pc_C7	= exp(8.3634-0.0566/$gamma_C7-(0.24244+2.2898/$gamma_C7+0.11857/(pow($gamma_C7,2)))*pow(10,-3)*$T_b+(1.4685+3.648/$gamma_C7+0.47227/(pow($gamma_C7,2)))*(pow(10,-7))*pow($T_b,2)-(0.42019+1.6977/(pow($gamma_C7,2)))*(pow(10,-10))*pow($T_b,3));
    
    	$A_1=0.32650;
    	$A_2=-1.07000;
    	$A_3=-0.53390;
    	$A_4=0.01569;
    	$A_5=-0.05165;
    	$A_6=0.54750;
    	$A_7=-0.73610;
    	$A_8=0.18440;
    	$A_9=0.10560;
    	$A_10=0.61340;
    	$A_11=0.72100;
    
    	$yi1	=$M_C1/1;
    	$yi2	=$M_C2/1;
    	$yi3	=$M_C3/1;
    	$yi4	=$M_C4n/1;
    	$yi5	=$M_C4i/1;
    	$yi6	=$M_C5n/1;
    	$yi7	=$M_C5i/1;
    	$yi8	=$M_C6n/1;
    	$yi9	=$M_C7/1;
    
    	$Tc1	=$yi1*(-116.67+460);
    	$Tc2	=$yi2*(89.92+460);
    	$Tc3	=$yi3*(206.06+460);
    	$Tc4	=$yi4*(305.62+460);
    	$Tc5	=$yi5*(274.46+460);
    	$Tc6	=$yi6*(385.8+460);
    	$Tc7	=$yi7*(369.1+460);
    	$Tc8	=$yi8*(453.6+460);
    	$Tc9	=$yi9*$Tc_C7;
    
    	$Mw1	=$yi1*16.04;
    	$Mw2	=$yi2*30.07;
    	$Mw3	=$yi3*44.1;
    	$Mw4	=$yi4*58.12;
    	$Mw5	=$yi5*58.12;
    	$Mw6	=$yi6*72.15;
    	$Mw7	=$yi7*72.15;
    	$Mw8	=$yi8*86.18;
    	$Mw9	=$yi9*128;
    
    	$Pc1	=$yi1*666.4;
    	$Pc2	=$yi2*706.5;
    	$Pc3	=$yi3*616;
    	$Pc4	=$yi4*550.6;
    	$Pc5	=$yi5*527.9;
    	$Pc6	=$yi6*488.6;
    	$Pc7	=$yi7*490.4;
    	$Pc8	=$yi8*436.9;
    	$Pc9	=$yi9*$Pc_C7;
    
    	$I30 = $Mw1+$Mw2+$Mw3+$Mw4+$Mw5+$Mw6+$Mw7+$Mw8+$Mw9;
    	$I31 = $I30/29;
    
    	$T_pc 	= $Tc1+$Tc2+$Tc3+$Tc4+$Tc5+$Tc6+$Tc7+$Tc8+$Tc9;
    	$P_pc 	= $Pc1+$Pc2+$Pc3+$Pc4+$Pc5+$Pc6+$Pc7+$Pc8+$Pc9;
    
    	$T_pr	= ($T_obs+460)/$T_pc;
    	$P_pr	= $P_obs/$P_pc;
    
    	$C_1	=$A_1+ $A_2/$T_pr+$A_3/pow($T_pr,3)+$A_4/pow($T_pr,4)+$A_5/pow($T_pr,5);
    	$C_2 	=$A_6+ $A_7/$T_pr+$A_8/pow($T_pr,2);
    	$C_3	=$A_9*($A_7/$T_pr+$A_8/pow($T_pr,2));
    
    	$z=1;
    	$ii=0;
    	while(true)
    	{
    		$ii++;
    		if($ii>100)
    			break;
    			$rho=0.27*$P_pr/($z*$T_pr);
    			$C_4=$A_10*(1+$A_11*pow($rho,2))*(pow($rho,2)/pow($T_pr,3))*exp(-$A_11*pow($rho,2));
    			$Fx=$z-(1+$C_1*$rho+$C_2*pow($rho,2)-$C_3*pow($rho,5)+$C_4);
    			$dFx=1+($C_1*$rho/$z)+(2*$C_2*pow($rho,2)/$z)-(5*$C_3*pow($rho,5)/$z)+(2*$A_10*pow($P_pr,2)*pow(0.27,2))*exp(-$A_11*pow(($P_pr*0.27/($T_pr*$z)),2))*(-1*pow($A_11,2)*pow($P_pr,4)*pow(0.27,4)+$A_11*pow($P_pr,2)*pow($T_pr,2)*pow(0.27,2)*pow($z,2)+pow($T_pr,4)*pow($z,4))/(pow($T_pr,9)*pow($z,7));
    
    			if(abs($Fx)<0.0000000000001)
    				break;
    
    				$z=$z-$Fx/$dFx;
    	}
    
    	if($ii>100)
    		return -1;
    		else
    		{
    			$Bg=0.02827*$z*($T_obs+460)/$P_obs;
    			return $Bg;
    		}
    }
    //********************************************************************************************************************
    
    public static function calculateGas($T_obs, $P_obs,
    		$MolWt_C7,
    		$gamma_C7,
    		$M_C1,
    		$M_C2,
    		$M_C3,
    		$M_C4n,
    		$M_C4i,
    		$M_C5n,
    		$M_C5i,
    		$M_C6n,
    		$M_C7,
    		$M_H2S,
    		$M_CO2,
    		$M_N2
    		)
    {
    	if($M_H2S>0 || $M_CO2>0 || $M_N2>0)
    		return self::calculateGasWithImp(
    				$T_obs,
    				$P_obs,
    				$M_C1,
    				$M_C2,
    				$M_C3,
    				$M_C4n,
    				$M_C4i,
    				$M_C5n,
    				$M_C5i,
    				$M_C6n,
    				$M_C7,
    				$M_H2S,
    				$M_CO2,
    				$M_N2,
    				$MolWt_C7);
    		else
    			return self::calculateGasNoImp(
    					$T_obs,
    					$P_obs,
    					$M_C1,
    					$M_C2,
    					$M_C3,
    					$M_C4n,
    					$M_C4i,
    					$M_C5n,
    					$M_C5i,
    					$M_C6n,
    					$M_C7,
    					$MolWt_C7,
    					$gamma_C7);
    
    }
    //********************************************************************
    
public static function calculateCrudeOil($T_obs, $P_obs, $API_obs) {
		// Step 1: Check for data's validity
		if ($T_obs <= - 58.0)
			$T_obs = - 58.0;
		if ($T_obs >= 302.0)
			$T_obs = 302.0;
		if ($P_obs <= 0)
			$P_obs = 0;
		if ($P_obs >= 1500)
			$P_obs = 1500;
		
		$a_1 = - 0.148759;
		$a_2 = - 0.267408;
		$a_3 = 1.08076;
		$a_4 = 1.269056;
		$a_5 = - 4.089591;
		$a_6 = - 1.871251;
		$a_7 = 7.438081;
		$a_8 = - 3.536296;
		
		$R1 = ($T_obs - 32) / 1.8;
		$tau = $R1 / 630;
		$R9 = ($a_1 + ($a_2 + ($a_3 + ($a_4 + ($a_5 + ($a_6 + ($a_7 + $a_8 * $tau) * $tau) * $tau) * $tau) * $tau) * $tau) * $tau) * $tau;
		$R13 = $R1 - $R9;
		$R17 = 1.8 * $R13 + 32;
		
		$gamma_o = 141.5 / ($API_obs + 131.5);
		$rho_o_obs = $gamma_o * 999.016;
		if ($rho_o_obs <= 470.5)
			$rho_o_obs = 470.5;
		if ($rho_o_obs >= 1201.8)
			$rho_o_obs = 1201.8;
		
		$T_star = $R17;
		$delta_60 = 0.013749795470;
		
		// Step 2: Initial Density
		
		$C32 = $rho_o_obs;
		$C33 = 341.0957;
		$C34 = 0;
		$C35 = 0;
		
		$ii = 0;
		while ( true ) {
			$ii ++;
			if ($ii >= 100)
				break;
			
			$C36 = $delta_60 / 2 * (($C33 / $C32 + $C34) / $C32 + $C35);
			$C37 = (2 * $C33 + $C34 * $C32) / ($C33 + ($C34 + $C35 * $C32) * $C32);
			$C38 = $C32 * (1 + (exp ( $C36 * (1 + 0.8 * $C36) ) - 1) / (1 + $C36 * (1 + 1.6 * $C36) * $C37));
			$C39 = ($C33 / $C38 + $C34) / $C38 + $C35;
			$C40 = exp ( - $C39 * ($T_star - 60.0068749) * (1 + 0.8 * $C39 * ($T_star - 60.0068749 + $delta_60)) );
			$C41 = 2;
			$C42 = exp ( - 1.9947 + 0.00013427 * $T_star + (793920 + 2326 * $T_star) / ($C38 * $C38) );
			$C43 = 1 / (1 - 0.00001 * $C42 * $P_obs);
			$C44 = $C40 * $C43;
			$C45 = $C32 * $C44;
			
			$C48 = $rho_o_obs - $C45;
			$C51 = $rho_o_obs / $C44 - $C32;
			$C52 = $C41 * $C39 * ($T_obs - 60) * (1 + 1.6 * $C39 * ($T_obs - 60));
			$C53 = (2 * $C43 * $P_obs * $C42 * (7.9392 + 0.02326 * $T_obs)) / ($C32 * $C32);
			$C54 = $C51 / (1 + $C52 + $C53);
			
			$C32 = $C32 + $C54;
			
			if (abs ( $C48 ) < 0.000001) {
				$rho_60 = $C32;
				break;
			}
		}
		if ($ii >= 100)
			return - 4;
		if ($C39 <= 0.00023 || $C39 >= 0.00093)
			return - 5;
		
		$B62 = $rho_60;
		$B63 = $B62 / 999.016;
		$B64 = $C40;
		$B65 = $C42;
		$B66 = $C43;
		$B67 = $C44;
		$B68 = round ( $B67, 8 );
		
		$ret = $B68;
		return $ret;
	}
    
    //***************************************************************
    
	
	public static function calculateTankVolume($tank_id,$tank_level){
		$tank2 = StrappingTableData:: whereHas('Tank',function ($query) use ($tank_id) {
													$query->where("ID",$tank_id );
											})
					->where("STRAPPING_READING",'>=',$tank_level )
					->orderBy('STRAPPING_READING')
					->first();
		if($tank2){
			if($tank2->STRAPPING_READING==$tank_level) return $tank2->STRAPPING_VALUE;
			
			$tank1 = StrappingTableData:: whereHas('Tank',function ($query) use ($tank_id) {
								$query->where("ID",$tank_id );
							})
							->where("STRAPPING_READING",'<',$tank_level )
							->orderBy('STRAPPING_READING','desc')
							->first();
			if($tank1){
				return $tank1->STRAPPING_VALUE+
						(($tank2->STRAPPING_VALUE-$tank1->STRAPPING_VALUE)*($tank_level-$tank1->STRAPPING_READING)/
							($tank2->STRAPPING_READING-$tank1->STRAPPING_READING));
			}
		}
		return -1;
	}
	
	
	public static function getDataFormulaContract($qltyFormulas,$contractId,$year) {
		$aryMstCalcu = array ();
		$aryValue = array ();
		$x = array ();
		$contractIdGlobal = $contractId;
		$yearGlobal = $year;
		
		foreach($qltyFormulas 	as 	$key 	=> $row) {
			if ($row->LEVEL == 0) {
				$str = str_replace ( 'contract_attr(', '', $row ->FORMULA );
				$str = str_replace ( ')', '', $str );
				$str = $row->ID . "," . $str;
				$str = "$str,,$contractId,$year";
				
				$aryMstCalcu ['fn' . $row->FORMULA_NO] = call_user_func_array ( "contract_attr", explode ( ',', $str ) );
				$aryValue [$row->ID] = ( int ) $aryMstCalcu ['fn' . $row->FORMULA_NO];
			} else {
			
				$x [$row->ID] = $row->FORMULA;
			}
		}
		
		/* while ( $row = mysql_fetch_array ( $result ) ) {
			if ($row ['LEVEL'] == 0) {
				$str = str_replace ( 'contract_attr(', '', $row ['FORMULA'] );
				$str = str_replace ( ')', '', $str );
				$str = $row ['ID'] . "," . $str;
	
				$aryMstCalcu ['fn' . $row ['FORMULA_NO']] = call_user_func_array ( "contract_attr", explode ( ',', $str ) );
				$aryValue [$row ['ID']] = ( int ) $aryMstCalcu ['fn' . $row ['FORMULA_NO']];
			} else {
	
				$x [$row ['ID']] = $row ['FORMULA'];
			}
		} */
		fnc( 1, $aryMstCalcu );
		set_error_handler("evalErrorHandler");
		foreach ( $x as $kk => $vv ) {
			$vvoutput = preg_replace("/fn\((\w+)\)/", "fnc('$1')", $vv);
			try {
				eval ( '$aryValue[$kk] = (' . $vvoutput . ');' );
			} catch( Exception $e ){
				\Log::info("Exception with eval $vv ".$e->getMessage());
				$aryValue[$kk] = 0;
			}
			 
		}
		restore_error_handler();
		return $aryValue;
	}
    
}
