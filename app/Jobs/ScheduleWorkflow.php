<?php
namespace App\Jobs;
use App\Http\Controllers\DVController;
use App\Models\TmWorkflow;
use App\Trail\ScheduleJobTrail;

class ScheduleWorkflow extends Job {

	use ScheduleJobTrail;
	
	public function shouldRun(){
		return true;
		///////////////////////////
		$should			= false;
		$tmWorkflowId	= $this->getTmWorkflowId();
		if ($tmWorkflowId) {
			$tmWorkflow	= TmWorkflow::find($tmWorkflowId);
			if ($tmWorkflow) {
				$should	= $tmWorkflow->ISRUN=='no';
				\Log::info("tmWorkflowId $tmWorkflowId ISRUN ".$tmWorkflow->ISRUN." should $should");
			}
		}
		else
			\Log::info("tmWorkflowId $tmWorkflowId not found");
		return $should;
	}
	
	public function handle() {
		$tmWorkflowId	= $this->getTmWorkflowId();
		if ($tmWorkflowId) {
			$dvController = new DVController;
			$dvController->runWorkFlowId($tmWorkflowId);
			return "handle success";
		}
		return "handle nothing : TmWorkflow id is not specified";
	}
	
	public function stop(){
		$tmWorkflowId	= $this->getTmWorkflowId();
		if ($tmWorkflowId) {
			$dvController = new DVController;
			$dvController->stopWorkFlowId($tmWorkflowId);
			$this->tmTask->updateStopStatus();
			return "stop success";
		}
		return "stop nothing : TmWorkflow id is not specified";
	}
	
	public function getTmWorkflowId(){
		$tmWorkflowId	= null;
		$taskConfig		= $this->tmTask->task_config;
		if ($taskConfig&&is_array($taskConfig)&&array_key_exists("TmWorkflow", $taskConfig)) {
			$tmWorkflowId 	= $taskConfig["TmWorkflow"];
		}
		else
			\Log::info("could not receive tmworkflowid from taskConfig");
			
		return $tmWorkflowId;
	}
}
