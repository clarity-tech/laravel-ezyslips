<?php

namespace ClarityTech\Ezyslips;

use Illuminate\Support\ServiceProvider;

class EzyslipsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ezyslips.php' => config_path('ezyslips.php'),
            ], 'ezyslips-config');
        }
        

        $this->app->alias('Ezyslips', 'ClarityTech\Ezyslips\Facades\Ezyslips');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ezyslips.php', 'ezyslips');

        $this->app->singleton('ezyslips', function ($app) {
            return new Ezyslips();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
