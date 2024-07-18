<?php

namespace App\Console;

use Workerman\Worker;
use Workerman\Lib\Timer;
use PHPSocketIO\SocketIO;
use Config;

class LaravelWorkermanServer extends SocketIO
{
	protected $lastConnection;
	
	/**
	 * Start SocketIO server.
	 */
	public static function start() {
		/*
		$port = Config::get('laravel-workerman.server.port');
		$events = Config::get('laravel-workerman.events');
		
		$server = new Self($port);
		
		foreach ($events as $event) {
			new $event($server);
		}
		
		Worker::runAll();
		*/

// Create A Worker and Listens 2346 port, use Websocket protocol
$ws_worker = new Worker("websocket://0.0.0.0:3000");

// 4 processes
$ws_worker->count = 4;

// Emitted when new connection come
$ws_worker->onConnect = function($connection)
{
	// Emitted when websocket handshake done
	$connection->onWebSocketConnect = function($connection)
	{
		echo "New connection\n";
		//var_dump($connection);
		$lastConnection = $connection;
		//$connection->send('hello ' . $connection->id);
	};
};

// Emitted when data is received
$ws_worker->onMessage = function($connection, $data)
{
	// Send hello $data
	$connection->send('hello ' . $data);
};

// Emitted when connection closed
$ws_worker->onClose = function($connection)
{
	echo "Connection closed\n";
};

$ws_worker->onWorkerStart = function($ws_worker)
{
    $time_interval = 3;
    $timer_id = Timer::add($time_interval,
        function() use ($ws_worker)
        {
            foreach($ws_worker->connections as $connection) {
				$data = ["time" => date('Y-m-d H:i:s'), "value" => rand(10,100)];
                $connection->send(json_encode($data));
            }
        }
    );
};

// Run worker
Worker::runAll();		
	}
	
	/**
	 * Stop SocketIO server.
	 */
	public static function stop() {
		Worker::stopAll();
	}
	
	/**
	 * Get SocketIO server status.
	 */
	public static function getStatus() {
		return Worker::getStatus();
	}
}