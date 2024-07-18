<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;


class UserSettingController extends EBController {
	
	use AuthenticatesAndRegistersUsers;
	
	public function index(){
		$user = auth()->user();
		return view ( 'me.setting',['user'=>$user]);
	}
	
	public function saveSetting(Request $request){
		
		$postData 			= $request->all();
     	$dateformat 		= array_key_exists('dateformat',  		$postData)?$postData['dateformat']:null;
     	$timeformat 		= array_key_exists('timeformat',  		$postData)?$postData['timeformat']:null;
     	$numberformat 		= array_key_exists('numberformat',  	$postData)?$postData['numberformat']:null;
     	$defaultDashboard 	= array_key_exists('defaultDashboard',  $postData)?$postData['defaultDashboard']:null;
     	$currentUser 		= auth()->user();
		if ($currentUser){
			if (($dateformat!=null||$timeformat!=null)) {
				$currentUser->saveDateTimeFormat($dateformat,$timeformat);
			}
			if (($numberformat!=null)) {
				$currentUser->saveNumberFormat($numberformat);
			}
			if (($defaultDashboard!==null)) {
				$currentUser->update(["DASHBOARD_ID" => $defaultDashboard?$defaultDashboard:null]);
			}
		}
		else return response()->json(['empty post data']);
		
 		session()->put('configuration', $currentUser->configuration());
		
    	return response()->json(['success']);
	}
	

	public function changePass(LoginRequest $request,Guard $auth){
		$postData 		= $request->all();
		$currentUser 	= auth()->user();
		if ($currentUser){
			$credentials 				= $request->only('password');
			$credentials["username"]	= $currentUser->username;
			if($auth->validate($credentials)){
				$newPassword 			= array_key_exists('newPassword',  	$postData)?$postData['newPassword']:null;
				if ($newPassword&&count($newPassword)>0) {
					$obj 					= new CommonController();
					$currentUser->PASSWORD 	= $obj->myencrypt($newPassword);
					$currentUser->save();
					return response()->json(['update password successfully']);
				}
				return response()->json(['password not validated']);
			}
			return response()->json(['old password is incorrect']);
		}
		else 
			return response()->json(['user not found']);
	}
	
}
