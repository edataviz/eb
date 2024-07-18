<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\Demurrage;
use App\Models\PdBerth;
use App\Models\PdCargo;
use App\Models\PdCargoLoad;
use App\Models\PdCargoUnload;
use App\Models\PdCodeDemurrageEbo;
use App\Models\PdCodeLoadActivity;
use App\Models\PdContractData;
use App\Models\TerminalTimesheetData;

use Illuminate\Http\Request;

class DemurrageeboController extends CodeController {
    
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
		 
		$mdlName 					= $postData[config("constants.tabTable")];
		$mdl 						= "App\Models\\$mdlName";
		$date_end 					= $postData['date_end'];
    	$date_end					= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
		$storage_id 				= $postData['Storage'];
		
		$pdCargoLoad 				= PdCargoLoad::getTableName();
		$terminalTimesheetData 		= TerminalTimesheetData::getTableName();
		$pdCodeLoadActivity 		= PdCodeLoadActivity::getTableName();
		$pdBerth 					= PdBerth::getTableName();
		$pdCargo 					= PdCargo::getTableName();
		$pdCodeDemurrageEbo 		= PdCodeDemurrageEbo::getTableName();
		$pdCargoUnload				= PdCargoUnload::getTableName();
		$demurrage					= Demurrage::getTableName();
		$pdContractData				= PdContractData::getTableName();
		
		$result 					= array();
		$aryMst 					= array();
		
		$lquery = PdCargoLoad::whereHas("TerminalTimesheetData",
										function ($query) use ($terminalTimesheetData) {
											$query->where("$terminalTimesheetData.IS_LOAD",'=',1);
									})
								->join($pdCargo,
										function ($query) use ($pdCargo,$pdCargoLoad,$storage_id) {
											$query->on("$pdCargo.ID",'=',"$pdCargoLoad.CARGO_ID")
											->where("$pdCargo.STORAGE_ID",'=',$storage_id);
										})
								->leftJoin($pdBerth,"$pdCargoLoad.BERTH_ID", '=', "$pdBerth.ID")
								->leftJoin($pdCodeDemurrageEbo,"$pdCargoLoad.DEMURRAGE_EBO", '=', "$pdCodeDemurrageEbo.ID")
								->select(
									"$pdCargoLoad.CARGO_ID",           
									"$pdCargoLoad.BERTH_ID",           
									"$pdCargoLoad.ID as ID",           
									"$pdCargoLoad.DATE_LOAD",           
									"$pdCargoLoad.DEMURRAGE_EBO",
									"$pdBerth.NAME as BERTH_NAME",
									"$pdCargo.NAME as CARGO_NAME",
									"$pdCargo.CONTRACT_ID",
									"$pdCodeDemurrageEbo.NAME as DEMURRAGE_EBO_NAME"
									)
								->with(["TerminalTimesheetData"	=> function ($query) use ($terminalTimesheetData) {
												$query->where("$terminalTimesheetData.IS_LOAD",'=',1);
								}])
								->with(["Demurrage"		=>	function ($query) use ($terminalTimesheetData,$demurrage) {
															$query->where("$demurrage.ACTIVITY_ID",'=',"$terminalTimesheetData.ACTIVITY_ID") ;
								}])
								->with(["PdContractData"=>	function ($query) use ($pdContractData,$pdCargo) {
															$query->where("$pdContractData.CONTRACT_ID",'=',"$pdCargo.CONTRACT_ID") ;
								}]);
								
		$ulquery = PdCargoUnload::whereHas("TerminalTimesheetData",
											function ($query) use ($terminalTimesheetData) {
												$query->whereNull("$terminalTimesheetData.IS_LOAD")
														->orWhere("$terminalTimesheetData.IS_LOAD",'=',0);
								})
								->join($pdCargo,
										function ($query) use ($pdCargo,$pdCargoUnload,$storage_id) {
											$query->on("$pdCargo.ID",'=',"$pdCargoUnload.CARGO_ID")
													->where("$pdCargo.STORAGE_ID",'=',$storage_id);
										})
								->leftJoin($pdBerth,"$pdCargoUnload.BERTH_ID", '=', "$pdBerth.ID")
								->leftJoin($pdCodeDemurrageEbo,"$pdCargoUnload.DEMURRAGE_EBO", '=', "$pdCodeDemurrageEbo.ID")
								->select(
										"$pdCargoUnload.CARGO_ID",
										"$pdCargoUnload.BERTH_ID",
										"$pdCargoUnload.ID as ID",
										"$pdCargoUnload.DATE_UNLOAD as DATE_LOAD",
										"$pdCargoUnload.DEMURRAGE_EBO",
										"$pdBerth.NAME as BERTH_NAME",
										"$pdCargo.NAME as CARGO_NAME",
										"$pdCargo.CONTRACT_ID",
										"$pdCodeDemurrageEbo.NAME as DEMURRAGE_EBO_NAME"
								)
								->with(["TerminalTimesheetData"	=> function ($query) use ($terminalTimesheetData) {
									$query->whereNull("$terminalTimesheetData.IS_LOAD")
										->orWhere("$terminalTimesheetData.IS_LOAD",'=',0);
								}])
								->with(["Demurrage"		=>	function ($query) use ($terminalTimesheetData,$demurrage) {
									$query->where("$demurrage.ACTIVITY_ID",'=',"$terminalTimesheetData.ACTIVITY_ID") ;
								}])
								->with(["PdContractData"=>	function ($query) use ($pdContractData,$pdCargo) {
									$query->where("$pdContractData.CONTRACT_ID",'=',"$pdCargo.CONTRACT_ID") ;
								}]);
								
// 		$query		= $lquery->union($ulquery);//->orderBy("START_TIME","desc");
		\DB::enableQueryLog();
		$ldataSet 	= $lquery->get();
		\Log::info(\DB::getQueryLog());
		$uldataSet 	= $ulquery->get();
 		$dataSet 	= $ldataSet->merge($uldataSet);
// 		$dataSet	= $uldataSet;
		$DT_RowId = 100;
		foreach($dataSet as $key 	=> $t ){
			$t->DT_RowId 			= $DT_RowId++;
			$timesheetDatas			= $t->TerminalTimesheetData;
			$demurrages				= $t->Demurrage;
			$pdContractDatas		= $t->PdContractData;
			$rate					= $pdContractDatas&&$pdContractDatas->count()>0?$pdContractDatas->first()->ATTRIBUTE_VALUE:0;
			foreach($timesheetDatas as $index	=> $tsheet ){
				$elapse_time 			= null;
				if ($tsheet->END_TIME&&$tsheet->START_TIME) {
					$endTime 			= $tsheet->END_TIME;
					$startTime 			= $tsheet->START_TIME;
					$elapse_time 		= $endTime->diffInHours($startTime); 
				}
				$elapse_time 			= !$elapse_time||$elapse_time < 0 ? null : $elapse_time;
				$tsheet->ELAPSE_TIME 	= $elapse_time; 
				
				$demurrage 				= $demurrages->where("ACTIVITY_ID",$tsheet->ACTIVITY_ID)->first();
				$tsheet->OVERRIDE_AMOUNT= $demurrage?$demurrage->OVERRIDE_AMT:"";
				
				$amount 				= $elapse_time?$elapse_time * $rate:null;
				$tsheet->AMOUNT 		= $amount&&$amount!=0?$amount:"0.000";
				$tsheet->RATE_HOUR 		= ($rate == 0)?null:$rate;
				
				if ($index==0) {
					$t->TE_ID 			= $tsheet->ID;
					$t->IS_LOAD 		= $tsheet->IS_LOAD;
					$t->ACTIVITY_NAME 	= $tsheet->ACTIVITY_ID;
					$t->START_TIME 		= $tsheet->getAttributes()["START_TIME"];
					$t->END_TIME 		= $tsheet->getAttributes()["END_TIME"]; 
					$t->ELAPSE_TIME 	= $tsheet->ELAPSE_TIME;
	  				$t->OVERRIDE_AMOUNT	= $tsheet->OVERRIDE_AMOUNT;
	  				$t->RATE_HOUR		= $tsheet->RATE_HOUR;
	  				$t->AMOUNT			= $tsheet->AMOUNT;
				}
			}
		}
		
		return ['dataSet'		=>	$dataSet,
		];
	}
	
	public function getFirstProperty($dcTable){
		return  null;
	}
	
 	public function saveDemurrage(Request $request){
 		$postData = $request->all();
 		$datas = $postData['data'];
 		
 		$insert = array();
 		foreach ($datas as $data){
 			$tmp = array();
 			
 			$tmp['CARGO_ID'] = $data['CARGO_ID'];
 			$tmp['BERTH_ID'] = $data['BERTH_ID'];
 			$tmp['ACTIVITY_ID'] = $data['ACTIVITY_ID'];
 			$tmp['DEMURRAGE_TYPE'] = $data['DEMURRAGE_EBO'];
 			$tmp['START_TIME'] = $data['START_TIME'];
 			$tmp['END_TIME'] = $data['END_TIME'];
 			$tmp['DEMURRAGE_RATE'] = $data['RATE_HOUR'];
 			$tmp['AMOUNT'] = $data['AMOUNT'];
 			$tmp['OVERRIDE_AMT'] = $data['OVERRIDE_AMT'];
 			
 			array_push($insert, $tmp);
 		}
 		
 		Demurrage::insert($insert);
 		
		return response ()->json ( 'ok' );
	}
	
	public function loadsrc(Request $request){
		$postData = $request->all();
		
		$dataSet = array();
		$dataSet['START_TIME'] ='2016-01-01 12:12';
		$dataSet['END_TIME'] = '2016-01-02 12:12';
		$dataSet['ELAPSE_TIME'] = 1234;
		 
		return response()->json(['dataSet'=>$dataSet,
				'postData'=>$postData]);
	}
}
