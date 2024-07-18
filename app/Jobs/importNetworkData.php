<?php

namespace App\Jobs;
use Carbon\Carbon;
use App\Http\Controllers\InterfaceController;

class importNetworkData extends EBWorkJob{
	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(){
		$task_id = 0;
		if(isset($this->param['taskid'])
				&&isset($this->param['connection_id'])
				&&isset($this->param['tagset_id'])
				&&isset($this->param['cal_method'])){
			$task_id 					= $this->param['taskid'];
			$this->param['update_db'] 	= true;
			if(isset($this->param['begin_time']) && isset($this->param['end_time'])){
				$bts 	= explode(':', $this->param['begin_time']);
				$bt_h 	= (isset($bts[0])?(int)$bts[0]:0);
				$bt_m 	= (isset($bts[1])?(int)$bts[1]:0);
				$bt_s 	= (isset($bts[2])?(int)$bts[2]:0);
				$ets 	= explode(':', $this->param['end_time']);
				$et_h 	= (isset($ets[0])?(int)$ets[0]:0);
				$et_m 	= (isset($ets[1])?(int)$ets[1]:0);
				$et_s 	= (isset($ets[2])?(int)$ets[2]:0);
				$now 	= Carbon::now();
				$beginDateTime 			= $now->copy();
				$endDateTime 			= $now->copy();
				$beginDateTime->hour 	= $bt_h;
				$beginDateTime->minute 	= $bt_m;
				$beginDateTime->second 	= $bt_s;
				$endDateTime->hour 		= $et_h;
				$endDateTime->minute 	= $et_m;
				$endDateTime->second 	= $et_s;
				if($beginDateTime->greaterThan($endDateTime)){
					$beginDateTime 		= $beginDateTime->subDay();
				}
				if($endDateTime->greaterThan($now)){
					$beginDateTime 		= $beginDateTime->subDay();
					$endDateTime 		= $endDateTime->subDay();
				}
				$this->param['date_begin'] 	= $beginDateTime;
				$this->param['date_end'] 	= $endDateTime;
			}
			else{
				$yesterday					= Carbon::now()->subDay();
				$startOfDay					= $yesterday->copy();
				$startOfDay->hour 			= 0;
				$startOfDay->minute 		= 0;
				$startOfDay->second 		= 0;
				$this->param['date_begin'] 	= $startOfDay;
				$endOfDay					= $yesterday->copy();
				$endOfDay->hour 			= 23;
				$endOfDay->minute 			= 59;
				$endOfDay->second 			= 59;
				$this->param['date_end'] 	= $endOfDay;
			}
			try{
				$itfController	= new InterfaceController();
				$result 		= $itfController->ip21ImportData($this->param);
			}catch (\Exception $e){
				$result			= $e->getMessage();
				\Log::info($result);
      			\Log::info($e->getTraceAsString());
			}
		}
		else{
			$result		= "Please check the task parameters";
		}
		$this->log		= $result;
		$this->finalizeTask($task_id,($this->error_count>0?3:1),$this->log);
		return $result;
	}
}
