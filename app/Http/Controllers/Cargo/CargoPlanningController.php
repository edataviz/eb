<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\BaAddress;
use App\Models\Flow;
use App\Models\FlowDataValue;
use App\Models\PdCargoShipper;
use App\Models\PdCargo;
use App\Models\PdLiftingAccount;
use App\Models\PdShipper;
use App\Models\StorageDataValue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CargoPlanningController extends CodeController {
    
	/* public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	} */
	public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$properties = collect([
 				(object)['data' =>	'UOM',			'title' => 'Month',			'width'	=>	70,'INPUT_TYPE'=>2,		'DATA_METHOD'=>5,'FIELD_ORDER'=>1],
				(object)['data' =>	"cargo_name",	'title' => 'Cargo',			'width'	=>	130,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
				(object)['data' =>	"xdate",		'title' => 'Date',			'width'	=>	60,'INPUT_TYPE'=>3,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"opening_balance",'title' => 'Opening Balance','width'=>110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"n_qty",		'title' => 'Nominated Qty',	'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>4],
				(object)['data' =>	"b_qty",		'title' => 'Lifted Qty',	'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>5],
				(object)['data' =>	"flow_qty",		'title' => 'Flow Qty',		'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>6],
				(object)['data' =>	"flow_name",	'title' => 'Flow Name',		'width'	=>	110,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>7],
				(object)['data' =>	"cal_qty",		'title' => 'Balance Qty',	'width'	=>	110,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>8],
		]);
		/* $uoms		= [];
		$uoms[]		= \App\Models\PdCodeOrginality::all();
		$uoms[]		= \App\Models\PdCodeNumber::all();

		$selects 	= ['BaAddress'		=> \App\Models\BaAddress::all()]; */
		
		$results 	= ['properties'		=> $properties,
// 				'selects'		=> $selects,
// 				'suoms'			=> $uoms,
		];
		return $results;
	}
	
	public function load(Request $request){
		$postData 			= $request->all();
		$occur_date 		= null;
    	if (array_key_exists('date_begin',  $postData)){
    		$occur_date 	= $postData['date_begin'];
    		$occur_date 	= \Helper::parseDate($occur_date);
    	}
		$date_end 			= $postData['date_end'];
		$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
		$storage_id 		= $postData['Storage'];
		
		$baAddress 			= 	BaAddress::getTableName();
		$pdLiftingAccount 	= 	PdLiftingAccount::getTableName();
		$pdLiftingAccounts	= 	PdLiftingAccount::join($baAddress,"$baAddress.ID","=","$pdLiftingAccount.COMPANY")
								->where("$pdLiftingAccount.STORAGE_ID","=",$storage_id)
								->select(
										"$pdLiftingAccount.NAME as LA_NAME",
										"$pdLiftingAccount.ID as LA_ID",
										"$baAddress.NAME as BA_NAME",
										"$pdLiftingAccount.INTEREST_PCT")
								->get();
										
		$ent_r1 			= [];
		$ent_r2 			= [];
		$interest_percents 	= [];
		$lifting_acc_ids 	= [];
		$liftingShippers	= null;
		if ($pdLiftingAccounts->count()>0) {
			foreach($pdLiftingAccounts as $row ){
				$laId 			= $row->LA_ID;
				$ent_r1[$laId] 	= $row->LA_NAME;
				$ent_r2[$laId] 	= $row->BA_NAME;
				$interest_percents[$laId] = $row->INTEREST_PCT;
				$lifting_acc_ids[] = $laId;
			}
					
			$pdCargoShipper		= PdCargoShipper::getTableName();
			$pdShipper 			= PdShipper::getTableName();
			$liftingShippers 	= PdLiftingAccount::join($pdCargoShipper,"$pdLiftingAccount.ID","=","$pdCargoShipper.LIFTING_ACCOUNT_ID")
								->join($pdShipper,"$pdShipper.ID","=","$pdCargoShipper.SHIPPER_ID")
								->where("$pdLiftingAccount.STORAGE_ID","=",$storage_id)
								->select(
										"$pdLiftingAccount.ID as LA_ID",
										"$pdShipper.ID as SHIPPER_ID",
										"$pdShipper.NAME as SHIPPER_NAME",
										"$pdShipper.CARGO_SIZE")
								->get();
		}


		$bal_data 		= null;
		$balanceData 	= isset($postData['balanceData'])?$postData['balanceData']:[];
		if(count($balanceData)>0){
			$bal_data = [];
			foreach($balanceData as $bData){
				$dateString				= $bData["D"];
				$dateString				= Carbon::parse($dateString);
				$dateString				= $dateString->toDateString();
				$bal_data[$dateString] 	= (float)$bData["V"];
			}
		}
		

		$la_data 	= [];
		$laData 	= array_key_exists("laData",$postData)?$postData['laData']:[];
		if(count($laData)>0){
			foreach($laData as $bData){
				$dateString				= $bData["D"];
				$dateString				= Carbon::parse($dateString);
				$dateString				= $dateString->toDateString();
				if (!array_key_exists($dateString, $la_data)) $la_data[$dateString]	= [];
				foreach($ent_r1 as $la_id => $name){
					$la_data[$dateString][$la_id] = (float)$bData["V"]*$interest_percents["$la_id"]/100;
				}
			}
		}
		else if(count($lifting_acc_ids)>0){
			$flowDataValue 		= 	FlowDataValue::getTableName();
			$flow 				= 	Flow::getTableName();
			$pdLiftingAccount 	= 	PdLiftingAccount::getTableName();
			$storageDataValue 	= 	StorageDataValue::getTableName();
		
			$flowDataValues		= 	FlowDataValue::join($flow,"$flowDataValue.FLOW_ID","=","$flow.COST_INT_CTR_ID")
									->join($pdLiftingAccount,function ($query) use ($pdLiftingAccount,$flow,$lifting_acc_ids) {
										$query->on("$pdLiftingAccount.PROFIT_CENTER","=","$flow.COST_INT_CTR_ID")
										->whereIn("$pdLiftingAccount.ID",$lifting_acc_ids);
									})
									->whereDate("$flowDataValue.OCCUR_DATE",">=",$occur_date)
									->whereDate("$flowDataValue.OCCUR_DATE","<=",$date_end)
									->whereHas("StorageDataValue", function($q) use ($storageDataValue,$storage_id){
										$q->where("$storageDataValue.STORAGE_ID", '=', $storage_id);
									})
									->select("$pdLiftingAccount.ID as LIFTING_ACCOUNT_ID",
											"$flowDataValue.OCCUR_DATE",
											"$pdLiftingAccount.ID",
											\DB::raw("round(sum($flowDataValue.FL_DATA_GRS_VOL*$pdLiftingAccount.INTEREST_PCT/100),3) as QTY"))
									->groupBy("$pdLiftingAccount.ID","$flowDataValue.OCCUR_DATE")
									->get();
		
			foreach($flowDataValues as $row ){
				$dateString				= $row->OCCUR_DATE->toDateString();
				if (!array_key_exists($dateString, $la_data)) $la_data[$dateString]	= [];
				$la_data[$dateString][$row->LIFTING_ACCOUNT_ID] = $row->QTY;
			}
		}
		
		return view ( 'front.cargomonitoring.planningload',
				['date_from'			=> $occur_date,
				'date_to'				=> $date_end,
				'storage_id'			=> $storage_id,
				'postData'				=> $postData,
				'pdLiftingAccounts'		=> $pdLiftingAccounts,
				'ent_r1'				=> $ent_r1,
				'ent_r2'				=> $ent_r2,
				'interest_percents'		=> $interest_percents,
				'lifting_acc_ids'		=> $lifting_acc_ids,
				'liftingShippers'		=> $liftingShippers,
				'bal_data'				=> $bal_data,
				'la_data'				=> $la_data,
				]);
	}
	
	public function gen(Request $request){
		$postData 			= $request->all();
		$cargo_data			= $postData["cargo_data"];
		$count 				= 0;
		foreach($cargo_data as $cargo){
			$attributes	= ["LIFTING_ACCT"	=> $cargo["la_id"],
							"STORAGE_ID"	=> $cargo["storage_id"],
							"REQUEST_DATE"	=> $cargo["req_date"]
			];
			$values		=  ["LIFTING_ACCT"	=> $cargo["la_id"],
							"STORAGE_ID"	=> $cargo["storage_id"],
							"REQUEST_DATE"	=> $cargo["req_date"],
							"REQUEST_QTY"	=> $cargo["qty"],
							"NAME"			=> "Generated by cargo planning",
							"PRIORITY"		=> 1
					
			];
			PdCargo::updateOrCreate( $attributes, $values);
			$count++;
		}
		return response()->json("$count Cargo Entry generated successfully");
		
	}
	
}
