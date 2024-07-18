<?php

namespace App\Http\Controllers;
use App\Models\CodeQltySrcType;
use App\Models\QltyData;
use App\Models\QltyDataDetail;
use App\Models\QltyProductElementType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PreosController extends CodeController {
	
	public function getWorkingTable($postData){
		return null;
	}
	
	public function getObjectTable($src,$data_source){
		$table			=	$src."_DATA_".$data_source;
		$mdl 			= 	\Helper::getModelName($table);
		return $mdl;
	}
	
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$objs 			= 	$this->getPostObjects($postData);
		if ($objs&&count($objs)>0) {
			$objectArray	=	[(object)[
											'data' 			=>	"NAME",
											'title' 		=> 	"NAME",
											'width'			=>	45,
											'INPUT_TYPE'	=>	1,
											'DATA_METHOD'	=>	2]
			];
			foreach($objs as $obj){
				$ss				=	explode(":",$obj);
				$obj_id			=	$ss[1];
				$src			=	$ss[0];
				$obj_name		=	$ss[2];
				
				$objectArray[]	=	(object)[
											'data' 			=>	"$src"."_$obj_id",
											'title' 		=> 	$obj_name,
											'width'			=>	55,
											'INPUT_TYPE'	=>	2,
											'DATA_METHOD'	=>	2,
				];
			}
			
			$properties 	= collect($objectArray);
			$results 		= ['properties'	=>$properties];
			return $results;
		}
		return null;
	}
	
	public function getPostObjects($postData){
		$objs 			= 	$postData['objs'];
		if ($objs&&!empty($objs)) {
			$objs			=	explode(";",$objs);
		}
		else $objs = null;
		return $objs;
	}
	
	public function getElement($obj_name){
		return array(
// 						"Object"=>$obj_name,
						"Pressure"=>"0",
						"Temperature"=>"0",
						"Volume"=>"0",
						"CO2"=>"0",
						"Nitrogen"=>"0",
						"H2S"=>"0",
						"Methane"=>"0",
						"Ethane"=>"0",
						"Propane"=>"0",
						"i-Butane"=>"0",
						"n-Butane"=>"0",
						"i-Pentane"=>"0",
						"n-Pentane"=>"0",
						"22-Mbutane"=>"0",
						"23-Mbutane"=>"0",
						"3-Mpentane"=>"0",
						"n-Hexane"=>"0",
						"2-Mhexane"=>"0",
						"3-Mhexane"=>"0",
						"n-Heptane"=>"0",
						"22-Mhexane"=>"0",
						"2-Mheptane"=>"0",
						"3-Mheptane"=>"0",
						"n-Octane"=>"0",
						"25-Mheptane"=>"0",
						"n-Nonane"=>"0",
						"n-Decane"=>"0",
						"n-C11"=>"0",
						"n-C12"=>"0",
						"n-C13"=>"0",
						"n-C14"=>"0",
						"n-C15"=>"0",
						"n-C16"=>"0",
						"n-C17"=>"0",
						"n-C18"=>"0",
						"n-C19"=>"0",
						"n-C20"=>"0",
						"n-C21"=>"0",
						"n-C22"=>"0",
						"n-C23"=>"0",
						"n-C24"=>"0",
						"n-C25"=>"0",
						"n-C26"=>"0",
						"n-C27"=>"0",
						"n-C28"=>"0",
						"n-C29"=>"0",
						"n-C30"=>"0",
						"Cyclohexane"=>"0",
						"Mcyclopentan"=>"0",
						"11Mcycpentan"=>"0",
						"1tr2ci4-MCC5"=>"0",
						"1-tr2-MCC6"=>"0",
						"1-ci2-MCC6"=>"0",
						"Benzene"=>"0",
						"Toluene"=>"0",
						"o-Xylene"=>"0"
				);
	}
	
	public function getInputDataSet($postData,$occur_date){
		$phase_type 	= 	$postData['ExtensionPhaseType'];
		$value_type 	= 	$postData['ExtensionValueType'];
		$data_source 	= 	$postData['ExtensionDataSource'];
		$objs 			= 	$this->getPostObjects($postData);
		
		$objdata=array();
		$objinfo = [];
		foreach($objs as $obj)
			if($obj){
				$ss				=	explode(":",$obj);
				$obj_id			=	$ss[1];
				$src			=	$ss[0];
				$obj_name		=	$ss[2];
				$pre			=	$src;
				$pvalue			=	$src;
				$ele			= 	$this->getElement($obj_name);
				
				$where = ["OCCUR_DATE" => $occur_date];
				if($src=="ENERGY_UNIT"){
					$pre="EU";
					$pvalue=$pre;
					$where['FLOW_PHASE'] = $phase_type;
				}
				else if($src=="FLOW") $pvalue="FL";
				$dataField		= $src!="STORAGE"?"DATA_":"";
				$valueField		= "$pvalue"."_$dataField$value_type as OBJ_VALUE";
				$where["$pre"."_ID" ] = $obj_id;
				
				$qlty_src_type	=	CodeQltySrcType::where('CODE','=',$src)->first();
				
				$mdl 			= 	$this->getObjectTable($src,$data_source);
				$objectData 	= 	$mdl::where($where)
										->select(
												"OBS_TEMP",
												"OBS_PRESS",
												$valueField
												)
										->orderBy('OCCUR_DATE')
										->first();
				
				$qltyData 				=	QltyData::getTableName();
    			$qltyProductElementType =	QltyProductElementType::getTableName();
    			$qltyDataDetail 		=	QltyDataDetail::getTableName();
    			$codeQltySrcTypel 		=	CodeQltySrcType::getTableName();
    			
//     			\DB::enableQueryLog();
				$qlty_datas = QltyData::join($qltyDataDetail,"$qltyData.ID", '=', "$qltyDataDetail.QLTY_DATA_ID")
							    	->join($qltyProductElementType,function ($query) use ($qltyDataDetail,$phase_type,$qltyProductElementType) {
									    		$query->on("$qltyDataDetail.ELEMENT_TYPE",'=',"$qltyProductElementType.ID")
									    			  ->where("$qltyProductElementType.SAMPLE_TYPE",'=',$phase_type) ;
								    })
							    	->whereHas('CodeQltySrcType',function ($query) use ($src,$codeQltySrcTypel) {
										$query->where("$codeQltySrcTypel.CODE",$src );//->skip(0)->take(1);
									})
									->where("$qltyData.SRC_ID",$obj_id)
									->where("$qltyData.SAMPLE_TYPE",$phase_type)
									->where("$qltyData.EFFECTIVE_DATE",'=',$occur_date)
									->select(
											"$qltyProductElementType.NAME",
											"$qltyDataDetail.MOLE_FACTION"
											)
									->get();
// 				\Log::info(\DB::getQueryLog());
// 				foreach($workingDataSet as $objectData){
				if ($objectData) {
					foreach($qlty_datas as $qlty){
						if (array_key_exists($qlty->NAME,$ele)){
							$ele[$qlty->NAME]=$qlty->MOLE_FACTION?$qlty->MOLE_FACTION:0;
						}
					}
						
					$ele["Pressure"]			=	\Helper::getRoundValue($objectData->OBS_PRESS);
					$ele["Temperature"]			=	\Helper::getRoundValue($objectData->OBS_TEMP);
					$ele["Volume"]				=	\Helper::getRoundValue($objectData->OBJ_VALUE);
					$objdata["$src"."_$obj_id"]	=	$ele;
					$objinfo[]=array("src"=>$src,"pre"=>$pre,"obj_id"=>$obj_id);
						
				}
// 				}
			}
		return ['data'	=>$objdata,
				'info'	=>$objinfo,
		];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if (!$properties) return [];

    	$objdata	=	$this->getInputDataSet($postData,$occur_date);
    	$objdata	=	$objdata['data'];
    	if (count($objdata)>0) {
			$ele = array_values($objdata)[0];
			$renderData = [];
			foreach($ele as $key =>$value){
				$values = ['NAME'=>$key];
				foreach($objdata as $source =>$objValue){
					$values[$source] = $objValue[$key];
				}
				$renderData[] = $values;
			}
		}
		else $renderData = $objdata;
		
    	return ['dataSet'=>$renderData];
    }
    
    public function run(Request $request){
    	$postData 		= 	$request->all();
		$phase_type 	= 	$postData['ExtensionPhaseType'];
		$cb_update_db	=	$postData['cb_update_db'];
    	$occur_date 	= 	$postData['date_begin'];
		$value_type 	= 	$postData['ExtensionValueType'];
    	$occur_date		= 	\Helper::parseDate($occur_date);
    	$inputDataSet	=	$this->getInputDataSet($postData,$occur_date);
    	$objdata		=	$inputDataSet['data'];
    	$objinfo		=	$inputDataSet['info'];
    	
// 		$mkey			=	"";
 		$mkey			=	"_".date("Ymdhis_").rand(100,1000)/* ."hung_test" */;
		$preos 			= "./matlab/preos/";
		$files = [
				'gas'				=>	"$preos"."prvap.exe",
				'oil'				=>	"$preos"."prliq.exe",
				'data'				=>	"$preos"."data$mkey.txt",
				'm_ij'				=>	"$preos"."m_ij$mkey.txt",
				'prop'				=>	"$preos"."prop$mkey.txt",
				'error'				=>	"$preos"."error$mkey.txt",
				'PR_single_V'		=>	"$preos"."PR_single_V$mkey.csv",
				'PR_single_L'		=>	"$preos"."PR_single_L$mkey.csv",
		];
		
		$cc=count($objdata);
		if($cc<=0) return response('empty input data', 501);//['error'=>"empty input data"];
		
		$ele = array_values($objdata)[0];
		$data="";
		$inputData = [];
		foreach ($ele as $key => $value)
		{
			$ss=[];
			foreach($objdata as $source =>$objValue){
				if($objValue[$key]!==""){
					$ss[]=$objValue[$key];
					$inputData[] = $objValue[$key]." <- [$source][$key]";
				}
			}
			
			if(count($ss)>0) $data.=($data?"\r\n":"").implode(",", $ss);
		}
		
		file_put_contents($files['data'],$data);
		
		$error = [];
		$results = [];
		$sqls = [];
		//Gas
		$exe=$phase_type==2?$files['gas']:$files['oil'];
		if(!file_exists($exe)) $error[] = "Exec $exe file not found";
		else{
			if(file_exists($files['data']) /* && file_exists($files['m_ij']) && file_exists($files['prop']) */){
				set_time_limit(300);
				$rsText	= exec("$exe $mkey");
				$results["exec"] = [$rsText];
				if(file_exists($files['error'])) $error[] = file_get_contents($files['error'], true);
				
				if(file_exists($files['PR_single_V'])) {
					$fileName = $files['PR_single_V'];
					$file = fopen($fileName,"r");
					$lastline="";
					$result = [];
					while(! feof($file))
					{
						$line=fgets($file);
						$result[] =$line;
						if($line)
						{
							if($line) $lastline=$line;
						}
					}
					$results[$fileName] = $result;
					fclose($file);
					unlink($fileName);
						
					$fileName = $files['PR_single_L'];
					$file = fopen($fileName,"r");
					$lastline="";
					$result = [];
					while(! feof($file))
					{
						$line=fgets($file);
						$result[] =$line;
						if($line)
						{
							if($line) $lastline=$line;
						}
					}
					$results[$fileName] = $result;
					fclose($file);
					unlink($fileName);
						
					if($lastline && $cb_update_db=='true')
					{
						$xs=explode(",",$lastline);
						$i=0;
						foreach($xs as $svol){
							if ($i<count($objinfo)) {
								$src= $objinfo[$i]["src"];
								$pre= $objinfo[$i]["pre"];
								$table=$src."_DATA_VALUE";
								$field=$pre."_DATA_$value_type";
								$field = strtoupper( $field );
								
								$attributes = ["OCCUR_DATE" => $occur_date];
								if($src=="ENERGY_UNIT"){
									$attributes['FLOW_PHASE'] = $phase_type;
								}
								$attributes["$pre"."_ID" ] = $objinfo[$i]["obj_id"];
								$values = $attributes;
								$values[$field] = $svol;
								$mdl 			= 	\Helper::getModelName($table);
								
								\DB::enableQueryLog();
								$mdl::updateOrCreate($attributes,$values);
								$sqls[]=\Helper::logger();
							}
							$i++;
						}
						\DB::disableQueryLog();
					}
				}
				else{
					$error[]= "Result file not found";
				}
			}
			else $error[]= "Input files not found";
		}
		
		unlink($files['data']);
		
		$finalResults = [
				'data'			=>$inputData,
				'warning'		=>'',
// 				'params'		=>'',
// 				'time'			=>'',
				'result'		=>$results,
				'error'			=>$error,
				'key'			=>$mkey,
				'exe'			=>$exe,
				'sqls'			=>$sqls,
				
		];
		
// 		$this->cleanFiles($mkey);
    	return response()->json($finalResults);
    }
    
    
    public function cleanFiles($mkey)
    {
    	if(file_exists("forecast_q$mkey.csv")) unlink("forecast_q$mkey.csv");
    	if(file_exists("forecast_Np$mkey.csv")) unlink("forecast_Np$mkey.csv");
    	if(file_exists("data$mkey.txt")) unlink("data$mkey.txt");
    	if(file_exists("t$mkey.txt")) unlink("t$mkey.txt");
    	if(file_exists("prop$mkey.txt")) unlink("prop$mkey.txt");
    	if(file_exists("error$mkey.txt")) unlink("error$mkey.txt");
    }
}
