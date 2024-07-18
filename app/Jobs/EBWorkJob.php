<?php

namespace App\Jobs;

use App\Http\Controllers\WorkflowProcessController;
use App\Jobs\Job;
use App\Models\TmWorkflowTask;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EBWorkJob extends Job implements ShouldQueue, SelfHandling
{
	use InteractsWithQueue, SerializesModels;
    protected $param=[], $log, $error_count = 0, $alloc_act = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
	}

    public function finalizeTask($task_id,$status,$log,$email=""){
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
    	
    	if($this->log){
    		$emails			= explode( ';',$email);
    		$subjectName 	= ($this->error_count>0?"[ERROR] ":"")."Automatic task's log";
    		$data 			= ['content' => strip_tags($this->log)];
    		$this->log		.=\Helper::sendEmail($emails,$subjectName,$data);
    	}
    }

    protected function _log($s,$type){
    	$ret=true;
    	$h=$s;
    	if($type==1){
    		$h="<font color='red'>$s</font><br>";
    		$this->error_count++;
    		$ret=false;
    	}
    	else if($type==2)
    		$h="<font color='blue'>$s</font><br>";
    	else
    		$h="$s<br>";
    	$this->log.=$h;
    	return $ret;
    }
}
