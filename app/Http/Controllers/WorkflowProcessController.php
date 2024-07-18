<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Mail;

use App\Jobs\autoSaveEnergyUnit;
use App\Jobs\autoSaveEuTest;
use App\Jobs\autoSaveFlow;
use App\Jobs\autoSaveStorage;
use App\Jobs\export;
use App\Jobs\runAllocation;
use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use App\Models\User;
use App\Models\TmTask;
use App\Models\Formula;

class WorkflowProcessController extends EBController {
	protected $tmworkflowtask=[], $task_id;
	
	public function __construct($task_id, $tmworkflowtask) {
		parent::__construct();
		$this->tmworkflowtask = $tmworkflowtask;
		$this->task_id = $task_id;
	}
	
	public function runTask($task_id,$r=null){
		\Log::info('runTask task_id :'.$task_id);
		
		if($task_id>0){
			$r = TmWorkflowTask::where(['ID'=>$task_id])->first();
		}
	
		if(!$r) {
			\Log::info('task not found');
			return;
		}
        $wf_id = null;
		if(isset($r->wf_id) && $r->wf_id) {
            $wf_id      = $r->wf_id;
            $workflow	= TmWorkflow::find($wf_id);
			if (!$workflow||$workflow->ISRUN!="yes") {
				\Log::info('task is not launched because workflow not running');
				return;
			}
		}
		
		$task_id=$r['id'];
		$now = Carbon::now('Europe/London');
		$time = date('Y-m-d H:i:s', strtotime($now));
		
		if($r['isbegin'] == 1){
			\Log::info('BEGIN');
			//BEGIN node
            if ($wf_id) $this->resetNodeConfig($wf_id);
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'START_TIME'=>$time, 'FINISH_TIME'=>$time]);
			$this->processNextTask($task_id);
		}
		else if($r['isbegin'] == -1){
			\Log::info('END');
			//END node
            if ($wf_id) $this->resetNodeConfig($wf_id);
            TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'START_TIME'=>$time, 'FINISH_TIME'=>$time]);
			$this->finishWorkflow($task_id);
		}
		else if(strpos($r->task_config,'formula_id') !== false){
			//CONDITION node
			$taskconfig=json_decode($r->task_config);
	
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>2, 'START_TIME'=>$time]);
	
			$date_type=$taskconfig->type;
			$formula_id=$taskconfig->formula_id;
			$cases=$taskconfig->condition;
			$from_date= isset($taskconfig->from)?$taskconfig->from:null;
			$to_date=isset($taskconfig->to)?$taskconfig->to:null;

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

			if(!$from_date) $occur_date	= Carbon::now();
			else $occur_date = \Helper::parseDate($from_date);
			
			\Log::info("Processing NODE_CONDITION: formula_id:$formula_id, date_type:$date_type, occur_date:$occur_date, conditions:".json_encode($taskconfig->condition).". ");

			$formula = Formula::find($formula_id);
			$r = \FormulaHelpers::evalFormula($formula, $occur_date);
			\Log::info("cases:");
			\Log::info($cases);
			\Log::info("Formula:");
			\Log::info($formula);
			\Log::info("evalFormula: $r");

			foreach ($cases as $case) {
				$match = 0;
				eval("\$match=$case->condition;");
				if($match) {
					$this->runTask($case->target_task_id);
				}
			}

			// $formula_id=$taskconfig->formula_id;
			// $type=$taskconfig->type;
			
			// if(isset($taskconfig->from)){
			// 	$from=explode('-',$taskconfig->from);
			// 	$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			// }else {
			// 	$from_date = null;
			// }
			
			// if(isset($taskconfig->to)){
			// 	$to=explode('-',$taskconfig->to);
			// 	$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			// }else{
			// 	$to_date = null;
			// }
			
			// $compare=1;
			// //$r=evalFormula($formula_id,false);
	
			// $conditions=$taskconfig->condition; //array
			// foreach ($conditions as $cond_item) {
			// 	$b=false;
			// 	$exp=str_replace("=","==",$cond_item->condition);
	
			// 	//echo '$b=('.$exp.');';
			// 	eval('$b=('.$exp.');');
			// 	if($b === $compare){
			// 		//echo "*************";
			// 		TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
	
			// 		$target_task_id=$cond_item->target_task_id;
			// 		if($target_task_id>0){
			// 			$this->runTask($target_task_id, null);
			// 			return;
			// 		}
			// 	}
			// }
		}
		else if($r->task_code == 'NODE_COMBINE' || (!$r->task_code && !$r->task_config)){
			//COMBINE node
            \Log::info("TASK NODE_COMBINE or ( task_code null & task_config null ) : $task_id");
			$prev_id=str_replace('NaN,','',$r->prev_task_config).'0';
			$finish = $this->check_prev_finish($prev_id,$task_id);
			\Log::info("preview task finish: $finish .");
            if($finish){
                TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
                $this->processNextTask($task_id);
			}
		}
		else{
			//task node
			\Log::info("TASK : $task_id");
			TmWorkflowTask::where(['ID'=>$task_id])->update(['ISRUN'=>2, 'FINISH_TIME'=>$time]);
			if($r->runby == 1){
				$this->sysRunTask($r);
			}
		}
		
		$shouldCheckEmailSending = $r['isbegin'] != 1 && $r['isbegin'] != -1 && isset($r->runby) && $r->runby == 2;
		if($shouldCheckEmailSending ){
			$usersString	= $r->user;
			if ($usersString) {
				$usersString	= rtrim($usersString,',');
				$users			= explode(',',$usersString);
				$emails			= User::whereIn("USERNAME",$users)->get(["EMAIL"])->pluck("EMAIL")->toArray();
				$taskName		= isset($r->name) ? $r->name : "UNDEFINED";
				if(isset($r->wf_id)) {
					$workflow	= TmWorkflow::find($r->wf_id);
					$workFlowName = $workflow?$workflow->NAME:"UNDEFINED";
				}
				else 
					$workFlowName	= "UNDEFINED";
				$subjectName 	= "Task [$taskName] in workflow [$workFlowName] is waiting for you to process";
				$data 			= ['content' => $subjectName];
	    		\Helper::sendEmail($emails,$subjectName,$data);
			}
		}
	}
	
	public function processNextTask($id){
		\Log::info('processNextTask  of :'.$id);
		$r = TmWorkflowTask::where(['ID'=>$id])->select('next_task_config')->first();
	
		$config_next_task=str_replace('NaN,','',$r['next_task_config']).'0';
		$ids=explode(',',$config_next_task);
        \Log::info("next tasks text : $config_next_task .");
		if($config_next_task == ''){
			\Log::info("no next task config_next_task $config_next_task");
			return -1;
		}
	
		if(count($ids) >= 1){
			$tmps = TmWorkflowTask::whereIn('ID', $ids)
			->whereNotIn('ID', [$id])
			->get();
			foreach ($tmps as $tmp){
				\Log::info("TASK $id -> processNextTask -> runTask  with  tmp (r)");
				$this->runTask(null,$tmp);
			}
		}
	}
	
	public function finishWorkflow($task_id){
		$r = TmWorkflowTask::where(['ID'=>$task_id])->select('wf_id')->first();
		if($r){
			TmWorkflow::where(['ID'=>$r['wf_id']])->update(['ISRUN'=>'no']);
			TmTask::updateReferenceFrom($r['wf_id']);
		}
	}
	
	private function check_prev_finish($prev_id,$task_id=null){
        \Log::info("previous tasks text : $prev_id .");
		$ids=explode(',',$prev_id);
		if(count($ids) <= 1) return true;
        if ($task_id){
            $ignore = false;
            $result = null;
            try{
                $result = \DB::transaction(function () use ($task_id,$ids){
                    $r    = TmWorkflowTask::where(['ID'=>$task_id])->first();
                    \Log::info("NODE_CONFIG before ".$r->node_config);
                    $nodeConfigConcat	= config('database.default')==='oracle'?
                        \DB::raw("CASE
                                            WHEN node_config IS NULL
                                            THEN '1$1' 
                                            ELSE (concat(node_config,'1$1'))
                                        END"):
                        (config('database.default')==='sqlsrv'?
                            \DB::raw("CASE 
                                                WHEN node_config IS NULL
                                                THEN '1$1' 
                                                ELSE (node_config+'1$1')
                                            END"):
                            \DB::raw("CASE 
                                                WHEN node_config IS NULL
                                                THEN '1$1' 
                                                ELSE (concat(node_config,'1$1'))
                                            END"));
                    TmWorkflowTask::where(['ID'=>$task_id])->update(['node_config' => $nodeConfigConcat]);
                    $r    = TmWorkflowTask::where(['ID'=>$task_id])->first();
                    $taskFinishedCount = $r->node_config?count(explode('$',$r->node_config))-1:0;
                    if (($key = array_search('0', $ids)) !== false) unset($ids[$key]);
                    if (($key = array_search(0, $ids)) !== false) unset($ids[$key]);
                    \Log::info("NODE_CONFIG after ".$r->node_config." .");
                    \Log::info("$taskFinishedCount previous task finished. all tasks need to be finished :".count($ids));
                    $result = count($ids) <= $taskFinishedCount;
                    if ($result){
                        $r->node_config = null;
                        $r->save();
                    }
                    return $result;
                });
            }
            catch (\Exception $e){
                if (!$e) $e = new \Exception("Exception when check previous task finish");
                \Log::info($e->getMessage());
                \Log::info($e->getTraceAsString());
                $ignore = true ;
            }
            if(!$ignore && is_bool($result)) return $result;
        }

		$tmp = TmWorkflowTask::whereIn('ID', $ids)
		->where('ISRUN', '<>', 1)
		->where('TASK_CODE', '<>', 'CONDITION_BLOCK')
		->where('TASK_CODE', '<>', '')
		->whereNotNull('TASK_CODE')->first();
	
		if($tmp){
			return false;
		}else{
            return true;
		}
	}
	
	public function sysRunTask($r){
		$taskname=$r['task_code'];
		$taskid=$r['id'];
		\Log::info("sysRunTask taskid : $taskid , taskname : $taskname");
		
		$taskconfig=json_decode($r['task_config']);
		$taskconfig->task_id = $taskid;
		$taskconfig->task_name = $taskname;
	
		if($taskname == 'ALLOC_RUN'){
			$job_id=$taskconfig->jobid;
			$type=$taskconfig->type;
			if(isset($taskconfig->from)){
				//$from=explode('-',$taskconfig->from);
				//$from_date=$from[1].'-'.$from[2].'-'.$from[0];
				$from_date = $taskconfig->from;
			}
			else{
				$from_date="null";
			}
			if(isset($taskconfig->to)){
				//$to=explode('-',$taskconfig->to);
				//$to_date=$to[1].'-'.$to[2].'-'.$to[0];
				$to_date = $taskconfig->to;
			}
			else{
				$to_date="null";
			}
			$email=$taskconfig->email;
			$alloc_act = 'run';
			 
			$param = [
					'taskid'=> $taskid,
					'act'=> $alloc_act,
					'job_id'=> $job_id,
					'type'=> $type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			/* $obj = new run($param);
			 $obj->handle(); */
	
			$job =(new runAllocation($param));
			$this->dispatch($job);
		}
		else if($taskname=='ALLOC_CHECK'){
			$job_id=$taskconfig->jobid;
			$type=$taskconfig->type;
			if(isset($taskconfig->from)){
				//$from=explode('-',$taskconfig->from);
				//$from_date=$from[1].'-'.$from[2].'-'.$from[0];
				$from_date = $taskconfig->from;
			}
			else{
				$from_date="null";
			}
			if(isset($taskconfig->to)){
				//$to=explode('-',$taskconfig->to);
				//$to_date=$to[1].'-'.$to[2].'-'.$to[0];
				$to_date = $taskconfig->to;
			}
			else{
				$to_date="null";
			}

			$email=$taskconfig->email;
			$alloc_act = 'check';
			 
			$param = [
					'taskid'=> $taskid,
					'act'=> $alloc_act,
					'job_id'=> $job_id,
					'type'=> $type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			$job = (new runAllocation($param));
			$this->dispatch($job);
		}
		else if($taskname=='VIS_REPORT'){
			$job = (new export($taskconfig));
			$this->dispatch($job);
		}
		else if($taskname=='INT_IMPORT_DATA'){
			$conn_id=$taskconfig->conn_id;
			$tagset_id=$taskconfig->tagset_id;
			$type=$taskconfig->type;
			$from=explode('-',$taskconfig->from);
			$from_date=$from[1].'-'.$from[2].'-'.$from[0];
			$to=explode('-',$taskconfig->to);
			$to_date=$to[1].'-'.$to[2].'-'.$to[0];
			$email=$taskconfig->email;
			execInBackground("..\\..\\interface\\pi.php $taskid $conn_id $tagset_id $type $from_date $to_date $email");
			//header('location:'."../../report/export.php?report_id={$id}&type=PDF&date_from={$from_date}&date_to={$to_date}&facility_id={$facility}&email={$email}");
		}
		else if($taskname=='FDC_FLOW'){
			$type=$taskconfig->type;
			$facility=$taskconfig->facility;
			$freq=$taskconfig->freq;
			$phase_type=$taskconfig->phase_type;
			$email=isset($taskconfig->email)?$taskconfig->email:null;
			$from_date=isset($taskconfig->from)?$taskconfig->from:null;
			$to_date=isset($taskconfig->to)?$taskconfig->to:null;
			
			//execInBackground("..\\..\\dc\\flow-save.php $taskid $type $facility $freq $phase_type $from_date $to_date $email");

			$param = [
					'taskid'=> $taskid,
					'type'=> $type,
					'facility'=> $facility,
					'record_freq'=> $freq,
					'phase_type'=> $phase_type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			$job =(new autoSaveFlow($param));
			$this->dispatch($job);
		}
		else if($taskname=='FDC_EU'){
			$type=$taskconfig->type;
			$facility=$taskconfig->facility;
			$eugroup_id=$taskconfig->eugroup_id;
			$freq=$taskconfig->freq;
			$phase_type=$taskconfig->phase_type;
			$event_type=$taskconfig->event_type;
			$alloc_type=$taskconfig->alloc_type;
			$plan_type=$taskconfig->plan_type;
			$forecast_type=$taskconfig->forecast_type;
			$email=isset($taskconfig->email)?$taskconfig->email:null;
			$from_date=isset($taskconfig->from)?$taskconfig->from:null;
			$to_date=isset($taskconfig->to)?$taskconfig->to:null;
			//execInBackground("..\\..\\dc\\eu-save.php $taskid $type $facility $eugroup_id $freq $phase_type $event_type $alloc_type $plan_type $forecast_type $from_date $to_date $email");

			$param = [
					'taskid'=> $taskid,
					'type'=> $type,
					'facility'=> $facility,
					'eugroup_id'=> $eugroup_id,
					'record_freq'=> $freq,
					'phase_type'=> $phase_type,
					'event_type'=> $event_type,
					'alloc_type'=> $alloc_type,
					'plan_type'=> $plan_type,
					'forecast_type'=> $forecast_type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			$job =(new autoSaveEnergyUnit($param));
			$this->dispatch($job);
		}
		else if($taskname=='FDC_STORAGE'){
			$type=$taskconfig->type;
			$facility=$taskconfig->facility;
			$product_type=$taskconfig->product_type;
			$email=isset($taskconfig->email)?$taskconfig->email:null;
			$from_date=isset($taskconfig->from)?$taskconfig->from:null;
			$to_date=isset($taskconfig->to)?$taskconfig->to:null;
			//execInBackground("..\\..\\dc\\storage-save.php $taskid $type $from_date $to_date $email $facility $product_type");
			$param = [
					'taskid'=> $taskid,
					'type'=> $type,
					'facility'=> $facility,
					'product_type'=> $product_type,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			$job =(new autoSaveStorage($param));
			$this->dispatch($job);
		}
		else if($taskname=='FDC_EU_TEST'){
			$type=$taskconfig->type;
			$facility=$taskconfig->facility;
			$eu_id=$taskconfig->eu_id;
			$email=isset($taskconfig->email)?$taskconfig->email:null;
			$from_date= isset($taskconfig->from)?$taskconfig->from:null;
			$to_date=isset($taskconfig->to)?$taskconfig->to:null;
			//execInBackground("..\\..\\dc\\eutest-save.php $taskid $type $from_date $to_date $email $facility $eu_id");
			$param = [
					'taskid'=> $taskid,
					'type'=> $type,
					'facility'=> $facility,
					'eu_id'=> $eu_id,
					'from_date'=> $from_date,
					'to_date'=> $to_date,
					'email'=> $email
			];
	
			$job =(new autoSaveEuTest($param));
			$this->dispatch($job);
		}
		/*else if($taskname=='NODE_CONDITION'){
			$date_type=$taskconfig->type;
			$formula_id=$taskconfig->formula_id;
			$cases=$taskconfig->condition;
			$from_date= isset($taskconfig->from)?$taskconfig->from:null;
			$to_date=isset($taskconfig->to)?$taskconfig->to:null;

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

			if(!$from_date) $occur_date	= Carbon::now();
			else $occur_date = \Helper::parseDate($from_date);
			
			\Log::info("Processing NODE_CONDITION: formula_id:$formula_id, date_type:$date_type, occur_date:$occur_date, conditions:".json_encode($taskconfig->condition).". ");

			$formula = Formula::find($formula_id);
			$r = \FormulaHelpers::evalFormula($formula, $occur_date);
			\Log::info("cases:");
			\Log::info($cases);
			\Log::info("Formula:");
			\Log::info($formula);
			\Log::info("evalFormula: $r");

			foreach ($cases as $case) {
				$match = 0;
				eval("\$match=$case->condition;");
				if($match) {
					$this->runTask($case->target_task_id);
				}
			}
		}*/
		else if($taskname == 'EMAIL'){
			$from = $taskconfig->from;
			$to = $taskconfig->to;
			$subject = $taskconfig->subject;
			$content = $taskconfig->content;
	
			if (filter_var($from, FILTER_VALIDATE_EMAIL) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
				try
				{
					$data = ['content' => $content];
					$ret = Mail::send('front.sendmail',$data, function ($message) use ($from, $subject, $to) {
						$message->from($from, config('mail.fromName'));
						$message->to($to)->subject($subject);
					});
				}catch (\Exception $e)
				{
					\Log::info($e->getMessage());
				}
			}
		}
		else{
            $now = Carbon::now();
            $time = date('Y-m-d H:i:s', strtotime($now));
            $log = "no process for config of task $taskid";
            \Log::info($log);
            TmWorkflowTask::where(['ID'=>$taskid])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time, 'LOG'=>addslashes($log)]);
            \Log::info("=====> next task");
            $this->processNextTask($taskid);
        }
	}

    private function resetNodeConfig($wf_id){
        TmWorkflowTask::where(['wf_id'=>$wf_id])
            ->where(function ($query) {
                $query->where("task_code",'=','NODE_COMBINE')
                        ->orWhere(function ($query2) {
                            $query2->whereNull("task_code")
                                    ->WhereNull("task_config");
                        });
            })
            ->update(['node_config'=> null]);
    }
}