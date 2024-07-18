<?php

namespace App\Http\Controllers;
use App\Jobs\runAllocation;
use App\Models\AllocJob;
use App\Models\CodeAllocType;
use App\Models\CodeAllocFromOption;
use App\Models\CodeAllocValueType;
use App\Models\CodeFlowPhase;
use App\Models\Facility;
use App\Models\Network;
use App\Models\AllocRunner;
use App\Models\AllocRunnerObjects;
use App\Models\Flow;
use App\Models\EnergyUnit;
use App\Models\Tank;
use App\Models\Storage;
use App\Models\AllocCondOut;
use App\Models\AllocCondition;
use App\Models\JobDiagram;

use DB;
use Excel;
use Illuminate\Http\Request;

class AllocationController extends CodeController {
	
    public function _index() {
        \Helper::setGetterUpperCase();
        $filterGroups = array(
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup'	=> [
                [
                    "name"			=> "Network",
                    "filterName"	=> "Allocation group",
                    "defaultEnable"	=> false,
                    "getMethod"		=> "loadBy"
                ],
            ],
            'enableButton' => false
        );
        return view ( 'front.allocrun',['filters'=>$filterGroups]);
    }
	
	public function getJobsRunAlloc(Request $request) {
		$data = $request->all ();
		
		$result = $this->getAllocJob($data['NETWORK_ID']);
						
		return response ()->json ( $result );
	}
	
	private function getAllocJob($network_id){
		\Helper::setGetterUpperCase();
		$allocjob = AllocJob::getTableName ();
		$code_alloc_value_type = CodeAllocValueType::getTableName();
		
		$result = DB::table ( $allocjob)
		->join ( $code_alloc_value_type, "$allocjob.VALUE_TYPE", '=', "$code_alloc_value_type.ID" )
		->where ( ["$allocjob.NETWORK_ID" => $network_id])
		->orderBy("$allocjob.ID")->select("$allocjob.*", "$code_alloc_value_type.name AS VALUE_TYPE_NAME")->get();
		
		return $result;
	}
	
	public function run_runner(Request $request) {
		$data = $request->all ();
		
		$objRun = new runAllocation($data);
		return response ()->json ($objRun->handle());
	}
	
	public function _indexconfig() {
		\Helper::setGetterUpperCase();
		$network = Network::where(['NETWORK_TYPE'=>1])->get(['ID', 'NAME']);
		$result = [];
		foreach ($network as $n){
			$tmp = [];
			$count = AllocJob::where(['NETWORK_ID'=>$n->ID])->count();
			if($count > 0){
				$tmp['NAME'] = $n->NAME.'('.$count.')';
                $tmp['AS_NAME'] = $n->NAME;
			}else{
				$tmp['NAME'] = $n->NAME;
                $tmp['AS_NAME'] = $n->NAME;
			}
	
			$tmp['ID'] = $n->ID;
	
			array_push($result, $tmp);
		}
		
		$code_alloc_value_type = CodeAllocValueType::all('ID', 'NAME');
		$facility = Facility::all('ID', 'NAME');
		$code_alloc_type = CodeAllocType::all('ID', 'NAME');
		$codeFlowPhase = CodeFlowPhase::all('ID', 'NAME');
		$codeAllocValueType = CodeAllocValueType::all('ID', 'NAME');
		$codeAllocFromOption = CodeAllocFromOption::all('ID', 'NAME');
		$baAddress	= \App\Models\BaAddress::all('ID', 'NAME');

		$excelFile = '';
	
		return view ( 'front.allocset', [
				'result'=>$result, 
				'baAddress'=>$baAddress,
				'CodeAllocValueType'=>$code_alloc_value_type,
				'facility'=>$facility,
				'codeAllocType'=>$code_alloc_type,
				'codeFlowPhase' =>$codeFlowPhase,
				'codeAllocFromOption' =>$codeAllocFromOption,
				'codeAllocValueType' =>$codeAllocValueType,
				'excelFile' => $excelFile
		]);
	}
	
	public function addJob (Request $request) {
		$data = $request->all ();
        $param = [
            'NAME' => $data['NAME'],
            'NETWORK_ID' => $data['NETWORK_ID'],
            'VALUE_TYPE' => $data['VALUE_TYPE'],
            'ALLOC_GAS' => $data['ALLOC_GAS'],
            'ALLOC_OIL' => $data['ALLOC_OIL'],
            'ALLOC_WATER' => $data['ALLOC_WATER'],
            'ALLOC_COMP' => $data['ALLOC_COMP'],
            'ALLOC_GASLIFT' => $data['ALLOC_GASLIFT'],
            'ALLOC_CONDENSATE' => $data['ALLOC_CONDENSATE'],
            'DAY_BY_DAY' => $data['DAY_BY_DAY'],
            'BEGIN_DATE' => ($data['BEGIN_DATE'] != '') ? $data['BEGIN_DATE']: null,
            'END_DATE' => ($data['END_DATE'] != '') ? $data['END_DATE']: null
        ];
		AllocJob::insert($param);
        //$new_job = AllocJob::firstOrCreate($param);
		return response ()->json ('ok');
	}
	
	public function addrunner(Request $request) {
		$data = $request->all ();
		//\Log::info($data);
		$job_id = $data ['job_id'];
		$runner_name = $data ['runner_name'];
		$order = $data ['order'];
		$fifo =  $data ['fifo'];
		$baId 	= $data ['BA_ID'];
		$baId 	= $baId?$baId:null;
		$alloc_type = $data ['alloc_type'];
		$theor_value_type = $data ['theor_value_type'];
		$theor_phase = $data ['theor_phase'];
		$from_option = $data ['from_option'];
		$to_option = $data ['to_option'];
		$obj_froms = explode ( ',', $data ['obj_from'] );
		$obj_tos = explode ( ',', $data ['obj_to'] );
		$begin_date = ($data['begin_date'] == null || $data['begin_date'] == "") ? null : \Helper::parseDate($data['begin_date']);
		$end_date = ($data['end_date'] == null || $data['end_date'] == "") ? null : \Helper::parseDate($data['end_date']);
		
		$param1 = [
			'NAME' =>$runner_name,
			'JOB_ID' =>	$job_id,
			'ORDER'.($this->isReservedName?'_':'')=>$order,
			'FIFO'=>$fifo,
			'ALLOC_TYPE'=>$alloc_type,
            'THEOR_VALUE_TYPE'=>($theor_value_type>0?$theor_value_type:null),
            'THEOR_PHASE'=>($theor_phase>0?$theor_phase:null),
            'FROM_OPTION'=>($from_option>0?$from_option:null),
            'TO_OPTION'=>$to_option,
            'BEGIN_DATE'=>$begin_date,
            'END_DATE'=>$end_date
		];

		$condition = array (
				'ID' => null
		);

        //$runner = (config('database.default')==='oracle') ? AllocRunner::updateOrCreate ( $condition, $param1 ) : AllocRunner::firstOrCreate($param1);
		
		//$runner_id = $runner->ID;
		$runner_id = AllocRunner::insertGetId($param1);
		if(!$runner_name && $runner_id>0)
		{
			AllocRunner::where(['ID'=>$runner_id])->update(['NAME'=>'R'.$runner_id]);
		}
		
		foreach($obj_froms as $obj_from) if($obj_from)
		{
			$xs=explode(':',$obj_from);
			if($xs[1]=="")continue;		//Added by Q
			AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs[1], 'OBJECT_ID'=>$xs[0], 'DIRECTION'=>1,'MINUS' . ($this->isReservedName ? '_' : '') => $xs[2]]); //'MINUS'=>$xs[2]
		}

		foreach($obj_tos as $obj_to) if($obj_to)
		{
			$xs=explode(':',$obj_to);
			if($xs[1]=="")continue;		//Added by Q
			AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs[1], 'OBJECT_ID'=>$xs[0], 'DIRECTION'=>0, 'FIXED'=>$xs[2]]);
		}
		
		return response ()->json ('ok');
	}
	
	public function getrunnerslist(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$alloc_runner = AllocRunner::getTableName();
		$code_alloc_value_type = CodeAllocValueType::getTableName();
		$code_alloc_type = CodeAllocType::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		$code_alloc_from_option = CodeAllocFromOption::getTableName();
		
		$result = DB::table ( $alloc_runner )
		->leftjoin ( $code_alloc_value_type, "$alloc_runner.theor_value_type", '=', "$code_alloc_value_type.ID" )
		->leftjoin ( $code_alloc_type, "$alloc_runner.alloc_type", '=', "$code_alloc_type.ID" )
		->leftjoin ( $code_flow_phase, "$alloc_runner.theor_phase", '=', "$code_flow_phase.ID" )
		->leftjoin ( $code_alloc_from_option, "$alloc_runner.from_option", '=', "$code_alloc_from_option.ID" )
		->where ( ["$alloc_runner.JOB_ID" => $data['job_id']])
		->orderBy("$alloc_runner.ORDER".($this->isReservedName?'_':''))->select("$alloc_runner.*", "$code_alloc_value_type.NAME AS THEOR_VALUE_TYPE_NAME", "$code_alloc_type.NAME AS ALLOC_TYPE_NAME","$code_flow_phase.NAME AS THEOR_PHASE_NAME","$code_alloc_from_option.NAME as ALLOC_FROM_SOURCE")->get();
		$i=0;
		$str = "";
		$runner_options="";
		foreach ($result as $row){	
			$runner_options .= "<option value='$row->ID'>$row->NAME</option>";
			$allocrunnerobjects = AllocRunnerObjects::where(['RUNNER_ID'=>$row->ID])->get();
			//if(count($allocrunnerobjects) > 0)
			{
				$o_in="";$o_out="";
				$o_in_x="";
				$o_out_x="";
				$count_in=0;
				$count_out=0;				
				$s = [];
				foreach($allocrunnerobjects as $ro){
					 $vname = '';
					 if($this->isReservedName) $ro->MINUS = $ro->MINUS_;
					 if($ro->OBJECT_TYPE == 1){
						 $f = Flow::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 if($f) $vname = $f->NAME;
					 }elseif($ro->OBJECT_TYPE == 2){
						 $f = EnergyUnit::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 if($f) $vname = $f->NAME;
					 }elseif($ro->OBJECT_TYPE == 3){
						 $f = Tank::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 if($f) $vname = $f->NAME;
					 }elseif($ro->OBJECT_TYPE == 4){
						 $f = Storage::where(['ID'=>$ro->OBJECT_ID])->select('NAME')->first();
						 if($f) $vname = $f->NAME;
					 }
					 	
					 $ro['OBJECT_NAME'] = $vname;
					 
					if($ro->DIRECTION == 1) //in
					{
						$o_in.="<span id='".$ro->ID."' o_type='".$ro->OBJECT_TYPE."' o_id='".$ro->OBJECT_ID."' minus='".$ro->MINUS."' style='display:block'>".($ro->MINUS==1? '<font color="red">[ - ] </font>': '').$ro->OBJECT_NAME."</span>";
						if($count_in == 3)
						{
							$o_in_x="<span id='Q_I_$row->ID'>$o_in_x"."<br>... <span style='cursor:pointer;color:blue;text-decoration: underline;font-size:8pt' onclick='$(\"#Qobjectfrom$row->ID\").show();$(\"#Q_I_$row->ID\").hide();'>Show all {objects}</span></span>";
						}
						else if($count_in<3)
						{
							$o_in_x.=($o_in_x?"<br>":"").$ro->OBJECT_NAME;
						}
						$count_in++;
					}
					else
					{
						$o_out.= "<span id='".$ro->ID."' o_type='".$ro->OBJECT_TYPE."' o_id='".$ro->OBJECT_ID."' fixed='".$ro->FIXED."' style='display:block;'>".($ro->FIXED==1? '<font color="#609CB9">[ F ]</font>': '')." ".$ro->OBJECT_NAME."</span>";
						if($count_out==3)
						{
							$o_out_x="<span id='Q_O_$row->ID'>$o_out_x"."<br>... <span style='cursor:pointer;color:blue;text-decoration: underline;font-size:8pt' onclick='$(\"#Qobjectto$row->ID\").show();$(\"#Q_O_$row->ID\").hide();'>Show all {objects}</span></span>";
						}
						else if($count_out<3)
						{
							$o_out_x.=($o_out_x?"<br>":"").$ro->OBJECT_NAME;
						}
						$count_out++;
					}
				}
				$i++;
				if ($i % 2 == 0)
					$bgcolor = "#eeeeee";
				else
					$bgcolor = "#f8f8f8";
				$str .= "<tr bgcolor='$bgcolor' class='runner_item' id='runner_item{$row->ID}' data-from_option='" . $row->FROM_OPTION . "'data-to_option='" . $row->TO_OPTION . "'>";
				$str .= "<td><input type='checkbox' runner-id='{$row->ID}' class='select-runner'></td><td style=\"cursor:pointer\" onclick=\"\"><span id='Qorder{$row->ID}'>".($this->isReservedName?$row->ORDER_:$row->ORDER)."</span></td>";
				$str .= "<td><span id='Qrunner_name{$row->ID}'>$row->NAME</span>".($row->FIFO=='Y'?'<br><font color="green">[FIFO]</font>':'').
				"<span style='display:none' id='QBaAddress{$row->ID}'>$row->BA_ID</span></td>";
				$str .= "<td><span style='display:none' id='alloc_type{$row->ID}'>$row->ALLOC_TYPE</span><span style='display:none' id='theor_value_type{$row->ID}'>$row->THEOR_VALUE_TYPE</span><span style='display:none' id='theor_phase{$row->ID}'>$row->THEOR_PHASE</span><span style='display:none' id='fifo{$row->ID}'>$row->FIFO</span><span style='display:none' id='excel_template{$row->ID}'>$row->EXCEL_TEMPLATE</span><span style='display:none' id='use_excel{$row->ID}'>$row->USE_EXCEL</span><span style='display:none' id='run_sql{$row->ID}'>$row->RUN_SQL</span>$row->ALLOC_TYPE_NAME" . ($row->THEOR_PHASE_NAME ? "<br><font size=1 color=green>(Theor phase: $row->THEOR_PHASE_NAME)</font>" : "") . ($row->THEOR_VALUE_TYPE_NAME ? "<br><font size=1 color=green>(Theor value type: $row->THEOR_VALUE_TYPE_NAME)</font>" : "") . "</td>";
				$in_option = "";
				if($row->ALLOC_FROM_SOURCE){
					$in_option = "<font color='green'>&gt;&gt; {$row->ALLOC_FROM_SOURCE}".($row->TO_OPTION?"&rarr;{$row->TO_OPTION}":"")."</font><br>";
				}
				if($row->USE_EXCEL=='Y'){
					$str .= "<td colspan='2' align='center'><span id='Qobjectfrom" . $row->ID . "' style='display:none'>$o_in</span><span id='Qobjectto" . $row->ID . "' style='display:none'>$o_out</span>(Excel allocation)</td>";
				}
				else {
					if ($count_in > 5)
						$str .= "<td>$in_option" . str_replace ( "{objects}", "$count_in objects", $o_in_x ) . "<span id='Qobjectfrom" . $row->ID . "' style='display:none'>$o_in</span></td>";
					else
						$str .= "<td>$in_option<span id='Qobjectfrom" . $row->ID . "'>$o_in</span></td>";
					if ($count_out > 5)
						$str .= "<td>" . str_replace ( "{objects}", "$count_out objects", $o_out_x ) . "<span id='Qobjectto" . $row->ID . "' style='display:none'>$o_out</span></td>";
					else
						$str .= "<td><span id='Qobjectto" . $row->ID . "'>$o_out</span></td>";
				}
				
				if ($row->BEGIN_DATE == null || $row->BEGIN_DATE == "") {
					$runner_begin_date_formart = null;
				} else {
	                $runner_begin_date_formart = date_create($row->BEGIN_DATE);
	                $runner_begin_date_formart = date_format($runner_begin_date_formart,"m/d/Y");
				}
				if ($row->END_DATE == null || $row->END_DATE == "") {
					$runner_end_date_formart = null;
				} else {
                	$runner_end_date_formart = date_create($row->END_DATE);
                	$runner_end_date_formart = date_format($runner_end_date_formart,"m/d/Y");
				}
				$str .= "<td><span id='Qbegindate{$row->ID}'>$runner_begin_date_formart</span></td>";
				$str .= "<td><span id='Qenddate{$row->ID}'>$runner_end_date_formart</span></td>";
				$str .= "<td width='170' style='font-size:8pt'>&nbsp;";
				$str .= "<a href=\"javascript:checkRunner($row->ID)\">Simulate</a> |";
				$str .= "<a href=\"javascript:deleteRunner($row->ID)\">Delete</a> |";
				$str .= "<a href=\"javascript:editRunner($row->ID)\">Edit</a> |";
				$str .= "<a href=\"javascript:runRunner($row->ID)\">Run</a> |";
				$str .= "<a href=\"javascript:clearAllocData($row->ID)\">Clear</a></td>";
				$str .= "</tr>";
			}
			
			$result = $str."#$%".$runner_options;
		}
		
		return response ()->json ($result);
	}

	public function genTemplateFile($strIDs) {
		$runner_ids = explode(',', $strIDs);
		$path = storage_path('alloc_template/base/basic_template.xlsx');
		$fileName = 'Allocation Template '.date('YmdHis');
		Excel::load($path, function($doc) use ($runner_ids, $fileName) {
			$title = 'Allocation Template';

			$tableRunner = AllocRunner::getTableName();
			$tableRunnerObject = AllocRunnerObjects::getTableName();
			$allocJob = AllocJob::getTableName();
			$code_alloc_value_type = CodeAllocValueType::getTableName();
			$code_alloc_type = CodeAllocType::getTableName();
			$code_flow_phase = CodeFlowPhase::getTableName();
			
			$result = DB::table ( $tableRunner . ($this->isReservedName?' ':' AS ').'b' )
			->join ( $allocJob . ($this->isReservedName?' ':' AS ').'j', 'j.ID', '=', 'b.JOB_ID' )
			->leftjoin ( $tableRunnerObject . ($this->isReservedName?' ':' AS ').'a', 'a.RUNNER_ID', '=', 'b.ID' )
			->leftjoin ( $code_alloc_value_type, "b.theor_value_type", '=', "$code_alloc_value_type.ID" )
			->leftjoin ( $code_alloc_value_type . ($this->isReservedName?' ':' AS ').'v', "j.VALUE_TYPE", '=', "v.ID" )
			->leftjoin ( $code_alloc_type, "b.alloc_type", '=', "$code_alloc_type.ID" )
			->leftjoin ( $code_flow_phase, "b.theor_phase", '=', "$code_flow_phase.ID" )
			->whereIn('b.ID', $runner_ids)
			->select('v.NAME as ALLOC_VALUE_TYPE','a.*','b.ID as RUNNER_ID', 
				DB::raw("case when j.ALLOC_OIL>0 then 'Oil' else (
							case when j.ALLOC_GAS>0 then 'Gas' else (
								case when j.ALLOC_WATER>0 then 'Water' else (
									case when j.ALLOC_GASLIFT>0 then 'Gas Lift' else (
										case when j.ALLOC_CONDENSATE>0 then 'Condensate' else null end) end) end) end) end ALLOC_PHASE"),
				DB::raw("case a.OBJECT_TYPE 
					when 1 then (select NAME from FLOW where id=a.OBJECT_ID) 
					when 2 then (select NAME from ENERGY_UNIT where id=a.OBJECT_ID) 
					when 3 then (select NAME from TANK where id=a.OBJECT_ID) 
					when 4 then (select NAME from STORAGE where id=a.OBJECT_ID) 
					else null end OBJECT_NAME"), 
				DB::raw("case a.OBJECT_TYPE 
					when 1 then 'Flow' 
					when 2 then 'Well' 
					when 3 then 'Tank' 
					when 4 then 'Storage' 
					else null end OBJ_TYPE"), 
				'b.NAME as RUNNER_NAME',"$code_alloc_type.NAME as ALLOC_TYPE","$code_flow_phase.NAME as THEOR_PHASE","$code_alloc_value_type.NAME as THEOR_VALUE_TYPE",'b.FIFO')
			->orderBy('b.ORDER'.($this->isReservedName?'_':''))->orderBy('OBJECT_NAME')->get();

			$sheet = $doc->setActiveSheetIndex(0);
			$sheet->setCellValue('A1', $title?$title:$fileName);
			//$runnerInfos = [];
			$runnerID = "";
			$rowIndexConfig = 6;
			$rowIndexSource = 2;
			$rowIndexTarget = 2;
			foreach($result as $ro){
				$runnerName = "{$ro->RUNNER_NAME} ({$ro->RUNNER_ID})";
				$objectName = $ro->OBJECT_NAME;//"{$ro->OBJECT_NAME} ({$ro->OBJECT_ID})";
				if($ro->RUNNER_ID!=$runnerID){
					$runnerID = $ro->RUNNER_ID;
					//$runnerInfos[$runnerName] = ['ALLOC_TYPE'=> $ro->ALLOC_TYPE, 'THEOR_PHASE'=> ($ro->THEOR_PHASE?$ro->THEOR_PHASE:$ro->ALLOC_PHASE), 'THEOR_VALUE_TYPE'=> ($ro->THEOR_VALUE_TYPE?$ro->THEOR_VALUE_TYPE:$ro->ALLOC_VALUE_TYPE)];
					$doc->sheet(0, function($sheet) use ($runnerName, $rowIndexConfig, $ro) {
						$sheet->row($rowIndexConfig,[$runnerName,$ro->ALLOC_TYPE,
							($ro->THEOR_PHASE?$ro->THEOR_PHASE:$ro->ALLOC_PHASE),
							($ro->THEOR_VALUE_TYPE?$ro->THEOR_VALUE_TYPE:$ro->ALLOC_VALUE_TYPE),
							"=SUMIF(Source!\$A:\$A,A$rowIndexConfig,Source!\$H:\$H)",
							"=SUMIF(Target!\$A:\$A,A$rowIndexConfig,Target!\$I:\$I)",
							"=IF(F$rowIndexConfig<>0,E$rowIndexConfig/F$rowIndexConfig,0)"]);
					});
					$rowIndexConfig++;
				}
				if($ro->DIRECTION>0){
					$doc->sheet('Source', function($sheet) use ($runnerName, $objectName, $rowIndexSource, $ro) {
						$sheet->row($rowIndexSource,[$runnerName,$ro->OBJ_TYPE,$objectName,$ro->OBJECT_ID,'Standard',
							$ro->ALLOC_VALUE_TYPE,'',($ro->MINUS?"=-":"=")."G$rowIndexSource"]);
					});
					if($ro->MINUS){
						$doc->setActiveSheetIndex(1)->cells("A$rowIndexSource:H$rowIndexSource", function($cells) {
							$cells->setBackground('#e0e0e0');
						});
					}
					$rowIndexSource++;
				}
				else {
					$doc->sheet('Target', function($sheet) use ($runnerName, $objectName, $rowIndexTarget, $ro) {
						$sheet->row($rowIndexTarget,[$runnerName,$ro->OBJ_TYPE,$objectName,$ro->OBJECT_ID,'Theoretical',
							($ro->THEOR_VALUE_TYPE?$ro->THEOR_VALUE_TYPE:$ro->ALLOC_VALUE_TYPE),'',$ro->FIXED?'Standard':'',"=G$rowIndexTarget"]);
					});
					$doc->sheet('Result', function($sheet) use ($runnerName, $objectName, $rowIndexTarget, $ro) {
						$sheet->row($rowIndexTarget,[$runnerName,$ro->OBJ_TYPE,$objectName,$ro->OBJECT_ID,$ro->ALLOC_VALUE_TYPE,"=Target!I$rowIndexTarget*VLOOKUP(A$rowIndexTarget,Config!\$A\$6:\$G\$99,7,FALSE)"]);
					});
					if($ro->FIXED){
						$doc->setActiveSheetIndex(2)->cells("A$rowIndexTarget:I$rowIndexTarget", function($cells) {
							$cells->setBackground('#e0e0e0');
						});
						$doc->setActiveSheetIndex(3)->cells("A$rowIndexTarget:F$rowIndexTarget", function($cells) {
							$cells->setBackground('#e0e0e0');
						});
					}
					$rowIndexTarget++;
				}
			}
/*
			$doc->sheet('Source', function($sheet) use ($result, $doc) {
				$rowIndexSource=2;
				$lastRunnerName="";
				foreach($result as $ro){
					if($ro->DIRECTION>0){
						$runnerName = "{$ro->RUNNER_NAME} ({$ro->RUNNER_ID})";
						$sheet->row($rowIndexSource,[$runnerName,$ro->OBJ_TYPE,$ro->OBJECT_NAME,'Standard',$ro->ALLOC_VALUE_TYPE,'',($ro->MINUS?"=-":"=")."F$rowIndexSource"]);
						if($ro->MINUS){
							$doc->setActiveSheetIndex(1)->cells("A$rowIndexSource:G$rowIndexSource", function($cells) {
								$cells->setBackground('#e0e0e0');
							});
						}
						if($runnerName!=$lastRunnerName){
							$doc->setActiveSheetIndex(1)->cells("A$rowIndexSource:A$rowIndexSource", function($cells) {
								$cells->setBackground('#fff9ba');
							});
							$lastRunnerName = $runnerName;							
						}
						$rowIndexSource++;
					}
				}
			});

			$doc->sheet('Target', function($sheet) use ($result, $doc) {
				$rowIndexTarget=2;
				$lastRunnerName="";
				foreach($result as $ro){
					$runnerName = "{$ro->RUNNER_NAME} ({$ro->RUNNER_ID})";
					if($ro->DIRECTION==0){
						$sheet->row($rowIndexTarget,[$runnerName,$ro->OBJ_TYPE,$ro->OBJECT_NAME,'Theoretical',$ro->ALLOC_VALUE_TYPE,'',$ro->FIXED?'Standard':'']);
						if($ro->FIXED){
							$doc->setActiveSheetIndex(2)->cells("A$rowIndexTarget:H$rowIndexTarget", function($cells) {
								$cells->setBackground('#e0e0e0');
							});
						}
						if($runnerName!=$lastRunnerName){
							$doc->setActiveSheetIndex(2)->cells("A$rowIndexTarget:A$rowIndexTarget", function($cells) {
								$cells->setBackground('#fff9ba');
							});
							$lastRunnerName = $runnerName;							
						}
						$rowIndexTarget++;
					}
				}
			});

			$sheet = $doc->setActiveSheetIndex(0);
			$sheet->setCellValue('A1', $title?$title:$fileName);
			$rowIndex = 6;
			foreach($runnerInfos as $runnerName=>$runnerInfo){
				$sheet->setCellValue("A$rowIndex", $runnerName);
				$sheet->setCellValue("B$rowIndex", $runnerInfo["ALLOC_TYPE"]);
				$sheet->setCellValue("C$rowIndex", $runnerInfo["THEOR_PHASE"]);
				$sheet->setCellValue("D$rowIndex", $runnerInfo["THEOR_VALUE_TYPE"]);
				$sheet->setCellValue("E$rowIndex", "=SUMIF(Source!\$A:\$A,A$rowIndex,Source!\$G:\$G)");
				$sheet->setCellValue("F$rowIndex", "=SUMIF(Target!\$A:\$A,A$rowIndex,Target!\$H:\$H)");
				$sheet->setCellValue("G$rowIndex", "=IF(F$rowIndex<>0,E$rowIndex/F$rowIndex,0)");
				
				$rowIndex++;
			}
*/
			$doc->setFilename($fileName);
		})
		//->download('xlsx');
		->store('xlsx', storage_path('alloc_template'));
		$file = storage_path("alloc_template/{$fileName}.xlsx");
		if(file_exists($file)){
			$filetype=filetype($file);
			$filename=basename($file);
			header ("Content-Type: ".$filetype);
			header ("Content-Length: ".filesize($file));
			header ("Content-Disposition: attachment; filename=".$filename);
			readfile($file);
			unlink($file);
		}
		else
			echo "Can not generate file";
	}

	public function downloadExcelAllocFile($fileName, $folder = 'alloc_result'){
		$file = storage_path("$folder/{$fileName}");
		if(file_exists($file)){
			$filetype=filetype($file);
			$filename=basename($file);
			header ("Content-Type: ".$filetype);
			header ("Content-Length: ".filesize($file));
			header ("Content-Disposition: attachment; filename=".$filename);
			readfile($file);
		}
		else
			echo "Can not download file $fileName";
	}
	
	public function downloadAllocTemplateFile($fileName){
		return $this->downloadExcelAllocFile($fileName, 'alloc_template');
	}
	
	public function getconditionslist(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$alloc_condition = AllocCondition::getTableName();
		$alloc_runner = AllocRunner::getTableName();
		$alloc_cond_out = AllocCondOut::getTableName();
		
		$result = DB::table ( $alloc_condition . ($this->isReservedName?' ':' AS ').'a' )
		->leftjoin ( $alloc_runner . ($this->isReservedName?' ':' AS ').'b', 'a.RUNNER_TO_ID', '=', 'b.ID' )
		->join ( $alloc_runner . ($this->isReservedName?' ':' AS ').'c', 'a.RUNNER_FROM_ID', '=', 'c.ID' )
		->where ( ['c.job_id'=>$data['job_id']])
		->select('a.*', 'b.NAME AS RUNNER_TO_NAME', 'c.NAME AS RUNNER_FROM_NAME')->get();		
		
		$str = "";
		$i = 0;
		
		foreach ($result as $row){
			$tmp = DB::table ( $alloc_cond_out . ($this->isReservedName?' ':' AS ').'a' )
			->join ( $alloc_runner . ($this->isReservedName?' ':' AS ').'b', 'a.RUNNER_TO_ID', '=', 'b.ID' )
			->where ( ['a.CONDITION_ID'=>$row->ID])
			->select('a.*', 'b.NAME AS RUNNER_TO_NAME')->get();
			
			$r_out="";
			$r_out_x="";
			
			foreach ($tmp as $ro){
				$r_out.=($r_out==""?"":", ")."$ro->VALUE: $ro->RUNNER_TO_NAME";
				$r_out_x.=($r_out_x==""?"":",")."$ro->VALUE:$ro->RUNNER_TO_ID:$ro->RUNNER_TO_NAME";
			}
			
			if($r_out=="")
			{
				$r_out="default:$row->RUNNER_TO_NAME";
				$r_out_x="default:$row->RUNNER_TO_ID:$row->RUNNER_TO_NAME";
			}
			
			$i++;
			if($i % 2==0) $bgcolor="#eeeeee"; else $bgcolor="#f8f8f8";
			$str .= "<tr bgcolor='$bgcolor'>";
			$str .= "<td><span id='Qcondition_name_".$row->ID."'>$row->NAME</span></td>";
			$str .= "<td><span id='Qcondition_out_".$row->ID."' style='display:none'>$r_out_x</span><input type='hidden' id='RUNNER_FROM_ID_".$row->ID."' value='$row->RUNNER_FROM_ID'><input type='hidden' id='RUNNER_TO_ID_".$row->ID."' value='$row->RUNNER_TO_ID'><span style='display:none' id='EXPRESSION_".$row->ID."'>$row->EXPRESSION</span>".substr($row->EXPRESSION,0,20).(strlen($row->EXPRESSION)>20?"...":"")."</td>";
			$str .= "	<td><span id='Qcondition_from_".$row->ID."'>$row->RUNNER_FROM_NAME</span></td>";
			$str .= "				<td>$r_out</td>";
			$str .= "				<td style='font-size:8pt'>&nbsp;";
			$str .= "				<a href=\"javascript:deleteCondtion($row->ID)\">Delete</a> |";
			$str .= "				<a href=\"javascript:editCondition($row->ID)\">Edit</a>";
			$str .= "				</td>";
			$str .= "				</tr>";
		}
		
		return response ()->json ($str);
	}
	
	public function deletejob(Request $request) {
		$data = $request->all ();
        $alloc_runner = AllocRunner::where('JOB_ID','=',$data['job_id'])->select('ID','JOB_ID')->get();
        if(count($alloc_runner) > 0){
            foreach ($alloc_runner as $value)
                AllocRunnerObjects::where('RUNNER_ID','=',$value->ID)->delete();
            AllocRunner::where('JOB_ID','=',$data['job_id'])->delete();
        }
        AllocJob::where('ID','=',$data['job_id'])->delete();
		return response ()->json ('ok');
	}
	
	public function deleterunner(Request $request) {
		$data = $request->all ();
	
		AllocRunnerObjects::where(['RUNNER_ID'=>$data['runner_id']])->delete();
		AllocRunner::where(['ID'=>$data['runner_id']])->delete();
	
		return response ()->json ('ok');
	}
	
	public function savecondition(Request $request) {
		$data = $request->all ();
		$job_id = $data['job_id'];
		$condition_id = $data['condition_id'];
		$condition = $data['condition'];
		$name = $data['name'];
		$expression = $data['expression'];
		$from_runner_id = $data['from_runner_id'];
		if ($condition_id > 0)
		{
			if(!$name) $name="C$condition_id";
			AllocCondition::where(['ID'=>$condition_id])->update(['NAME'=>$name, 'EXPRESSION'=>$expression, 'RUNNER_FROM_ID'=>$from_runner_id]);
		}
		else
		{
			/* $sql="insert into alloc_condition(NAME,EXPRESSION,RUNNER_FROM_ID) values('$name','$expression','$from_runner_id')";
			$re=mysql_query($sql) or die("Error: ".mysql_error());
			$condition_id=mysql_insert_id(); */
			
			$where = array (
					'ID' => -1
			);
			
			$allocCondition = AllocCondition::updateOrCreate ( $where, ['NAME'=>$name, 'EXPRESSION'=>$expression, 'RUNNER_FROM_ID'=>$from_runner_id] ); //AllocRunner::insert($param1);
			$condition_id = $allocCondition->ID;
			if(!$name)
			{
				$name="C$condition_id";
				/* $sql="UPDATE `alloc_condition` SET `NAME`='$name' WHERE `ID`='".$condition_id."';";
				$re=mysql_query($sql) or die("Error: ".mysql_error()); */
				
				AllocCondition::where(['ID'=>$condition_id])->update(['NAME'=>$name]);
			}
		}
		/* $sql="delete from alloc_cond_out where CONDITION_ID='$condition_id'";
		$re=mysql_query($sql) or die("Error: ".mysql_error()); */
		
		AllocCondOut::where(['CONDITION_ID'=>$condition_id]);
		
		$ss=explode(',',$condition);
		$xx=explode(':',$ss[0]);
		if(count($ss)>1 || $xx[0]!="")
		{
			foreach($ss as $s)
			{
				$xx=explode(':',$s);
				/* $s_f="INSERT INTO alloc_cond_out (CONDITION_ID, RUNNER_TO_ID, VALUE) VALUES('".$condition_id."', '".$xx[1]."', '".c."')";
				$re=mysql_query($s_f) or die("Error: ".mysql_error()); */
				
				AllocCondOut::insert(['CONDITION_ID'=>$condition_id, 'RUNNER_TO_ID'=>$xx[1], 'VALUE'=>'c']);
			}
		}
		else
		{
			/* $sql="UPDATE `alloc_condition` SET `RUNNER_TO_ID`='$xx[1]' WHERE `ID`='".$condition_id."';";
			$re=mysql_query($sql) or die("Error: ".mysql_error()); */
			
			AllocCondition::where(['ID'=>$condition_id])->update(['RUNNER_TO_ID'=>$xx[1]]);
		}
		
		return response ()->json ('ok');
	}
	
	public function clonenetwork(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		
		$network_id=$data['network_id'];
		$network_name=addslashes($data['network_name']);
		$message = "Clone allocation group successfully";
		$success = true;
		$new_network_id = -1;
		if($network_id>0 && $network_name){			
			$condition = array (
					'ID' => -1
			);
			$tmp = NetWork::updateOrCreate ( $condition, ['NAME'=>$network_name, 'NETWORK_TYPE'=>1] );
			$new_network_id = $tmp->ID;
			
			if($new_network_id>0){
				$result_job = AllocJob::where(['NETWORK_ID' => $network_id])
					->select('ID' ,'CODE', 'NAME', 'NETWORK_ID', 'VALUE_TYPE', 'LAST_RUN', 'ALLOC_OIL', 'ALLOC_GAS', 'ALLOC_WATER', 'ALLOC_COMP', 'ALLOC_GASLIFT', 'ALLOC_CONDENSATE')
					->get();
				foreach ($result_job as $row_job)
				{
					$allocjob = $row_job->toArray();
					$allocjob['ID'] = null;
					$allocjob['NETWORK_ID'] = $new_network_id;
					
					$condition = array (
							'ID' => -1
					);
					$tmp = AllocJob::updateOrCreate ( $condition, $allocjob );
					$new_job_id = $tmp->ID;
					if($new_job_id>0){
						$result_runner = AllocRunner::where(['JOB_ID'=>$row_job->ID])->select('ID', 'CODE', 'NAME', 'JOB_ID', 'ORDER'.($this->isReservedName?'_':''), 'ALLOC_TYPE', 'THEOR_PHASE', 'THEOR_VALUE_TYPE', 'LAST_RUN')->get();
						foreach ($result_runner as $row_runner)
						{
							$allocRunner = $row_runner->toArray();
							$allocRunner["ID"] = null;
							$allocRunner["JOB_ID"] = $new_job_id;
							$condition = array (
									'ID' => -1
							);
							$tmp = AllocRunner::updateOrCreate ( $condition, $allocRunner );
							$new_runner_id = $tmp->ID;
							if($new_runner_id>0){
								$result_objs = AllocRunnerObjects::where(['RUNNER_ID'=>$row_runner->ID])->select('RUNNER_ID', 'OBJECT_TYPE', 'OBJECT_ID', 'DIRECTION', 'FIXED', 'MINUS'.($this->isReservedName?'_':''))->get();
								foreach ($result_objs as $row_objs)
								{									
									$allocRunnerObjects = $row_objs->toArray();
									$allocRunnerObjects["ID"] = null;
									$allocRunnerObjects["RUNNER_ID"] = $new_runner_id;
									AllocRunnerObjects::insert($allocRunnerObjects);
								}
							}
						}
					}
				}
			}
			else{
				$success = false;
				$message = "Can not add new network";
			}
		}
		else{
			$success = false;
			$message = "Incorect input data";
		}
		return response ()->json ( ["success" => $success, "message" => $message, "new_network_id" => $new_network_id ] );
	}

	public function renameAllocationGroup(Request $request){
        \Helper::setGetterUpperCase();
        $data = $request->all ();
        $condition = array (
            'ID' => $data['ID']
        );
        $param = array (
            'NAME' => $data['GROUPNAME']
        );
        $data =  NetWork::updateOrCreate($condition,$param);
        return response()->json($data);
    }

    public function deleteAllocationGroup(Request $request){
        \Helper::setGetterUpperCase();
        $data = $request->all ();
        $id_network = $data['ID'];
        $alloc_job = AllocJob::where("NETWORK_ID",$id_network)->get();
        $data = [];
        if(count($alloc_job) == 0){
            NetWork::where('ID', $id_network)->delete();
            $allocset_group = $this->getAllocsetGroup();
            $data['NET_WORK']=$allocset_group;
            $data['MESS']    = true;
        }else $data['MESS'] = false;
        return response()->json($data);
    }

    public function newAllocationGroup(Request $request){
        \Helper::setGetterUpperCase();
        $data = $request->all ();
        $param = [
            'NAME'         =>$data['GROUPNAME'],
            'NETWORK_TYPE' => 1
        ];
        NetWork::firstOrCreate($param);
        $allocset_group = $this->getAllocsetGroup();
        return response()->json($allocset_group);
    }

    private function getAllocsetGroup(){
        return NetWork::where("NETWORK_TYPE", 1)->select("ID","NAME")->get();
    }

	public function jobdiagram($job_id) {
		return view ( 'front.jobdiagram', [ 
				'job_id' => $job_id 
		] );
	}
	public function loaddiagram($job_id) {
		$tmp = JobDiagram::where ( [ 
				'JOB_ID' => $job_id 
		] )->select ( 'DIAGRAM_CODE' )->first ();
		return response ()->json ( $tmp ['DIAGRAM_CODE'] );
	}
	public function editJob(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		$job_id = $data ['id'];
		$job_name = $data ['name'];
		$value_type = $data ['value_type'];
		$gas = $data ['alloc_gas'];
		$oil = $data ['alloc_oil'];
		$water = $data ['alloc_water'];
		$comp = $data ['alloc_comp'];
		$gaslift = $data ['alloc_gaslift'];
		$condensate = $data ['alloc_condensate'];
		$daybyday = $data ['alloc_daybyday'];
		$begin_date = ($data['begin_date'] == null || $data['begin_date'] == "") ? null : \Helper::parseDate($data['begin_date']);
		$end_date = ($data['end_date'] == null || $data['end_date'] == "") ? null : \Helper::parseDate($data['end_date']);
		if ($gas == 0)
			$comp = 0;

		if ($data ['clone'] == 1) {
			$r = AllocJob::where(['ID'=>$job_id])->select('NETWORK_ID')->first();
			$network_id = $r->NETWORK_ID;
			
			$condition = array (
					'ID' => -1
			);
			
			$allocjob = [
				'NAME'=>$job_name,
				'NETWORK_ID'=>$network_id,
				'VALUE_TYPE'=>$value_type,
				'ALLOC_GAS'=>$gas,
				'ALLOC_OIL'=>$oil,
				'ALLOC_WATER'=>$water,
				'ALLOC_COMP'=>$comp,
				'ALLOC_GASLIFT'=>$gaslift,
				'ALLOC_CONDENSATE'=>$condensate,
				'DAY_BY_DAY'=>$daybyday,
				'BEGIN_DATE'=>$begin_date,
				'END_DATE'=>$end_date
			];

			//$tmp = AllocJob::updateOrCreate ( $condition, $allocjob );
            //$tmp = AllocJob::firstOrCreate($allocjob);
            $new_job_id = AllocJob::insertGetId($allocjob);
			//$new_job_id = $tmp->ID;
            $order = (config('database.default')==='oracle') ? "ORDER_" : "ORDER";
            $minus = (config('database.default')==='oracle') ? "MINUS_" : "MINUS";
			$result = AllocRunner::where('JOB_ID','=',$job_id)->select('ID','CODE','NAME',"$order",'ALLOC_TYPE','THEOR_PHASE','THEOR_VALUE_TYPE','LAST_RUN','FROM_OPTION','TO_OPTION','BEGIN_DATE','END_DATE')->get()->toArray();
			if (count($result) > 0) {
                foreach ($result as $row) {
                    $old_runner_id=$row['ID'];
//                    $allocRunner = AllocRunner::where(['ID'=>$old_runner_id])->get(['JOB_ID', 'ORDER'.($this->isReservedName?'_':''), 'ALLOC_TYPE', 'THEOR_PHASE']);
//                    $allocRunner->JOB_ID = $new_job_id;
//
//                    $allocRunner = json_decode(json_encode($allocRunner), true);
//                    $condition = array (
//                            'ID' => -1
//                    );
//                    $tmp = AllocRunner::updateOrCreate ( $condition, $allocRunner );
//                    $new_runner_id = $tmp->ID;
//
//                    $alloc_runner_objects = AllocRunnerObjects::where(['RUNNER_ID'=>$old_runner_id])
//                        ->select(DB::raw($new_runner_id.' AS RUNNER_ID') , 'OBJECT_TYPE', 'OBJECT_ID', 'DIRECTION', 'FIXED', 'MINUS'.($this->isReservedName?'_':''))->get()->toArray();
//                    $condition = array (
//                            'ID' => -1
//                    );
//                    $tmp = AllocRunner::updateOrCreate ( $condition, $alloc_runner_objects );
                    $condition = array (
                        'ID' => null
                    );

                    unset($row['ID']);
                    $row['JOB_ID'] = $new_job_id;
                    $alloc_runner = (config('database.default')==='oracle') ? AllocRunner::updateOrCreate ( $condition, $row ) : AllocRunner::firstOrCreate($row);
                    $alloc_runner_id = $alloc_runner->ID;
                    $alloc_runner_objects = AllocRunnerObjects::where('RUNNER_ID','=',$old_runner_id)->select('OBJECT_TYPE','OBJECT_ID','DIRECTION','FIXED',"$minus")->get()->toArray();
                    foreach ($alloc_runner_objects as $value){
                        $value['RUNNER_ID'] = $alloc_runner_id;
                        //AllocRunnerObjects::firstOrCreate($value);
                        (config('database.default')==='oracle') ? AllocRunnerObjects::updateOrCreate ( $condition, $value ) : AllocRunnerObjects::firstOrCreate($value);
                    }
                }
            }
		}
		else
		{
			$param = [
				'NAME'=>$job_name,
				'VALUE_TYPE'=>$value_type,
				'ALLOC_GAS'=>$gas,
				'ALLOC_OIL'=>$oil,
				'ALLOC_WATER'=>$water,
				'ALLOC_COMP'=>$comp,
				'ALLOC_GASLIFT'=>$gaslift,
				'ALLOC_CONDENSATE'=>$condensate,
				'DAY_BY_DAY'=>$daybyday,
				'BEGIN_DATE'=>$begin_date,
				'END_DATE'=>$end_date
			];

			AllocJob::where(['ID'=>$job_id])->update($param);
		}
		
		return response ()->json ('ok');
	}
	
	public function saveEditRunner(Request $request) {
		$data = $request->all ();
		
		$runner_id = $data ['runner_id'];
		$runner_name = $data ['runner_name'];
		if (! $runner_name)
			$runner_name = "R$runner_id";
		$obj_froms = explode ( ',', $data ['obj_from'] );
		$obj_tos = explode ( ',', $data ['obj_to'] );
		$order = $data ['order'];
		$fifo = $data ['fifo'];
		$baId 	= $data ['BA_ID'];
		$baId 	= $baId?$baId:null;
		$alloc_type = $data ['alloc_type'];
		$theor_value_type = $data ['theor_value_type'];
		$theor_phase = $data ['theor_phase'];
		$from_option = $data ['from_option'];
		$to_option = $data ['to_option'];
		$begin_date = ($data['begin_date'] == null || $data['begin_date'] == "") ? null : \Helper::parseDate($data['begin_date']);
		$end_date = ($data['end_date'] == null || $data['end_date'] == "") ? null : \Helper::parseDate($data['end_date']);

		$use_excel = $data ['use_excel'];
		$run_sql = $data ['run_sql'];
		$fileName = $data ['file_name'];
		$file = $data ['file'];
		if($file){
			$fileName = /*date('YmdHis').'-'.*/$file->getClientOriginalName ();
			$file = $file->move ( storage_path() . '/alloc_template/', $fileName );
		}
		//Update order
		//AllocRunner::where(['ID'=>$runner_id])->update(['NAME'=>$runner_name, 'ORDER'.($this->isReservedName?'_':'')=>$order, 'ALLOC_TYPE'=>$alloc_type, 'THEOR_VALUE_TYPE'=>$theor_value_type, 'THEOR_PHASE'=>$theor_phase, 'FROM_OPTION'=>$from_option, 'TO_OPTION'=>$to_option, 'BEGIN_DATE'=>$begin_date, 'END_DATE'=>$end_date]);
		$param = [
            'NAME'=>$runner_name,
            'ORDER'.($this->isReservedName?'_':'')=>$order,
			'FIFO'=>$fifo,
            'ALLOC_TYPE'=>$alloc_type,
            'THEOR_VALUE_TYPE'=>($theor_value_type>0?$theor_value_type:null),
            'THEOR_PHASE'=>($theor_phase>0?$theor_phase:null),
            'FROM_OPTION'=>($from_option>0?$from_option:null),
            'TO_OPTION'=>$to_option,
            'BEGIN_DATE'=>$begin_date,
            'END_DATE'=>$end_date,
			'BA_ID'=>$baId,
			'EXCEL_TEMPLATE'=>$fileName,
			'USE_EXCEL' => $use_excel,
			'RUN_SQL' => $run_sql,
        ];
        $condition = array (
            'ID' => $runner_id
        );
        AllocRunner::updateOrCreate ( $condition, $param );
		
		//Delete all Object in runner
		AllocRunnerObjects::where(['RUNNER_ID'=>$runner_id])->delete();
		
		//Add again

        foreach ($obj_froms as $obj_from) {
            $xs_f = explode(':', $obj_from);
            if ($obj_from != '') {
                if ($xs_f[1] == "") continue;
                $p = [
                    'RUNNER_ID' => $runner_id,
                    'OBJECT_TYPE' => $xs_f[1],
                    'OBJECT_ID' => $xs_f[0],
                    'DIRECTION' => 1,
                    'MINUS' . ($this->isReservedName ? '_' : '') => $xs_f[2]
                ];
                //AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs_f[1], 'OBJECT_ID'=>$xs_f[0], 'DIRECTION'=>1, 'MINUS'.($this->isReservedName?'_':'')=>$xs_f[2]]);
                AllocRunnerObjects::firstOrCreate($p);
            }
        }

        foreach($obj_tos as $obj_to)
        {
            $xs_t=explode(':',$obj_to);
            if ($obj_to != '') {
                if ($xs_t[1] == "") continue;
                //AllocRunnerObjects::insert(['RUNNER_ID'=>$runner_id, 'OBJECT_TYPE'=>$xs_t[1], 'OBJECT_ID'=>$xs_t[0], 'DIRECTION'=>0, 'FIXED'=>$xs_t[2]]);
                $v = [
                    'RUNNER_ID' => $runner_id,
                    'OBJECT_TYPE' => $xs_t[1],
                    'OBJECT_ID' => $xs_t[0],
                    'DIRECTION' => 0,
                    'FIXED' => $xs_t[2]
                ];
                AllocRunnerObjects::firstOrCreate($v);
            }
        }
		return response ()->json ('ok');
	}
}