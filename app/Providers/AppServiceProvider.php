<?php

namespace App\Providers;

use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository();
        });

        $this->app->bind(UserService::class, function ($app) {
            return new UserService($app->make(UserRepository::class));
        });
    }

    public function boot(): void
    {
        //
    }
}