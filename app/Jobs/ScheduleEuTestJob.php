<?php
namespace App\Jobs;
use App\Trail\ScheduleJobTrail;

class ScheduleEuTestJob extends autoSaveEuTest {

	use ScheduleJobTrail {
        ScheduleJobTrail::__construct as private scheduleJobConstruct;
    }
	
	public function __construct($tmTask){
		$this->scheduleJobConstruct($tmTask);
		$taskConfig			= $tmTask->task_config;
		if ($taskConfig) {
			$type				= $taskConfig['STARTTIME']["type"];
			$facility			= $taskConfig['Facility'];
			$eu_id				= $taskConfig['EnergyUnit'];
			$from_date			= $taskConfig['STARTTIME']["value"];
			$to_date			= $taskConfig['ENDTIME']["value"];
			$email				= $taskConfig['SENDLOG'];
			
			$param = [
				'taskid'		=> -2,
				'type'			=> $type,
				'facility'		=> $facility,
				'eu_id'			=> $eu_id,
				'from_date'		=> $from_date,
				'to_date'		=> $to_date,
				'email'			=> $email,
			];
			$this->param = $param;
		}
		else throw new \Exception("task not config parameters");
	}
}
