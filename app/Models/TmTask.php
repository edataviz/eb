<?php 
namespace App\Models; 
use App\Jobs\ScheduleChekAllocation;
use App\Jobs\ScheduleEuJob;
use App\Jobs\ScheduleEuTestJob;
use App\Jobs\ScheduleFlowJob;
use App\Jobs\ScheduleImportNetworkDataJob;
use App\Jobs\ScheduleRunAllocation;
use App\Jobs\ScheduleStorageJob;
use App\Jobs\ScheduleWorkflow;
use Carbon\Carbon;

 class TmTask extends EbBussinessModel 
{ 
	const STOPPED			= 0;
	const STARTING 			= 1;
	const READY 			= 2;
	const RUNNING			= 3;
	const CANCELLING		= 4;
	const DONE				= 5;
	const NONE				= 15;
	
	const RUN_BY_SYSTEM 	= 1;
	const RUN_BY_USER 		= 2;
	const EXTRA_TIME 		= 1380;
	
	protected $casts = [
			'task_config' => 'object',
			'TASK_CONFIG' => 'object',
			'time_config' => 'object',
			'TIME_CONFIG' => 'object',
	];
	
// 	protected $dateFormat = 'Y-m-d H:i:s';
	
	protected $table = 'TM_TASK';
// 	protected $dates = ["expire_date",'last_run','last_check','next_run',"cdate"];
	protected $fillable  = ['name',
							'runby',
							'user',
							'expire_date',
							'intro',
							'time_config',
							'task_group',
							'task_code',
							'task_config',
							'author',
							'count_run',
							'result',
							'cdate',
							'status',
							'last_run',
							'last_check',
							'next_run'];
	protected $shouldCheckExpireDate = false;
	
	public function setTaskConfigAttribute($value){
		$tacf = json_encode($value);
		$this->attributes['task_config'] = $tacf;
	}
	
	public function setTimeConfigAttribute($value){
		$ticf = json_encode($value);
		$this->attributes['time_config'] = $ticf;
	}
	
	public function getTimeConfigAttribute($value){
		return json_decode ( $value , true );
	}
	
	public function getTaskConfigAttribute($value){
		return json_decode ( $value , true );
	}
	
	/* public function correctData($column){
		if ($this->isOracleModel) {
			if ($column=='TASK_CONFIG'||$column=='TIME_CONFIG'){
				$this->$column 	= $this->$column;
			}
		}
	} */
	
    public static function loadStatus($option=null){
	    return collect([
			    		(object)['ID' =>	self::STOPPED		,'NAME' => 'STOPPED'   	],
			    		(object)['ID' =>	self::STARTING 		,'NAME' => 'STARTING'   ],
			    		(object)['ID' =>	self::READY 		,'NAME' => 'READY'   	],
			    		(object)['ID' =>    self::RUNNING	 	,'NAME' => 'RUNNING'   	],      
			    		(object)['ID' =>    self::CANCELLING  	,'NAME' => 'CANCELLING' ],  
			    		(object)['ID' =>    self::DONE			,'NAME' => 'DONE'   	],
	    				(object)['ID' =>    self::NONE			,'NAME' => ''   		],      
	    		
	    ]);
    }
    
    public static function loadActiveTask($option=null){
    	return self::whereIn("status",[	
										//self::STARTING 		,
										self::READY 		,
										//self::RUNNING	 	,
    									])
    			->get();
    }
    
    public static function updateReferenceFrom($referenceId){
    	if ($referenceId) {
	    	$tmTasks	= static::whereIn('status'	,[ self::RUNNING ,self::CANCELLING])
	    						->where("task_code","=","VIS_WORKFLOW")
	    						->get();
		    foreach($tmTasks as $key => $tmTask ){
		    	$task_config	= $tmTask->task_config;
		    	if ($task_config&&array_key_exists("TmWorkflow", $task_config)&&$task_config['TmWorkflow']==$referenceId) {
		    		$tmTask->stop();
		    		$time_config	= $tmTask->time_config;
		    		if ($time_config&&array_key_exists("FREQUENCEMODE", $time_config)&&$time_config['FREQUENCEMODE']!="ONCETIME") {
			    		$tmTask->start();
		    		}
		    	}
		    }
    	}
    }
    
	public function validateTaskCondition(){
		$validated	= $this->shouldRunByStatus();
		$validated	= $validated&&$this->shouldRunByTimeConfig();
		return $validated;
	}
	
	public function shouldRunByStatus(){
 		$validated	= 	$this->runby==self::RUN_BY_SYSTEM&&
 						($this->status==self::STARTING||$this->status==self::READY);
 		$tagLog 	= " runby {$this->runby} "." status {$this->status} ";
 		if ($this->shouldCheckExpireDate) {
 			$validated	= $validated&&(!$this->expire_date||($this->expire_date&&$this->expire_date->gt(Carbon::now())));
 			$tagLog .= " expire_date {$this->expire_date} ";
 		}
 		\Log::info('task with status is '.($validated ? 'valid' : 'invalid').$tagLog);
 		return $validated;
	}
	
	public function shouldRunByTimeConfig(){
		$should		= false;
		$timeConfig	= $this->time_config;
		if ($timeConfig) {
			$frequenceMode		= array_key_exists('FREQUENCEMODE'	, $timeConfig)?$timeConfig["FREQUENCEMODE"]	:"ONCETIME";
			$intervalDay		= array_key_exists('INTERVALDAY'	, $timeConfig)?$timeConfig["INTERVALDAY"]	:1;
			$startTime			= array_key_exists('StartTime'		, $timeConfig)&&$timeConfig["StartTime"]?	Carbon::parse($timeConfig["StartTime"])	:null;
			$endTime			= array_key_exists('EndTime'		, $timeConfig)&&$timeConfig["EndTime"]?		Carbon::parse($timeConfig["EndTime"])	:null;
			$dailyTime			= array_key_exists('DailyTime'		, $timeConfig)&&$timeConfig["DailyTime"]?	Carbon::parse($timeConfig["DailyTime"])	:null;
			$weekDays			= array_key_exists('WEEKDAY'		, $timeConfig)?$timeConfig["WEEKDAY"]		:[];
			$days				= array_key_exists('MONTHDAY'		, $timeConfig)?$timeConfig["MONTHDAY"]		:[];
			$months				= array_key_exists('MONTH'			, $timeConfig)?$timeConfig["MONTH"]			:[];
// 			$now				= Carbon::now('UTC');
			$now				= Carbon::now();
			$this->last_check	= $now;
			\Log::info("startTime $startTime endTime $endTime" );
			if ($startTime&&$now->lt($startTime)) {
				\Log::info('task should not run now due to now not pass start time');
				return false;				
			}
			if ($endTime&&$now->gt($endTime)){
				\Log::info('task should not run now due to end time is over');
				return false;
			}
			
			$lastRun				= $this->last_run?Carbon::parse($this->last_run):null;
			if ($dailyTime) {
				$todayTime			= $dailyTime->copy();
				$todayTime->hour 	= $now->hour;
				$todayTime->minute 	= $now->minute;
				$todayTime->second	= $now->second;
				$extraTime 			= $lastRun?self::EXTRA_TIME:2;
				if ($dailyTime->gt($todayTime)||($todayTime->diffInMinutes($dailyTime)>$extraTime)) {
					\Log::info("daily time {$dailyTime->hour}:{$dailyTime->minute} \t diffInMinutes {$todayTime->diffInMinutes($dailyTime)} ");
					\Log::info('task should not run now due to the running time is set');
					return false;
				}
				else{
					$todayTime			= $dailyTime->copy();
					$todayTime->year 	= $now->year;
					$todayTime->month 	= $now->month;
					$todayTime->day 	= $now->day;
					$this->last_check	= $todayTime;
				}
			}
			
			switch ($frequenceMode) {
				case "ONCETIME":
					$should		= true;
					/* if ($startTime)			{
						$should	=	$now->gte($startTime);
					}
					if ($should&&$endTime) 	{
						$should	=	$now->lte($endTime);
					} */
					break;
					
				case "DAILY":
					$gte = $lastRun?$now->subDays($intervalDay)->gte($lastRun):false;
					$should		= (!$lastRun || $gte);
					\Log::info("lastRun $lastRun intervalDay $intervalDay should run $should");
					break;
					
				case "WEEKLY":
// 					\Log::info("WEEKLY task weekDays  $weekDays dayOfWeek {$now->dayOfWeek}");
					$should		= array_search($now->dayOfWeek, $weekDays) !== false;
					$should		= $should && (!$lastRun || ($now->subDay()->gte($lastRun)));
					break;
				case "MONTHLY":
					//\Log::info("MONTHLY now {$now->dayOfWeek},{$now->day},{$now->month}");
					//\Log::info($weekDays);
					//\Log::info($days);
					//\Log::info($months);
					//\Log::info($lastRun);
					$should		= 	(array_search($now->dayOfWeek, $weekDays) !== false
									|| array_search($now->day, $days) !== false)
									&& array_search($now->month, $months) !== false;
					$should		= $should && (!$lastRun || ($now->subDay()->gte($lastRun)));
					break;
			}
		}
		\Log::info('task with time config is '.($should ? 'valid' : 'invalid'));
		return $should;
	}
	
	public function preRunTask($scheduleJob){
		\Log::info("preRunTask ".$this->name);
		if (!$this->count_run)$this->count_run = 0;
		$this->count_run	= $this->count_run+1;
		$this->status		= self::RUNNING;
		$this->save();
	}
	
	public function isOnetimeRunning(){
		$result = false;
		$timeConfig	= $this->time_config;
		if ($timeConfig) {
			$frequenceMode	= array_key_exists('FREQUENCEMODE'	, $timeConfig)?$timeConfig["FREQUENCEMODE"]	:"ONCETIME";
			$result			= $frequenceMode=="ONCETIME";
		}
		return $result;
	}

	public function afterRunTask($scheduleJob,$result){
		$this->last_run		= $this->last_check;
		$result	= $result?$result:"no return";
		//TODO check expire date
		if ($result instanceof \Exception) {
			$this->result	= "ERROR : ".$result->getMessage();
		}
		else if(is_string($result)){
			$result = strip_tags($result);
			if (strlen($result)>250) $result = substr($result,0,250);
			$this->result	= $result;
		}
		else{
			$this->result	= "RETURN object ";
		}
		
		if ($this->task_code=="VIS_WORKFLOW") {
		}
		else{
			if (($this->expire_date&&$this->expire_date->lte(Carbon::now())||
					$this->isOnetimeRunning())){
				$this->status	= self::DONE;
			}
			else{
				$this->status	= $this->command==self::CANCELLING?self::STOPPED:self::READY;
			}
		}
		$this->command	= 0;
		$this->save();
		\Log::info("afterRunTask ".$this->name." result ".$this->result);
	}
	
	public function stop(){
		$this->command		= self::CANCELLING;
		//if ($this->status	!= self::RUNNING) 
		$this->updateStopStatus();
		$scheduleJob = $this->initScheduleJob();
		if($scheduleJob) $scheduleJob->stop();
	}
	
	public function start(){
		$this->command		= self::STARTING;
		if ($this->status	!= self::RUNNING) {
			$this->status	= self::READY;
			$this->command	= self::NONE;
		}
		$this->save();
	}
	
	public function updateStopStatus(){
		$this->status	= self::STOPPED;
		$this->command	= self::NONE;
		$this->save();
	}
	
	public function initScheduleJob() {
		$scheduleJob = null;
		try{
			switch ($this->task_code) {
				case "ALLOC_RUN":
					$scheduleJob = new ScheduleRunAllocation($this);
					break;
				case "ALLOC_CHECK":
					$scheduleJob = new ScheduleChekAllocation($this);
					break;
				case "VIS_WORKFLOW":
					$scheduleJob = new ScheduleWorkflow($this);
					break;
				case "FDC_FLOW":
					$scheduleJob = new ScheduleFlowJob($this);
					break;
				case "FDC_EU":
					$scheduleJob = new ScheduleEuJob($this);
					break;
				case "FDC_EU_TEST":
					$scheduleJob = new ScheduleEuTestJob($this);
					break;
				case "FDC_STORAGE":
					$scheduleJob = new ScheduleStorageJob($this);
					break;
				case "INT_IMPORT_DATA":
					$scheduleJob = new ScheduleImportNetworkDataJob($this);
					break;
				default:
					throw  new \Exception("task code {$this->task_code} is not implemented for handle");
					break;
			}
		}
		catch (\Exception $e){
			\Log::info("\nException when init schedule job\n ");
			if (!$e) $e =  new \Exception("Exception when init schedule job. Please check task config");
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
// 			throw $e;
		}
		return $scheduleJob;
	}
	
 } 
