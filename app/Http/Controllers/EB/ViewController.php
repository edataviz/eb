<?php

namespace App\Http\Controllers\EB;
use App\Http\Controllers\EBController;

class ViewController extends EBController {
	 
	public function index($menu = '')
	{
        /*
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
		*/
		return view ( 'eb.index',["user"=>auth()->user()]);
	}

    public function flow() {
        $config = [];
		return view ( 'eb.flow', ['config' => $config]);
    }
}
	