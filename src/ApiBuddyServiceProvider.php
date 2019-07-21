<?php
/**
 * ApiBuddyServiceProvider
 *
 * @author: tuanha
 * @last-mod: 11-July-2019
 */

namespace Bkstar123\ApiBuddy;

use Illuminate\Support\ServiceProvider;
use Bkstar123\ApiBuddy\Exceptions\Handler;
use Bkstar123\ApiBuddy\Services\ApiResponser;
use Bkstar123\ApiBuddy\Contracts\ApiResponsible;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Bkstar123\ApiBuddy\Services\ResourceCollectionProcessor;
use Bkstar123\ApiBuddy\Console\Commands\PublishConfiguration;
use Bkstar123\ApiBuddy\Contracts\ResourceCollectionProcessable;

class ApiBuddyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/bkstar123_apibuddy.php' => config_path('bkstar123_apibuddy.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishConfiguration::class,
            ]);
        }

        if (config('bkstar123_apibuddy.replace_exceptionhandler')) {
            $this->app->singleton(ExceptionHandler::class, Handler::class);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/bkstar123_apibuddy.php', 'bkstar123_apibuddy');
        $this->app->singleton(ResourceCollectionProcessable::class, ResourceCollectionProcessor::class);
        $this->app->singleton(ApiResponsible::class, ApiResponser::class);
    }
}
