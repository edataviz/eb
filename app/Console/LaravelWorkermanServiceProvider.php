<?php

namespace ArmandGarot\LaravelWorkerman;

use Illuminate\Support\ServiceProvider;

use Illuminate\Foundation\Application as LaravelApplication;
use App\Console\Commands\WorkermanCommand;

class LaravelWorkermanServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 */
	public function boot()
	{
        $this->publishes([
            __DIR__ . '/../config/laravel-workerman.php' => config_path('laravel-workerman.php'),
        ], 'config');
	}

	/**
	 * Register the service provider.
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/laravel-workerman.php', 'laravel-workerman');

		$this->app->bind('command.workerman:server', WorkermanCommand::class);

		$this->commands([
			'command.workerman:server',
		]);
	}
}
