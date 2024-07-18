<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\ThrottlesLogins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\UserRepository;
use App\Jobs\SendMail;
use App\Models\User;

class AuthController extends Controller
{

	use AuthenticatesAndRegistersUsers, ThrottlesLogins;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest', ['except' => ['getLogout','postDCLogin','refresh']]);
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  App\Http\Requests\LoginRequest  $request
	 * @param  Guard  $auth
	 * @return Response
	 */
	public function postLogin(
		LoginRequest $request,
		Guard $auth)
	{
		$logValue = $request->input('log');

		$logAccess = filter_var($logValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $throttles = in_array(
            ThrottlesLogins::class, class_uses_recursive(get_class($this))
        );

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
			return redirect('/auth/login')
				->with('error', trans('front/login.maxattempt'))
				->withInput($request->only('log'));
        }

		$credentials = [
			$logAccess  => $logValue, 
			'password'  => $request->input('password')
		];

		if(!$auth->validate($credentials)) {
			if ($throttles) {
	            $this->incrementLoginAttempts($request);
	        }

			return redirect('/auth/login')
				->with('error', trans('front/login.credentials'))
				->withInput($request->only('log'));
		}
			
		$user = $auth->getLastAttempted();

		if($user->confirmed) {
			if ($throttles) {
                $this->clearLoginAttempts($request);
            }

			$auth->login($user, $request->has('memory'));

			if($request->session()->has('user_id'))	{
				$request->session()->forget('user_id');
			}

			return redirect('/');
		}
		
		$request->session()->put('user_id', $user->id);	

		return redirect('/auth/login')->with('error', trans('front/verify.again'));			
	}


	/**
	 * Handle a login request to the application.
	 *
	 * @param  App\Http\Requests\LoginRequest  $request
	 * @param  Guard  $auth
	 * @return Response
	 */
	public function postEBLogin(
			LoginRequest $request,
			Guard $auth)
	{
		$throttles = in_array(ThrottlesLogins::class, class_uses_recursive(get_class($this)));

		if ($throttles && $this->hasTooManyLoginAttempts($request))
			return $this->getLoginReponse(trans('front/login.maxattempt'),$request,422);
		
		$postData	 	= $request->all();
		if(!isset($postData["type"])){
			//usleep( 3000 * 1000 );
			//return $this->getLoginReponse('Wrong request',$request,422);
		}
		$wasRecentlyCreated	= false;
		$loginType		= $request->path()=="dclogin"?"basic":$postData["type"];
		switch ($loginType) {
			case "basic":
				$credentials = $request->only('username', 'password');
				if(!$auth->validate($credentials)) {
					if ($throttles) $this->incrementLoginAttempts($request);
					return $this->getLoginReponse('Wrong username or password', $request, 422);
					/*
					$user = User::where('username', '=', $request->input('username'))->first();
					if ($user == null)
						return $this->getLoginReponse('Wrong username',$request,422);
					else
						return $this->getLoginReponse('Wrong password',$request,422);
					*/
				}
				$user = $auth->getLastAttempted();
				if($user->ACTIVE !=1 ){
					return $this->getLoginReponse("User account is unavailable", $request, 422);
				}
				break;
			case "sso":
				//if (!$request->ajax()) 
				//	return $this->getLoginReponse("Energy builder not support this method",$request,422);
				
				$credentials 	= ['username'	=> $postData['username']];
				$user 			= User::where('username', '=', $postData['username'])->first();
				if (!$user) {// user doesn't exist
					$credentials['password']  = \Hash::make(str_random(8));
					$user 					= User::firstOrCreate($credentials);
       				$user->NAME 			= $postData['username'];
       				$user->save();
					$wasRecentlyCreated 	= true;
				}
				break;
			default:
				return $this->getLoginReponse("Energy builder not support this method",$request,422);
			break;
		}
        if ($throttles) $this->clearLoginAttempts($request);

        $auth->login($user, $request->has('memory'));

        if($request->session()->has('user_id'))
            $request->session()->forget('user_id');

        if($request->path()=="dclogin"){
//         	return response()->json(["message" => "ok"]);
        	if (! $token = auth("api")->attempt($credentials)) {
        		return response()->json(['error' => 'Unauthorized'], 401);
        	}
        	
        	return $this->respondWithToken($token);
		}
		else {
			$url = '/home';
			if (isset($postData['url'])) $url = $postData['url'];
			return redirect($url);
		}
		/*
        else
       		return response([
				'action'	=>	'login',
				'status'	=>	true,
				'msg' 				=> 'ok',
				'language'				=> $user->language,
				'dashboard'				=> $user->DASHBOARD_ID,
				'type'					=> $loginType,
				'menu'  				=> \Helper::generateHomeMenu(),
				'wasRecentlyCreated'	=> $wasRecentlyCreated
//                            'right' => $user->role()
				], 200) // 200 Status Code: Standard response for successful HTTP request
				->header('Content-Type', 'application/json');
		*/
	}
	
	public function postDCLogin(
			LoginRequest $request,
			Guard $auth)
	{
		return $this->postEBLogin($request, $auth);
	}
	
	public function getLoginReponse($message, LoginRequest $request, $responseCode){
		if($request->path()=="dclogin")
			return response()->json(["message" => $message]);
		else{
			//return response($message, $responseCode )->header('Content-Type', 'application/json');
			return redirect('/')->with('loginMessage',$message);
		}
	}
	
	/**
	 * Handle a registration request for the application.
	 *
	 * @param  App\Http\Requests\RegisterRequest  $request
	 * @param  App\Repositories\UserRepository $user_gestion
	 * @return Response
	 */
	public function postRegister(
		RegisterRequest $request,
		UserRepository $user_gestion)
	{
		$user = $user_gestion->store(
			$request->all(), 
			$confirmation_code = str_random(30)
		);

		$this->dispatch(new SendMail($user));

		return redirect('/')->with('ok', trans('front/verify.message'));
	}

	/**
	 * Handle a confirmation request.
	 *
	 * @param  App\Repositories\UserRepository $user_gestion
	 * @param  string  $confirmation_code
	 * @return Response
	 */
	public function getConfirm(
		UserRepository $user_gestion,
		$confirmation_code)
	{
		$user = $user_gestion->confirm($confirmation_code);

        return redirect('/')->with('ok', trans('front/verify.success'));
	}

	/**
	 * Handle a resend request.
	 *
	 * @param  App\Repositories\UserRepository $user_gestion
	 * @param  Illuminate\Http\Request $request
	 * @return Response
	 */
	public function getResend(
		UserRepository $user_gestion,
		Request $request)
	{
		if($request->session()->has('user_id'))	{
			$user = $user_gestion->getById($request->session()->get('user_id'));

			$this->dispatch(new SendMail($user));

			return redirect('/')->with('ok', trans('front/verify.resend'));
		}

		return redirect('/');        
	}
	
	public function refresh()
	{
		return $this->respondWithToken(auth("api")->refresh());
	}
	
	/**
	 * Get the token array structure.
	 *
	 * @param  string $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function respondWithToken($token)
	{
		return response()->json([
				'message' => "ok",
				'access_token' => $token,
				'token_type' => 'bearer',
				'expires_in' => auth("api")->factory()->getTTL() * 60
		]);
	}
	
}
