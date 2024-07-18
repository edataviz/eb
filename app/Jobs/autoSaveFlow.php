<?php

namespace App\Jobs;

use App\Models\Flow;
use DB;

class autoSaveFlow extends EBWorkJob {

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		//All dates in 'Y-m-d' format
		//\Log::info ($this->param);
		$task_id = 0;
		if(isset($this->param['taskid'])){
			$task_id = $this->param['taskid'];
			$date_type = $this->param['type'];			
			$facility_id = $this->param['facility'];
			$record_freq = $this->param['record_freq'];
			$flow_phase = $this->param['phase_type'];
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
			$this->_log("from_date: $from_date, to_date: $to_date",2);	
		}
		if(!$task_id){
    		$this->_log("Unknown task to perform",1);
    		exit();
		}

    	\Log::info ("date: $from_date, $to_date");
		$occur_date=$from_date;

		//Get object Ids
		$occur_date = $from_date;
    	$obj = Flow::getTableName();
    	 
    	$where = ['FACILITY_ID' => $facility_id, 'FDC_DISPLAY' => 1];
    	if ($record_freq>0) $where["$obj.RECORD_FREQUENCY"]= $record_freq;
		$ds=explode("-",$occur_date);
		$day=$ds[2];
		$FREQ_MONTH=6;
		if($day != 1) $where[]= ["$obj.RECORD_FREQUENCY",'<>',$FREQ_MONTH];

//      	\DB::enableQueryLog();
    	$dataSet = Flow::where($where)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	->select(
				    			"$obj.ID as OBJECT_ID"
				    			) 
  		    			->get();
		$objectIds = [];
		foreach($dataSet as $row){
			$objectIds[] = $row->OBJECT_ID;
		}
		$objectIds = array_unique($objectIds);
		
		//Save data
		if($from_date && $to_date){
			$d1 = $from_date;
			$d2 = $to_date;
			while (strtotime($d1) <= strtotime($d2)) {
				$occur_date=$d1;
				$this->saveData($occur_date,$facility_id,$record_freq,$flow_phase,$objectIds);
				$d1 = date ("Y-m-d", strtotime("+1 day", strtotime($d1)));
			}
		}

		$this->_log("Finish at ".date('m/d/Y h:i:s a', time()).($this->error_count>0?" <font color='red'>({$this->error_count} error)</font>":" (no error)"),2);
		if($task_id>0){
			$this->finalizeTask($task_id,($this->error_count>0?3:1),$this->log,$email);
		}else{
			//\Log::info($this->log);
			return $this->log;
		}
	}

	function saveData($occur_date,$facility_id,$record_freq,$flow_phase,$objectIds){
		//check date
		$ds=explode("-",$occur_date);
		$day=$ds[2];
		$month=$ds[1];
		$year=$ds[0];
		if(!($day>=1 && $day<=31 && $month>=1 && $month<=12 && $year>=1900 && $year<=3000)){
			$this->_log("Wrong occur date ($occur_date)",1);
			return;
		}
		//CHECK DATA LOCKED
		$islocked = [];
		$tables = ["FLOW_DATA_FDC_VALUE","FLOW_DATA_VALUE","FLOW_DATA_THEOR","FLOW_DATA_ALLOC","FLOW_DATA_PLAN","FLOW_DATA_FORECAST"];
		foreach($tables as $table){
			$islocked[$table] = \Helper::checkLockedTable($table,$occur_date,$facility_id);
			if($islocked[$table]){
				echo "Table locked ($table, date: $occur_date, facility_id: $facility_id)";
				$this->_log("Table locked ($table, date: $occur_date, facility_id: $facility_id)",2);
			}
		}
		/*
		foreach($tables as $table){
			if(!$islocked[$table])
				doFormula($table,"id",getRowIDs($table));
		}
		*/
		
		foreach($tables as $table)
		if(!$islocked[$table]){
			$fo_mdlName = \Helper::camelize(strtolower ($table),'_');
			\FormulaHelpers::applyFormula($fo_mdlName,$objectIds,$occur_date);
		}
		$this->_log("saveData $occur_date",2);
	}
}
