<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkflowProcessController;
use App\Models\TmWorkflowTask;
use App\Models\RptReport;
use App\Models\RptParam;
use  DB, Carbon\Carbon, Mail;

class export extends Job implements ShouldQueue, SelfHandling
{
    use InteractsWithQueue, SerializesModels;
    
    protected $param;

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
    public function handle()
    {
		$taskconfig=$this->param;
		\Log::info("******export job handle start");
		
		$task_id = $taskconfig->task_id;
		$report_id = $taskconfig->reportid;
		$date_type = $taskconfig->type;
		$exportType = (property_exists($taskconfig, 'export') ? ($taskconfig->export ? $taskconfig->export : 'PDF') : 'PDF');

		if($date_type=="day0"){
			$from_date = date('Y-m-d')."";
			$to_date = $from_date;
		}
		else if($date_type=="month0"){
			$from_date = date('Y-m-01')."";
			$to_date = $from_date;
		}
		else if($date_type=="day"){
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') .' -1 day'))."";
			$to_date = $from_date;
		}
		else if($date_type=="month"){
			$from_date = date('Y-m-d', strtotime(date('Y-m-01') .' -1 month'))."";
			$to_date = $from_date;
		}
		else{
			$from_date = isset($taskconfig->from)?$taskconfig->from:null;
			$to_date = isset($taskconfig->to)?$taskconfig->to:null;
		}
		$email = $taskconfig->email;
		$ch = (strpos($email, ',') !== false?',':';');
		$emails = explode($ch,$email);
		
		try
		{
			\Log::info("******export job handle genReport start");
			$report_file = $report_id;
			$isOracle = config('database.default')==='oracle';
			if($isOracle) $originAttrCase = \Helper::setGetterUpperCase();
			$report = RptReport::where(['FILE'.($isOracle?'_':'') => $report_file])->select("ID", "NAME")->first();
			$rows = RptParam::where('REPORT_ID','=', $report->ID)
				->select("CODE", "VALUE_TYPE")
				->orderBy('ORDER')
				->get();
			$params = "";
			$data = [];
			foreach ($rows as $row){
				$param_name = $row->CODE;
				$param_lowcase = strtolower($param_name);
				$param_type = $row->VALUE_TYPE;
				$data[$param_name] = null;
				if($param_type==3 || $param_type==5 || $param_type==6){
					if(strpos($param_lowcase, 'end') !== false || strpos($param_lowcase, 'to') !== false) {
						$taskconfig->$param_name = $to_date;
					}
					else
						$taskconfig->$param_name = $from_date;
				}
				if(isset($taskconfig->$param_name)){
					$params .= "&{$param_name}__T_{$param_type}={$taskconfig->$param_name}";
					$data[$param_name] = $taskconfig->$param_name;
					if(strpos($param_lowcase, 'email') !== false) {
						$data['email'] = $taskconfig->$param_name;
					}
				}
			}
			if (count($emails)>0) {
				$reportName = is_object($report) ? $report->NAME : 'Report';
				$reportDate = ($from_date ? $from_date : date('Y-m-d', strtotime(date('Y-m-d') .' -1 day')));
				$exportFilename = preg_replace('/[^a-zA-Z0-9\-\s_]/','', $reportName) . " " . $reportDate;
				$url = "keep_output_file=1&export=$exportType&filename=$exportFilename&file=$report_file{$params}";
				\Log::info("genReport: ".$url);
				$filePath = (new ReportController)->genReport($url);
				\Log::info($filePath);

				$data['ReportName'] = $reportName;
				$data['ReportDate'] = $reportDate;
				$data['date'] = date('d/m/Y');
				$data['datetime'] = date('d/m/Y H:i:s');

				$subject = "$reportName $reportDate";
				
				\Log::info("******export job handle sendEmail start");
				$template = "front.email.$report_file";
				if(!view()->exists($template)){
					$template = 'front.email.report_default';
				}				
				$ret=\Helper::sendEmail($emails,$subject,$data,$template,$filePath);
				unlink($filePath);
			}
		}
		catch (\Exception $e){
			\Log::info("******export job handle error genReport or email sending");
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
		}
		if(isset($originAttrCase)) \Helper::setGetterCase($originAttrCase);
		if($task_id>0){
			$this->finalizeTask($task_id,1,null,null);
		}
		\Log::info("******export job handle end");
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
}
