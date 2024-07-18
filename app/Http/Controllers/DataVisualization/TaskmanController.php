<?php

namespace App\Http\Controllers\DataVisualization;

use App\Http\Controllers\CodeController;
use App\Models\EbFunctions;
use App\Models\TmTask;

class TaskmanController extends CodeController {
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [ 
				'task_group' => [ 
						'column' 	=> 'task_code',
						'model' 	=> 'EbFunctions' 
				] 
		];
	}
	
	public function getFirstProperty($dcTable) {
		return [ 
				'data' => $dcTable,
				'title' => 'Command',
				'width' => 120 
		];
	}
	
	public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties) {
		$mdlName 	= $postData[config("constants.tabTable")];
    	$mdl 		= "App\Models\\$mdlName";
    	$date_end 	= $postData['date_end'];
    	$date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();

    	$columns	= $this->extractRespondColumns($dcTable,$properties);
    	if (!$columns) $columns = [];
    	array_push($columns,"$dcTable.ID as $dcTable",
							"$dcTable.id as ID",
    						"$dcTable.ID as DT_RowId",
    						"$dcTable.STATUS",
    						"$dcTable.CDATE as CDATE");
    	
     	$wheres 	= ['runby' => 1];
    	$dataSet 	= $mdl::where($wheres)
// 				    	->whereBetween('CDATE', [$occur_date,$date_end])
				    	->select($columns) 
  		    			->orderBy("$dcTable.CDATE")
  		    			->get();
    	
  		$extraDataSet 	= $this->getExtraDataSet($dataSet, null);
		return [ 
				'dataSet' 		=> $dataSet,
      			'extraDataSet'	=> $extraDataSet
		];
	}
	
	public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
		$data = null;
		switch ($sourceColumn) {
			case 'task_group':
				$group		= EbFunctions::findByCode($sourceColumnValue);
				if ($group) $data = $group->ExtensionEbFunctions();
				break;
		}
		return $data;
	}
	
	public function update($command,$id){
		$result			= ["CODE"	=>"ATTEMP_FAILT"];
		try{
			$task		= TmTask::find($id);
			if ($task) {
				switch ($command) {
					case "start":
						$task->start();
						break;
					case "stop":
						$task->stop();
						break;
					case "refresh":
						break;
					default:
					break;
				}
				$result["CODE"]		= "ATTEMP_SUCCESS";
				$result["task"]		= $task;
			}
		}
		catch (\Exception $e){
			\Log::info("\n--------------------------------\nException when init update task\n ");
			if (!$e) $e = new \Exception("Exception when init update task");
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
			return response($e->getMessage(), 400);
		}
		return response()->json($result);
	}
}