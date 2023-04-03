<?php

namespace App\Providers;

use App\Contracts\InvoiceServer;
use App\Contracts\UserServer;
use App\Services\InvoiceService;
use App\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserServer::class, function (Application $app) {
            return new UserService();
        });
        $this->app->bind(InvoiceServer::class, function (Application $app) {
            return new InvoiceService($app->get('redis'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            UserServer::class,
            InvoiceServer::class,
        ];
    }
}
