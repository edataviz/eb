<?php
namespace App\Http\Controllers;
use App\Models\IntConnection;
use App\Models\IntImportLog;
use App\Models\IntImportSetting;
use App\Models\IntSystem;
use App\Models\IntTagMapping;
use App\Models\IntTagSet;
use App\Models\IntTagTrans;
use App\Models\Formula;

use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;
use Input;
use PHPExcel_Shared_Date;
use Schema;

class InterfaceController extends EBController {

    protected $isOracle = false;

    public function __construct() {
        parent::__construct();
        $this->isOracle = config('database.default')==='oracle';
    }

    public function _exportData(){
        $filterGroups	= \Helper::getCommonGroupFilterExport();
        $filterGroups['frequenceFilterGroup'][0]["attributes"] = "multiple";
//        $filterGroups['frequenceFilterGroup'][1]["attributes"] = "multiple";
        $filterGroups['frequenceFilterGroup'][2]["attributes"] = "multiple";
        $filterGroups['frequenceFilterGroup'][3]["attributes"] = "multiple";
        $filterGroups['frequenceFilterGroup'][4]["attributes"] = "multiple";
        $filterGroups['frequenceFilterGroup'][5]["attributes"] = "multiple";
//         $filterGroups['frequenceFilterGroup'][6]["attributes"] = "multiple";
        return view ( 'front.exportdata',['filters'=>$filterGroups]);
    }

	public function _index() {
		$int_import_setting = $this->loadImportSetting();
		return view ( 'front.importdata', ['int_import_setting'=>$int_import_setting]);
	}
	
	public function _indexDataloader() {
		$int_import_setting = $this->loadImportSetting();
		return view ( 'front.dataloader', ['int_import_setting'=>$int_import_setting]);
	}
	
	public function getImportSetting(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$int_import_setting = IntImportSetting::where(['ID'=>$data['id']])->select ('*')->first();
		
		return response ()->json ( $int_import_setting );
	}
	
	private function loadImportSetting(){
		\Helper::setGetterUpperCase();
		$int_import_setting = IntImportSetting::all('ID', 'NAME');
		return  $int_import_setting;
	}
	
	public function _indexConfig() {
		$int_import_setting = IntImportSetting::all('ID', 'NAME');
		$int_system = IntSystem::all('CODE', 'NAME');
		return view ( 'front.sourceconfig', ['int_import_setting'=>$int_import_setting, 'int_system'=>$int_system]);
	}
	
	public function detailsConnection(Request $request) {
        \Helper::setGetterUpperCase();
		$data = $request->all ();
	
		$dt = IntConnection::where(['ID'=>$data['id']])->select('*')->get();
		
		$int_tag_set = $this->getIntTagSet($data['id']);
		
		return response ()->json ( ['dt'=>$dt, 'int_tag_set'=>$int_tag_set] );
	}
	
	public function saveConn(Request $request) {
		$data = $request->all ();
	
		$condition = array (
				'ID' => $data ['id']
		);
		
		if (isset ( $data ['name'] ))
			$obj ['NAME'] = $data ['name'];
		
		if (isset ( $data ['server'] ))
			$obj ['SERVER'] = $data ['server'];
		
		if (isset ( $data ['system'] ))
			$obj ['SYSTEM'] = $data ['system'];
		
		if (isset ( $data ['username'] ))
			$obj ['USER_NAME'] = $data ['username'];
		
		if (isset ( $data ['password'] ))
			$obj ['PASSWORD'] = $data ['password'];
		
		if (isset ( $data ['id']) && $data ['id'] <= 0)
			$obj ['TYPE'] = $data ['type'];
		
//		$result = IntConnection::updateOrCreate ( $condition, $obj );
		$result = ($data ['id'] != 0) ? IntConnection::updateOrCreate($condition,$obj) : IntConnection::firstOrCreate($obj);
		$id = $result->ID;

		return response ()->json (['id'=>$id, 'conn'=>$this->loadCon($data ['type'])]);
	}
	
	public function loadTagSets(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		return response ()->json ($this->getIntTagSet($data['connection_id']));
	}
	
	public function renameTagSet(Request $request) {
		$data = $request->all ();
			
		IntTagSet::where(['ID'=>$data['id']])->update(['NAME'=>$data['name']]);
		return response ()->json ($data['id']);
	}
	
	public function deleteTagSet(Request $request) {
		$data = $request->all ();
			
		IntTagSet::where(['ID'=>$data['id']])->delete();
		return response ()->json ($data['id']);
	}
	
	private function getIntTagSet($conn_id) {
		$int_tag_set = IntTagSet::where(['CONNECTION_ID'=>$conn_id])->get(['ID', 'NAME']);
		return $int_tag_set;
	}
	
	public function renameConn(Request $request) {
		$data = $request->all ();
	
		IntConnection::where(['ID'=>$data['id']])->update(['NAME'=>$data['name']]);
		return response ()->json (['id'=>$data['id'], 'conn'=>$this->loadCon($data ['type'])]);
	}
	
	public function deleteConn(Request $request) {
		$data = $request->all ();
	
		IntConnection::where(['ID'=>$data['id']])->delete();
		return response ()->json (['conn'=>$this->loadCon($data ['type'])]);
	}
	
	public function loadIntServers(Request $request) {
        \Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$intconnection = $this->loadCon($data['type']);
		return response ()->json ( $intconnection );
	}
	
	public function loadTagSet(Request $request) {
        \Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$int_tag_set = IntTagSet::where(['ID'=>$data['set_id']])->select('TAGS')->first();
		
		return response ()->json ( $int_tag_set['TAGS'] );
	}
	
	public function saveTagSet(Request $request) {
		$data = $request->all ();
		
		$condition = [];
		if($data ['id'])
			$condition['ID'] = $data ['id'];
		
		if (isset ( $data ['name'] ))
			$obj ['NAME'] = addslashes($data ['name']);
		
		if (isset ( $data ['tags'] ))
			$obj ['TAGS'] = addslashes($data ['tags']);
		
		if ($data ['id'] <= 0)
			$obj ['CONNECTION_ID'] = $data ['conn_id'];
		
		//$result = IntTagSet::updateOrCreate ( $condition, $obj );
        $result = ($data ['id'] == 0 ) ? IntTagSet::firstOrCreate ( $obj ) : IntTagSet::updateOrCreate ( $condition, $obj );
		$id = $result->ID;
		
		return response ()->json ("ok:".$id);
	}
	
	public function loadCon($type) {
		\Helper::setGetterUpperCase();
		$intconnection = IntConnection::where(['TYPE'=>$type])->get(['ID', 'NAME']);
		return  $intconnection ;
	}
	
	public function saveImportSetting(Request $request) {
		$data = $request->all ();
        $id = $data ['id'];

		if(isset($data ['name']))
			$obj ['NAME'] = $data ['name'];
		
		if(isset($data ['tab']))
			$obj ['TAB'] = $data ['tab'];
		
		if(isset($data ['table']))
            $obj ['TABLE_NAME'] = $data ['table'];

		if(isset($data ['col_tag']) && !is_null($data ['col_tag']))
			$obj ['COL_TAG'] = $data ['col_tag'];
		
		if(isset($data ['col_time']) && !is_null($data ['col_time']))
			$obj ['COL_TIME'] = $data ['col_time'];
		
		if(isset($data ['col_value']) && !is_null($data ['col_value']))
			$obj ['COL_VALUE'] = $data ['col_value'];
		
		if(isset($data ['row_start']))
			$obj ['ROW_START'] = $data ['row_start'];
		
		if(isset($data ['row_finish']))
			$obj ['ROW_FINISH'] = $data ['row_finish'];
		
		if(isset($data ['cols_mapping']))
			$obj ['COLS_MAPPING'] = $data ['cols_mapping'];		
		
		if(isset($data ['cols_mapping']))
			$obj ['COL_OBJECT_ID'] = $data ['col_object'];		
		
		if(isset($data ['cols_mapping']))
			$obj ['COL_DATE'] = $data ['col_date'];		
		
		if(isset($data ['cols_mapping']))
			$obj ['AUTO_FORMULA'] = $data ['auto_formula'];

        if($id != 0) IntImportSetting::where('ID', $id)->update($obj);
        else $id = IntImportSetting::insertGetId($obj);
		$int_import_setting = $this->loadImportSetting();
			
		return response ()->json (['int_import_setting'=>$int_import_setting, 'id'=>$id]);
	}
	
	public function deleteSetting(Request $request) {
		$data = $request->all ();
	
		IntImportSetting::where(['ID'=>$data['id']])->delete();
		
		$int_import_setting = $this->loadImportSetting();
	
		return response ()->json ( $int_import_setting );
	}
	
	public function renameSetting(Request $request) {
		$data = $request->all ();
	
		IntImportSetting::where(['ID'=>$data['id']])->update(['NAME'=>$data['name']]);
	
		$int_import_setting = $this->loadImportSetting();
	
		return response ()->json (['int_import_setting'=>$int_import_setting, 'id'=>$data['id']]);
	}
	
	public function getCellValue($sheet,$cellName,$isDateTime=false){
		$cellValue	= null;
		$cell 		= $sheet->getCell($cellName);
		if ($cell) {
			$cellType	= $cell->getDataType();
			$cellValue	= $cellType&&$cellType=="f"?
						$cell->getOldCalculatedValue():
						($isDateTime?$cell->getValue():($sheet->rangeToArray($cellName)[0][0]));
			
						/* $cell 		= $sheet->getCell($timeColumn.$row);
						 $rowData 	= $sheet->rangeToArray($timeColumn.$row);
						 $style		= $sheet->getParent()->getCellXfByIndex($cell->getXfIndex());
						 $formatCode	= ($style && $style->getNumberFormat()) ?
						 $style->getNumberFormat()->getFormatCode() :
						 PHPExcel_Style_NumberFormat::FORMAT_GENERAL;
						 $cvl 		= $cell->getOldCalculatedValue();
						 $cvl 		= $cell->getValue(); */
		}
		return $cellValue;
	}
	
        public function doImport() {
		$files 			= Input::all ();
		$tabIndex 		= $files['tabIndex'];
		$tagColumn 		= $files['tagColumn'];
		$timeColumn 	= $files['timeColumn'];
		$valueColumn 	= $files['valueColumn'];
		$rowStart 		= $files['rowStart'];
		$rowFinish 		= $files['rowFinish'];
		$summaryMethod 	= $files['cal_method'];
		$timeBegin 	= $files['date_begin'];
		$timeBegin 	= Carbon::parse($timeBegin);
		$timeEnd 		= $files['date_end'];
		$timeEnd	 	= Carbon::parse($timeEnd);
		$update_db 		= $files['update_db'];
		$update_db 		= $update_db==1;
		$path 			= "";
		$tmpFilePath 	= '/fileUpload/';
		$error 			= false;
		$str 			= "";
		
		if (count ( $files ) > 0) {
			$file = $files['file'];
			$tmpFileName = $file->getClientOriginalName ();
			$fileName = $tmpFileName;
			$v = explode ( '.', $tmpFileName );
			$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];			
			$data = [];
			$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
			if ($file) {				
				$path =  public_path () .$tmpFilePath . $tmpFileName;
				ini_set('max_execution_time', 300);
 				$xxx = Excel::selectSheets($tabIndex)->load($path, function($reader) 
 						use ($data, $tagColumn, $timeColumn, $valueColumn, $tabIndex, $rowStart, $rowFinish, 
							$timeBegin, $timeEnd, $fileName, $update_db, $summaryMethod, &$str, $path) {

					$objExcel = $reader->getExcel(); 
					$sheet = $objExcel->getSheet(0);
					$highestRow = $sheet->getHighestRow();
					$highestColumn = $sheet->getHighestColumn();

					if($sheet->getTitle()!=$tabIndex && $highestColumn=='A'){
						$str = "Sheet not found. Please check sheet name";
						return;
					}
					
					if($rowFinish > $highestRow) $rowFinish = $highestRow;
					
					$current_username = '';
					if((auth()->user() != null)){
						$current_username = auth()->user()->username;
					}
						
					$condition = array(
							'ID'=>-1
					);
					$begin_time = date('Y-m-d H:i:s');
					$obj['FILE_NAME'] = $fileName;
					$obj['FILE_SIZE'] = $highestRow;
					$obj['BEGIN_TIME'] = $begin_time;
					$obj['USER_NAME'] = $current_username;
					
					$int_import_log = IntImportLog::updateOrCreate($condition,$obj);
					$log_id=$int_import_log->ID;
					
					$tags_read=0;
					$tags_override=0;
					$tags_loaded=0;
					$tags_rejected=0;
					$tags_addnew = 0;
					
					$html = "";
					$datatype = "";
					if(!$datatype) $datatype="NUMBER";
 					$db_schema = ENV('DB_DATABASE');
// 					$db_schema="energy_builder";
					
					for ($row = $rowStart; $row <= $rowFinish; $row++)
					{
						$arr = [];
						$tags_read++;
						$hasError=false;
						$err="";
						$statusCode="Y";
						try{
 							$dateTimeVL	= $this->getCellValue($sheet,$timeColumn.$row,true);
							$unixTime	= PHPExcel_Shared_Date::ExcelToPHP($dateTimeVL);
// 							$carbonDate = $this->proDate($time);
 							$carbonDate = Carbon::createFromTimestamp($unixTime);
 							$date 		= $carbonDate->format('m/d/Y');
							if($carbonDate&&$carbonDate->gte($timeBegin) && $timeEnd->gte($carbonDate)){
// 								$tagID 	= $sheet->rangeToArray($tagColumn.$row)[0][0];
// 								$sheet->rangeToArray($valueColumn.$row)[0][0];
								$tagID 	= $this->getCellValue($sheet,$tagColumn.$row);
								$value 	= $this->getCellValue($sheet,$valueColumn.$row);
								if(!$tagID||$tagID==""){
									$hasError=true;
									if (($date&&$date!="")||($value&&$value!="")) {
										$statusCode="NTG";
										$err="No tag ID";
									}
									else {
										$statusCode="NT";
										$err="No tag";
									}
								}
								else if($datatype=="NUMBER" &&!is_numeric($value)){
									$hasError = true;
									$statusCode = "NF";
									$err = "Not a number: $value";
								}
							
								if(!$hasError){
									if((!$date||$date=="")){
										$hasError=true;
										$statusCode="ND";
										$err="No date";
									}
									else{
										$Y = $carbonDate->year;
										if($Y==1970){
											$hasError=true;
											$statusCode="NWD";
											$err="Wrong date";
										}
									}
							}
							
							$impSQL	="";
							$sqls	= [];
							if(!$hasError){
								$r_t = IntTagMapping::where(['TAG_ID'=>$tagID])->get();
								if($r_t->count()<=0){
									$hasError=true;
									$statusCode="NG";
									$err="Tag mapping not found";
								}
								else{
									foreach ($r_t as $r){
										$table_name=strtoupper($r->TABLE_NAME);
										$column_name=strtoupper($r->COLUMN_NAME);
//                                        \Log::info("table name $table_name");
										/*
										$cc = \DB::table('INFORMATION_SCHEMA.TABLES')
													->where('TABLE_SCHEMA','=',$db_schema)
													->where('TABLE_NAME','=',$table_name)
													->select("TABLE_NAME")
													->first();
										if($cc){
											$cc = \DB::table('INFORMATION_SCHEMA.COLUMNS')
													->where('TABLE_SCHEMA','=',$db_schema)
													->where('TABLE_NAME','=',$table_name)
													->where('COLUMN_NAME','=',$column_name)
													->select("COLUMN_NAME")
													->first();
											if(!$cc){
												$hasError=true;
												$statusCode="NC";
												$err="Column not found ($column_name)";
											}
										}
										else{
											$hasError=true;
											$statusCode="NT";
											$err="Table not found ($table_name)";
										}
										*/
										if(!$hasError){
											$objIDField	= $this->getObjectIDFiledName($table_name);
											$values 	= [	$objIDField		=> $r->OBJECT_ID,
// 															"OCCUR_DATE"	=> $carbonDate->toDateString(),
															];
											$attributes	= [	$objIDField		=> $r->OBJECT_ID,
// 															"OCCUR_DATE"	=> $carbonDate->toDateString(),
															];
											
											$sF="";
											$sV="";
// 											$dateString = $date->format('m/d/Y');
											$dateString = $date;
											if(substr($table_name,0,12)=="ENERGY_UNIT_")
											{
												$sF.=",FLOW_PHASE";
												$sV.=",$r->FLOW_PHASE";
												$sF.=",EVENT_TYPE";
												$sV.=",$r->EVENT_TYPE";
												
												$attributes["FLOW_PHASE"] 	= $r->FLOW_PHASE;
												$attributes["EVENT_TYPE"] 	= $r->EVENT_TYPE;
												$values["FLOW_PHASE"] 		= $r->FLOW_PHASE;
												$values["EVENT_TYPE"] 		= $r->EVENT_TYPE;
											}
//											\Log::info($table_name);
											if($table_name=="ENERGY_UNIT_DATA_ALLOC")
											{
												$sF.=",ALLOC_TYPE";
												$sV.=",$r->ALLOC_TYPE";
												
												$attributes["ALLOC_TYPE"] 	= $r->ALLOC_TYPE;
												$values["ALLOC_TYPE"] 		= $r->ALLOC_TYPE;
											}
											else if($table_name=="KEYSTORE_INJECTION_POINT_DAY")
											{
												$sF.=",INJECTION_POINT_ID";
												$sV.=",$r->INJECTION_POINT_ID";
												
												$attributes["INJECTION_POINT_ID"] 	= $r->INJECTION_POINT_ID;
												$values["INJECTION_POINT_ID"] 		= $r->INJECTION_POINT_ID;
											}
											
 											$mdl		= \Helper::getModelName($table_name);

											if(method_exists($mdl, "receiveDataFromTagMapping")) {
												$tValues	= [$column_name	=> $value];
												$sSQL= $mdl::receiveDataFromTagMapping($tValues,$r,$carbonDate,$update_db);
											}
											else if($update_db){
												$attributes["OCCUR_DATE"] 	= $carbonDate->toDateString();
												$values["OCCUR_DATE"] 		= $carbonDate->toDateString();
												$values[$column_name] 		= $value;
//                                                \Log::info($attributes);
//                                                \Log::info($values);
												$entry 	= $mdl::updateOrCreate($attributes,$values);

												if ($entry->wasRecentlyCreated)	{
													$tags_addnew++;
													$sSQL="insert into $table_name($objIDField,OCCUR_DATE,$column_name$sF) values($r->OBJECT_ID,'$date','$value'$sV)";
												}
												else {
													$tags_override++;
													$rID = $entry->ID;
													$sSQL="update $table_name set $column_name='$value' where ID=$rID";
												}
											}
											else{
                                                $attributes["OCCUR_DATE"] 	= $carbonDate->toDateString();
												$entry 	= $mdl::where($attributes)->first();
//												\Log::info($entry);
												if ($entry)	{
                                                    $rID = $entry->ID;
                                                    $sSQL="update $table_name set $column_name='$value' where ID='$rID'";
												}
												else {
                                                    $sSQL="insert into $table_name($objIDField,OCCUR_DATE,$column_name$sF) values($r->OBJECT_ID,'$date','$value'$sV)";

												}
											}
											$sqls[]	= $sSQL;
											$impSQL.=($impSQL?"<bt>":"").$sSQL;
											$tags_loaded++; 
										}
									}
								}
							}
							if($hasError) $tags_rejected ++;
							if($tagID&&$tagID!=="" && $value&&$value!=="" &&$carbonDate){								
								$load_time=date('Y-m-d H:i:s');
								IntTagTrans::insert([
									'LOG_ID'=> $log_id,
									'TAG_ID'=>$tagID,
									'VALUE'=>$value,
									'DATE'.($this->isOracle?'_':'')=>$carbonDate,
									'DATA_TYPE'=>$datatype,
									'LOAD_TIME'=>$load_time,
									'STATUS_CODE'=>$err
								]);
							}
							
							$html.="<tr><td>$tagID</td><td>$value</td><td>$date</td><td>$statusCode</td><td>$err</td><td>$impSQL</td></tr>";
						
						}else{
							$hasError=true;
							$statusCode="DOR";
							$err="Date/time out of range";
						}
						
						}catch(Exception $e) {
							$hasError=true;
							$statusCode=$e->getMessage();
						}
					}
					
					$end_time=date('Y-m-d H:i:s');					
					IntImportLog::where(['ID'=>$log_id])
								->update([
									'END_TIME'=>$end_time, 
									'TAGS_READ'=>$tags_read, 
									'TAGS_LOADED'=>$tags_loaded, 
									'TAGS_REJECTED'=>$tags_rejected, 
									'TAGS_OVERRIDE'=>$tags_override
								]);
					
					$str .= "<h3>Import log</h3>";
					$str .= "<input type='button' style='display:none' value='Back' onclick=\"document.location.href='/doimport';\" />";
					$str .= "<table>";
					$str .= "<tr><td>Filename</td><td>: <b>".$fileName."</b></td><td> Filesize</td><td>: <b> " . $highestRow . "</b></td></tr>";
					$str .= "<tr><td>From date</td><td>: " . $timeBegin . "</td></tr>";
					$str .= "<tr><td>To date</td><td>: " . $timeEnd . "</td></tr>";
					$str .= "<tr><td>Tab</td><td>: " . $tabIndex . "</td></tr>";
					$str .= "<tr><td>Tag Column</td><td>: " . $tagColumn . "</td></tr>";
					$str .= "<tr><td>Time column</td><td>: " . $timeColumn . "</td></tr>";
					$str .= "<tr><td>Value column</td><td>: " . $valueColumn . "</td></tr>";
					$str .= "<tr><td>Row start</td><td>: " . $rowStart . "</td></tr>";
					$str .= "<tr><td>Row finish</td><td>: " . $rowFinish . "</td></tr>";
					$str .= "<tr><td>Update database</td><td>: <b>" . ($update_db?"Yes":"No") . "</b></td></tr>";
					$str .= "<tr><td>Data method</td><td>: " . $summaryMethod . "</td></tr>";
					$str .= "<tr><td></td></tr>";
					$str .= "<tr><td>Tags read</td><td>: " . $tags_read . "</td></tr>";
					$str .= "<tr><td>Tags loaded</td><td>: " . $tags_loaded . "</td></tr>";
					$str .= "<tr><td>Tags rejected</td><td>: " . $tags_rejected . "</td></tr>";
					$str .= "<tr><td>Tags override</td><td>: " . $tags_override . "</td></tr>";
					$str .= "<tr><td>Tags added</td><td>: " . $tags_addnew . "</td></tr>";
					$str .= "<tr><td>Begin time</td><td>: " . $begin_time . "</td></tr>";
					$str .= "<tr><td>End time</td><td>: " . $end_time . "</td></tr>";
					$str .= "</table>";
					$str .= "<br>";
					$str .= "<table><tr>";
					$str .= "<td><b>Tag</b></td><td><b>Value</b></td><td><b>Date/time</b></td>";
					$str .= "<td><b>Code</b></td><td><b>Status</b></td><td><b>Command</b></td>";
					$str .= "</tr> " . $html . "	</table>";
				});
			} 
		}
		
		if (file_exists($path)) { unlink ($path); }
		return response ()->json (['log'=>$str]);
	}
	
	private function getObjectIDFiledName($table)
	{
		if($table=="FLOW_DATA_TREND")
			return "OBJECT_ID";
		if(substr($table,0,5)=="EQUIP")
			return "EQUIPMENT_ID";
		if(substr($table,0,5)=="FLOW_")
			return "FLOW_ID";
		if(substr($table,0,12)=="ENERGY_UNIT_" || substr($table,0,3)=="EU_" || $table=="ZT_ENERGY_UNIT_FDC_VALUE_SUB")
			return "EU_ID";
		if(substr($table,0,8)=="STORAGE_")
			return "STORAGE_ID";
		if(substr($table,0,5)=="TANK_")
			return "TANK_ID";
		if($table=="KEYSTORE_INJECTION_POINT_DAY")
			return "KEYSTORE_ID";
		
		$mdl	= \Helper::getModelName($table);
		if(method_exists($mdl, "receiveDataFromTagMapping")) return $mdl::$idField;
	} 
	
	private function proDate($date){
		$m; $d; $y;
		$ds=explode('-',$date);
		if(count($ds) == 1){
			$ds=explode('/',$date);
		}
		
		$m = $ds [0]; 
		$d = $ds [1]; 
		$y = $ds [2];
		
		if (strlen ( $m ) == 1)
			$m = "0" . $m;
		if (strlen ( $d ) == 1)
			$d = "0" . $d;
		if (strlen ( $y ) == 2)
			$y = "20" . $y;
		
		if (strlen ( $m ) == 2 && strlen ( $d ) == 2 && strlen ( $y ) == 4) {
			$date = $m . "/" . $d . "/" . $y;
		}
		$date = Carbon::createFromFormat('m/d/Y h:i', $date);
		$date->addYear(2000);
		return $date;
	}
	
	function processTagFormula($fid,$value,$date){
		if($fid>0){
			$formula = Formula::find($fid);
			$vars = [];
			$vars['X'] = $value;
			$tmp = \FormulaHelpers::evalFormula($formula, Carbon::now(), false, $vars);
			if(is_numeric($tmp))
				return $tmp;
		}
		return $value;
	}
	
	function saveTagData($tagID, $date, $value, $update_db){

		$impSQL="";
		$hasError=false;
		$statusCode="Y";
		$err="";
		
		$r_t = IntTagMapping::where(['TAG_ID'=>$tagID])->get();
	
		if(count($r_t)<=0)
		{
			$hasError=true;
			$statusCode="NG";
			$err="Tag mapping not found";
		}
		else {
			foreach ($r_t as $r){
				$table_name=strtoupper($r->TABLE_NAME);
				$column_name=strtoupper($r->COLUMN_NAME);
				$objIDField	= $this->getObjectIDFiledName($table_name);
				if(!$objIDField){
					$hasError=true;
					$statusCode="OID";
					$err="No object ID field for table $table_name";
				}
				if(!$hasError){
					$values 	= [	$objIDField		=> $r->OBJECT_ID,
									];
					$attributes	= [	$objIDField		=> $r->OBJECT_ID,
									"OCCUR_DATE"	=> $date,
								];
					
					$sF="";
					$sV="";
					$dateString = $date;
					if(substr($table_name,0,12)=="ENERGY_UNIT_")
					{
						$sF.=",FLOW_PHASE";
						$sV.=",$r->FLOW_PHASE";
						$sF.=",EVENT_TYPE";
						$sV.=",$r->EVENT_TYPE";
						
						$attributes["FLOW_PHASE"] 	= $r->FLOW_PHASE;
						$attributes["EVENT_TYPE"] 	= $r->EVENT_TYPE;
						$values["FLOW_PHASE"] 		= $r->FLOW_PHASE;
						$values["EVENT_TYPE"] 		= $r->EVENT_TYPE;
					}
					if($table_name=="ENERGY_UNIT_DATA_ALLOC")
					{
						$sF.=",ALLOC_TYPE";
						$sV.=",$r->ALLOC_TYPE";
						
						$attributes["ALLOC_TYPE"] 	= $r->ALLOC_TYPE;
						$values["ALLOC_TYPE"] 		= $r->ALLOC_TYPE;
					}
					else if($table_name=="KEYSTORE_INJECTION_POINT_DAY")
					{
						$sF.=",INJECTION_POINT_ID";
						$sV.=",$r->INJECTION_POINT_ID";
						
						$attributes["INJECTION_POINT_ID"] 	= $r->INJECTION_POINT_ID;
						$values["INJECTION_POINT_ID"] 		= $r->INJECTION_POINT_ID;
					}

					//modify value by formula if be configured
					if($r->FORMULA_ID>0){
						$value = $this->processTagFormula($r->FORMULA_ID,$value,$date);
					}

					$mdl	= \Helper::getModelName($table_name);
					if(method_exists($mdl, "receiveDataFromTagMapping")) {
						$tValues	= [$column_name	=> $value];
						$sSQL= $mdl::receiveDataFromTagMapping($tValues,$r,$date,$update_db);
					}
					else{
						if($update_db){
							$values["OCCUR_DATE"] 		= $date;
							$values[$column_name] 		= $value;
							$entry 	= $mdl::updateOrCreate($attributes,$values);
							if ($entry->wasRecentlyCreated)	{
								$sSQL="insert into $table_name($objIDField,OCCUR_DATE,$column_name$sF) values($r->OBJECT_ID,'$date','$value'$sV)";
								IntTagMapping::where(['TAG_ID'=>$tagID])->update([
									'LAST_TIME'=>$date,
									'LAST_VALUE'=>$value
								]);
							}
							else {
								$rID = $entry->ID;
								$sSQL="update $table_name set $column_name='$value' where ID=$rID";
							}
						}
						else{
							$entry 	= $mdl::where($attributes)->select('ID')->first();
							if (!$entry) {
								$sSQL="insert into $table_name($objIDField,OCCUR_DATE,$column_name$sF) values($r->OBJECT_ID,'$date','$value'$sV)";
							}
							else {
								$rID = $entry->ID;
								$sSQL="update $table_name set $column_name='$value' where ID=$rID";
							}
						}
					}
					$sqls[]	= $sSQL;
					$impSQL.=($impSQL?"<br>":"").$sSQL;
				}
			}					
		}
		return ['sql' => $impSQL, 'code' => $statusCode, 'status' => !$hasError, 'message' => $err];
	}

	public function ip21Import(Request $request) {
		$info 	= $request->all ();
		$str	= $this->ip21ImportData($info);
		return  response ()->json ($str);
	}
	
	public function ip21ImportData($info) {
		$info['system_type'] = 'IP21';
		return $this->_importNetworkData($info);
	}

	public function pi(Request $request) {
		$info = $request->all ();
		$info['system_type'] = 'PI';
		$str = $this->_importNetworkData($info);
		return response ()->json ($str);
	}
	
	public function importNetworkData(Request $request) {
		$info = $request->all ();
		$info['system_type'] = '';
		$str = $this->_importNetworkData($info);
		return response ()->json ($str);
	}
	
	function _importNetworkData($info){
		$str = "";
		$connection_id 	= $info['connection_id'];
		$tagset_id 		= $info['tagset_id'];
		$cal_method 	= $info['cal_method'];
		$date_begin 	= $info['date_begin'];
		$date_end 		= $info['date_end'];
		$update_db 		= $info['update_db'];
		
		$tagSetName = "";
		$ptags = "";
		if(array_key_exists("tags", $info)){
			$ptags = $info['tags'];
		}
		else{
			$intTagSet = IntTagSet::where(['ID'=>$tagset_id])->select('TAGS','NAME')->first();
			if($intTagSet){
				$tagSetName = $intTagSet->NAME;
				$ptags = $intTagSet->TAGS;
			}
			else{
				return "Error: Tag set not found";
			}
		}
		$ptags = trim($ptags);
		if(!$ptags){
			return "Error: No tag";
		}
		$tags = explode ( "\n", $ptags );

		$int_connection = IntConnection::where(['ID'=>$connection_id])->select('SERVER', 'SYSTEM', 'USER_NAME', 'PASSWORD')->first();		
		$server = $int_connection->SERVER;
		$username = $int_connection->USER_NAME;
		$password = $int_connection->PASSWORD;
		$system_type = ($int_connection->SYSTEM?$int_connection->SYSTEM:$info['system_type']);
		
		if(!$system_type){
			$str .= " <font color='red'><b>Warning:</b></font> Connection's System not determined. <b>IP21 will be used by default</b>. Please go to <a target='_blank' href='/sourceconfig'>SOURCE CONFIGURATION</a> to set correct System for the connection.<br><br>";
			$system_type = 'IP21';
		}
		
		$isCurrentData = false;
		$result = null;
		if($system_type=='IP21'){
			if (is_string($date_begin)) {
				$date_begin = Carbon::parse($date_begin);
			}
			if (is_string($date_end)) {
				$date_end = Carbon::parse($date_end);
			}
			if(strpos($tagSetName, '[TREND]') !== false){
				$date_begin 	= $date_begin->format('d-M-y H:i:s.u');
				$date_end 		= $date_end->format('d-M-y H:i:s.u');
				$tagsInfo = IntTagMapping::whereIn('TAG_ID', $tags)->select('TAG_ID','LAST_TIME')->get();
				$result = app('App\Http\Controllers\DVController')->getIP21HistoricalData($tagsInfo, $date_begin,false,$date_end);
			}
			else{
				$isCurrentData = true;
				//$date_begin = $date_begin->format('Y-m-d');
				$date_begin = $date_begin->format('Y-m-d H:i:s');
				$result = app('App\Http\Controllers\DVController')->getIP21Data($tags);
			}			
		}
		else if($system_type=='PI'){
			$isCurrentData = true;
			$result = $this->getPIData($server,$username,$password,$cal_method,$date_begin,$date_end,$tags);
		}
		else if($system_type=='PI_AF'){
			$isCurrentData = true;
			$result = $this->getPIAFData($server,$username,$password,$cal_method,$date_begin,$date_end,$tags);
		}
		else{
			return " <font color='red'><b>$system_type not supported.</b></font>";
		}
		
		$str .= " <b>Import $system_type data</b><br>";
		$str .= " Server: <b>".$server."</b><br>";
		$str .= " Data method: <b>".$cal_method."</b><br>";
		$str .= " Update database: <b>".($update_db?'Yes':'No')."</b><br>";
		
		if(is_string($result)){
			return "$str <br><font color='red'><b>$result</b></font>";
		}
		
		if(is_array($result)){
			$str .= " <table><tr><td><b>Tag</b></td><td><b>Date/time</b></td><td><b>Value</b></td><td><b>Code</b></td><td><b>Status</b></td><td><b>Command</b></td></tr>";
			$result_count = 0;
			foreach($result as $key => $dataItem)
			{
				$tag = (isset($dataItem['tag'])?$dataItem['tag']:$key);
				//$date=($isCurrentData?$date_begin:$dataItem['time']);
				$date=explode(' ',($isCurrentData?$date_begin:$dataItem['time']))[0];
				$value=$dataItem['value'];
				//$ret = $this->saveTagData($tag, $date, $dataItem['time'], $value, $update_db);
				$ret = $this->saveTagData($tag, $date, $date_begin, $value, $update_db);
				$str .= " <tr><td>$tag</td><td>$date</td><td>$value</td><td>".$ret['code']."</td><td>".$ret['message']."</td><td>".$ret['sql']."</td></tr>";
				$result_count++;
			}
			$str .= " </table><br>";
		}
		else{
			$str .= " No data.<br>";
		}
		
		$str .= " <br />The number of records retrieved is: ".$result_count."<br /><br />";
		
		$str .= " Finished ".date('H:i:s')."<br>";
		return $str;
	}
	
	public function getTableFieldsAll(Request $request) {
		$data = $request->all ();
		$field_num = Schema::getColumnListing($data['table']);
		return response ()->json ($field_num);
	}

	public function doImportDataLoader(Request $request) {
		$data = $request->all ();
		//\Log::info($data);
		$tab = $data ['tabIndex'];
		$tagColumn = $data ['tagColumn'];
		$timeColumn = $data ['timeColumn'];
		$valueColumn = $data ['valueColumn'];
		$rowStart = $data ['rowStart'];
		$rowFinish = $data ['rowFinish'];
		$summaryMethod = $data ['cal_method'];
		$timeBegin = $data ['date_begin'];
		$timeEnd = $data ['date_end'];
		$update_db = $data ['update_db'];
		$override_data = $data ['cboOveride'];
		$table_name = addslashes ( $data ['txtTable'] );
		$mapping = addslashes ( $data ['txtMapping'] );
		$applyFormula = false;
		if(isset($data ['apply_formula']))
			$applyFormula = $data ['apply_formula'];
		
		$timeBegin = date ( 'Y-m-d', strtotime ( $timeBegin ) );
		$timeEnd = date ( 'Y-m-d', strtotime ( $timeEnd ) );
		
		$str = "";
		$path = "";
		$tmpFilePath = '/fileUpload/';
		
		if (! ($rowStart > 0 && $rowStart <= $rowFinish))
			return response ()->json ( "Wrong rows range" );
		
		$file = $data ['file'];
		$tmpFileName = $file->getClientOriginalName ();
		$fileName = $tmpFileName;
		$v = explode ( '.', $tmpFileName );
		$tmpFileName = $v [0] . '_' . time () . '.' . $v [1];
		$file = $file->move ( public_path () . $tmpFilePath, $tmpFileName );
		if ($file) {
			$path = public_path () . $tmpFilePath . $tmpFileName;
			$xxx = Excel::selectSheets($tab)->load($path, function ($reader) use ($data, $tagColumn, $timeColumn, 
					$valueColumn, $tab, $rowStart, $rowFinish, $timeBegin, $timeEnd, $fileName, $update_db, 
					$summaryMethod, $path, $mapping, $table_name, $override_data, $applyFormula, &$str) {
				
				$objExcel = $reader->getExcel ();
				$sheet = $objExcel->getSheet ( 0 );
				$highestRow = $sheet->getHighestRow ();
				$highestColumn = $sheet->getHighestColumn ();

				if($sheet->getTitle()!=$tab && $highestColumn=='A'){
					$str = "Sheet not found. Please check sheet name";
					return;
				}
				if($applyFormula){
					$objectIds = [];
					$dates = [];
					$col_obj_id = $data ['fo_obj_id_col'];
					$col_date = $data ['fo_date_col'];
					$fo_mdlName = \Helper::camelize(strtolower ($table_name),'_');
				}
				
				$current_username = '';
				if ((auth ()->user () != null)) {
					$current_username = auth ()->user ()->username;
				}
				
				$condition = array (
						//'ID' => - 1
				);
				$begin_time = date ( 'Y-m-d H:i:s' );
				$obj ['FILE_NAME'] = $fileName;
				$obj ['FILE_SIZE'] = $highestRow;
				$obj ['BEGIN_TIME'] = $begin_time;
				$obj ['USER_NAME'] = $current_username;

				$int_import_log = IntImportLog::updateOrCreate ( $condition, $obj );
				$log_id = $int_import_log->ID;
				
				$tags_rejected = 0;
				$tags_loaded = 0;
				$tags_read = 0;
				$tags_override = 0;
				$tags_addnew = 0;
				$html = "";
				$datatype = "";
				$dateformat = "";
				if (! $datatype)
					$datatype = "NUMBER";
				
				$maps = explode ( "\n", $mapping );
				if(count($maps) > 0){
				$sql = "";
					$keys_check = "";
					$F = "";
					$V = "";
					$X = "";
					$vars = array ();
					
					foreach ( $maps as $map ) {
						$str .= "map:$map<br>";
						$exps = explode ( '=', $map );
						if (count ( $exps ) == 2) {
							$field = trim ( $exps [0] );
							$exp = trim ( $exps [1] );
							$iskey = false;
							if (strpos ( $exp, '*' ) !== false) {
								$iskey = true;
								$exp = str_replace ( '*', '', $exp );
							}
							$dateformat = "";
							$k = strpos ( $exp, '{' );
							if ($k > 0) {
								$l = strpos ( $exp, '}', $k );
								if ($l > $k)
									$dateformat = substr ( $exp, $k + 1, $l - $k - 1 );
								$exp = substr ( $exp, 0, $k );
							}
							
							$is_constant = false;
							if($exp[0]=='"') // constant
							{
								$is_constant = true;
								$exp = str_replace ( '"', '', $exp );
							}
							$value = "'$exp'";
							
							if(!$is_constant){
								$is_col_ref = true;
								for ($ie=0; $ie<strlen($exp); $ie++) {
									$ord = ord($exp[$ie]);
									if($ord < 65 || $ord > 90){
										$is_col_ref = false;
										break;
									}
								}
								if($is_col_ref){
									$vars[] = $exp;
									$value = "'@VALUE_{$exp}@'";
								}
							}

							if ($dateformat)
								$value = ($this->isOracle?"TO_DATE":"STR_TO_DATE")."($value,'$dateformat')";
							if ($iskey) {
								$keys_check .= ($keys_check ? " and " : "") . "$field=$value";
							}
							$F .= ($F ? "," : "") . "$field";
							$V .= ($V ? "," : "") . $value;
							$X .= ($X ? "," : "") . "$field=$value";
						}
					}
				}
				
				if ($F) {
					{
						if ($rowFinish > $highestRow)
							$rowFinish = $highestRow;
						
						$originAttrCase = \Helper::setGetterUpperCase();
						for($row = $rowStart; $row <= $rowFinish; $row ++) {
							$html .= "<tr>";
							$tags_read ++;
							
							$keys_check_x = $keys_check;
							$V_x = $V;
							$X_x = $X;
							$isWrongKey = false;
							foreach ( $vars as $var ) {
								$value = $sheet->rangeToArray ( $var . $row ) [0] [0];
								if(($value==="" || $value===null) && strpos($keys_check_x, "@VALUE_{$var}@")!==false){
									$isWrongKey = true;
									break;
								}
								if ($keys_check_x)
									$keys_check_x = str_replace ( "@VALUE_{$var}@", $value, $keys_check_x );
									
								$V_x = str_replace ( "@VALUE_{$var}@", $value, $V_x );
								$X_x = str_replace ( "@VALUE_{$var}@", $value, $X_x );
								//\Log::info("@VALUE_{$var}@, $value, $keys_check_x ");
							}
							if($isWrongKey){
								continue;
							}
							if($applyFormula){
								if($col_obj_id){
									$value = $sheet->rangeToArray ( $col_obj_id . $row ) [0] [0];
									$objectIds[] = $value;
								}
								if($col_date){
									$value = $sheet->rangeToArray ( $col_date . $row ) [0] [0];
									$dates[] = $value;
								}
							}
							
							$sSQL = "";
							$isInsert = true;
							if ($keys_check_x) {
								$tmp = DB::select("select ID from $table_name where $keys_check_x");
								if(count($tmp))
								{
									$id = $tmp[0]->ID;
									$sSQL = "update $table_name set $X_x where ID=$id";
									$isInsert = false;
								}
							}
							if (! $sSQL) {
								$sSQL = "insert into $table_name($F) values($V_x)";
							}
							if ($isInsert)
								$tags_addnew ++;
							else
								$tags_override ++;
							
							$sSQL = str_replace ( "''", "null", $sSQL );
							$status = "Display only";
							if ($update_db == 1) {
								$status = "Error";
								try {
									DB::statement ( $sSQL );
									$status = "Executed";
								}
								catch (\Exception $e){
									if (!$e) $e = new \Exception("Exception when run dataloader statement");
									$status = $e->getMessage();
									//\Log::info($e->getMessage());
									//\Log::info($e->getTraceAsString());
								}
							}
							$tags_loaded ++;
							$html .= "<td>$sSQL</td><td>$status</td></tr>";
						}
						\Helper::setGetterCase($originAttrCase);
					}
				}
				
// Process revelant formula
				if($applyFormula && $fo_mdlName && count($objectIds) > 0 && count($dates) > 0 && $dateformat){
					$objectIds = array_unique($objectIds);
					$dates = array_unique($dates);
					$tmp_date_format = str_replace("%","",$dateformat);
					foreach($dates as $date){
						$occur_date = Carbon::createFromFormat($tmp_date_format, $date)."";
						$occur_date = explode(" ", $occur_date);
						$occur_date = $occur_date[0];
						$applieds = \FormulaHelpers::applyFormula($fo_mdlName,$objectIds,$occur_date);
					}
				}
///////////////////////////							

				$end_time=date('Y-m-d H:i:s');
				$sSQL="update int_import_log set END_TIME='$end_time',TAGS_READ='$tags_read',TAGS_LOADED='$tags_loaded',TAGS_REJECTED='$tags_rejected',TAGS_OVERRIDE='$tags_override' where ID=$log_id";
				$sSQL=str_replace("''","null",$sSQL);
				DB:: update($sSQL) or die (mysql_error());
				
				$str .= " <h3>Loader log</h3> ";
				$str .= " <table>";
				$str .= " <tr><td>Table</td><td>:". $table_name."</td></tr>";
				$str .= " <tr><td>Tab</td><td>:". $tab."</td></tr>";
				$str .= " <tr><td>Row start</td><td>:". $rowStart."</td></tr>";
				$str .= " <tr><td>Row finish</td><td>:". $rowFinish."</td></tr>";
				$str .= " <tr><td>File size</td><td>:". $highestRow."</td></tr>";
				$str .= " <tr><td>Update database</td><td>: <b>".($update_db?"Yes":"No")."</b></td></tr>";
				$str .= " <tr><td>Override data</td><td>: <b>".($override_data?"Yes":"No")."</b></td></tr>";
				$str .= " <tr><td></td></tr>";
				$str .= " <tr><td>Tags read</td><td>:". $tags_read."</td></tr>";
				$str .= " <tr><td>Tags loaded</td><td>:". $tags_loaded."</td></tr>";
				$str .= " <tr><td>Tags rejected</td><td>:". $tags_rejected."</td></tr>";
				$str .= " <tr><td>Tags override</td><td>:". $tags_override."</td></tr>";
				$str .= " <tr><td>Tags added</td><td>:". $tags_addnew."</td></tr>";
				$str .= " <tr><td>Loader start</td><td>:". $begin_time."</td></tr>";
				$str .= " <tr><td>Loader finish</td><td>:". $end_time."</td></tr>";
				$str .= " </table>";
				$str .= " <br>";
				$str .= " <table border='1'>";
				$str .= " <tr>";
				$str .= " <td><b>Command</b></td>";
				$str .= " <td><b>Status</b></td>";
				$str .= " </tr>".$html;
				$str .= " </table>";
							}
			);
		}
		
		if (file_exists($path)) { unlink ($path); }
		return response ()->json (['log' => $str]);
	}
	
	public function getRealtimeData(Request $request) {
        //\Helper::setGetterUpperCase();
		$data 		= $request->all ();
		$tags 		= $data["tags"];
		$lastValue 	= array_key_exists("lastValue", $data)?$data["lastValue"]:0;
		
		$arrTags 	= [];
		$tagsInfo 	= [];
		foreach($tags as $tag){
			$arrTags[] 	= $tag["tag"];
			$tagsInfo[] = (object)[	"TAG_ID"	=> $tag["tag"]];
			\Log::info("getRealtimeData tagname ".$tag["tag"]);
		}
		\Log::info("getRealtimeData count tagsInfo ".count($tagsInfo)." count arrTags ".count($arrTags));
		
		if ($lastValue>0) {
			$ret = app('App\Http\Controllers\DVController')->getIP21Data($arrTags);
			//\Log::info($ret);
			for($i=0;$i<count($tags);$i++) if(array_key_exists($tags[$i]["tag"], $ret)){
				$pair	= [	"value"	=> $ret[$tags[$i]["tag"]]["value"],
                            "time"	=> $ret[$tags[$i]["tag"]]["time"],
                            "unit"  => $ret[$tags[$i]["tag"]]["unit"],
                            "tag"	=> $ret[$tags[$i]["tag"]]["tag"],];
				if (!isset($tags[$i]["addtion"])) $tags[$i]["addtion"] = [];
				$tags[$i]["addition"][] = $pair;
			}
		}
		else{
// 			$tagsInfo 			= IntTagMapping::whereIn('TAG_ID', $arrTags)->select('TAG_ID','LAST_TIME')->get();
			$timebase 			= array_key_exists("timebase", $data)?$data["timebase"]:5*60000;
			$lastDate 			= array_key_exists("lastDate", $data)?$data["lastDate"]:null;
			$lastDate 			= $lastDate?\Helper::parseDate($lastDate,"H:i"):null;
			$secondDiff			= $timebase/1000;
			$startTime 			= $lastDate?new \DateTime($lastDate):new \DateTime();
			$startTime->sub(new \DateInterval("PT".$secondDiff."S"));
			$startTime 			= config('constants.simulateExternalDatasource',true)?$startTime->format('Y-m-d H:i:s'):$startTime->format('d-M-y H:i:s');
			$lastDate 			= $lastDate?(config('constants.simulateExternalDatasource',true)?$lastDate->format('Y-m-d H:i:s'):$lastDate->format('d-M-y H:i:s')):null;
			$sampleInterval 	= array_key_exists("sampleInterval", $data)?$data["sampleInterval"]:false;
			$ret 		= app('App\Http\Controllers\DVController')->getIP21HistoricalData($tagsInfo, $startTime,$sampleInterval,$lastDate);
			for($i=0;$i<count($tags);$i++){
				$tagName	= $tags[$i]["tag"];
				if (!isset($tags[$i]["addition"])) $tags[$i]["addition"] = [];
				foreach($ret as $index => $row){
					if($tagName&&$tagName==$row["tag"]){
						$tags[$i]["addition"][] = $row;
						unset($ret[$index]);
					}
				}
			}
		}
		
		return response ()->json (['results'=>$tags]);
	}
	
	function getExtDataIP21($server, $username, $password, $tags){
		$cond = "";
		$data = [];
		foreach($tags as $tag) if($tag) {
			$cond .= ($cond?" or ":"")."NAME='$tag'";
		}
		if(!$cond)
			return $data;

		$connection = new \COM("ADODB.Connection");
		$connection_string = "Driver={AspenTech SQLPlus};HOST=$server;PORT=10014";
		$connection->Open("$connection_string;User ID=$username;Password=$password;");
		
		$sql = "select NAME,IP_INPUT_VALUE,IP_ENG_UNITS,IP_INPUT_TIME from IP_SAN_Anadef where $cond";
		$result_set = $connection->Execute($sql);
		$i=0;
		while (!$result_set->EOF && $i<100) 
		{   
			$NAME=$result_set->fields[0]->value;
			$IP_INPUT_VALUE=round($result_set->fields[1]->value,3);
			$IP_ENG_UNITS=$result_set->fields[2]->value;
            $time = \DateTime::createFromFormat('d-M-y H:i:s.u', $result_set->fields[3]->value)->format('Y-m-d H:i:s').".000";
			$data[$NAME] = ["value" => $IP_INPUT_VALUE,
                "time" => $time,
                "unit" => mb_convert_encoding($IP_ENG_UNITS, 'UTF-8', 'UTF-8'),
                "tag" => $NAME];
			$result_set->MoveNext();
			$i++;
		}
		return $data;
	}

	public function getExtDataExaquantum($server, $timeBegin, $timeEnd, $tags, $sampleInterval = false) {
		$aggregation = false;
		if($sampleInterval == 3600) 
			$aggregation='Hour';
		else if($sampleInterval == 3600*24) 
			$aggregation='Day';
		else if($sampleInterval > 0)
			$aggregation='Minute';
			
		$url=$sampleInterval?
			"http://".$server."/api/V2.2/QTREND?TagName=".($aggregation=='Minute'?$tags:str_replace('PV.Value', 'PV.Aggregations.'.$aggregation.'.Mean.Value', $tags))."&TagStarDate=".str_replace(' ', 'T', $timeBegin)."&TagEndDate=".str_replace(' ', 'T', $timeEnd):
			"http://".$server."/api/V2.2/QDATA?TagName=".implode(",",$tags)."&&TagDateTime=".str_replace(' ', 'T', $timeEnd);
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	/*
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		  
		curl_setopt($ch, CURLOPT_GSSAPI_DELEGATION, CURLGSSAPI_DELEGATION_FLAG);  
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);  
		curl_setopt($ch, CURLOPT_USERPWD, ":"); 
	*/
		$result = curl_exec ( $ch );
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			\Log::info($error_msg);
		}
		curl_close($ch);

		$data = [];
		$items = json_decode ( $result );

		$lastTime = false;
		if(is_array($items)){
			foreach($items as $item){
				if(isset($item->TagName)){
					if($item->Quality == 192){
						$ds = explode('T',$item->TimeStamp);
						$date=$ds[0];
						$time = str_replace('T',' ',$item->TimeStamp);
						$st = strtotime($time);
						if($lastTime && $sampleInterval>0){
							if($st - $lastTime < $sampleInterval) continue;
						}
						$lastTime = $st;
						$data[] = ["tag" => $item->TagName, "value" => round((double)$item->Value, 6), "time" => $time, "unit" => ""];
					}
				}
			}
		}
		return $data;
	}

	public function getExtDataPI($server, $username, $password, $summaryMethod, $timeBegin, $timeEnd, $tags) {

		$tagcondition = "";
		foreach ( $tags as $tag )
			if ($tag)
				$tagcondition .= ($tagcondition ? " or " : "") . "tag='$tag'";
		
		if ($summaryMethod == "max" || $summaryMethod == "min")
			$sql = "SELECT tag, time, value
FROM [piarchive].[pi$summaryMethod]
WHERE ($tagcondition) AND value is not null AND time BETWEEN '$timeBegin' AND '$timeEnd'";
		else if ($summaryMethod == "first" || $summaryMethod == "last") {
			$func = ($summaryMethod == "first" ? "min" : "max");
			$sql = "SELECT tt.tag,tt.TIME,tt.value
						FROM [piarchive].[picomp] tt
						inner join
						(
						SELECT tag tagx,$func(time) mtime
						FROM [piarchive].[picomp]
						WHERE ($tagcondition)
						AND time BETWEEN '$timeBegin' AND '$timeEnd' group by tag
						) grouped on tt.tag=grouped.tagx and tt.time=grouped.mtime
						WHERE ($tagcondition)
						AND value is not null
						AND time BETWEEN '$timeBegin' AND '$timeEnd'";
		}
		else if ($cal_method == "interpolation") {
			$sql = "SELECT tt.tag,tt.TIME,tt.value
						FROM [piarchive].[piinterp] tt
						inner join
						(
						SELECT tag tagx,max(time) mtime
						FROM [piarchive].[piinterp]
						WHERE ($tagcondition)
						AND time BETWEEN '$timeBegin' AND '$timeEnd' group by tag
						) grouped on tt.tag=grouped.tagx and tt.time=grouped.mtime
						WHERE ($tagcondition)
						AND value is not null
						AND time BETWEEN '$timeBegin' AND '$timeEnd'";
		}
		else if ($summaryMethod == "average") {
			$sql = "SELECT tag, time, value
FROM piarchive.piavg
WHERE ($tagcondition) AND value is not null AND time BETWEEN '$timeBegin' AND '$timeEnd'";
		}
		else
			$sql="SELECT tag, time, value
FROM piarchive.picomp
WHERE ($tagcondition) AND value is not null AND time BETWEEN '$timeBegin' AND '$timeEnd'";

		$connection = new \COM("ADODB.Connection") or die("Cannot start ADO");
		if($username === null || $username === "")
			$connection->Open("Provider=PIOLEDB.1;Initial Catalog=piarchive;Data Source=$server;Integrated Security=SSPI;");
		else
			$connection->Open("Provider=PIOLEDB.1;Initial Catalog=piarchive;Data Source=$server;User ID =$username;Password=$password;");
		
		$result_set = $connection->Execute($sql);
		$data = [];
		while (!$result_set->EOF)
		{
			$tagID=$result_set->fields[0]->value;
			$value=$result_set->fields[2]->value;
			$ds = explode('/',explode(' ',$result_set->fields[1]->value)[0]);
			$date="$ds[2]-$ds[1]-$ds[0]";//.' '.explode(' ',$result_set->fields[1]->value)[1];
			$data[] = ["tag" => $tagID, "value" => $value, "time" => $date];
			$result_set->MoveNext();
		}
		return $data;
	}
	
	public function getExtDataPIAF($server, $username, $password, $summaryMethod, $timeBegin, $timeEnd, $tags) {

		$tagcondition = "";
		$ss = explode('/', $server);
		if(count($ss)<2){
			throw new \Exception('Wrong AF server format (server/database)');
		}
		$server = $ss[0];
		$database = $ss[1];
		if ($this->isSimulate)
			$server = 'simulation';
		$tags_ = implode(',', $tags);
		//$cwd = getcwd();
		//chdir("exe");
		$result = shell_exec("exe\\eb-pi-af-adapter.exe $server $username $password $database \"$tags_\" \"$timeBegin\" \"$timeEnd\" $summaryMethod nouom limit=2000");
		$results = explode(';', $result);
		$data = [];
		foreach($results as $line){
			if(substr($line, 0, strlen("Error:")) === "Error:"){
				return $line;
			}
			$ls = explode(',', $line);
			if(count($ls)>=3){
				$tagID=$tags[$ls[0]];
				$value=$ls[2];
				$time = $ls[1];
				$data[] = ["tag" => $tagID, "value" => $value, "time" => $time, "unit" => ""];
			}
		}
		return $data;
	}

	public function getExtData($tags, $summaryMethod = 'last', $timeBegin = null, $timeEnd = null){

		$isSimulated = config('constants.simulateExternalDatasource',true);
		$data = [];
        $baseTime = Carbon::now('UTC')->toDateTimeString();
		if($isSimulated){
			foreach($tags as $tag) if($tag) {
				$data[$tag] = ["value" => rand(10,100), "time" => $baseTime, "unit" => "uom","tag" => $tag];
			}
			return $data;
		}

		$conn = \App\Models\IntConnection::getDefaultConnection();
		if($conn == null)
			return null;
		
		if(!$timeBegin)
			$timeBegin = $baseTime;
		if(!$timeEnd)
			$timeEnd = $timeBegin;
		
		if($conn->SYSTEM=='EXAQUANTUM')
			$data = $this->getExtDataExaquantum($conn->SERVER, $timeBegin, $timeEnd, $tags);
		else if($conn->SYSTEM=='PI')
			$data = $this->getExtDataPI($conn->SERVER, $conn->USER_NAME, $conn->PASSWORD, $summaryMethod, $timeBegin, $timeEnd, $tags);
		else if($conn->SYSTEM=='PI-AF')
			$data = $this->getExtDataPIAF($conn->SERVER, $conn->USER_NAME, $conn->PASSWORD, $summaryMethod, $timeBegin, $timeEnd, $tags);
		else if($conn->SYSTEM=='IP21')
			$data = $this->getExtDataIP21($conn->SERVER, $conn->USER_NAME, $conn->PASSWORD, $tags);

		return $data;
	}
}