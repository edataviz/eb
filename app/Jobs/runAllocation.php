<?php
namespace App\Jobs;

define("OBJ_TYPE_FLOW", 1);
define("OBJ_TYPE_EU", 2);
define("OBJ_TYPE_TANK", 3);
define("OBJ_TYPE_STORAGE", 4);

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TmWorkflowTask,
	App\Models\AllocJob,
	App\Models\AllocRunner,
	App\Models\WellComp,
	App\Models\WellCompDataAlloc,
	App\Models\WellCompInterval,
	App\Models\WellCompIntervalDataAlloc,
	App\Models\WellCompIntervalPerf,
	App\Models\WellCompIntervalPerfDataAlloc,
	App\Models\Flow,
	App\Models\FlowDataAlloc,
	App\Models\FlowDataFifoAlloc,
	App\Models\FlowCoEntDataAlloc,
	App\Models\FlowCoEntDataFifoAlloc,
	App\Models\FlowCompDataAlloc,
	App\Models\Tank,
	App\Models\TankDataAlloc,
	App\Models\TankDataFifoAlloc,
	App\Models\TankCoEntDataAlloc,
	App\Models\TankCoEntDataFifoAlloc,
	App\Models\Storage,
	App\Models\StorageDataAlloc,
	App\Models\StorageDataFifoAlloc,
	App\Models\StorageCoEntDataAlloc,
	App\Models\StorageCoEntDataFifoAlloc,
	App\Models\EnergyUnit,
	App\Models\EnergyUnitDataAlloc,
	App\Models\EnergyUnitCoEntDataAlloc,
	App\Models\EnergyUnitDataFifoAlloc,
	App\Models\EnergyUnitCoEntDataFifoAlloc,
	App\Models\EnergyUnitCompDataAlloc,
	App\Models\QltyProductElementType,
	App\Models\AllocRunnerObjects;
use  DB, Carbon\Carbon, Mail, Excel;
use App\Http\Controllers\WorkflowProcessController;
use Illuminate\Support\Facades\Auth;

class runAllocation extends Job implements ShouldQueue, SelfHandling
{
	use InteractsWithQueue, SerializesModels;
    protected $param=[], $log, $error_count = 0, $alloc_act = "";
	protected $object_table_name = [OBJ_TYPE_FLOW => "FLOW", OBJ_TYPE_EU => "ENERGY_UNIT", OBJ_TYPE_TANK => "TANK", OBJ_TYPE_STORAGE => "STORAGE"];
	protected $data_field_prefix = [OBJ_TYPE_FLOW => "FL", OBJ_TYPE_EU => "EU", OBJ_TYPE_TANK => "TANK", OBJ_TYPE_STORAGE => "STORAGE"];
	protected $id_fields = [OBJ_TYPE_FLOW => "FLOW_ID", OBJ_TYPE_EU => "EU_ID", OBJ_TYPE_TANK => "TANK_ID", OBJ_TYPE_STORAGE => "STORAGE_ID"];
	protected $compositions = ['C1','C2','C3','C4I','C4N','C5I','C5N','C6','C7','CO2','H2S','N2'];
	protected $isOracle = false;
	protected $dbType = '';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
		$this->dbType = config('database.default');
		$this->isOracle = ($this->dbType==='oracle');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		\Helper::setGetterUpperCase();
		//All dates in 'Y-m-d' format
		//\Log::info ($this->param);
		$task_id = 0;
		$runner_id = 0;
		$job_id = isset($this->param['job_id'])?$this->param['job_id']:0;
    	$this->alloc_act = $this->param['act'];
		if(isset($this->param['taskid'])){
			$task_id = $this->param['taskid'];
			$date_type = $this->param['type'];
			$from_date = $this->param['from_date'];
			$to_date = $this->param['to_date'];
			$email = $this->param['email'];
			if($date_type == "day"){
				$date = date('Y-m-d');
				$from_date = date('Y-m-d', strtotime($date .' -1 day'))."";
				$to_date = $from_date;
			}
			else if($date_type == "month"){
				$date = date('Y-m-d');
				$from_date = date('Y-m-01', strtotime($date .' -1 month'))."";
				$to_date = $from_date;
			}
		}
		else{
			$runner_id = isset($this->param['runner_id'])?$this->param['runner_id']:0;
			$from_date = $this->param['from_date']; //Carbon::createFromFormat('m/d/Y',$this->param['from_date'])->format('Y-m-d');
			$to_date = $this->param['to_date']; //Carbon::createFromFormat('m/d/Y',$this->param['to_date'])->format('Y-m-d');
		}

    	if(!($job_id>0) && !($runner_id>0)){
    		$this->_log("Unknown job or runner to run allocation",1);
    		if($task_id>0){
    			$this->finalizeTask($task_id,3,$this->log,$email);
    		}
    		exit();
    	}

    	//\Log::info ("date: $from_date, $to_date");
    	if ($runner_id > 0) {
    		$tmp = DB::table ( 'alloc_runner'.($this->isOracle?'':' AS').' b' )->join ( 'alloc_job'.($this->isOracle?'':' AS').' a', 'a.id', '=', 'b.job_id' )->where ( [
    				'b.id' => $runner_id
    		] )->select ( 'a.DAY_BY_DAY' )->first ();

    		$daybyday = $tmp->DAY_BY_DAY;
    		if ($daybyday == 1) {
    			//$ds = explode ( "-", $from_date );
    			//$d1 = "$ds[2]-$ds[0]-$ds[1]";
    			//$ds = explode ( "-", $to_date );
    			//$d2 = "$ds[2]-$ds[0]-$ds[1]";
				$d1 = date ( "Y-m-d", strtotime ( $from_date ) );
				$d2 = strtotime ( $to_date );
    			while ( strtotime ( $d1 ) <= $d2 ) {
    				$this->exec_runner ( $runner_id, $d1, $d1 );
    				$d1 = date ( "Y-m-d", strtotime ( "+1 day", strtotime ( $d1 ) ) );
    			}
    		} else {
    			$this->exec_runner ( $runner_id, $from_date, $to_date );
    		}
    	} else if ($job_id > 0) {
    		$tmp = AllocJob::where ( [
    				'ID' => $job_id
    		] )->select ( 'DAY_BY_DAY' )->first ();
    		$daybyday = $tmp['DAY_BY_DAY'];
    		if ($daybyday == 1) {
				$d1 = date ( "Y-m-d", strtotime ( $from_date ) );
				$d2 = strtotime ( $to_date );
    			while ( strtotime ( $d1 ) <= $d2 ) {
    				$this->exec_job ( $job_id, $d1, $d1 );
    				$d1 = date ( "Y-m-d", strtotime ( "+1 day", strtotime ( $d1 ) ) );
    			}
    		} else {
    			$this->exec_job ( $job_id, $from_date, $to_date );
    		}
    	}

    	$this->_log("Finish.".($this->error_count>0?" <font color='red'>($this->error_count error)</font>":" (no error)"),2);

		if($this->log && $this->error_count>0 && isset($email)){
				$emails			= explode( ';',$email);
				foreach ($emails as $index=>$aEmail){
					if (!filter_var($aEmail, FILTER_VALIDATE_EMAIL)) unset ($emails[$index]);
				}
				if (count($emails)>0) {
					$subjectName = ($this->error_count>0?"[ERROR] ":"")."Automatic allocation task's log";
					$data = ['content' => strip_tags($this->log)];
					try{
						$ret=\Helper::sendEmail($emails,$subjectName,$data);
					}catch (\Exception $e){
						\Log::error($e->getMessage());
					}
				}
		}
    	if($task_id > 0){
    		$this->finalizeTask($task_id,($this->error_count>0?3:1),$this->log,$email);
    	}
		if($this->alloc_act == "run")
		{
			//save log file
    		//\Log::info($this->log);
			$logFolder = 'C:/allocation_logs';
			if (!file_exists($logFolder)) {
				mkdir($logFolder, 0777);
			}
			if (file_exists($logFolder)) {
				$myfile = file_put_contents("$logFolder/{$this->alloc_act}_allocation_".date('Y-m-d-H-i-s.').rand(100,999).'.html', $this->log.PHP_EOL , FILE_APPEND | LOCK_EX);
			}
			//remove too old files
			$lfs = scandir($logFolder,1);
			for($i=100;$i<count($lfs);$i++){
				if(strlen($lfs[$i])>10)
					unlink($logFolder.'/'.$lfs[$i]);
			}

			//save log to database
			//$results = DB::select('select max(a.runner_name) runner_name, max(a.job_name) job_name from (select a.name runner_name, null job_name from alloc_runner a where a.id=? union all select null runner_name, a.name job_name from alloc_job a where a.id=?) a', [$runner_id, $job_id]);
			$results = DB::select('select a.name runner_name, b.name job_name from alloc_runner a, alloc_job b where a.id = ? and a.job_id=b.id', [$runner_id]);
			DB::table('allocation_log')->insert([
				'runner_id' => $runner_id>0?$runner_id:null,
				'job_id' => $job_id>0?$job_id:null,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'runner_name' => isset($results[0]) ? $results[0]->runner_name : null,
				'job_name' => isset($results[0]) ? $results[0]->job_name : null,
				'action' => $this->alloc_act,
				'run_by' => Auth::user()->username,
				'run_time' => DB::raw('now()'),
				'logs' => $this->log,
			]);
    	}
		return $this->log;
    }

    private function allocWellCompletion($eu_id,$date,$phase_type,$event_type,$alloc_attr,$value)
    {
    	$F="";
    	if($phase_type==1) $F="OIL_RATE";
    	else if($phase_type==2) $F="GAS_RATE";
    	else if($phase_type==3) $F="WATER_RATE";
    	else return;

    	$result = WellComp::where(['EU_ID'=>$eu_id])->whereDate('EFFECTIVE_DATE', '<=', $date)->get();

    	$total_fixed = 0;
    	foreach ($result as $row){
    		if($row->$F){
    			$v=$row->$F*$value;
    			$comp_id=$row->ID;

    			$ro = WellCompDataAlloc::where(['COMP_ID'=>$comp_id, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type])
    			->whereDate('OCCUR_DATE', '=', $date)
    			->select('ID')->first();

    			if($ro){
    				if($this->alloc_act == "run"){
    					WellCompDataAlloc::where(['ID'=>$ro->ID])->update([$alloc_attr=>$v]);
    				}
    				$sSQL="update well_comp_data_alloc set $alloc_attr=$v where ID=$ro[ID]";

    			}else{
    				if($this->alloc_act == "run"){
    					WellCompDataAlloc::insert(['COMP_ID'=>$comp_id, 'OCCUR_DATE'=>$date, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type, $alloc_attr=>$v]);
    				}
    				$sSQL="insert into well_comp_data_alloc(COMP_ID,OCCUR_DATE,FLOW_PHASE,EVENT_TYPE,$alloc_attr) values('$comp_id','$date',$phase_type,$event_type,$v)";
    			}
    			$this->_log($sSQL,2);

    			$this->allocWellInterval($comp_id,$date,$phase_type,$event_type,$alloc_attr,$v);
    		}
    	}
    }

    private function allocWellInterval($comp_id,$date,$phase_type,$event_type,$alloc_attr,$value)
    {
    	$F="";
    	if($phase_type==1) $F="OIL_RATE";
    	else if($phase_type==2) $F="GAS_RATE";
    	else if($phase_type==3) $F="WATER_RATE";
    	else return;

    	$result = WellCompInterval::where(['COMP_ID'=>$comp_id])->whereDate('EFFECTIVE_DATE', '<=', $date)->get();
    	$total_fixed = 0;
    	foreach ($result as $row){
    		if($row->$F){
    			$v=$row->$F*$value;
    			$interval_id=$row->ID;

    			$ro = WellCompIntervalDataAlloc::where(['COMP_INTERVAL_ID'=>$interval_id, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type])
    			->whereDate('OCCUR_DATE', '=', $date)
    			->select('ID')->first();

    			if($ro){
    				if($this->alloc_act == "run"){
    					WellCompIntervalDataAlloc::where(['ID'=>$ro->ID])->update([$alloc_attr=>$v]);
    				}
    				$sSQL="update well_comp_interval_data_alloc set $alloc_attr=$v where ID=$ro[ID]";
    			}else{
    				if($this->alloc_act == "run"){
    					WellCompIntervalDataAlloc::insert(['COMP_INTERVAL_ID'=>$interval_id, 'OCCUR_DATE'=>$date, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type, $alloc_attr=>$v]);
    				}
    				$sSQL="insert into well_comp_interval_data_alloc(COMP_INTERVAL_ID,OCCUR_DATE,FLOW_PHASE,EVENT_TYPE,$alloc_attr) values('$interval_id','$date',$phase_type,$event_type,$v)";
    			}
    			$this->_log($sSQL,2);

    			$this->allocWellPerforation($interval_id,$date,$phase_type,$event_type,$alloc_attr,$v);
    		}
    	}
    }

    private function allocWellPerforation($interval_id,$date,$phase_type,$event_type,$alloc_attr,$value)
    {
    	$F="";
    	if($phase_type==1) $F="OIL_RATE";
    	else if($phase_type==2) $F="GAS_RATE";
    	else if($phase_type==3) $F="WATER_RATE";
    	else return;

    	$result = WellCompIntervalPerf::where(['COMP_INTERVAL_ID'=>$interval_id])->whereDate('EFFECTIVE_DATE', '<=', $date)->get();
    	$total_fixed = 0;
    	foreach ($result as $row){
    		if($row->$F)
    		{
    			$v=$row->$F*$value;
    			$perf_id=$row->ID;

    			$ro = WellCompIntervalPerfDataAlloc::where(['COMP_INTERVAL_PERF_ID'=>$perf_id, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type])
    			->whereDate('OCCUR_DATE', '=', $date)
    			->select('ID')->first();

    			if($ro){
    				if($this->alloc_act == "run"){
    					WellCompIntervalPerfDataAlloc::where(['ID'=>$ro->ID])->update([$alloc_attr=>$v]);
    				}
    				$sSQL="update WELL_COMP_INT_PERF_DATA_ALLOC set $alloc_attr=$v where ID=$ro[ID]";

    			}else{
    				if($this->alloc_act == "run"){
    					WellCompIntervalPerfDataAlloc::insert(['COMP_INTERVAL_PERF_ID'=>$perf_id, 'OCCUR_DATE'=>$date, 'FLOW_PHASE'=>$phase_type, 'EVENT_TYPE'=>$event_type, $alloc_attr=>$v]);
    				}
    				$sSQL="insert into WELL_COMP_INT_PERF_DATA_ALLOC(COMP_INTERVAL_PERF_ID,OCCUR_DATE,FLOW_PHASE,EVENT_TYPE,$alloc_attr) values('$perf_id','$date',$phase_type,$event_type,$v)";
    			}
    			$this->_log($sSQL,2);
    		}
    	}
    }

    private function getQualityGas($object_id,$object_type_code,$occur_date,$F)
    {
    	// Find composition %Mol
    	$field = ($F == "MASS" ? "MASS_FRACTION" : "MOLE_FACTION");

    	//\DB::enableQueryLog ();
    	$row = DB::table ( 'qlty_data'.($this->isOracle?'':' AS').' a' )->join ( 'code_qlty_src_type'.($this->isOracle?'':' AS').' b', 'a.SRC_TYPE', '=', 'b.ID' )->where ( [
    			'a.SRC_ID' => $object_id,
    			'b.CODE' => $object_type_code
    	] )->whereDate ( 'a.EFFECTIVE_DATE', '<=', $occur_date )->orderBy ( 'a.EFFECTIVE_DATE', 'DESC' )->SELECT ( 'a.ID' )->first ();
    	//\Log::info ( \DB::getQueryLog () );

    	if ( $row ) {
    		$data = [ ];
			$qlty_data_id = $row->ID;
			$rows = DB::table ( 'qlty_data_detail'.($this->isOracle?'':' AS').' a' )->join ( 'qlty_product_element_type'.($this->isOracle?'':' AS').' b', 'a.ELEMENT_TYPE', '=', 'b.ID' )->where ( [
    				'a.QLTY_DATA_ID' => $qlty_data_id,
    				'b.SAMPLE_TYPE' => 2
    		] )->select ( DB::raw("max(a.$field) as ELEMENT_VALUE"), 'a.ELEMENT_TYPE' )->groupBy('a.ELEMENT_TYPE')->get ();
			foreach($rows as $row){
				$data[$row->ELEMENT_TYPE] = $row->ELEMENT_VALUE;
			}
    		return $data;
    	}
    	return null;
    }

	private function joinDataTable(&$query, $subfix, $alias, $obj_type, $flow_phase, $event_type){
		switch($obj_type){
			case OBJ_TYPE_FLOW:
				$join_func = function($join) use ($alias){
					$join->on ( "$alias.FLOW_ID", '=', 'o.ID' );
					$join->on ( "$alias.OCCUR_DATE", '=', 'td.DB_DATE' );
					if($alias=='f')
						$join->on ( "$alias.OUTFLOW_DATE", '=', 'td.DB_DATE' );						
				};
				break;
			case OBJ_TYPE_EU:
				$join_func = function($join) use ($alias, $flow_phase, $event_type){
					$join->on ( "$alias.EU_ID", '=', 'o.ID' );
					$join->on ( "$alias.OCCUR_DATE", '=', 'td.DB_DATE' );
					$join->where ( "$alias.FLOW_PHASE", '=', "$flow_phase" );
					$join->where ( "$alias.EVENT_TYPE", '=', "$event_type" );
					if($alias=='f')
						$join->on ( "$alias.OUTFLOW_DATE", '=', 'td.DB_DATE' );						
				};
				break;
			case OBJ_TYPE_TANK:
				$join_func = function($join) use ($alias){
					$join->on ( "$alias.TANK_ID", '=', 'o.ID' );
					$join->on ( "$alias.OCCUR_DATE", '=', 'td.DB_DATE' );
					if($alias=='f')
						$join->on ( "$alias.OUTFLOW_DATE", '=', 'td.DB_DATE' );						
				};
				break;
			case OBJ_TYPE_STORAGE:
				$join_func = function($join) use ($alias){
					$join->on ( "$alias.STORAGE_ID", '=', 'o.ID' );
					$join->on ( "$alias.OCCUR_DATE", '=', 'td.DB_DATE' );
					if($alias=='f')
						$join->on ( "$alias.OUTFLOW_DATE", '=', 'td.DB_DATE' );						
				};
				break;
		}
		$query->leftjoin ( $this->object_table_name[$obj_type]."_DATA_{$subfix}".($this->isOracle?'':' AS')." $alias", $join_func);
	}
	private function funcIfNull(){
		if($this->dbType == 'oracle')
			return 'NVL';
		else if($this->dbType == 'sqlsrv')
			return 'ISNULL';
		else
			return 'IFNULL';
	}
	private function buildFromData($ids_from, $ids_minus, $from_date, $to_date, $alloc_phase, $event_type, $alloc_attr, $alloc_attr_eu, $from_option){

		if(!$from_option)
			$from_option = 0;
		$sum_all = null;
		$alloc_comp_query = [];
		//\DB::enableQueryLog ();
		foreach($ids_from as $obj_type_from => $arrfrom){
			$ids_minus_str = "-999";
			if(array_key_exists($obj_type_from,$ids_minus)){
				foreach($ids_minus[$obj_type_from] as $obj_id_minus){
					$ids_minus_str .= ",$obj_id_minus";
				}
			}
			$id_minus_check = "(case when o.ID in ($ids_minus_str) then -1 else 1 end)";
			$DATA = ($obj_type_from == OBJ_TYPE_TANK || $obj_type_from == OBJ_TYPE_STORAGE?"_":"_DATA_");
			$object_table = $this->object_table_name[$obj_type_from];
			$object_prefix = $this->data_field_prefix[$obj_type_from];
			$query = DB::table ( 'TIME_DIMENSION'.($this->isOracle?'':' AS').' td' )->join ( "$object_table".($this->isOracle?'':' AS')." o", function($join) use ($arrfrom) {$join->whereIn ( 'o.ID', $arrfrom );} );
			switch ($from_option){
				case 0: //default: alloc >> std >> theor
					$this->joinDataTable($query, $subfix = "ALLOC", $alias = "a", $obj_type_from, $alloc_phase, $event_type);
					$this->joinDataTable($query, $subfix = "VALUE", $alias = "v", $obj_type_from, $alloc_phase, $event_type);
					$this->joinDataTable($query, $subfix = "THEOR", $alias = "t", $obj_type_from, $alloc_phase, $event_type);
					$value_empty_check = $this->funcIfNull()."(a.{$object_prefix}{$DATA}{$alloc_attr}, ".$this->funcIfNull()."(v.{$object_prefix}{$DATA}{$alloc_attr}, t.{$object_prefix}{$DATA}{$alloc_attr}))";
					$hrs_empty_check = "case when a.{$object_prefix}{$DATA}{$alloc_attr} is null then case when v.{$object_prefix}{$DATA}{$alloc_attr} is null then t.ACTIVE_HRS else v.ACTIVE_HRS end else a.ACTIVE_HRS end";
					break;
				case 1: //std >> theor
					$this->joinDataTable($query, $subfix = "VALUE", $alias = "v", $obj_type_from, $alloc_phase, $event_type);
					$this->joinDataTable($query, $subfix = "THEOR", $alias = "t", $obj_type_from, $alloc_phase, $event_type);
					$value_empty_check = $this->funcIfNull()."(v.{$object_prefix}{$DATA}{$alloc_attr}, t.{$object_prefix}{$DATA}{$alloc_attr})";
					$hrs_empty_check = "case when v.{$object_prefix}{$DATA}{$alloc_attr} is null then t.ACTIVE_HRS else v.ACTIVE_HRS end";
					break;
				case 2: //std
					$this->joinDataTable($query, $subfix = "VALUE", $alias = "v", $obj_type_from, $alloc_phase, $event_type);
					$value_empty_check = "v.{$object_prefix}{$DATA}{$alloc_attr}";
					$hrs_empty_check = "v.ACTIVE_HRS";
					break;
				case 3: //theor
					$this->joinDataTable($query, $subfix = "THEOR", $alias = "t", $obj_type_from, $alloc_phase, $event_type);
					$value_empty_check = "t.{$object_prefix}{$DATA}{$alloc_attr}";
					$hrs_empty_check = "t.ACTIVE_HRS";
					break;
				case 4: //alloc
					$this->joinDataTable($query, $subfix = "ALLOC", $alias = "a", $obj_type_from, $alloc_phase, $event_type);
					$value_empty_check = "a.{$object_prefix}{$DATA}{$alloc_attr}";
					$hrs_empty_check = "a.ACTIVE_HRS";
					break;
				case 5: //FIFO Outflow >> alloc >> std >> theor
					$this->joinDataTable($query, $subfix = "FIFO_ALLOC", $alias = "f", $obj_type_from, $alloc_phase, $event_type);
					$this->joinDataTable($query, $subfix = "ALLOC", $alias = "a", $obj_type_from, $alloc_phase, $event_type);
					$this->joinDataTable($query, $subfix = "VALUE", $alias = "v", $obj_type_from, $alloc_phase, $event_type);
					$this->joinDataTable($query, $subfix = "THEOR", $alias = "t", $obj_type_from, $alloc_phase, $event_type);
					$value_empty_check = $this->funcIfNull()."(f.{$object_prefix}_DATA_".str_replace('_','_OUTFLOW_',$alloc_attr).", ".$this->funcIfNull()."(a.{$object_prefix}{$DATA}{$alloc_attr}, ".$this->funcIfNull()."(v.{$object_prefix}{$DATA}{$alloc_attr}, t.{$object_prefix}{$DATA}{$alloc_attr})))";
					$hrs_empty_check = "case when a.{$object_prefix}{$DATA}{$alloc_attr} is null then case when v.{$object_prefix}{$DATA}{$alloc_attr} is null then t.ACTIVE_HRS else v.ACTIVE_HRS end else a.ACTIVE_HRS end";
					break;
			}
			if($value_empty_check){
				$query->whereDate ( 'td.DB_DATE', '>=', $from_date )->whereDate ( 'td.DB_DATE', '<=', $to_date );
				$sum = $query->SELECT ( DB::raw ( "sum($id_minus_check*$value_empty_check) AS TOTAL_FROM" ) )->get ();
				//\Log::info ( \DB::getQueryLog () );
				$alloc_comp_from = $query->SELECT ( DB::raw ( "$value_empty_check AS ALLOC_VALUE"), 'o.ID AS OBJECT_ID', 'o.NAME AS OBJECT_NAME', DB::raw ( "$hrs_empty_check AS ACTIVE_HRS"), 'td.DB_DATE as OCCUR_DATE' );
				$sum_all += $sum [0]->TOTAL_FROM;
				$alloc_comp_query[$obj_type_from] = $alloc_comp_from;
			}
		}
		return ["sum" => $sum_all, "alloc_comp_query" => $alloc_comp_query];
	}
	
	function allocFifo($runner_id,$date,$valueFrom,$obj_type_to,$arrto,$alloc_attr,$co_ent,$baID){
		
		if(!($valueFrom>0)){
			return false;
		}

		$fifo_table = $this->object_table_name[$obj_type_to].($co_ent?'_CO_ENT':'').'_DATA_FIFO_ALLOC';
		$mdl = \Helper::getModelName($fifo_table);
		$this->_log ( "=== $fifo_table ===", 2 );

		$input_field = $this->data_field_prefix[$obj_type_to].'_DATA_' . str_replace('_','_INFLOW_',$alloc_attr);
		$output_field = $this->data_field_prefix[$obj_type_to].'_DATA_' . str_replace('_','_OUTFLOW_',$alloc_attr);
		$begin_field = $this->data_field_prefix[$obj_type_to].'_DATA_' . str_replace('_','_BEGIN_',$alloc_attr);
		$end_field = $this->data_field_prefix[$obj_type_to].'_DATA_' . str_replace('_','_END_',$alloc_attr);
		
		//remove fifo alloc data
		if ($this->alloc_act == "run"){
			$mdl::whereIn ($this->id_fields[$obj_type_to],$arrto)->where($output_field,'>',0)->where('RUNNER_ID','=',$runner_id)->whereDate ( 'OUTFLOW_DATE', '=', $date )->update ( [
					'OUTPUT_DATE' => null,
					'OUTFLOW_DATE' => null,
					$output_field => 0,
					$end_field => DB::raw("$begin_field+$input_field")
			] );
			$mdl::whereIn ($this->id_fields[$obj_type_to],$arrto)->where($begin_field,'>',0)->where($output_field,'=',0)->where('RUNNER_ID','=',$runner_id)->whereDate ( 'OUTFLOW_DATE', '=', $date )->delete();
		}

		$query = DB::table ( $fifo_table )->whereIn ( $this->id_fields[$obj_type_to], $arrto )->whereNull('OUTPUT_DATE');
		if($co_ent && $baID>0)
			$query = $query->where('BA_ID','=',$baID);
		$sum = $query->select ( DB::raw ( "sum($end_field)".($this->isOracle?'':' AS').' TOTAL_TO' ) )->first ();
		if(!($sum->TOTAL_TO>=$valueFrom)){
			if($sum->TOTAL_TO){
				if($sum->TOTAL_TO+0.000001<$valueFrom){
					$this->_log ( "Not enough quantity (".round($sum->TOTAL_TO,6).")", 1 );
					return false;
				}
			} else{
				$this->_log ( "No data to FIFO allocate", 2 );
				return false;
			}
		}
		
		$query = DB::table ( $fifo_table.($this->isOracle?'':' AS').' a')
			->join ( $this->object_table_name[$obj_type_to].($this->isOracle?'':' AS').' b', 'a.'.$this->id_fields[$obj_type_to], '=', 'b.ID' );
		if($co_ent)
			$query = $query
			->join ( 'COST_INT_CTR'.($this->isOracle?'':' AS').' c', 'a.COST_INT_CTR_ID', '=', 'c.ID' )
			->join ( 'BA_ADDRESS'.($this->isOracle?'':' AS').' d', 'a.BA_ID', '=', 'd.ID' );
		$query = $query->whereIn ( 'a.'.$this->id_fields[$obj_type_to], $arrto )->whereNull('a.OUTPUT_DATE');
		if($co_ent && $baID>0)
			$query = $query->where('a.BA_ID','=',$baID);
		$selects = [
				'a.ID',
				'a.'.$this->id_fields[$obj_type_to].' AS OBJECT_ID',
				'b.NAME AS OBJECT_NAME',
				'a.OCCUR_DATE',
				'a.INFLOW_DATE',
				'a.INPUT_DATE',
				"a.$end_field"
		];	
			
		if($co_ent)
			array_push($selects,
				'a.COST_INT_CTR_ID',
				'a.BA_ID',
				'c.NAME AS COST_INT_CTR_NAME',
				'd.NAME AS BA_NAME'
			);
			
		if($obj_type_to == OBJ_TYPE_EU)
			array_push($selects,
				'a.ALLOC_TYPE',
				'a.FLOW_PHASE',
				'a.EVENT_TYPE'
			);
		$query = $query->select($selects)->orderBy('a.INFLOW_DATE')->orderBy('b.NAME');
		if($co_ent)
			$query = $query->orderBy('c.NAME')->orderBy('d.NAME');
		$rows = $query->get ();
		$lastDate="";
		$sum=0;
		$fifos=[];
		$ids=[];
		$quantity = $valueFrom;
		$i=0;
		$step=0;
		$count = count($rows);
		foreach($rows as $row){
			$i++;
			if($i>=$count){
				$lastDate = $row->INFLOW_DATE;
				$fifos[] = $row;
				$ids[]=$row->ID;
				$sum += $row->$end_field;
			}
			if(($row->INFLOW_DATE != $lastDate && $lastDate) || $i>=$count){
				$r = ($quantity>=$sum || abs($quantity-$sum)<=0.000001)?1:$quantity/$sum;
				$step++;
				$this->_log ( "[$step] Date $lastDate, allocate $sum &rArr; ".($quantity>=$sum?$sum:$quantity).", factor $r", 2 );
				$this->_log ( "<table>", 3 );
				if ($this->alloc_act == "run")
					$mdl::whereIn ('ID',$ids)->update ( [
							'OUTPUT_DATE' => Carbon::now(),
							'OUTFLOW_DATE' => $date,
							'RUNNER_ID' => $runner_id,
							$output_field => DB::raw ("$r*$end_field"),
							$end_field => ($r==1?0:DB::raw ("(1-$r)*$end_field"))
					] );
				if($quantity>=$sum || abs($quantity-$sum)<=0.000001){
					foreach($fifos as $fifo){
						$this->_log ( $co_ent?
						"<tr><td>--- $fifo->OBJECT_NAME</td><td>&rArr;</td><td>$fifo->BA_NAME:</td><td> {$fifo->$end_field}</td><td>&rArr; {$fifo->$end_field}</td></tr>":
						"<tr><td>--- $fifo->OBJECT_NAME:</td><td> {$fifo->$end_field}</td><td>&rArr; {$fifo->$end_field}</td></tr>",
						3 );
					}
				}
				else{
					foreach($fifos as $fifo){
						$output = $fifo->$end_field*$r;
						$end = $fifo->$end_field-$output;
						if ($this->alloc_act == "run"){
							$insert_arr = [
								$this->id_fields[$obj_type_to] => $fifo->OBJECT_ID,
								'OCCUR_DATE' => $fifo->OCCUR_DATE,
								'INFLOW_DATE' => $fifo->INFLOW_DATE,
								'INPUT_DATE' => $fifo->INPUT_DATE,
								//'OUTFLOW_DATE' => $date,
								'RUNNER_ID' => $runner_id,
								$input_field => 0,
								$output_field => 0,
								$begin_field => $end,
								$end_field => $end
							];
							if($co_ent){
								$insert_arr['COST_INT_CTR_ID'] = $fifo->COST_INT_CTR_ID;
								$insert_arr['BA_ID'] = $fifo->BA_ID;
							}
							if($obj_type_to == OBJ_TYPE_EU){
								$insert_arr['ALLOC_TYPE'] = $fifo->ALLOC_TYPE;
								$insert_arr['FLOW_PHASE'] = $fifo->FLOW_PHASE;
								$insert_arr['EVENT_TYPE'] = $fifo->EVENT_TYPE;
							}
							$mdl::insert ( $insert_arr );
						}
						$this->_log ( $co_ent?
							"<tr><td>--- $fifo->OBJECT_NAME</td><td>&rArr;</td><td>$fifo->BA_NAME:</td><td> {$fifo->$end_field}</td><td>&rArr; {$output}</td></tr>":
							"<tr><td>--- $fifo->OBJECT_NAME:</td><td> {$fifo->$end_field}</td><td>&rArr; $output</td></tr>",
							3 );
					}
				}
				$this->_log ( "</table>", 3 );
				if($quantity<=$sum || abs($quantity-$sum)<=0.000001)
					break;
				$quantity -= $sum;
				$sum=0;
				$fifos=[];
				$ids=[];
			}

			$lastDate = $row->INFLOW_DATE;
			$fifos[] = $row;
			$ids[]=$row->ID;
			$sum += $row->$end_field;
		}				

		return true;
	}

	function run_excel_allocation($runner, $from_date){
		$file = $runner->EXCEL_TEMPLATE;
		$path = storage_path() . '/alloc_template/' . $file;
		Excel::load($path, function($doc) use ($from_date) {
			$tableValueTypes =[
				'Standard' => 'VALUE',
				'Allocation' => 'ALLOC',
				'Theoretical' => 'THEOR',
				'FDC' => 'FDC_VALUE',
			];
			$tableObjectTypes=[
				'Flow' => 'FLOW',
				'Well' => 'ENERGY_UNIT',
				'Tank' => 'TANK',
				'Storage' => 'STORAGE',
			];
			$objIDFields = [
				'Flow' => 'FLOW_ID',
				'Well' => 'EU_ID',
				'Tank' => 'TANK_ID',
				'Storage' => 'STORAGE_ID',
			];
			$valueFieldsPrefix = [
				'Flow' => 'FL_DATA_',
				'Well' => 'EU_DATA_',
				'Tank' => 'TANK_',
				'Storage' => 'STORAGE_',
			];
			$valueFieldsSubfix = [
				'Gross Volume' => 'GRS_VOL', 
				'Net Volume' => 'NET_VOL', 
				'Gross Mass' => 'GRS_MASS', 
				'Net Mass' => 'NET_MASS', 
				'Gross Energy' => 'GRS_ENGY', 
				'Gross Power' => 'GRS_PWR', 
			];
			$allocTypes = [
				'Produce' => 1,
				'Inject' => 2,
			];
			$allocPhases = [
				'Oil' => 1,
				'Gas' => 2,
				'Water' => 3,
				'Condensate' => 5,
				'Gas lift' => 9,
			];
	
			$sheet = $doc->getSheetByName('Config');
			$sheet->setCellValue('B3', $from_date);

			$sheet = $doc->getSheetByName('Config');
			$highestRow = $sheet->getHighestRow();
			$rowIndex = 6;
			$runnerInfos = [];
			while(true){
				$row = $sheet->rangeToArray("A$rowIndex:D$rowIndex")[0];
				$runner = $row[0];
				if(!$runner) break;
				$runnerInfos[$runner] = [
					'allocType' => $allocTypes[$row[1]],
					'allocPhase' => $allocPhases[$row[2]],
					'theorValueType' => $row[3],
				];
				$rowIndex++;
			}

			$resultSheet = $doc->getSheetByName('Result');
			foreach(['Source','Target'] as $tab){
				$sheet = $doc->getSheetByName($tab);
				$highestRow = $sheet->getHighestRow();
				$rowIndex = 2;
				while(true){
					$row = $sheet->rangeToArray("A$rowIndex:F$rowIndex")[0];
					$runner = $row[0];
					if(!$runner) break;
	
					$objType = $row[1];
					$objName = $row[2];
					$objID = $row[3];
					$dataSource = $row[4];
					$valueType = $row[5];
					$tableName = $tableObjectTypes[$objType].'_DATA_'.$tableValueTypes[$dataSource];
					$q = DB::table($tableName.($this->isOracle?'':' AS').' a')
					//->join($tableObjectTypes[$objType].($this->isOracle?'':' AS').' b', "a.{$objIDFields[$objType]}", '=', 'b.ID' )
					//->where('b.ID',$objID)
					->where("a.{$objIDFields[$objType]}",$objID)
					->whereDate('a.OCCUR_DATE','=',$from_date);
					if($objType=='Well'){
						$q = $q->where('EVENT_TYPE',$runnerInfos[$runner]['allocType'])->where('FLOW_PHASE',$runnerInfos[$runner]['allocPhase']);
						if($valueType=='Allocation'){
							$q = $q->where('ALLOC_TYPE',$runnerInfos[$runner]['allocType']);
						}
					}
					$q = $q->select("{$valueFieldsPrefix[$objType]}{$valueFieldsSubfix[$valueType]} as V")->first();
					$sheet->setCellValue("G$rowIndex", $q?$q->V:0);
/*
					if($tab=='Target'){
						$sheet->setCellValue("H$rowIndex", "=F$rowIndex");
						$resultSheet->setCellValue("A$rowIndex", "=Target!A$rowIndex");
						$resultSheet->setCellValue("B$rowIndex", "=Target!B$rowIndex");
						$resultSheet->setCellValue("C$rowIndex", "=Target!C$rowIndex");
						$resultSheet->setCellValue("E$rowIndex", "=Target!H$rowIndex*VLOOKUP(A$rowIndex,Config!\$A\$6:\$G\$99,7,FALSE)");
					}
*/
					$rowIndex++;
				}
			}
			//$doc->setFilename('filename');
		})->store('xlsx', storage_path('alloc_result'));
		$path = storage_path() . '/alloc_result/' . $file;
		if(file_exists($path)){
			$this->_log ( "Excel allocation completed. <a target='_blank' href='/downloadAllocResultFile/$file'>Download</a>", 2 );
		}
	}
	
    private function run_runner($runner, $from_date, $to_date, $alloc_phase)
    {
		if($runner->USE_EXCEL=='Y'){
			return $this->run_excel_allocation($runner, $from_date);
		}

		$runner_id=$runner->RUNNER_ID;
		$pi = strpos($runner->RUNNER_NAME, '##');
		$proc = ($pi!==false?substr($runner->RUNNER_NAME,$pi+2):"");
		$alloc_comp=($runner->ALLOC_COMP == 1 || $runner->ALLOC_COMP == 'Y');
		$alloc_attr=$runner->ALLOC_ATTR_CODE;
		$alloc_type=$runner->ALLOC_TYPE;
		$theor_attr=$runner->THEOR_ATTR_CODE;
		$theor_phase=$runner->THEOR_PHASE;
		$from_option=$runner->FROM_OPTION;
		$to_option=$runner->TO_OPTION;
		$fifo=$runner->FIFO;

		$phase = \App\Models\CodeFlowPhase::where('ID','=',$alloc_phase)->first()->NAME;
    	$this->_log ( "From date: $from_date, to date: $to_date, alloc_attr: $alloc_attr, alloc_phase: $phase", 2 );
    	$xdate = date_create ( "2016-01-01" );
    	//$from_date = date ( 'Y-m-d', strtotime ( $from_date ) );
    	//$to_date = date ( 'Y-m-d', strtotime ( $to_date ) );
    	if (date_create ( $from_date ) < $xdate || date_create ( $to_date ) < $xdate) {
    		$ret = $this->_log ( "Can not run allocation for the date earlier than 01/01/2016.", 1 );
    		return false;
    	}

    	$success = true;
    	$event_type = 1;
    	if ($alloc_type == 2 || $alloc_type == 11) //alloc_type is INJECTION or GAS LIFT
    		$event_type = 2;

		if ($alloc_phase != 2)
			$alloc_comp = false;

		if (!$theor_phase)
			$theor_phase = $alloc_phase;
		else if ($theor_phase != $alloc_phase)
			$this->_log ( "Theor. phase changed to: $theor_phase", 2 );

		$F = "VOL";
		if (strpos ( $alloc_attr, 'MASS' ) !== false) {
			$F = "MASS";
		}
		$alloc_attr_eu = $alloc_attr;
		//if ($alloc_attr == "NET_VOL")
		//	$alloc_attr_eu = "GRS_VOL";
		if (!$theor_attr)
			$theor_attr = $alloc_attr;
		else if ($theor_attr != $alloc_attr)
			$this->_log ( "Theor. value type changed to: $theor_attr", 2 );

		$theor_attr_eu = $theor_attr;
		//if ($theor_attr == "NET_VOL")
		//	$theor_attr_eu = "GRS_VOL";

		$total_from = 0;
		$total_to = 0;
		$total_fixed = 0;

		$ids_from = [];
		$ids_to = [];
		$ids_fixed = [];
		$ids_minus = [];

		//$obj_type_from = [];
		//$obj_type_to = "";

		$rows = AllocRunnerObjects::where ( [
				'RUNNER_ID' => $runner_id
		] )->get ();

		foreach ( $rows as $row ) {
			if ($row->DIRECTION == 1) {
				$ids_from[$row->OBJECT_TYPE][] = $row->OBJECT_ID;
				if (($this->isOracle?$row->MINUS_:$row->MINUS) == 1)
					$ids_minus[$row->OBJECT_TYPE][] = $row->OBJECT_ID;
			} else {
				if ($row->FIXED == 1){
					$ids_fixed[$row->OBJECT_TYPE][] = $row->OBJECT_ID;
				}else{
					$ids_to[$row->OBJECT_TYPE][] = $row->OBJECT_ID;
				}
			}
		}

		if (count($ids_from)>0) {
			$ret = $this->buildFromData($ids_from, $ids_minus, $from_date, $to_date, $alloc_phase, $event_type, $alloc_attr, $alloc_attr_eu, $from_option);
			$total_from = $ret["sum"];
			$alloc_from_query_all = $ret["alloc_comp_query"];
			$this->_log ( "total_from: $total_from", 2 );
			if(!$total_from) $total_from= 0;
			
		} else {
			$ret = $this->_log ( "From objects not found", 1 );
			if ($ret === false)
				return false;
		}
		if($fifo=='Y'){
			$this->_log ( "Fifo allocation - date $from_date - quantity $total_from", 2 );
			if(!($total_from>0)){
				$this->_log ( "Not allocate for zero value", 1 );
			} else {
				$baID=$runner->BA_ID;
				foreach($ids_to as $obj_type_to => $arrto){
					$this->allocFifo($runner_id,$from_date,$total_from,$obj_type_to,$arrto,$alloc_attr,$co_ent=false,$baID);
					$this->allocFifo($runner_id,$from_date,$total_from,$obj_type_to,$arrto,$alloc_attr,$co_ent=true,$baID);
				}
			}
		}
		else{
		if (count($ids_to)>0) {
			//$arrto = explode ( ',', $ids_to );
			$sSQL_alloc_all = [ ];
			$sSQL_alloc_to_all = [ ];
			$total_to = 0;
			foreach($ids_to as $obj_type_to => $arrto){
				if ($obj_type_to == OBJ_TYPE_FLOW) {
					$sum = DB::table ( 'FLOW_DATA_'.($to_option=='STD'?'VALUE':'THEOR').($this->isOracle?'':' AS').' a' )->join ( 'FLOW'.($this->isOracle?'':' AS').' b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrto )->where ( [
							'b.PHASE_ID' => $theor_phase
					] )->select ( DB::raw ( 'sum(FL_DATA_' . $theor_attr . ')'.($this->isOracle?'':' AS').' TOTAL_TO' ) )->get ();

					$sSQL_alloc = DB::table ( 'FLOW_DATA_'.($to_option=='STD'?'VALUE':'THEOR').($this->isOracle?'':' AS').' a' )->join ( 'FLOW'.($this->isOracle?'':' AS').' b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrto )->where ( [
							'b.PHASE_ID' => $theor_phase
					] )->get ( [
							'a.FLOW_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.ACTIVE_HRS',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.FL_DATA_' . $theor_attr . ' AS ALLOC_THEOR'
					] );

					$sSQL_alloc_to = DB::table ( 'FLOW_DATA_ALLOC'.($this->isOracle?'':' AS').' a' )->join ( 'FLOW'.($this->isOracle?'':' AS').' b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.FLOW_ID', $arrto )->where ( [
							'b.PHASE_ID' => $theor_phase
					] )->get ( [
							'a.FLOW_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.ACTIVE_HRS',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.FL_DATA_' . $theor_attr . ' AS ALLOC_VALUE'
					] );
				} else if ($obj_type_to == OBJ_TYPE_EU) {
					$sum = DB::table ( 'ENERGY_UNIT_DATA_'.($to_option=='STD'?'VALUE':'THEOR').($this->isOracle?'':' AS').' a' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrto )->where ( [
							'a.FLOW_PHASE' => $theor_phase,
							'a.EVENT_TYPE' => $event_type
					] )->select ( DB::raw ( 'sum(EU_DATA_' . $theor_attr_eu . ') AS TOTAL_TO' ) )->get ();
	//\DB::enableQueryLog ();
					$sSQL_alloc = DB::table ( 'ENERGY_UNIT_DATA_'.($to_option=='STD'?'VALUE':'THEOR').($this->isOracle?'':' AS').' a' )->join ( 'ENERGY_UNIT'.($this->isOracle?'':' AS').' b', 'a.EU_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrto )->where ( [
							'a.FLOW_PHASE' => $theor_phase,
							'a.EVENT_TYPE' => $event_type
					] )->get ( [
							'a.EU_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.ACTIVE_HRS',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.FLOW_PHASE',
							'EU_DATA_' . $theor_attr_eu . ' AS ALLOC_THEOR'
					] );
	//\Log::info ( \DB::getQueryLog () );
					$sSQL_alloc_to = DB::table ( 'ENERGY_UNIT_DATA_ALLOC'.($this->isOracle?'':' AS').' a' )->join ( 'ENERGY_UNIT'.($this->isOracle?'':' AS').' b', 'a.EU_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrto )->where ( [
							'a.FLOW_PHASE' => $theor_phase,
							'a.EVENT_TYPE' => $event_type
					] )->get ( [
							'a.EU_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.ACTIVE_HRS',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.FLOW_PHASE',
							'EU_DATA_' . $theor_attr_eu . ' AS ALLOC_VALUE'
					] );
				} else if ($obj_type_to == OBJ_TYPE_TANK) {
					$sum = DB::table ( 'TANK_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'TANK'.($this->isOracle?'':' AS').' b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrto )->select ( DB::raw ( 'sum(TANK_' . $theor_attr . ') AS TOTAL_TO' ) )->get ();

					$sSQL_alloc = DB::table ( 'TANK_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'TANK'.($this->isOracle?'':' AS').' b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrto )->get ( [
							'a.TANK_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.TANK_DATA_' . $theor_attr . ' AS ALLOC_THEOR'
					] );

					$sSQL_alloc_to = DB::table ( 'TANK_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'TANK'.($this->isOracle?'':' AS').' b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrto )->get ( [
							'a.TANK_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.TANK_' . $theor_attr . ' AS ALLOC_VALUE'
					] );
				} else if ($obj_type_to == OBJ_TYPE_STORAGE) {
					$sum = DB::table ( 'STORAGE_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'STORAGE'.($this->isOracle?'':' AS').' b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrto )->select ( DB::raw ( 'sum(STORAGE_' . $theor_attr . ') AS TOTAL_TO' ) )->get ();

					$sSQL_alloc = DB::table ( 'STORAGE_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'STORAGE'.($this->isOracle?'':' AS').' b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrto )->get ( [
							'a.STORAGE_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.STORAGE_' . $theor_attr . ' AS ALLOC_THEOR'
					] );

					$sSQL_alloc_to = DB::table ( 'STORAGE_DATA_ALLOC'.($this->isOracle?'':' AS').' a' )->join ( 'STORAGE'.($this->isOracle?'':' AS').' b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrto )->get ( [
							'a.STORAGE_ID AS OBJECT_ID',
							'b.NAME AS OBJECT_NAME',
							'a.OCCUR_DATE',
							'a.OCCUR_DATE AS OCCUR_DATE',
							'a.STORAGE_' . $theor_attr . ' AS ALLOC_VALUE'
					] );
				}				
				$total_to += $sum [0]->TOTAL_TO;
				$sSQL_alloc_all[$obj_type_to] = $sSQL_alloc;
				$sSQL_alloc_to_all[$obj_type_to] = $sSQL_alloc_to;
			}
			// _log("command: $sSQL");
			$this->_log ( "total_to (theor): $total_to", 2 );
			$alloc_factor = 0;
			if($total_from && $total_to)
				$alloc_factor = $total_from/$total_to;
					 
			$this->_log ( "Allocation factor: $alloc_factor", 2 );
		} else {
			$ret = $this->_log ( "TO object not found", 1 );
			if ($ret === false)
				return false;
		}

		if (count($ids_fixed)>0) {
			$rows_all = [ ];
			//$arrfixed = explode ( ',', $ids_fixed );
			foreach($ids_fixed as $obj_type_to => $arrfixed){
				if ($obj_type_to == OBJ_TYPE_FLOW) {
					$rows = DB::table ( 'FLOW_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->leftjoin ( 'FLOW_DATA_THEOR'.($this->isOracle?'':' AS').' t', function ($join) {
						$join->on ( 't.FLOW_ID', '=', 'a.FLOW_ID' );
						$join->on ( 't.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
					} )->join ( 'FLOW'.($this->isOracle?'':' AS').' b', 'a.FLOW_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->where ( [
							'b.PHASE_ID' => $alloc_phase
					] )->whereIn ( 'a.FLOW_ID', $arrfixed )->SELECT ( DB::raw ( 'case when '.$this->funcIfNull().'(a.FL_DATA_' . $alloc_attr . ',0)<>0 then a.FL_DATA_' . $alloc_attr . ' else t.FL_DATA_' . $alloc_attr . ' end AS FIXED_VALUE'), 'a.FLOW_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.ACTIVE_HRS', 'a.OCCUR_DATE' )->get ();
				} else if ($obj_type_to == OBJ_TYPE_EU) {
					//\DB::enableQueryLog ();
					$rows = DB::table ( 'ENERGY_UNIT_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->leftjoin ( 'ENERGY_UNIT_DATA_THEOR'.($this->isOracle?'':' AS').' t', function ($join) {
						$join->on ( 't.EU_ID', '=', 'a.EU_ID' );
						$join->on ( 't.OCCUR_DATE', '=', 'a.OCCUR_DATE' );
						$join->on ( 't.FLOW_PHASE', '=', 'a.FLOW_PHASE' );
						$join->on ( 't.EVENT_TYPE', '=', 'a.EVENT_TYPE' );
					} )
					->join ( 'ENERGY_UNIT'.($this->isOracle?'':' AS').' b', 'a.EU_ID', '=', 'b.ID' )
					->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.EU_ID', $arrfixed )->where ( [
							'a.FLOW_PHASE' => $alloc_phase,
							'a.EVENT_TYPE' => $event_type
					] )->

					SELECT ( DB::raw ( 'case when '.$this->funcIfNull().'(a.EU_DATA_' . $alloc_attr . ',0)<>0 then a.EU_DATA_' . $alloc_attr . ' else t.EU_DATA_' . $alloc_attr . ' end AS FIXED_VALUE'), 'a.EU_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.ACTIVE_HRS', 'a.OCCUR_DATE' )->get ();
					//\Log::info ( \DB::getQueryLog () );
				} else if ($obj_type_to == OBJ_TYPE_TANK) {
					//\DB::enableQueryLog ();
					$rows = DB::table ( 'TANK_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'TANK'.($this->isOracle?'':' AS').' b', 'a.TANK_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.TANK_ID', $arrfixed )->SELECT ( 'a.TANK_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.OCCUR_DATE', 'TANK_' . $alloc_attr . ' AS FIXED_VALUE' )->get ();
					//\Log::info ( \DB::getQueryLog () );
				} else if ($obj_type_to == OBJ_TYPE_STORAGE) {
					//\DB::enableQueryLog ();
					$rows = DB::table ( 'STORAGE_DATA_VALUE'.($this->isOracle?'':' AS').' a' )->join ( 'STORAGE'.($this->isOracle?'':' AS').' b', 'a.STORAGE_ID', '=', 'b.ID' )->whereDate ( 'a.OCCUR_DATE', '>=', $from_date )->whereDate ( 'a.OCCUR_DATE', '<=', $to_date )->whereIn ( 'a.STORAGE_ID', $arrfixed )->SELECT ( 'a.STORAGE_ID AS OBJECT_ID', 'b.NAME AS OBJECT_NAME', 'a.OCCUR_DATE', 'STORAGE_' . $alloc_attr . ' AS FIXED_VALUE' )->get ();
					//\Log::info ( \DB::getQueryLog () );
				}
				$rows_all[$obj_type_to] = $rows;
			}
			$this->_log ( "Create allocation data from fixed objects:", 2 );
			$total_fixed = 0;
			foreach($rows_all as $obj_type_to => $rows){
				foreach ( $rows as $row ) {
					$v_to = $row->FIXED_VALUE;
					$total_fixed += $v_to;
					if ($obj_type_to == OBJ_TYPE_FLOW) {
						$ro = FlowDataAlloc::where ( [
								'FLOW_ID' => $row->OBJECT_ID,
								'ALLOC_TYPE' => $alloc_type
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

						if ( $ro ) {
							if ($this->alloc_act == "run") {
								FlowDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'FL_DATA_' . $alloc_attr => $v_to
								] );
							}
							$sSQL = "update FLOW_DATA_ALLOC set FL_DATA_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								FlowDataAlloc::insert ( [
										'FLOW_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'ALLOC_TYPE' => $alloc_type,
										'FL_DATA_' . $alloc_attr => $v_to
								] );
							}
							$sSQL = "insert into FLOW_DATA_ALLOC(FLOW_ID,OCCUR_DATE,FL_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
						}

						$this->_log ($row->OBJECT_NAME.": fixed value = $v_to", 2 );
					}
					else if ($obj_type_to == OBJ_TYPE_EU) {
						$ro = EnergyUnitDataAlloc::where ( [
								'EU_ID' => $row->OBJECT_ID,
								'FLOW_PHASE' => $alloc_phase,
								'EVENT_TYPE' => $event_type,
								'ALLOC_TYPE' => $alloc_type
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
						if ( $ro ) {
							if ($this->alloc_act == "run") {
								EnergyUnitDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'EU_DATA_' . $alloc_attr_eu => $v_to
								] );
							}
							$sSQL = "update ENERGY_UNIT_DATA_ALLOC set EU_DATA_" . $alloc_attr_eu . "='" . $v_to . "' where ID=" . $ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								$EnergyUnitDataAlloc = EnergyUnitDataAlloc::insert ( [
										'EU_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'FLOW_PHASE' => $alloc_phase,
										'EVENT_TYPE' => $event_type,
										'ALLOC_TYPE' => $alloc_type,
										'EU_DATA_' . $alloc_attr_eu => $v_to
								] );
							}
							$sSQL = "insert into ENERGY_UNIT_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,EVENT_TYPE,ALLOC_TYPE,EU_DATA_" . $alloc_attr_eu . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $alloc_phase . "," . $event_type . "," . $alloc_type . "," . $v_to . ")";
						}
						$this->_log ($row->OBJECT_NAME.": fixed value = $v_to", 2 );
					} else if ($obj_type_to == OBJ_TYPE_TANK) {
						$ro = TankDataAlloc::where ( [
								'TANK_ID' => $row->OBJECT_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

						if ( $ro ) {
							if ($this->alloc_act == "run") {
								TankDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'TANK_' . $alloc_attr => $v_to
								] );
							}
							$sSQL = "update TANK_DATA_ALLOC set TANK_DATA_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								TankDataAlloc::insert ( [
										'TANK_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'TANK_' . $alloc_attr =>$v_to
								] );
							}
							$sSQL = "insert into TANK_DATA_ALLOC(TANK_ID,OCCUR_DATE,TANK_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
						}

						$this->_log ($row->OBJECT_NAME.": fixed value = $v_to", 2 );
					} else if ($obj_type_to == OBJ_TYPE_STORAGE) {
						$ro = StorageDataAlloc::where ( [
								'STORAGE_ID' => $row->OBJECT_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

						if ( $ro ) {
							if ($this->alloc_act == "run") {
								StorageDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'STORAGE_' . $alloc_attr => $v_to
								] );
							}
							$sSQL = "update STORAGE_DATA_ALLOC set STORAGE_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								StorageDataAlloc::insert ( [
										'STORAGE_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'STORAGE_' . $alloc_attr=>$v_to
								] );
							}
							$sSQL = "insert into STORAGE_DATA_ALLOC(STORAGE_ID,OCCUR_DATE,STORAGE_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
						}

						$this->_log ($row->OBJECT_NAME.": fixed value = $v_to", 2 );
					}
				}				
			}

			$this->_log ( "total_fixed (std value ~ theor): $total_fixed", 2 );
			$total_from -= $total_fixed;
			$this->_log ( "total_from (minus total_fixed): $total_from", 2 );
		}

		// Alloc
		if ($total_to == 0 && $total_from !=0) {
			$ret = $this->_log ( "total_to is zero (total_from<>0), can not calculate", 1 );
			if ($ret === false)
				return false;
		}
		foreach($sSQL_alloc_all as $obj_type_to => $sSQL_alloc){
			foreach ( $sSQL_alloc as $row ) {
				if ($row->ALLOC_THEOR === '' || $row->ALLOC_THEOR == null) {
					$row->ALLOC_THEOR = 0;
				}
				$v_to = $row->ALLOC_THEOR * $alloc_factor;

				if ($obj_type_to == OBJ_TYPE_FLOW) {
					$flow = Flow::where(['ID' => $row->OBJECT_ID])->select ( 'FIFO' )->first ();
					$ro = FlowDataAlloc::where ( [
							'FLOW_ID' => $row->OBJECT_ID,
							'ALLOC_TYPE' => $alloc_type
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

					if ( $ro ) {
						if ($this->alloc_act == "run") {
							FlowDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'FL_DATA_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "update FLOW_DATA_ALLOC set FL_DATA_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							FlowDataAlloc::insert ( [
									'FLOW_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'RECORD_FREQUENCY' => 3,
									'ALLOC_TYPE' => $alloc_type,
									'FL_DATA_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "insert into FLOW_DATA_ALLOC(FLOW_ID,OCCUR_DATE,FL_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
					}
					$this->_log ($row->OBJECT_NAME.": ".floatval($row->ALLOC_THEOR)." * $alloc_factor = $v_to", 2 );
					if($flow->FIFO=='Y'){
						if ($this->alloc_act == "run") {
							FlowDataFifoAlloc::where ( [
									'FLOW_ID' => $row->OBJECT_ID,
							] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
							FlowDataFifoAlloc::insert ( [
									'FLOW_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'INFLOW_DATE' => $row->OCCUR_DATE,
									'INPUT_DATE' => Carbon::now(),
									'FL_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_to,
									'FL_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
									'FL_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
									'FL_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_to
							] );
						}
						$this->_log ("FIFO - ".$row->OBJECT_NAME.": ".floatval($row->ALLOC_THEOR)." * $alloc_factor = ".$v_to, 2 );
					}

					// ////// Flow COST_INT_CTR allocation
	/*
					if ($this->alloc_act == "run") {
						FlowCoEntDataAlloc::where ( [
								'FLOW_ID' => $row->OBJECT_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
						$sSQL = "delete from FLOW_CO_ENT_DATA_ALLOC where FLOW_ID=" . $row->OBJECT_ID . " and OCCUR_DATE='".$row->OCCUR_DATE."'";
						$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );
					}
	*/
					$re_co = DB::table ( 'FLOW'.($this->isOracle?'':' AS').' a' )
					->join ( 'COST_INT_CTR_DETAIL'.($this->isOracle?'':' AS').' b', 'a.COST_INT_CTR_ID', '=', 'b.COST_INT_CTR_ID' )
					->join ( 'COST_INT_CTR'.($this->isOracle?'':' AS').' c', 'b.COST_INT_CTR_ID', '=', 'c.ID' )
					->join ( 'BA_ADDRESS'.($this->isOracle?'':' AS').' d', 'b.BA_ID', '=', 'd.ID' )
					->where ( [
							'a.ID' => $row->OBJECT_ID,
							'b.FLOW_PHASE' => $alloc_phase
					] )->get ( [
							'c.NAME AS COST_INT_CTR_NAME',
							'd.NAME AS BA_NAME',
							'a.COST_INT_CTR_ID',
							'b.BA_ID',
							'b.INTEREST_PCT AS ALLOC_PERCENT'
					] );

					foreach ( $re_co as $ro_co ) {
						$v_co = $v_to * $ro_co->ALLOC_PERCENT / 100;
						$ro = FlowCoEntDataAlloc::where ( [
								'FLOW_ID' => $row->OBJECT_ID,
								'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
								'BA_ID' => $ro_co->BA_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
						if ( $ro ) {
							if ($this->alloc_act == "run") {
								FlowCoEntDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'FL_DATA_' . $alloc_attr => $v_co
								] );
							}
							$sSQL = "update FLOW_CO_ENT_DATA_ALLOC set FL_DATA_" . $alloc_attr . "=" . $v_co . " where ID=".$ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								FlowCoEntDataAlloc::insert ( [
										'FLOW_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'FL_DATA_' . $alloc_attr => $v_co
								] );
							}
							$sSQL = "insert into FLOW_CO_ENT_DATA_ALLOC(FLOW_ID,OCCUR_DATE,COST_INT_CTR_ID,BA_ID,FL_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "','" . $ro_co->COST_INT_CTR_ID . "','" . $ro_co->BA_ID . "'," . $v_co . ")";
						}
						$this->_log ("FlowCoEnt - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_to * ".floatval($ro_co->ALLOC_PERCENT)."% = $v_co", 2 );
						if($flow->FIFO=='Y'){
							if ($this->alloc_act == "run") {
								FlowCoEntDataFifoAlloc::where ( [
										'FLOW_ID' => $row->OBJECT_ID,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
								] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
								
								FlowCoEntDataFifoAlloc::insert ( [
										'FLOW_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'INFLOW_DATE' => $row->OCCUR_DATE,
										'INPUT_DATE' => Carbon::now(),
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'FL_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_co,
										'FL_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
										'FL_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
										'FL_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_co
								] );
							}
							$this->_log ("FlowCoEnt FIFO - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_to * ".floatval($ro_co->ALLOC_PERCENT)."% = $v_co", 2 );
						}
					}
					// /////// END of Flow COST_INT_CTR allocation
				} else if ($obj_type_to == OBJ_TYPE_EU) {
					$eu = EnergyUnit::where(['ID' => $row->OBJECT_ID])->select ( 'FIFO' )->first ();
					$ro = EnergyUnitDataAlloc::where ( [
							'EU_ID' => $row->OBJECT_ID,
							'FLOW_PHASE' => $alloc_phase,
							'EVENT_TYPE' => $event_type,
							'ALLOC_TYPE' => $alloc_type
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
					if ( $ro ) {
						if ($this->alloc_act == "run") {
							EnergyUnitDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'EU_DATA_' . $alloc_attr_eu => $v_to
							] );
						}
						$sSQL = "update ENERGY_UNIT_DATA_ALLOC set EU_DATA_" . $alloc_attr_eu . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							EnergyUnitDataAlloc::insert ( [
									'EU_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'FLOW_PHASE' => $alloc_phase,
									'EVENT_TYPE' => $event_type,
									'ALLOC_TYPE' => $alloc_type,
									'EU_DATA_' . $alloc_attr_eu => $v_to
							] );
						}
						$sSQL = "insert into ENERGY_UNIT_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,EVENT_TYPE,ALLOC_TYPE,EU_DATA_" . $alloc_attr_eu . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $alloc_phase . "," . $event_type . ",'" . $alloc_type . "'," . $v_to . ")";
					}

					$this->_log ($row->OBJECT_NAME.": ".floatval($row->ALLOC_THEOR)." * $alloc_factor = $v_to", 2 );
					if($eu->FIFO=='Y'){
						if ($this->alloc_act == "run") {
							EnergyUnitDataFifoAlloc::where ( [
									'EU_ID' => $row->OBJECT_ID
							] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
							
							EnergyUnitDataFifoAlloc::insert ( [
									'EU_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'INFLOW_DATE' => $row->OCCUR_DATE,
									'INPUT_DATE' => Carbon::now(),
									'FLOW_PHASE' => $alloc_phase,
									'EVENT_TYPE' => $event_type,
									'ALLOC_TYPE' => $alloc_type,
									'EU_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_to,
									'EU_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
									'EU_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
									'EU_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_to
							] );
						}
						$this->_log ("EnergyUnit FIFO - $row->OBJECT_NAME: ".floatval($row->ALLOC_THEOR)." * $alloc_factor = $v_to", 2 );
					}
	/*
					if ($this->alloc_act == "run") {
						EnergyUnitCoEntDataAlloc::where ( [
								'EU_ID' => $row->OBJECT_ID,
								'FLOW_PHASE' => $alloc_phase,
								'EVENT_TYPE' => $event_type,
								'ALLOC_TYPE' => $alloc_type
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
						$sSQL = "delete from ENERGY_UNIT_CO_ENT_DATA_ALLOC where EU_ID=" . $row->OBJECT_ID." and OCCUR_DATE='".$row->OCCUR_DATE."' and FLOW_PHASE=$alloc_phase and EVENT_TYPE=$event_type and ALLOC_TYPE=$alloc_type";
						$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );
					}
	*/
					// COST_INT_CTR allocation
					$re_co = DB::table ( 'ENERGY_UNIT'.($this->isOracle?'':' AS').' a' )
					->join ( 'COST_INT_CTR_DETAIL'.($this->isOracle?'':' AS').' b', 'a.COST_INT_CTR_ID', '=', 'b.COST_INT_CTR_ID' )
					->join ( 'COST_INT_CTR'.($this->isOracle?'':' AS').' c', 'b.COST_INT_CTR_ID', '=', 'c.ID' )
					->join ( 'BA_ADDRESS'.($this->isOracle?'':' AS').' d', 'b.BA_ID', '=', 'd.ID' )
					->where ( [
							'a.ID' => $row->OBJECT_ID,
							'b.FLOW_PHASE' => $alloc_phase
					] )->get ( [
							'c.NAME AS COST_INT_CTR_NAME',
							'd.NAME AS BA_NAME',
							'a.COST_INT_CTR_ID',
							'b.BA_ID',
							'b.INTEREST_PCT AS ALLOC_PERCENT'
					] );
					foreach ( $re_co as $ro_co ) {
						$v_co = $v_to * $ro_co->ALLOC_PERCENT / 100;
						$ro = EnergyUnitCoEntDataAlloc::where ( [
										'EU_ID' => $row->OBJECT_ID,
										'FLOW_PHASE' => $alloc_phase,
										'EVENT_TYPE' => $event_type,
										'ALLOC_TYPE' => $alloc_type,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
						if ( $ro ) {
							if ($this->alloc_act == "run") {
								EnergyUnitCoEntDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'EU_DATA_' . $alloc_attr_eu => $v_co
								] );
							}
							$sSQL = "update ENERGY_UNIT_CO_ENT_DATA_ALLOC set EU_DATA_" . $alloc_attr_eu . "='" . $v_co . "' where ID=" . $ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								EnergyUnitCoEntDataAlloc::insert ( [
										'EU_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'FLOW_PHASE' => $alloc_phase,
										'EVENT_TYPE' => $event_type,
										'ALLOC_TYPE' => $alloc_type,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'EU_DATA_' . $alloc_attr_eu => $v_co
								] );
							}
							$sSQL = "insert into ENERGY_UNIT_CO_ENT_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,EVENT_TYPE,ALLOC_TYPE,COST_INT_CTR_ID,BA_ID,EU_DATA_" . $alloc_attr_eu . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $alloc_phase . "," . $event_type . ",'" . $alloc_type . "','" . $ro_co->COST_INT_CTR_ID . "','" . $ro_co->BA_ID . "'," . $v_co . ")";
						}
						$this->_log ("EnergyUnitCoEnt - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_to * ".floatval($ro_co->ALLOC_PERCENT)."% = $v_co", 2 );
						if($eu->FIFO=='Y'){
							if ($this->alloc_act == "run") {
								EnergyUnitCoEntDataFifoAlloc::where ( [
										'EU_ID' => $row->OBJECT_ID,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
								] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
								
								EnergyUnitCoEntDataFifoAlloc::insert ( [
										'EU_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'INFLOW_DATE' => $row->OCCUR_DATE,
										'INPUT_DATE' => Carbon::now(),
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'FLOW_PHASE' => $alloc_phase,
										'EVENT_TYPE' => $event_type,
										'ALLOC_TYPE' => $alloc_type,
										'EU_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_co,
										'EU_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
										'EU_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
										'EU_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_co
								] );
							}
							$this->_log ("EnergyUnitCoEnt FIFO - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_co", 2 );
						}
					}
					// /////// END of Well COST_INT_CTR allocation

					// completion, interval
					if ($alloc_attr == "GRS_VOL" || $alloc_attr == "NET_VOL" || $alloc_attr == "GRS_MASS" || $alloc_attr == "GRS_PWR" || $alloc_attr == "GRS_ENGY") {
						$this->allocWellCompletion ( $row->OBJECT_ID, $row->OCCUR_DATE, $alloc_phase, $event_type, $alloc_attr, $v_to );
					}
				} else if ($obj_type_to == OBJ_TYPE_TANK) {
					$tank = Tank::where(['ID' => $row->OBJECT_ID])->select ( 'FIFO' )->first ();
					$ro = TankDataAlloc::where ( [
							'TANK_ID' => $row->OBJECT_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

					if ( $ro ) {
						if ($this->alloc_act == "run") {
							TankDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'TANK_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "update TANK_DATA_ALLOC set TANK_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							TankDataAlloc::insert ( [
									'TANK_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'TANK_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "insert into TANK_DATA_ALLOC(TANK_ID,OCCUR_DATE,TANK_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
					}

					$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );
					if($tank->FIFO=='Y'){
						if ($this->alloc_act == "run") {
							TankDataFifoAlloc::where ( [
									'TANK_ID' => $row->OBJECT_ID
							] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
							
							TankDataFifoAlloc::insert ( [
									'TANK_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'INFLOW_DATE' => $row->OCCUR_DATE,
									'INPUT_DATE' => Carbon::now(),
									'TANK_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_to,
									'TANK_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
									'TANK_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
									'TANK_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_to
							] );
						}
						$this->_log ("Tank FIFO - $row->OBJECT_NAME: $v_to", 2 );
					}
					// COST_INT_CTR allocation
					$re_co = DB::table ( 'TANK'.($this->isOracle?'':' AS').' a' )
					->join ( 'COST_INT_CTR_DETAIL'.($this->isOracle?'':' AS').' b', 'a.COST_INT_CTR_ID', '=', 'b.COST_INT_CTR_ID' )
					->join ( 'COST_INT_CTR'.($this->isOracle?'':' AS').' c', 'b.COST_INT_CTR_ID', '=', 'c.ID' )
					->join ( 'BA_ADDRESS'.($this->isOracle?'':' AS').' d', 'b.BA_ID', '=', 'd.ID' )
					->where ( [
							'a.ID' => $row->OBJECT_ID
					] )->get ( [
							'c.NAME AS COST_INT_CTR_NAME',
							'd.NAME AS BA_NAME',
							'a.COST_INT_CTR_ID',
							'b.BA_ID',
							'b.INTEREST_PCT AS ALLOC_PERCENT'
					] );

					foreach ( $re_co as $ro_co ) {
						$v_co = $v_to * $ro_co->ALLOC_PERCENT / 100;
						$ro = TankCoEntDataAlloc::where ( [
								'TANK_ID' => $row->OBJECT_ID,
								'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
								'BA_ID' => $ro_co->BA_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
						if ( $ro ) {
							if ($this->alloc_act == "run") {
								TankCoEntDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'TANK_DATA_' . $alloc_attr => $v_co
								] );
							}
							$sSQL = "update TANK_CO_ENT_DATA_ALLOC set TANK_DATA_" . $alloc_attr . "=" . $v_co . " where ID=".$ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								TankCoEntDataAlloc::insert ( [
										'TANK_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'TANK_DATA_' . $alloc_attr => $v_co
								] );
							}
							$sSQL = "insert into TANK_CO_ENT_DATA_ALLOC(TANK_ID,OCCUR_DATE,COST_INT_CTR_ID,BA_ID,TANK_DATA_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "','" . $ro_co->COST_INT_CTR_ID . "','" . $ro_co->BA_ID . "'," . $v_co . ")";
						}
						$this->_log ("TankCoEnt - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_co", 2 );
						if($tank->FIFO=='Y'){
							if ($this->alloc_act == "run") {
								TankCoEntDataFifoAlloc::where ( [
										'TANK_ID' => $row->OBJECT_ID,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
								] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
								
								TankCoEntDataFifoAlloc::insert ( [
										'TANK_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'INFLOW_DATE' => $row->OCCUR_DATE,
										'INPUT_DATE' => Carbon::now(),
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'TANK_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_co,
										'TANK_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
										'TANK_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
										'TANK_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_co
								] );
							}
							$this->_log ("TankCoEnt FIFO - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_co", 2 );
						}
					}
					// END of Tank COST_INT_CTR allocation
				} else if ($obj_type_to == OBJ_TYPE_STORAGE) {
					$storage = Storage::where(['ID' => $row->OBJECT_ID])->select ( 'FIFO' )->first ();
					$ro = StorageDataAlloc::where ( [
							'STORAGE_ID' => $row->OBJECT_ID
					] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();

					if ( $ro ) {
						if ($this->alloc_act == "run") {
							StorageDataAlloc::where ( [
									'ID' => $ro->ID
							] )->update ( [
									'STORAGE_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "update STORAGE_DATA_ALLOC set STORAGE_" . $alloc_attr . "='" . $v_to . "' where ID=" . $ro->ID;
					} else {
						if ($this->alloc_act == "run") {
							StorageDataAlloc::insert ( [
									'STORAGE_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'STORAGE_' . $alloc_attr => $v_to
							] );
						}
						$sSQL = "insert into STORAGE_DATA_ALLOC(STORAGE_ID,OCCUR_DATE,STORAGE_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "'," . $v_to . ")";
					}
					$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );
					if($storage->FIFO=='Y'){
						if ($this->alloc_act == "run") {
							StorageDataFifoAlloc::where ( [
									'STORAGE_ID' => $row->OBJECT_ID
							] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
							
							StorageDataFifoAlloc::insert ( [
									'STORAGE_ID' => $row->OBJECT_ID,
									'OCCUR_DATE' => $row->OCCUR_DATE,
									'INFLOW_DATE' => $row->OCCUR_DATE,
									'INPUT_DATE' => Carbon::now(),
									'STORAGE_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_to,
									'STORAGE_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
									'STORAGE_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
									'STORAGE_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_to
							] );
						}
						$this->_log ("Storage FIFO - $row->OBJECT_NAME: $v_to", 2 );
					}
					// COST_INT_CTR allocation
					$re_co = DB::table ( 'STORAGE'.($this->isOracle?'':' AS').' a' )
					->join ( 'COST_INT_CTR_DETAIL'.($this->isOracle?'':' AS').' b', 'a.COST_INT_CTR_ID', '=', 'b.COST_INT_CTR_ID' )
					->join ( 'COST_INT_CTR'.($this->isOracle?'':' AS').' c', 'b.COST_INT_CTR_ID', '=', 'c.ID' )
					->join ( 'BA_ADDRESS'.($this->isOracle?'':' AS').' d', 'b.BA_ID', '=', 'd.ID' )
					->where ( [
							'a.ID' => $row->OBJECT_ID
					] )->get ( [
							'c.NAME AS COST_INT_CTR_NAME',
							'd.NAME AS BA_NAME',
							'a.COST_INT_CTR_ID',
							'b.BA_ID',
							'b.INTEREST_PCT AS ALLOC_PERCENT'
					] );

					foreach ( $re_co as $ro_co ) {
						$v_co = $v_to * $ro_co->ALLOC_PERCENT / 100;
						$ro = StorageCoEntDataAlloc::where ( [
								'STORAGE_ID' => $row->OBJECT_ID,
								'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
								'BA_ID' => $ro_co->BA_ID
						] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->select ( 'ID' )->first ();
						if ( $ro ) {
							if ($this->alloc_act == "run") {
								StorageCoEntDataAlloc::where ( [
										'ID' => $ro->ID
								] )->update ( [
										'STORAGE_' . $alloc_attr => $v_co
								] );
							}
							$sSQL = "update STORAGE_CO_ENT_DATA_ALLOC set STORAGE_" . $alloc_attr . "=" . $v_co . " where ID=".$ro->ID;
						} else {
							if ($this->alloc_act == "run") {
								StorageCoEntDataAlloc::insert ( [
										'STORAGE_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'STORAGE_' . $alloc_attr => $v_co
								] );
							}
							$sSQL = "insert into STORAGE_CO_ENT_DATA_ALLOC(STORAGE_ID,OCCUR_DATE,COST_INT_CTR_ID,BA_ID,STORAGE_" . $alloc_attr . ") values('" . $row->OBJECT_ID . "','" . $row->OCCUR_DATE . "','" . $ro_co->COST_INT_CTR_ID . "','" . $ro_co->BA_ID . "'," . $v_co . ")";
						}
						$this->_log ("StorageCoEnt - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_co", 2 );
						if($storage->FIFO=='Y'){
							if ($this->alloc_act == "run") {
								StorageCoEntDataFifoAlloc::where ( [
										'STORAGE_ID' => $row->OBJECT_ID,
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
								] )->whereDate ( 'INFLOW_DATE', '=', $row->OCCUR_DATE )->delete();
								
								StorageCoEntDataFifoAlloc::insert ( [
										'STORAGE_ID' => $row->OBJECT_ID,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'INFLOW_DATE' => $row->OCCUR_DATE,
										'INPUT_DATE' => Carbon::now(),
										'COST_INT_CTR_ID' => $ro_co->COST_INT_CTR_ID,
										'BA_ID' => $ro_co->BA_ID,
										'STORAGE_DATA_'.str_replace('_','_INFLOW_',$alloc_attr) => $v_co,
										'STORAGE_DATA_'.str_replace('_','_OUTFLOW_',$alloc_attr) => 0,
										'STORAGE_DATA_'.str_replace('_','_BEGIN_',$alloc_attr) => 0,
										'STORAGE_DATA_'.str_replace('_','_END_',$alloc_attr) => $v_co
								] );
							}
							$this->_log ("StorageCoEnt FIFO - $row->OBJECT_NAME, $ro_co->COST_INT_CTR_NAME, $ro_co->BA_NAME: $v_co", 2 );
						}
					}
					// END of Storage COST_INT_CTR allocation					
				}
			}
		}

		// Composition
		if ($alloc_comp) {
			$this->_log ( "Begin composition allocation", 2 );
			$comp_sqls = array ();
			$comp_total_from = [];
			$comp_total_to = [];
			$comp_total_rate = [];

			// step 1: calculate composition for all "from" object
			foreach($alloc_from_query_all as $obj_type_from => $alloc_from_query){
				$obj_type_code = ($obj_type_from == 1 ? "FLOW" : "WELL");
				$alloc_from = $alloc_from_query->get();
				foreach ( $alloc_from as $row ) {
					$this->_log ( "Calculate composition _FROM, object_name: " . $row->OBJECT_NAME . ",date " . $row->OCCUR_DATE, 2 );
					$object_id = $row->OBJECT_ID;
					$occur_date = $row->OCCUR_DATE;
					$quality_from = $this->getQualityGas ( $object_id, $obj_type_code, $occur_date, $F );
					if ($quality_from) {
						foreach($quality_from as $ele_type => $ele_value){
							$comp_total_from[$ele_type] = (isset($comp_total_from[$ele_type])?$comp_total_from[$ele_type]:0) + $row->ALLOC_VALUE * $ele_value;
						}
						if ($obj_type_from == OBJ_TYPE_FLOW) {
							$sSQL = "delete from FLOW_COMP_DATA_ALLOC where FLOW_ID=$object_id and OCCUR_DATE='$row->OCCUR_DATE'";
							$this->_log ($row->OBJECT_NAME.": ", 2 );
							if ($this->alloc_act == "run") {
								FlowCompDataAlloc::where ( [
										'FLOW_ID' => $object_id
								] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
							}
							$param = [ ];
							foreach($quality_from as $ele_type => $ele_value){
								if(is_numeric($ele_value))
									array_push ( $param, [
											'FLOW_ID' => $object_id,
											'OCCUR_DATE' => $row->OCCUR_DATE,
											'COMPOSITION' => $ele_type,
											'FL_DATA_' . $alloc_attr => $row->ALLOC_VALUE * $ele_value
									] );
							}

							$sSQL = "insert into FLOW_COMP_DATA_ALLOC(FLOW_ID,OCCUR_DATE,COMPOSITION,FL_DATA_$alloc_attr) VALUES ";
							foreach ( $param as $pa ) {
								if ($this->alloc_act == "run"){
									FlowCompDataAlloc::insert ( $pa );
									$this->_log ($pa['COMPOSITION'].": ".$pa ['FL_DATA_' . $alloc_attr], 2 );
								}
								$sSQL .= "(". $pa ['FLOW_ID'] . "," . $pa ['OCCUR_DATE'] . "," . $pa ['COMPOSITION'] . "," . $pa ['FL_DATA_' . $alloc_attr] . ")\n";
							}

						} else if ($obj_type_from == OBJ_TYPE_EU){
							$sSQL = "delete from ENERGY_UNIT_COMP_DATA_ALLOC where EU_ID=$object_id and FLOW_PHASE=2 and OCCUR_DATE='".$row->OCCUR_DATE."'";
							$this->_log ($row->OBJECT_NAME.": ", 2 );
							if ($this->alloc_act == "run") {
								EnergyUnitCompDataAlloc::where ( [
										'EU_ID' => $object_id,
										'FLOW_PHASE' => 2
								] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
							}

							$param = [ ];
							foreach($quality_from as $ele_type => $ele_value){
								if(is_numeric($ele_value))
									array_push ( $param, [
										'EU_ID' => $object_id,
										'OCCUR_DATE' => $row->OCCUR_DATE,
										'FLOW_PHASE' => 2,
										'ALLOC_TYPE' => $alloc_type,
										'COMPOSITION' => $ele_type,
										'EU_DATA_' . $alloc_attr_eu => $row->ALLOC_VALUE * $ele_value
									] );
							}

							$sSQL = "insert into ENERGY_UNIT_COMP_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,ALLOC_TYPE,COMPOSITION,EU_DATA_$alloc_attr_eu) VALUES ";
							foreach ( $param as $pa ) {
								if ($this->alloc_act == "run") {
									EnergyUnitCompDataAlloc::insert ( $pa );
									$this->_log ($pa['COMPOSITION'].": ".$pa ['EU_DATA_' . $alloc_attr_eu], 2 );
								}
								$sSQL .= "(".$pa ['EU_ID'] . "," . $pa ['OCCUR_DATE'] . "," . $pa ['FLOW_PHASE'] . "," . $pa ['ALLOC_TYPE'] . "," . $pa ['COMPOSITION'] . "," . $pa ['EU_DATA_' . $alloc_attr_eu] . ")\n";
							}
						}
					} else {
						$ret = $this->_log ( "Quality data not found (_FROM object_id: $object_id, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE)", 2 );
						if ($ret === false)
							return false;
					}
				}
			}

			// step2:
			$this->_log ( "Calculate composition allocation rates", 2 );
			foreach($sSQL_alloc_to_all as $obj_type_to => $sSQL_alloc_to){
				if($obj_type_to != OBJ_TYPE_FLOW && $obj_type_to != OBJ_TYPE_EU)
					continue;
				$obj_type_code = ($obj_type_to == OBJ_TYPE_FLOW ? "FLOW" : "WELL");

				foreach ( $sSQL_alloc_to as $row ) {
					$object_id = $row->OBJECT_ID;
					$occur_date = $row->OCCUR_DATE;
					$quality_to = $this->getQualityGas ( $object_id, $obj_type_code, $occur_date, $F );
					if ($quality_to) {
						foreach($quality_to as $ele_type => $ele_value){
							if(!isset($comp_total_to[$ele_type]))
								$comp_total_to[$ele_type] = 0;
							$comp_total_to[$ele_type] += (isset($comp_total_to[$ele_type])?$comp_total_to[$ele_type]:0) + $row->ALLOC_VALUE * $ele_value;
						}
					} else {
						$ret = $this->_log ( "Quality data not found (_TO object_id: $object_id, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE)", 2 );
						if ($ret === false)
							return false;
					}
				}
				if ($success) {
					foreach ( $comp_total_to as $x => $x_value ) {
						if ($comp_total_to [$x] == 0) {
							$comp_total_rate [$x] = - 1;
						} else {
							$comp_total_rate [$x] = $comp_total_from [$x] / $comp_total_to [$x];
						}
						$this->_log ( "[$x] comp_total_from = $comp_total_from[$x], comp_total_to = $comp_total_to[$x], rate = $comp_total_rate[$x]", 2 );
					}

					foreach ( $sSQL_alloc_to as $row ) {
						$this->_log ( "Calculate composition allocation _TO, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE", 2 );
						$object_id = $row->OBJECT_ID;
						$occur_date = $row->OCCUR_DATE;
						$quality_from = $this->getQualityGas ( $object_id, $obj_type_code, $occur_date, $F );

						if ($quality_from) {
							if ($obj_type_to == OBJ_TYPE_FLOW) {
								$sSQL = "delete from FLOW_COMP_DATA_ALLOC where FLOW_ID=$object_id and OCCUR_DATE='$row->OCCUR_DATE'";
								$this->_log ($row->OBJECT_NAME.": ", 2 );
								if($this->alloc_act == "run"){
									FlowCompDataAlloc::where ( [
											'FLOW_ID' => $object_id
									] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
								}

								$sSQL = "insert into FLOW_COMP_DATA_ALLOC(FLOW_ID,OCCUR_DATE,COMPOSITION,FL_DATA_$alloc_attr) VALUES (";
								foreach ( $comp_total_rate as $x => $x_value ) {
									if ($x_value > 0 && $row->ALLOC_VALUE > 0 && $quality_from [$x] > 0) {
										$_v = $x_value * $row->ALLOC_VALUE * $quality_from [$x];
									} else {
										$_v = 0;
									}
									if($this->alloc_act == "run"){
										FlowCompDataAlloc::insert ( [
												'FLOW_ID' => $object_id,
												'OCCUR_DATE' => $row->OCCUR_DATE,
												'COMPOSITION' => $x,
												'FL_DATA_' . $alloc_attr => $_v
										] );
									}
									$sSQL .= $object_id . "," . $row->OCCUR_DATE . "," . $x . "," . $_v . "\n";
								}
								$sSQL .= ")";

								$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );
							} else {
								if($this->alloc_act == "run"){
									EnergyUnitCompDataAlloc::where ( [
											'EU_ID' => $object_id,
											'FLOW_PHASE' => 2
									] )->whereDate ( 'OCCUR_DATE', '=', $row->OCCUR_DATE )->delete ();
								}
								$sSQL = "delete from ENERGY_UNIT_COMP_DATA_ALLOC where EU_ID=$object_id and FLOW_PHASE=2 and OCCUR_DATE='$row->OCCUR_DATE'";
								$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );

								$sSQL = "insert into ENERGY_UNIT_COMP_DATA_ALLOC(EU_ID,OCCUR_DATE,FLOW_PHASE,ALLOC_TYPE,COMPOSITION,EU_DATA_$alloc_attr_eu) VALUES (";
								foreach ( $comp_total_rate as $x => $x_value ) {
									if ($x_value > 0 && $row->ALLOC_VALUE > 0 && $quality_from [$x] > 0) {
										$_v = $x_value * $row->ALLOC_VALUE * $quality_from [$x];
									} else {
										$_v = 0;
									}
									if($this->alloc_act == "run"){
										EnergyUnitCompDataAlloc::insert ( [
												'EU_ID' => $object_id,
												'OCCUR_DATE' => $row->OCCUR_DATE,
												'FLOW_PHASE' => $alloc_phase,
												'ALLOC_TYPE' => $alloc_type,
												'COMPOSITION' => $x,
												'EU_DATA_' . $alloc_attr_eu => $_v
										] );
									}
									$sSQL .= $object_id . "," . $row->OCCUR_DATE . "," . $alloc_phase . "," . "," . $alloc_type . "," . $x . "," . $_v . "\n";
								}
								$sSQL .= ")";

								$this->_log ($row->OBJECT_NAME.": ".$v_to, 2 );
							}
						} else {
							$ret = $this->_log ( "Quality data not found (_TO object_id: $object_id, object_name: $row->OBJECT_NAME, date $row->OCCUR_DATE)", 2 );
							if ($ret === false)
								return false;
						}
					}
				}
			}
		}
		}
		if($proc){
			$msg = "[$proc] executed";
			$msg_type = 2;
			try{
				if($this->dbType == 'sqlsrv')
					DB::statement("EXEC $proc $runner_id,'$from_date','$to_date'");
				else
					DB::statement("CALL $proc($runner_id,'$from_date','$to_date')");
			}
			catch (\Exception $e){
				$msg = $e->getMessage();
				$msg_type = 1;
			}
			$this->_log($msg, $msg_type);
		}
		$this->_log ( "End runner ID: $runner_id -------------------------------------------------------", 2 );
		return $success;
    }

    private function exec_runner($runner_id, $sfrom_date, $sto_date)
    {
    	//\DB::enableQueryLog ();
    	$tmps = DB::table ('alloc_runner'.($this->isOracle?'':' AS').' a' )
    	->join ( 'alloc_job'.($this->isOracle?'':' AS').' b', 'a.job_id', '=', 'b.ID' )
    	->join ( 'code_alloc_value_type'.($this->isOracle?'':' AS').' c', 'c.id', '=', 'b.value_type' )
    	->leftjoin('code_alloc_value_type'.($this->isOracle?'':' AS').' t', 'a.theor_value_type', '=', 't.id')
    	->where(['a.id'=>$runner_id])
    	->get(['a.ID as RUNNER_ID','a.ALLOC_TYPE','a.NAME as RUNNER_NAME','a.BEGIN_DATE as RUNNER_BEGIN_DATE','a.END_DATE as RUNNER_END_DATE', 'a.THEOR_PHASE', 'a.FIFO', 'a.ALLOC_COMP', 'a.BA_ID', 'a.FROM_OPTION', 'a.TO_OPTION', 'a.USE_EXCEL', 'a.EXCEL_TEMPLATE', 'c.CODE AS ALLOC_ATTR_CODE', 't.CODE AS THEOR_ATTR_CODE', 'b.*']);
    	//\Log::info ( \DB::getQueryLog () );
		$runner = $tmps[0];
    	if($runner)
    	{
			$from_date = $sfrom_date;
			$to_date = $sto_date;
			$begin_date = $runner->RUNNER_BEGIN_DATE;
			$end_date = $runner->RUNNER_END_DATE;
			
			if($begin_date){
				if($begin_date>$from_date)
					$from_date = $begin_date;
			}
			if($end_date){
				if($end_date<$to_date)
					$to_date = $end_date;
			}
			
			if($from_date > $to_date){
				//$this->_log("Runner $runner->RUNNER_NAME is not configured to run in date range $sfrom_date to $sto_date", 1);
			}
			else{
				$this->_log("Begin runner $runner->RUNNER_NAME:", 2);
				if($runner->ALLOC_OIL == 1) $this->run_runner($runner, $from_date, $to_date, 1);
				if($runner->ALLOC_GAS == 1)	$this->run_runner($runner, $from_date, $to_date, 2);
				if($runner->ALLOC_WATER == 1) $this->run_runner($runner, $from_date, $to_date, 3);
				if($runner->ALLOC_GASLIFT == 1) $this->run_runner($runner, $from_date, $to_date, 21);
				if($runner->ALLOC_CONDENSATE == 1) $this->run_runner($runner, $from_date, $to_date, 5);
			}
    	}
    	else
    	{
    		$this->_log("No runner info found",1);
    		return false;
    	}
    }

    private function exec_job($job_id, $sfrom_date, $sto_date)
    {
		\Helper::setGetterUpperCase();
    	$tmp = AllocJob::where(['ID'=>$job_id])->select('NAME')->first();
    	$job_name = $tmp['NAME'];

    	$this->_log("Begin job $job_name (id:$job_id) from date: $sfrom_date, to date: $sto_date",2);

    	//\DB::enableQueryLog ();
    	$tmps = DB::table ('alloc_runner'.($this->isOracle?'':' AS').' a' )
    	->join ( 'alloc_job'.($this->isOracle?'':' AS').' b', 'a.job_id', '=', 'b.ID' )
    	->join ( 'code_alloc_value_type'.($this->isOracle?'':' AS').' c', 'c.id', '=', 'b.value_type' )
    	->leftJoin ( 'code_alloc_value_type'.($this->isOracle?'':' AS').' c2', 'c2.id', '=', 'a.THEOR_VALUE_TYPE' )
    	->where(['b.id'=>$job_id])
    	->orderBy('a.ORDER'.($this->isOracle?'_':''))
    	->get(['a.ID as RUNNER_ID', 'a.NAME as RUNNER_NAME','a.BEGIN_DATE as RUNNER_BEGIN_DATE','a.END_DATE as RUNNER_END_DATE', 'a.ALLOC_TYPE', 'a.FROM_OPTION', 'a.TO_OPTION', 'a.THEOR_PHASE', 'a.FIFO', 'a.ALLOC_COMP', 'a.BA_ID', 'a.USE_EXCEL', 'a.EXCEL_TEMPLATE', 'c.CODE AS ALLOC_ATTR_CODE', 'c2.CODE AS THEOR_ATTR_CODE', 'b.*']);
    	//\Log::info ( \DB::getQueryLog () );
    	$count=0;
    	foreach ($tmps as $runner){
			$from_date = $sfrom_date;
			$to_date = $sto_date;

			$begin_date = $runner->BEGIN_DATE;
			$end_date = $runner->END_DATE;
			if($begin_date){
				if($begin_date>$from_date)
					$from_date = $begin_date;
			}
			if($end_date){
				if($end_date<$to_date)
					$to_date = $end_date;
			}
			
			$begin_date = $runner->RUNNER_BEGIN_DATE;
			$end_date = $runner->RUNNER_END_DATE;
			if($begin_date){
				if($begin_date>$from_date)
					$from_date = $begin_date;
			}
			if($end_date){
				if($end_date<$to_date)
					$to_date = $end_date;
			}
			
			if($from_date > $to_date){
				//$this->_log("Runner $runner->RUNNER_NAME and/or it's job is not configured to run in date range $sfrom_date to $sto_date", 1);
			}
			else{
				$this->_log("Begin runner $runner->RUNNER_NAME:", 2);
				if($runner->ALLOC_OIL == 1) $this->run_runner($runner, $from_date, $to_date, 1);
				if($runner->ALLOC_GAS == 1)	$this->run_runner($runner, $from_date, $to_date, 2);
				if($runner->ALLOC_WATER == 1) $this->run_runner($runner, $from_date, $to_date, 3);
				if($runner->ALLOC_GASLIFT == 1) $this->run_runner($runner, $from_date, $to_date, 21);
				if($runner->ALLOC_CONDENSATE == 1) $this->run_runner($runner, $from_date, $to_date, 5);
				$count++;
			}
    	}
    	if($count==0){
    		$this->_log("No runner to run!",2);
    		$this->_log("End job ID: $job_id =====================================================================",2);
    	}
    }

    private function fff()
    {
    	//echo "<b>Allocation request: ".$_REQUEST["act"]."</b><br>";
    }

    public function finalizeTask($task_id,$status,$log,$email){
    	if($task_id>0){

    		$now = Carbon::now();
    		$time = date('Y-m-d H:i:s', strtotime($now));

    		TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>$status, 'FINISH_TIME'=>$time, 'LOG'=>addslashes($log)]);

    		if($status==1){
    			//task finish, check next task
    			$objAll = new WorkflowProcessController(null, null);
    			$objAll->processNextTask($task_id);
    		}
    	}
    }

    private function _log($s,$type)
    {
    	$ret=true;
    	$h=$s;
    	if($type==1){
    		$h="<font color='red'>$s</font><br>";
    		$this->error_count++;
    		$ret=false;
    	}
    	else if($type==2)
    		$h="<font color='blue'>$s</font><br>";
    	else if($type==3)
    		$h=$s;
		else
			$h="$s<br>";
		$this->log.=$h;
		return $ret;
    }
}
