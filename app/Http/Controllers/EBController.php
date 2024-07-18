<?php

namespace App\Http\Controllers;

use App\Jobs\ChangeLocale;
use Carbon\Carbon;
use App\Http\Requests\LicenseKeyRequest;

class EBController extends Controller {
	
	protected $isReservedName = false;
	protected $isOracle = false;
	protected $isSqlServer = false;
	protected $isMySql = false;
	protected $user = null;
	
	public function __construct() {
		$this->middleware ( 'expire',	['except' => ['submitkey','licensekey']]);
		$this->middleware ( 'auth' 	, 	['except' => ['dcLoadConfig',"dcSave"]]);
		$this->isOracle = config('database.default')==='oracle';
		$this->isMySql = config('database.default')==='mysql';
		$this->isSqlServer = config('database.default')==='sqlsrv';
		$this->isReservedName = $this->isOracle;
		$this->user = auth()->user();
	}
	
	/**
	 * Change language.
	 *
	 * @param App\Jobs\ChangeLocaleCommand $changeLocale        	
	 * @param String $lang        	
	 * @return Response
	 */
	public function language($lang, ChangeLocale $changeLocale) {
		$lang = in_array ( $lang, config ( 'app.languages' ) ) ? $lang : config ( 'app.fallback_locale' );
		$changeLocale->lang = $lang;
		$this->dispatch ( $changeLocale );
		
		return redirect ()->back ();
	}
	
	function loadCodes($table_name){
		$mdl = \Helper::getModelName($table_name);
		$rows = $mdl::loadActive();
		$options = [];
		foreach($rows as $row) $options[] = [$row->ID,$row->NAME];
		return $options;
	}
	
	function submitkey(LicenseKeyRequest $request){
		$postData 	= $request->all();
		$key 		= array_key_exists("licenseKey", $postData)?$postData["licenseKey"]:null;
		$expiredDate= \Helper::getExpireDate($key);
		$code 		= "invalid";
		if ($expiredDate) {
			if ($expiredDate->lt(Carbon::now())){
				$message 	= "The license key you've submmited is expired";
				$code 		= "expired";
			}
			else{
				\App\Models\Params::saveLicenseKey($key);
				$message 	= "Your license is valid to<br>{$expiredDate->toDateString()}";
				$code 		= "valid";
			}
		}
		else
			$message = "Your license key is invalid";
		return view("core.expired")->with([	"message"	=>	$message,
											"code"		=>	$code
		]);
	}
	
	function licensekey(){
		$key 			= \App\Models\Params::getLicenseKey();
		$expiredDate  	= \Helper::getExpireDate($key);
		if (!$expiredDate||$expiredDate->lt(\Carbon\Carbon::now())) {
			$code 		= $expiredDate?"expired":"invalid";
			$message	= $expiredDate?	"Energy Builder is expired on ".$expiredDate->toDateString()
										:"Your license key is invalid";
		}
		else{
			$code 		= "valid";
			$message	= "Energy Builder is valid to ".$expiredDate->toDateString();
		}
		return view ('core.expired',
					['message' 	=> $message,
					"code"		=> $code
		]);	
	}
}
