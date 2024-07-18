<?php
namespace App\Jobs;

class ScheduleChekAllocation extends ScheduleRunAllocation {
	
	public function __construct($tmTask){
		parent::__construct($tmTask);
		$this->param['act'] = "check";
	}
	
}
