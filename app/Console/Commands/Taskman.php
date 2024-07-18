<?php namespace App\Console\Commands;

use App\Models\TmTask;
use Illuminate\Console\Command;

class Taskman extends Command {

	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'taskman';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'task manager';

	protected $scheduleJobs = [];
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$tmTasks	= TmTask::loadActiveTask();
		if ($tmTasks) {
		\Log::info('queue task number '.$tmTasks->count());
			$tmTasks->each(function ($tmTask, $key){
	  			\Log::info("key:$key name:{$tmTask->name}");
				$scheduleJob = $this->initScheduleJob($tmTask);
				if ($scheduleJob) {
					if ($scheduleJob->shouldRun()) {
						$tmTask->preRunTask($scheduleJob);
						try {
	 						$result = $scheduleJob->handle();
						} catch (\Exception $e) {
							$result = $e;
							\Log::info('exception when handle schedule job');
							\Log::info($e->getMessage());
							\Log::info($e->getTraceAsString());
						}
						$tmTask->afterRunTask($scheduleJob,$result);
					}
					else
						\Log::info('the task should not run this time');
				}
				else
					\Log::info('could not init schedule job');
				
			});
		}
		else 
			\Log::info('queue task number : empty ');
	}
	
	public function initScheduleJob($tmTask) {
		$scheduleJob	= null;
		try {
			$validated		= $tmTask->validateTaskCondition();
			if ($validated){
				$scheduleJob = $this->getScheduleJob($tmTask);
				if (!$scheduleJob) $scheduleJob	= $tmTask->initScheduleJob();
			}
			else
				\Log::info('task is invalid for running');
		} catch (\Exception $e) {
			$result = $e;
			\Log::info('exception when initScheduleJob');
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
			$scheduleJob = null;
		}
		if ($scheduleJob) $this->scheduleJobs[$tmTask->ID]	= $scheduleJob;
		return $scheduleJob;
	}
	
	public function getScheduleJob($tmTask) {
		if (array_key_exists($tmTask->ID, $this->scheduleJobs)){
			$scheduleJob	= $this->scheduleJobs[$tmTask->ID];
			$scheduleJob->setTask($tmTask);
			return $scheduleJob;
		}
		return null;
	}
}
