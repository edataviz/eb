<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 3/10/2018
 * Time: 2:02 PM
 */

namespace App\Events;

use App\Models\ChatHistory;
use Illuminate\Console\Command;
use PHPSocketIO\SocketIO;
use App\Models\UserActive;
use App\Models\User;
use Carbon\Carbon;


class SendChatMessage extends Command
{
    /**
     * Create a new event instance.
     *
     * @param PHPSocketIO\SocketIO $server
     * @return void
     */

    public function __construct($server)
    {

        $server->on('connection', function ($socket) use ($server) {
            $id = $socket->id;
            $socket->on('read-mess', function ($name) use($server){

                $my_name=$name[0];
                $name2=$name[1];

                $name_name = array($my_name, $name2);
                sort($name_name);

                $name1 = $name_name[0];
                $name3 = $name_name[1];
                $name_name2 = $name1 . "_" . $name3;
                ChatHistory::where('FromPersion',$name2)->where('id',$name_name2)->update(['isRead'=>1]);

                /*$read = ChatHistory::selectRaw('FromPersion , count(*) AS countElements')
                    ->where('id','like',"%_".$my_name)
                    ->where('isRead',"=",'0')
                    ->where('FromPersion','NOT LIKE',$my_name)
                    ->orWhere('id','like',$my_name."_%")
                    ->where('isRead',"=",'0')
                    ->where('FromPersion','NOT LIKE',$my_name)
                    ->groupBy('id')
                    ->get();
                $server->emit('waiting-message',$read);*/


            });

            /*$socket->on('client-get-listusername', function ($username) use ($server, $id){
                var_dump("aaa");
                $isdeleted = UserActive::select('username', 'socket_id')->where(['username' => $username])->get();
                \Log::info($isdeleted);

                if ($isdeleted != "[]") {
                    UserActive::where(['username' => $username])->delete();

                }

                UserActive::insert(['username' => $username, 'socket_id' => $id]);




            });*/

            $socket->on('client-sent-username', function ($username) use ($server, $id) {

                $isdeleted = UserActive::select('username', 'socket_id')->where(['username' => $username])->get();


                if ($isdeleted != "[]") {
                    UserActive::where(['username' => $username])->delete();
                    UserActive::insert(['username' => $username, 'socket_id' => $id]);
                }

                else{
                    UserActive::insert(['username' => $username, 'socket_id' => $id]);
                }
                $user = new \stdClass();
                $user->name = $username;
                $user->socket_id = $id;

                $server->emit('server-add-username',$user);

                $datas = UserActive::select('socket_id', 'username')->get();

                //var_dump($datas);
                $list_user = [];


                foreach ($datas as $key => $data) {

                    $list_user[$data->socket_id] = $data->username;
                }
                var_dump($list_user);
                $server->to($id)->emit('server-sent-username', $list_user);


                $read = ChatHistory::selectRaw('FromPersion , count(*) AS countElements')
                    ->where('id','like',"%_".$username)
                    ->where('isRead',"=",'0')
                    ->where('FromPersion','NOT LIKE',$username)
                    ->orWhere('id','like',$username."_%")
                    ->where('isRead',"=",'0')
                    ->where('FromPersion','NOT LIKE',$username)
                    ->groupBy('id')
                    ->get();


                $server->to($id)->emit('number-waitting-messs', $read);


            });
            $socket->on('chat message', function ($data) use ($server,$id) {

                $a = $data[1];

                $b = $data[2];
                $ab = $data[0];

                $name_name = array($data[3]['recived_name'], $data[2]['name']);
                $d= $data[3]['recived_name'];
                sort($name_name);

                $name1 = $name_name[0];
                $name2 = $name_name[1];
                $name_name2 = $name1 . "_" . $name2;

                $data = [$b['name'], $a['index'], $ab['socket_id']];
                $current_time = Carbon::now()->toDateTimeString();
                ChatHistory::insert(['id' => $name_name2, 'FromPersion' => $b['name'], 'Messengers' => $a['index'], 'time' => $current_time]);
                $server->to($ab['socket_id'])->emit('chat messages', $data);
                var_dump($d);
                var_dump('2222');
                $read = ChatHistory::selectRaw('FromPersion , count(*) AS countElements')
                    ->where('id','like',"%_".$d)
                    ->where('isRead',"=",'0')
                    ->where('FromPersion','NOT LIKE',$d)
                    ->orWhere('id','like',$d."_%")
                    ->where('isRead',"=",'0')
                    ->where('FromPersion','NOT LIKE',$d)
                    ->groupBy('id')
                    ->get();

                $server->to($ab['socket_id'])->emit('waiting-message',$read);
                var_dump('11111');

                //\Log::info($read);

            });
            $socket->on('disconnect', function () use ($server, $id) {

                UserActive::where(['socket_id' => $id])->delete();

                $server->emit('someone-disconnect', $id);
            });
        });

    }
}