<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
        view()->composer(
            'group.production', 'App\Http\ViewComposers\ProductionGroupComposer'
        );

        // Using Closure based composers...
        /* view()->composer('production_group_filter', function ($view) {
        	$view->with('links', ['hello','world!']);
        }); */
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}