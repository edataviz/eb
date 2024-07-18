<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdLiftingAccount;
use App\Models\PdLiftingAccountMthData;

class LiftMonthlyController extends CodeController {
    
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>' ','width'=> 50];
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$accountId 			= $postData['PdLiftingAccount'];
    	$date_end 			= array_key_exists('date_end',  $postData)?$postData['date_end']:null;
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
//     	\DB::enableQueryLog();
//     	$sSQL="SELECT a.ID, $fields FROM PD_LIFTING_ACCOUNT_MTH_DATA a WHERE LIFTING_ACCOUNT_ID = $accountId order by BALANCE_MONTH";
//  	\Log::info(\DB::getQueryLog());
  		
  		$pdLiftingAccountMthData 	= PdLiftingAccountMthData::getTableName();
  		
  		$query = PdLiftingAccountMthData::where("LIFTING_ACCOUNT_ID",$accountId)
// 								  		->whereDate("$shipCargoBlmr.DATE_TIME", '<=', $date_end)
								  		->select(
								  				"$pdLiftingAccountMthData.*",
								  				"$pdLiftingAccountMthData.ID as $pdLiftingAccountMthData",
								  				"$pdLiftingAccountMthData.ID as DT_RowId")
								  		->orderBy("BALANCE_MONTH");
  		$dataSet = $query->get();
  		return ['dataSet'=>$dataSet];
  		
    }
}
