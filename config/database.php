<?php
$domain = env('DOMAIN', 'energybuilder.co');
$defaultTenant = "default";
$tenant = $defaultTenant;
$tenant_dbs = [
	'default' => [
		'driver' => env('DB_TYPE', 'mysql'),
		'database' => env('DB_SCHEMA', 'eb'),
		'username'=> env('DB_USERNAME', 'eb'),
		'password' => env('DB_PASSWORD', ''),
	],
];
if (!array_key_exists($tenant,$tenant_dbs)){
	echo("Tenant not found ($tenant)");
	exit;
}

$driver 	= $tenant_dbs[$tenant]['driver'];
$database 	= $tenant_dbs[$tenant]['database'];
$username 	= $tenant_dbs[$tenant]['username'];
$password 	= $tenant_dbs[$tenant]['password'];

return [

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may desire to retrieve records in an
	| array format for simplicity. Here you can tweak the fetch style.
	|
	*/

	'fetch' => PDO::FETCH_CLASS,

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/
		'default' => $driver,
	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => [

		'mysql' => [
			'driver'    => 'mysql',
			'host'      => env('DB_HOST', 'localhost'),
			'database'  => $database,//env('DB_DATABASE', 'energy_builder'),
			'username'  => $username,//env('DB_USERNAME', 'eb'),
			'password'  => $password,//env('DB_PASSWORD', '4v@Zy#m.'),
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
			'options'   => [
			\PDO::ATTR_EMULATE_PREPARES => true
			]
		],

		'sqlsrv' => [
			'driver'   => 'sqlsrv',
			'host' 	   => env('DB_HOST_SQLSRV', 'SPU3-2-1-22-496\SQLEXPRESS'),
			'database' => $database,//env('DB_DATABASE_SQLSRV', 'eb7'),
			'username' => $username,//env('DB_USERNAME_SQLSRV', 'eblara'),
			'password' => $password,//env('DB_PASSWORD_SQLSRV', 'Sqlserver2017@#'),
// 			'prefix'   => env('DB_SCHEMA_SQLSRV', 'eb4'),
		],

		'oracle' => [
			'driver'   => 'oracle',
			'tns'      => env('DB_TNS', ''),
			'port'     => env('DB_PORT', '1521'),
			'host'     => env('DB_HOST_ORACLE', ''),
			'database' => $database,//env('DB_DATABASE_ORACLE', 'ENERGY_BUILDER'),
			'username' => $username,//env('DB_USERNAME_ORACLE', 'energy_builder'),
			'password' => $password,//env('DB_PASSWORD_ORACLE', 'eb123'),
			'charset'  => env('DB_CHARSET', 'AL32UTF8'),
			'prefix'   => env('DB_PREFIX', ''),
    		'options' => [
//     				PDO::ATTR_CASE => PDO::CASE_UPPER,
			],
		],

		'sqlite' => [
			'driver'   => 'sqlite',
			'database' => storage_path().'/database.sqlite',
			'prefix'   => '',
		],

		'pgsql' => [
			'driver'   => 'pgsql',
			'host' 	   => env('DB_HOST', 'localhost'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset'  => 'utf8',
			'prefix'   => '',
			'schema'   => 'public',
		],

	],

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => [

		'cluster' => false,

		'default' => [
			'host'     => '127.0.0.1',
			'port'     => 6379,
			'database' => 0,
		],

	],

];
