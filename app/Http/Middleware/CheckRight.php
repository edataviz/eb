<?php namespace App\Http\Middleware;

use Closure;

class CheckRight {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next,$right)
	{
		// Perform action
		$user = auth()->user();
		if ($user && $user->containRight($right)) {
			$request->route()->setParameter('rightCode', $right);
			return $next($request);		
		}
		
		if ($request->ajax()) 
			return response('Unauthorized: You has not right to access', 401);
		else {
			if($user)
				return view ( 'core.unauthorized');
			else {
				$view = (new \App\Http\Controllers\HomeController())->index();
				$view->with('loginMessage', 'Your session was ended. Please login again.');
				$view->with('nextURL', $request->fullUrl());
				return $view;
			}
		}
	}
}
