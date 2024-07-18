<?php
namespace App\Jobs;
use App\Trail\ScheduleJobTrail;

class ScheduleImportNetworkDataJob extends importNetworkData {

	use ScheduleJobTrail {
        ScheduleJobTrail::__construct as private scheduleJobConstruct;
    }
	
	public function __construct($tmTask){
		$this->scheduleJobConstruct($tmTask);
		$taskConfig			= $tmTask->task_config;
		if ($taskConfig) {
			$param = [
				'taskid'		=> -2,
				'connection_id'	=>  $taskConfig['IntConnection'],
				'tagset_id'		=>  $taskConfig['IntTagSet'],
				'cal_method'	=>  $taskConfig['CalMethod'],
				'begin_time'	=>  isset($taskConfig['DATA_BEGINTIME'])?$taskConfig['DATA_BEGINTIME']:null,
				'end_time'		=>  isset($taskConfig['DATA_ENDTIME'])?$taskConfig['DATA_ENDTIME']:null,
			];
			$this->param = $param;
		}
		else 
			throw new \Exception("task not config parameters");
	}
	
	/* public function handle(){
		$result		= "test sending email";
		$this->log		= $result;
		$this->finalizeTask(0,($this->error_count>0?3:1),$this->log,"aaa@gmail.com");
		return $this->log;
	} */
}
