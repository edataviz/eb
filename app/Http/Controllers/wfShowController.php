<?php

namespace App\Http\Controllers;
use App\Models\EbFunctions;
use App\Models\TmWorkflow;
use App\Models\TmWorkflowTask;
use Carbon\Carbon;
use Illuminate\Http\Request;

class wfShowController extends EBController {
	
	public function loadData() {
		$tmworkflow = $this->getTmworkflow();
		return view ( 'front.wfshow',  ['tmworkflow'=>$tmworkflow]);
	}
	
	public function getTmworkflow(){
		\Helper::setGetterUpperCase();
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
		
		$tableWf = TmWorkflow::getTableName();
		$tableTask = TmWorkflowTask::getTableName();
		$tmworkflow = TmWorkflow::where(['ISRUN'=>'yes'])
			->whereExists(function ($query) use($tableWf, $tableTask, $user_name) {
				$user_field = "$tableTask.USER".($this->isReservedName?'_':'');
				$query->select("$tableTask.ID")
					->from($tableTask)
					->where("$tableTask.WF_ID", "=", "$tableWf.ID")
					->whereIn("$tableTask.ISRUN", [2,3])
					->where ( function ($q) use ($user_field, $user_name) {
						$q->where($user_field, 'like', '%,'.$user_name.',%')
						->orWhere($user_field, 'like', $user_name.',%')
						->orWhere($user_field, 'like', '%,'.$user_name)
						->orWhere($user_field, '=', $user_name);
					});
			})
			->get(['ID', 'NAME']);
		
		return $tmworkflow;
	}
	
	public function reLoadtTmworkflow(){
		$tmworkflow = $this->getTmworkflow();
		return response ()->json ( $tmworkflow );
	}
	
	public function finish_workflowtask(Request $request){
		$data = $request->all ();
	
		$now = Carbon::now('Europe/London');
		$time = date('Y-m-d H:i:s', strtotime($now));
	
		TmWorkflowTask::where(['ID'=>$data['ID']])->update(['ISRUN'=>1, 'FINISH_TIME'=>$time]);
	
		$objRun = new WorkflowProcessController(null, null);
		$objRun->processNextTask($data['ID']);
		
		return response ()->json ( ['ok'=>'OK'] );
	}
	
	public function openTask(Request $request){
		$data = $request->all ();
	
		if($data['act'] == "opentask"){
			$taskcode = $data['taskcode'];
			$ebfunctions = EbFunctions::where(['CODE'=>$taskcode])->select('PATH')->first();
			$url = $ebfunctions['PATH'];
			
			return response ()->json ( ['url'=>$url] );
		}
	}
	
	public function countWorkflowTask(){		
		$user_name = '';
		if((auth()->user() != null)){
			$user_name = auth()->user()->username;
		}
					
		$tmworkflow = collect(TmWorkflow::where(['ISRUN'=>'yes'])
		->get(['ID']))->toArray();
			
		$tmworkflow = collect(TmWorkflow::where(['ISRUN'=>'yes'])
		->get(['ID']))->toArray();

		$tmworkflowtask = TmWorkflowTask::whereIn('ISRUN', [2,3])
			->whereIn('WF_ID', $tmworkflow)
			->where ( function ($q) use ($user_name) {
				$q->where('USER'.($this->isReservedName?'_':''), 'like', '%,'.$user_name.',%');
				$q->orWhere('USER'.($this->isReservedName?'_':''), 'like', $user_name.',%');
				$q->orWhere('USER'.($this->isReservedName?'_':''), 'like', '%,'.$user_name);
				$q->orWhere('USER'.($this->isReservedName?'_':''), '=', $user_name);
			} )
			->get(['WF_ID']);

		
		return response ()->json ( count($tmworkflowtask) );
	}
}