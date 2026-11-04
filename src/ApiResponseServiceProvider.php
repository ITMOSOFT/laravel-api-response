<?php

namespace ItmoSoft\ApiResponse;

use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/api-response.php', 'api-response');

        $this->app->singleton(ApiResponseManager::class, function ($app): ApiResponseManager {
            return new ApiResponseManager(
                response: $app->make('Illuminate\\Contracts\\Routing\\ResponseFactory'),
                config: $app->make('config'),
            );
        });

        $this->app->alias(ApiResponseManager::class, 'api-response');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/api-response.php' => config_path('api-response.php'),
            ], 'api-response-config');
        }
    }
}
