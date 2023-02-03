<?php namespace Studio308\MoesifLaravel;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Studio308\MoesifLaravel\Middleware\Moesif;
use Studio308\MoesifLaravel\Sender\MoesifApi;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/moesif.php' => config_path('moesif.php'),
        ]);

        $this->app->singleton(MoesifApi::class, function () {
            return MoesifApi::getInstance(config('moesif.applicationId', 'unknown'), [
                'fork' => true,
                'debug' => config('moesif.debug', false),
            ]);
        });

        $this->app->singleton(Moesif::class, function () {
            return new Moesif(app()->make(MoesifApi::class));
        });

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('moesif', Moesif::class);
    }
}
