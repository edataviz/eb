<?php namespace App\Http\Middleware;

use Closure;

class CheckTableLocked {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next,...$tableNames)
	{
// 		$wp = $request->only('date_begin', 'Facility');
		return $next($request);
	}
}
