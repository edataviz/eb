<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use App\Services\Validation;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		\Event::listen('Illuminate\Database\Events\QueryExecuted', function($query)
		{
			/*
			$sql = $query->sql;
			preg_match_all('/(insert into|delete from|update) (\w+)/i', $sql, $es, PREG_SET_ORDER);
			foreach($es as $ei){
				if(isset($ei[2])){
					$table = $ei[2];
				}
			}
	*/
			\Log::debug($query->sql);
//			\Log::info($query->bindings);
		});
		Validator::resolver(function($translator, $data, $rules, $messages)
		{
		    return new Validation($translator, $data, $rules, $messages);
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		if ($this->app->environment () == 'local') {
			$this->app->register ( 'Laracasts\Generators\GeneratorsServiceProvider' );
		}
	}

}
