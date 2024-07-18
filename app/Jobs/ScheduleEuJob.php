<?php
namespace App\Jobs;
use App\Trail\ScheduleJobTrail;

class ScheduleEuJob extends autoSaveEnergyUnit {

	use ScheduleJobTrail {
        ScheduleJobTrail::__construct as private scheduleJobConstruct;
    }
	
	public function __construct($tmTask){
		$this->scheduleJobConstruct($tmTask);
		$taskConfig			= $tmTask->task_config;
		if ($taskConfig) {
			$type				= $taskConfig['STARTTIME']["type"];
			$facility			= $taskConfig['Facility'];
			$freq				= $taskConfig['CodeReadingFrequency'];
			$phase_type			= $taskConfig['CodeFlowPhase'];
			$from_date			= $taskConfig['STARTTIME']["value"];
			$to_date			= $taskConfig['ENDTIME']["value"];
			$email				= $taskConfig['SENDLOG'];
			$eugroup_id			= $taskConfig['EnergyUnitGroup'];
			$event_type			= $taskConfig['CodeEventType'];
			$alloc_type			= $taskConfig['CodeAllocType'];
			$plan_type			= $taskConfig['CodePlanType'];
			$forecast_type		= $taskConfig['CodeForecastType'];
			
			$param = [
				'taskid'		=> -2,
				'type'			=> $type,
				'facility'		=> $facility,
				'record_freq'	=> $freq,
				'phase_type'	=> $phase_type,
				'from_date'		=> $from_date,
				'to_date'		=> $to_date,
				'email'			=> $email,
				'eugroup_id'	=> $eugroup_id,
				'event_type'	=> $event_type,
				'alloc_type'	=> $alloc_type,
				'plan_type'		=> $plan_type,
				'forecast_type'	=> $forecast_type,
			];
			$this->param = $param;
		}
		else throw new \Exception("task not config parameters");
	}
}
