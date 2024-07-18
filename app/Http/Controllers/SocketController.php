<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Request;
use LRedis;

class SocketController extends Controller {
    public function __construct()
    {
        //$this->middleware('guest');
    }

    private $currentUsername = false;
    function getCurrentUsername(){
        if($this->currentUsername === false){
            $this->currentUsername = null;
            $user = \Auth::user();
            if(isset($user->username))
                $this->currentUsername = $user->username;
        }
        return $this->currentUsername;
    }

    public function loadChatView()
    {
        $chatEnable = true;
        $users = \App\Models\User::select('username', 'FIRST_NAME', 'MIDDLE_NAME', 'LAST_NAME')->get();
        return view('partials.chat', ['username' => $this->getCurrentUsername(), 'chatEnable' => $chatEnable]);
    }

    public function registerClient(){
        $redis = LRedis::connection();
        $data = json_encode([
            'command' => 'REG_USER', 
            'username' => $this->getCurrentUsername(),
            'socket_id' => Request::input('socket_id'),
        ]);
        \Log::info($data);
        $redis->publish('command', $data);
    }

    public function sendChatMessage(){
        $redis = LRedis::connection();
		$from = Request::input('from');
        $redis->publish('chat',json_encode(['message' => Request::input('message'), 'from' => $from, 'to' => ''.Request::input('to')]));
		$privateChannel = Request::input('privateChannel');
		if($privateChannel)
			$redis->publish($privateChannel,"Alo from private channel ".date('Y-m-d H:i:s'));
    }
}