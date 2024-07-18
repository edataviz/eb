<?php
namespace App\Jobs;
use App\Trail\ScheduleJobTrail;

class ScheduleRunAllocation extends runAllocation {
	
	use ScheduleJobTrail {
        ScheduleJobTrail::__construct as private scheduleJobConstruct;
    }
	
	public function __construct($tmTask){
		$this->scheduleJobConstruct($tmTask);
		$taskConfig			= $tmTask->task_config;
		if ($taskConfig) {
			$job_id				= $taskConfig['AllocJob'];
			$email				= $taskConfig['SENDLOG'];
			$type				= $taskConfig['STARTTIME']["type"];
			$from_date			= $taskConfig['STARTTIME']["value"];
			$to_date			= $taskConfig['ENDTIME']["value"];
			$param = [
					'taskid'	=> -2,
					'act'		=> "run",
					'job_id'	=> $job_id,
					'type'		=> $type,
					'from_date'	=> $from_date,
					'to_date'	=> $to_date,
					'email'		=> $email
			];
			$this->param = $param;
		}
		else throw new \Exception("task not config parameters");
	}
	
    public function finalizeTask($task_id,$status,$log,$email){
    }
}
