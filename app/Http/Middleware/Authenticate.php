<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($request->path()=="dcloadconfig"||$request->path()=="dcsavedata"||$request->path()=="refreshtoken" ){
			try {
				if (auth("api")->guest()) return response()->json(["message"=>'Unauthorized']);
			} catch (\Exception $e) {
				return response()->json(["message"=>$e->getMessage()]);
			}
			return $next($request);
		}
		
		$user = $this->auth->user();
		if($user){
			if(!$user->ACTIVE){
				$this->auth->logout();
			}
		}

		if ($this->auth->guest()){
			if ($request->ajax()){
				return response('Unauthorized.', 401);
			}
			else{
				return redirect()->guest('/');
			}
		}
		return $next($request);
	}
	
	public function terminate($request, $response)
	{
		$user = $this->auth->user();
		if($user){
			\Log::info($user->username,['action' => $request->path(), 'data' => $request->all()]);
			//\Log::info($request->path());
			//Log::info('app.requests', ['request' => $request->all(), 'response' => $response]);
		}
	}
}
