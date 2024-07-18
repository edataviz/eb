<?php namespace App\Http\Middleware;

use Closure;

class SaveWorkspace {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$wp = $request->only('date_begin', 'Facility','date_end','disable_date_end');
		$currentUser = auth()->user();
		if ($currentUser) {
			$currentUser->saveWorkspace($wp['date_begin'],$wp['Facility'],$wp['date_end']);
			session()->put('disable_date_end', $wp['disable_date_end']);
		}
		return $next($request);
	}
}
