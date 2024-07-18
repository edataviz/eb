<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class TmWorkflowTask extends DynamicModel 
{ 
	protected $table = 'tm_workflow_task'; 
	
	public $timestamps = false;
	public $primaryKey  = 'id';
// 	protected $autoFillableColumns = false;
	
	protected $fillable  = [
			'wf_id', 'name', 'type', 'task_group', 'task_code', 'node_config', 'task_config', 
			'next_task_config', 'prev_task_config', 'runby', 'user', 'isbegin', 'isrun', 'result', 
			'cdate', 'status', 'log', 'start_time', 'finish_time'
	];	
} 