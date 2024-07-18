<?php

namespace App\Http\Controllers;

use App\Jobs\ChangeLocale;

class HomeController extends Controller
{
	public function __construct() {
		$this->middleware ( 'expire',	['except' => ['submitkey','licensekey']]);
	}

	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function index($menu = '')
	{
		$key 			= \App\Models\Params::getLicenseKey();
		$expiredDate  	= \Helper::getExpireDate($key);
		$message 		= null;
		$action 		= null;
		if ($expiredDate) {
			$now 		= \Carbon\Carbon::now();
			if ($expiredDate->lt($now))
				$action = ['url'=>"/submitkey","message" => "Energy Builder is expired."];
			elseif ($expiredDate->lt($now->addDays(30)))
				$action = ['url'=>"/submitkey","message" => "Energy Builder will be expired on {$expiredDate->toDateString()}. Please consider to extend license."];
		}
		else
			$action = ['url'=>"/submitkey","message" => "Energy Builder needs license key to be active."];
		
		if(\Auth::check()){
			$user = auth()->user();
			return view ( 'eb.home',[
				"action" => $action,
				"user" => $user,
				//'EB' => [
				//	'fav' => ['pm/flow', 'dc/eu', 'diagram','dc/quality','dc/storage','dc/wellstatus','dc/scssv','dc/eutest'],
				//]
			]);
		}
		else
			return view ( 'eb.login', ["action" => $action, "loginMessage" => \Session::get('loginMessage')] );
	}

	/**
	 * Change language.
	 *
	 * @param  App\Jobs\ChangeLocaleCommand $changeLocale
	 * @param  String $lang
	 * @return Response
	 */
	public function language( $lang,
		ChangeLocale $changeLocale)
	{		
		$lang = in_array($lang, config('app.languages')) ? $lang : config('app.fallback_locale');
		$changeLocale->lang = $lang;
		$this->dispatch($changeLocale);

		$au = auth();
		$un  = $au->user();
		if ($un) {
			$un->language	= $lang;
			$un->save();
		}
		return redirect()->back();
	}

	public function simplelogin()
	{
		return 'ok';
	}
	public function loginSuccess()
	{
		$au = auth();
		$un  = $au->user();
		return view('front.logginned');
	}
}
