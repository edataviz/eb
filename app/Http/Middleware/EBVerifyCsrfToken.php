<?php namespace App\Http\Middleware;

use Closure;

class EBVerifyCsrfToken extends VerifyCsrfToken {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	protected $except = [];

}
