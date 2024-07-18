<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\EnergyUnitDataForecast;

class EnergyUnitForecastController extends CodeController {
    
	public function getWorkingTable($postData){
		$data_source 	= 	$postData['ExtensionDataSource'];
		$src			=	'ENERGY_UNIT';
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
		return $mdl::getTableName();
	}
	
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$properties = $this->getOriginProperties($dcTable);
		$results = ['properties'	=>$properties];
		return $results;
	}

    public function getOriginProperties($dcTable,$configId=null){
		$properties = collect([
					(object)['data' =>	'OCCUR_DATE'	,'title' => 'Occur time'    	,	'width'=>50,'INPUT_TYPE'=>3,],
					(object)['data' =>	'T'				,'title' => 'Time'    			,	'width'=>25,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>2],
					(object)['data' =>	'V'				,'title' => 'Value'    			,	'width'=>25,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>3],
		]);
		return $properties;
	}
	
	public function getFirstProperty($dcTable){
    	return  null;
    }
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$date_end 		= $postData['date_end'];
    	$date_end		= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	$id 			= $postData['EnergyUnit'];
		
		$phase_type 	= $postData['ExtensionPhaseType'];
		$value_type 	= $postData['ExtensionValueType'];
		$data_source 	= $postData['ExtensionDataSource'];
		$table			= $this->getWorkingTable($postData);
		$mdl 			= \Helper::getModelName($table);
	   
	    $where = [	"EU_ID" 			=> $id,
	    			"FLOW_PHASE" 		=> $phase_type,];
	    
// 		\DB::enableQueryLog();
	    $dataSet = $mdl::where($where)
					    ->whereDate("OCCUR_DATE", '>=', $occur_date)
					    ->whereDate("OCCUR_DATE", '<=', $date_end)
					    ->select(
					    		"ID as DT_RowId",
					    		"OCCUR_DATE",
					    		\DB::raw("'$occur_date' as T "),
					    		"EU_DATA_$value_type as V"
					    		)
					   	->orderBy('OCCUR_DATE')
					    ->get();
// 		\Log::info(\DB::getQueryLog());
					    					
    	return ['dataSet'=>$dataSet];
    }
    
    
    public function run(Request $request){
		$postData = $request->all();
    	$date_end 		= 	$postData['date_end'];
		$date_end		= 	\Helper::parseDate($date_end);
    	$object_id 			= 	$postData['EnergyUnit'];
    	 
    	$phase_type 	= 	$postData['ExtensionPhaseType'];
    	$value_type 	= 	$postData['ExtensionValueType'];
    	$data_source 	= 	$postData['ExtensionDataSource'];
    	$table			=	$postData['EnergyUnit'];
    	$mdl 			= 	\Helper::getModelName($table);
    	
    	$cb_update_db	=	$postData['cb_update_db'];
    	$a				=	$postData['a'];
    	$b				=	$postData['b'];
    	$u				=	$postData['u'];
    	$l				=	$postData['l'];
    	$m				=	$postData['m'];
    	$c1				=	$postData['c1'];
    	$c2				=	$postData['c2'];
    	
    	$date_begin 	= 	$postData['date_begin'];
		$date_begin		= 	\Helper::parseDate($date_begin);
    	$date_from 		= 	$postData['f_from_date'];
		$date_from		= 	\Helper::parseDate($date_from);
    	$date_to 		= 	$postData['f_to_date'];
    	$date_to		= 	\Helper::parseDate($date_to);
    	 
    	$from_date 		= 	$date_begin;
    	
		$mkey="_".date("Ymdhis_").rand(100,1000)/* ."hung_test" */;
		
		$data="";
		$continous=true;
		$lastT=null;
		
		if (array_key_exists('forecast', $postData)) {
			$txt_modify_data=$postData['forecast'];
			$ds=explode("\n",$txt_modify_data);
			foreach($ds as $line)
				if($line)
				{
					$ls=explode(",",$line);
					if(count($ls)>=2)
					{
						$t=trim($ls[0]);
						$v=trim($ls[1]);
						$data.=($data?"\r\n":"")."$t,$v";
						if($lastT && ($t-$lastT)!=1 && $continous)
						{
							$continous=false;
						}
						$lastT=$t;
					}
				}
		}
		else {
			$qData 		= $this->getDataSet($postData,null,null,$date_begin,null);
			$dataSet 	= $qData['dataSet'];
			foreach($dataSet as $row){
				$occur_date = $row->OCCUR_DATE;
				$carbon_date = \Helper::parseDate($occur_date, null, 'Y-m-d');
				$time = $carbon_date->diffInDays($from_date);
				$value = $row->V;
				$data.=($data?"\r\n":"")."$time,$value";
				if($lastT && ($time-$lastT)!=1 && $continous)
				{
					$continous=false;
				}
				$lastT=$time;
			}
		}

		\Log::info("aaaa");
		$cwd = getcwd();
		chdir("matlab\pdforecast");
		file_put_contents("data$mkey.txt",$data);
		\Log::info("data$mkey.txt $data");
		
		//$end = '2013-08-29';
		//$start = '2013-08-25';
		/* $d1 = strtotime($date_from) - strtotime($date_begin);
		$d1 = floor($d1/(60*60*24));
		$d2 = strtotime($date_to) - strtotime($date_begin);
		$d2 = floor($d2/(60*60*24)); */
		
		$d1 = $date_from->timestamp - $date_begin->timestamp;
		$d1 = floor($d1/(60*60*24));
		$d2 = $date_to->timestamp - $date_begin->timestamp;
		$d2 = floor($d2/(60*60*24));
		
		$timeForecast="";
		for($i = $d1; $i < $d2 + 1; $i++){
		    $timeForecast.=($timeForecast?"\r\n":"").$i;
			if($lastT && $i-$lastT!=1 && $continous)
			{
				$continous=false;
			}
			$lastT=$i;
		}
		
		$sqls = [];
		$warning = '';
		if(!$continous) $warning = "Timing is not continuous";
		
		file_put_contents("t$mkey.txt",$timeForecast);
// 		echo "<b>Time forecast:</b> ".$timeForecast."<br>";
		
		if($a==="0" || $a==="1")
			$params="$a,$b,0,0,0,0,0";
		else if($c2>0)
			$params="$a,$b,0,0,0,$c1,$c2";
		else
			$params="$a,$b,$l,$u,$m,$c1,0";
		
		file_put_contents("prop$mkey.txt",$params);
		\Log::info("prop$mkey.txt $params");
		
		$error = [];
		$results = [];
		
		if(!file_exists('pdforecast.exe'))
		{
			\Log::info("no file_exists");
			$error[] = "Exec file not found";
		}
		else
		{
			\Log::info("file_exists");
			if(file_exists("data$mkey.txt") && file_exists("t$mkey.txt") && file_exists("prop$mkey.txt"))
			{
				set_time_limit(300);
				$mkey='_20210319063145_764';
				\Log::info("pdforecast.exe $mkey");

				//exec("pdforecast.exe $mkey");
				$last_line = system("pdforecast.exe $mkey", $retval);

				\Log::info("done $last_line");
				if(file_exists("error$mkey.txt"))
				{
					$error[] = file_get_contents("error$mkey.txt", true);
				}
		
				if(file_exists("forecast_q$mkey.csv"))
				{
// 					echo "<b>Result:</b><br>";
					$file = fopen("forecast_q$mkey.csv","r");
					
					$configuration = auth()->user()->getConfiguration();
					$format = $configuration['time']['DATE_FORMAT_CARBON'];//'m/d/Y';
					
					while(! feof($file))
					{
						$line=fgets($file);
// 						echo $line;
// 						$result.= $line;
						$result = ['value'=>$line];
						
						if($line)
						{
							$xs=explode(",",$line);
							if(count($xs)>=2)
							{
								$x_time=trim($xs[0]);
								$x_value=trim($xs[1]);
								if($x_time>=$d1)
								{
									$beginTimeStamp = $date_begin->timestamp;
									$x_time=($x_time)*60*60*24+$beginTimeStamp;
    								$x_date		= 	Carbon::createFromTimestamp($x_time);
									$rxDate=$x_date?$x_date->format($format):$x_date;
									$occurDate=$x_date?$x_date->format('Y-m-d'):$x_date;
									$result = ['value'=>$x_value];
									$result['timing']=$x_time;
									$result['date']=$rxDate;
									$result['occur_date']=$occurDate;
    								if($cb_update_db=='true')
									{
										$field="EU_DATA_$value_type";
										$field=strtoupper($field);
										
										$attributes = [
												'EU_ID'				=>$object_id,
												'OCCUR_DATE'		=>$x_date,
												"FLOW_PHASE" 		=>$phase_type
												
										];
										$values = [
												'EU_ID'				=>$object_id,
												'OCCUR_DATE'		=>$x_date,
												"FLOW_PHASE" 		=>$phase_type,
												$field				=>$x_value
										];
 										\DB::enableQueryLog();
										EnergyUnitDataForecast::updateOrCreate($attributes,$values);
										$result['sql']=\Helper::logger();
									}
									$results[] = $result;
								}
							}
						}
// 						echo "<br>";
					}
					\DB::disableQueryLog();
					fclose($file);
				}
				else
				{
// 					logError("Result file not found");
					$error[]= "Result file not found";
				}
			}
			else
			{
// 				logError("Input files not found");
				$error[]= "Input files not found";
			}
		}
		chdir($cwd);
		$finalResults = [
				'data'			=>$data,
				'warning'		=>$warning,
				'params'		=>$params,
				'time'			=>$timeForecast,
				'result'		=>$results,
				'error'			=>$error,
				'key'			=>$mkey,
		];
		
		$this->cleanFiles($mkey);

    	return response()->json($finalResults);
    }
    
    
    public function cleanFiles($mkey)
    {
		$cwd = getcwd();
		chdir("matlab\pdforecast");
    	if(file_exists("forecast_q$mkey.csv")) unlink("forecast_q$mkey.csv");
    	if(file_exists("forecast_Np$mkey.csv")) unlink("forecast_Np$mkey.csv");
    	if(file_exists("data$mkey.txt")) unlink("data$mkey.txt");
    	if(file_exists("t$mkey.txt")) unlink("t$mkey.txt");
    	if(file_exists("prop$mkey.txt")) unlink("prop$mkey.txt");
    	if(file_exists("error$mkey.txt")) unlink("error$mkey.txt");
		chdir($cwd);
    }
}
