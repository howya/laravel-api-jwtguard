<?php

namespace App\JWTGuard;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use RBennett\JWTGuard\Middleware\JWTScopes;
use GuzzleHttp\Client;

class JWTGuardServiceProvider extends ServiceProvider
{

    /**
     *
     */
    public function boot()
    {

        Auth::extend('jwt', function ($app, $name, array $config) {

            return new JWTGuard(request(), 'Authorization', new GuzzleAdapter(new Client()));
        });

        $this->app['router']->aliasMiddleware('jwtscopes', JWTScopes::class);

        $this->publishes([
            __DIR__.'/config/jwtguard.php' => config_path('jwtguard.php'),
        ], 'jwtguard');

    }

    /**
     *
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/auth.php', 'auth.guards'
        );
    }
}