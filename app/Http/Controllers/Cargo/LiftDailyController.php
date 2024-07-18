<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\Flow;
use App\Models\FlowDataValue;
use App\Models\PdCargo;
use App\Models\PdCargoNomination;
use App\Models\PdCodeLiftAcctAdj;
use App\Models\PdLiftingAccount;
use App\Models\PdLiftingAccountMthData;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use App\Models\ShipCargoBlmr;
use App\Models\StorageDataValue;
use Carbon\Carbon;



class LiftDailyController extends CodeController {
    
	/* public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 50];
	} */
	public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$properties = collect([
 				(object)['data' =>	'uom',			'title' => 'Month',			'width'	=>	60,'INPUT_TYPE'=>2,		'DATA_METHOD'=>5,'FIELD_ORDER'=>1],
				(object)['data' =>	"cargo_name",	'title' => 'Cargo',			'width'	=>	80,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
				(object)['data' =>	"xdate",		'title' => 'Date',			'width'	=>	60,'INPUT_TYPE'=>3,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"opening_balance",'title' => 'Opening Balance','width'=>60,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"n_qty",		'title' => 'Nominated Qty',	'width'	=>	60,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>4],
				(object)['data' =>	"b_qty",		'title' => 'Lifted Qty',	'width'	=>	60,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>5],
				(object)['data' =>	"flow_qty",		'title' => 'Flow Qty',		'width'	=>	60,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>6],
				(object)['data' =>	"flow_name",	'title' => 'Flow Name',		'width'	=>	110,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>7],
				(object)['data' =>	"cal_qty",		'title' => 'Balance Qty',	'width'	=>	150,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>8],
		]);
		
		$results 	= ['properties'		=> $properties];
		return $results;
	}
	
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$accountId 			= $postData['PdLiftingAccount'];
    	$date_end 			= array_key_exists('date_end',  $postData)?$postData['date_end']:null;
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
  		
  		$pdCargo 			= PdCargo::getTableName();
  		$pdCargoNomination 	= PdCargoNomination::getTableName();
  		$shipCargoBlmr 		= ShipCargoBlmr::getTableName();
  		$flowDataValue 		= FlowDataValue::getTableName();
  		$flow			 	= Flow::getTableName();
  		$pdLiftingAccount 	= PdLiftingAccount::getTableName();
		$storageDataValue	= StorageDataValue::getTableName();
		$pdVoyageDetail		= PdVoyageDetail::getTableName();
		$pdVoyage			= PdVoyage::getTableName();
		$storageID			= $postData["Storage"];
		$subXName			= \Helper::getSubNameSelectQuery("x");
		$subTName			= \Helper::getSubNameSelectQuery("t");
		$isOracle			= config('database.default')==='oracle';
		$ifNull				= $isOracle?"COALESCE":"ifnull";
		
  		$query = ShipCargoBlmr::join($pdCargo,function ($query) use ($shipCargoBlmr,$accountId,$pdCargo) {
							  			$query->on("$pdCargo.ID",'=',"$shipCargoBlmr.CARGO_ID")
							  			->where("$pdCargo.LIFTING_ACCT",'=',$accountId) ;
							  		})
							  		->whereNotNull("$shipCargoBlmr.DATE_TIME")
							  		->whereDate("$shipCargoBlmr.DATE_TIME", '>=', $occur_date)
							  		->whereDate("$shipCargoBlmr.DATE_TIME", '<=', $date_end)
							  		->select(
							  				"$shipCargoBlmr.CARGO_ID",
							  				"$pdCargo.NAME as cargo_name",
							  				"$shipCargoBlmr.DATE_TIME as xdate",
							  				\DB::raw("null as nom_qty"),
							  				"$shipCargoBlmr.ITEM_VALUE as b_qty"
							  		);
							  		
  		$cdquery = PdVoyageDetail::join($pdCargo,function ($query) use ($pdVoyageDetail,$accountId,$pdCargo) {
						  			$query->on("$pdCargo.ID",'=',"$pdVoyageDetail.CARGO_ID");
						  		})
						  		->whereDate("$pdVoyageDetail.LOAD_DATE", '>=', $occur_date)
						  		->whereDate("$pdVoyageDetail.LOAD_DATE", '<=', $date_end)
					  			->where("$pdVoyageDetail.LIFTING_ACCOUNT",'=',$accountId)
						  		->select(
						  				"$pdVoyageDetail.CARGO_ID",
						  				"$pdCargo.NAME as cargo_name",
						  				"$pdVoyageDetail.LOAD_DATE as xdate",
						  				"$pdVoyageDetail.LOAD_QTY as nom_qty",
						  				\DB::raw("null as b_qty")
						  				);
						  		
  		$cquery = PdCargoNomination::join($pdCargo,function ($query) use ($pdCargoNomination,$accountId,$pdCargo) {
							  			$query->on("$pdCargo.ID",'=',"$pdCargoNomination.CARGO_ID")
							  			->where("$pdCargo.LIFTING_ACCT",'=',$accountId) ;
							  		})
							  		->whereDate("$pdCargoNomination.NOMINATION_DATE", '>=', $occur_date)
							  		->whereDate("$pdCargoNomination.NOMINATION_DATE", '<=', $date_end)
							  		->select(
							  				"$pdCargoNomination.CARGO_ID",
							  				"$pdCargo.NAME as cargo_name",
							  				"$pdCargoNomination.NOMINATION_DATE as xdate",
							  				"$pdCargoNomination.NOMINATION_QTY as nom_qty",
							  				\DB::raw("null as b_qty")
							  		);
   		$cdquery->union($cquery);
  		
  		$cxquery = \DB::table(\DB::raw("({$cdquery->toSql()}) $subTName") )
			  		->select('t.*')
	  				->addBinding($cdquery->getBindings())
	  				->groupBy('t.xdate')
	  				->groupBy('t.CARGO_ID')
	  				->groupBy('t.cargo_name')
	  				->groupBy('t.nom_qty')
	  				->groupBy('t.b_qty');
  		
  		$query->union($cxquery);
  		
  		$xquery = \DB::table(\DB::raw("({$query->toSql()}) $subXName") )
			  		->select('x.CARGO_ID',
			  				'x.cargo_name',
			  				'x.xdate',
			  				\DB::raw('sum(x.nom_qty) as n_qty'),
			  				\DB::raw('sum(x.b_qty) as b_qty')
			  				)
	  				->addBinding($query->getBindings())
	  				->groupBy('x.xdate')
	  				->groupBy('x.CARGO_ID')
	  				->groupBy('x.cargo_name');
  		
  		$xxquery = \DB::table(\DB::raw("({$xquery->toSql()}) $subXName") )
  					->select(
			  				'x.cargo_name',
  							'x.xdate',
  							'x.n_qty',
  							'x.b_qty',
			  				\DB::raw('null as flow_name'),
			  				\DB::raw('null as flow_qty'),
			  				\DB::raw("-$ifNull(x.b_qty,x.n_qty) as cal_qty")
  							)
  					->addBinding($xquery->getBindings());
  		
  					
  		$flowNameConcat	= $isOracle?\DB::raw("concat(concat(concat($flow.name,' ('),round($pdLiftingAccount.INTEREST_PCT)),'%)') as flow_name"):
  								(config('database.default')==='sqlsrv'?
  								\DB::raw("($flow.name + ' (' + round($pdLiftingAccount.INTEREST_PCT) + '%)') as flow_name"):
  								\DB::raw("concat($flow.name,' (',round($pdLiftingAccount.INTEREST_PCT),'%)') as flow_name"));
  										
  		$flowquery = FlowDataValue::join($flow,"$flowDataValue.FLOW_ID", '=', "$flow.ID")
							  		->join($pdLiftingAccount,function ($query) use ($pdLiftingAccount,$accountId,$flow) {
								  			$query->on("$pdLiftingAccount.PROFIT_CENTER",'=',"$flow.COST_INT_CTR_ID")
								  					->where("$pdLiftingAccount.ID",'=',$accountId) ;
							  		})
							  		->whereDate("$flowDataValue.OCCUR_DATE", '>=', $occur_date)
							  		->whereDate("$flowDataValue.OCCUR_DATE", '<=', $date_end)
							  		->select(
							  				\DB::raw("null as cargo_name"),
							  				"$flowDataValue.OCCUR_DATE as xdate",
							  				\DB::raw("null as n_qty"),
							  				\DB::raw("null as b_qty"),
							  				$flowNameConcat,
							  				\DB::raw("round($flowDataValue.FL_DATA_GRS_VOL*$pdLiftingAccount.INTEREST_PCT/100,3) as flow_qty"),
							  				\DB::raw("round($flowDataValue.FL_DATA_GRS_VOL*$pdLiftingAccount.INTEREST_PCT/100,3) as cal_qty")
							  				)
							  		->groupBy("$flowDataValue.OCCUR_DATE")
							  		->groupBy("$flow.ID")
							  		->groupBy("$flow.name")
							  		->groupBy("$pdLiftingAccount.INTEREST_PCT")
							  		->groupBy("$flowDataValue.FL_DATA_GRS_VOL")
							  		;
  		$xxquery->union($flowquery);
  		
  		$xxxquery = \DB::table(\DB::raw("({$xxquery->toSql()}) $subXName") )
/*
						->leftjoin ( $storageDataValue." as y",function ($query) use($storageID){
								  			$query->on("y.OCCUR_DATE",'=',"x.xdate")
								  			->where("y.STORAGE_ID",'=',$storageID) ;
							  		})
*/
				  		->select(
				  				"x.*",
//								"y.AVAIL_SHIPPING_VOL as opening_balance",
			  					\DB::raw("(select AVAIL_SHIPPING_VOL from $storageDataValue y where y.storage_id=".$postData["Storage"]." and y.OCCUR_DATE=x.xdate) as opening_balance"),
			  					\DB::raw('case when x.b_qty is null then x.n_qty else null end as n_qty'),
				  				\DB::raw('sum(x.flow_qty) as flow_qty'),
				  				\DB::raw("$ifNull(sum(x.cal_qty),0) cal_qty"),
				  				\DB::raw("' ' as uom")
				  				)
				  		->addBinding($xxquery->getBindings())
  						->groupBy("x.xdate")
  						->groupBy("x.cargo_name")
  						->groupBy("x.flow_name")
  						->groupBy("x.n_qty")
  						->groupBy("x.b_qty")
  						->groupBy("x.flow_qty")
  						->groupBy("x.cal_qty")
  						->orderBy("x.xdate");
			
//   		\DB::enableQueryLog();
  		$originAttrCase = \Helper::setGetterLowerCase();
  		$dataSet = $xxxquery->get();
  		\Helper::setGetterCase($originAttrCase);
//   		\Log::info(\DB::getQueryLog());
  		$beginMonth					= $occur_date->copy()->startOfMonth();
  		$endMonth					= $date_end->copy()->endOfMonth();
  		$pdCodeLiftAcctAdj			= PdCodeLiftAcctAdj::getTableName();
  		$pdLiftingAccountMthData	= PdLiftingAccountMthData::getTableName();
  		
  		$originAttrCase = \Helper::setGetterUpperCase();
  		$monthlyData 				= PdLiftingAccountMthData::join($pdCodeLiftAcctAdj,
  															"$pdLiftingAccountMthData.ADJUST_CODE", '=', "$pdCodeLiftAcctAdj.ID")
  										->where("$pdLiftingAccountMthData.LIFTING_ACCOUNT_ID",$accountId)
  		 								->whereDate("$pdLiftingAccountMthData.BALANCE_MONTH", '>=', $beginMonth)
  		 								->whereDate("$pdLiftingAccountMthData.BALANCE_MONTH", '<=', $endMonth)
  		 								->select(
								  				"$pdCodeLiftAcctAdj.NAME as ADJUST_NAME",
								  				"$pdCodeLiftAcctAdj.CODE as ADJUST_CODE_NAME",
  		 										"$pdLiftingAccountMthData.LIFTING_ACCOUNT_ID",
								  				"$pdLiftingAccountMthData.BALANCE_MONTH",
								  				"$pdLiftingAccountMthData.ADJUST_CODE",
  		 								 		"$pdLiftingAccountMthData.BAL_VOL")
						  				->orderBy("$pdLiftingAccountMthData.BALANCE_MONTH")
  										->get();
  		\Helper::setGetterCase($originAttrCase);
  		$groupedMonthlyData	= null;
  		if ($monthlyData) {
    		$monthlyData->each(function ($item, $key){
    			$item->MONTH_KEY	= $item->BALANCE_MONTH->format('Y-m');
    		});
    		
    		$groupedMonthlyData	= $monthlyData->groupBy(function ($item, $key){
    			return $item->MONTH_KEY;
    		});
    		
    		foreach($groupedMonthlyData as $month => $accountBalances ){
    			$total	= 0;
    			if ($accountBalances) {
    				$accountBalances->each(function ($accountData, $adjustment) use (&$total){
	    				$total	+= $accountData->BAL_VOL;
    				});
    			}
    			$accountBalances->total	= $total;
    		}
  		}
  		
		$month		="";
		if ($groupedMonthlyData&&isset($groupedMonthlyData[$beginMonth->format('Y-m')])) {
			$balance 	= $groupedMonthlyData[$beginMonth->format('Y-m')]->total;
		}
		else  $balance 	= 0;
		$preItem	= null;
		foreach($dataSet as $key => $item ){
			$date 			= Carbon::parse($item->xdate);
			$item->carbonDate= $date;
			$balance		= $this->geMonthlytBalance($balance,$groupedMonthlyData,$date,$preItem);
			$balance		+=$item->cal_qty;
			$item->cal_qty 	= $balance;
			$preItem		= $item;
		}
  		
  		return ['dataSet'		=> $dataSet,
  				'monthlyData'	=> $monthlyData
  		];
  		
    }
    
    public function geMonthlytBalance($balance,$groupedMonthlyData,$date,$preItem){
//     	if ($preItem) {
	    	if ($groupedMonthlyData&&(($preItem&&$preItem->carbonDate->month!= $date->month)||(!$preItem))) {
				$monthKey		= $date->format('Y-m');
				$total			= null;
    		 	if(isset($groupedMonthlyData[$monthKey])){
    		 		$monthRow	= $groupedMonthlyData[$monthKey];
    		 		if ($monthRow) {
	    		 		$total		= $groupedMonthlyData[$monthKey]->total;
	    		 		if (count($monthRow) == 1 && $monthRow->get(0)->ADJUST_CODE_NAME== "MANADJ") {
	    		 			$total	+= $preItem?$preItem->cal_qty:0;
	    		 		}
    		 		}
    		 				
    		 	}
    		 	$balance	= $total?$total:$balance;
	    	}
//     	}
    	
    	return $balance;
    }
    
}
