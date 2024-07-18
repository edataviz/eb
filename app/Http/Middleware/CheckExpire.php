<?php namespace App\Http\Middleware;

use Closure;

class CheckExpire {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// Perform action
		$key 			= \App\Models\Params::getLicenseKey();
		$expiredDate  	= \Helper::getExpireDate($key);
		if (!$expiredDate||$expiredDate->lt(\Carbon\Carbon::now())) {
			$code 		= $expiredDate?"expired":"invalid";
			$message	= $expiredDate?	"Energy Builder is expired on ".$expiredDate->toDateString()
										:"Your license key is invalid";
		if ($request->ajax())
			return response($message, 400);		
		else
			return view ('core.expired',
					['message' 	=> $message,
					"code"		=> $code
			]);
		}
		return $next($request);		
	}
}
