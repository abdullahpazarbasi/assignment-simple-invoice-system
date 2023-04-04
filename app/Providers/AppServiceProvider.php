<?php

namespace App\Providers;

use App\Contracts\ClientMovementServer;
use App\Contracts\InvoiceServer;
use App\Contracts\UserServer;
use App\Repositories\ConcreteClientMovementRepository;
use App\Repositories\ConcreteInvoiceRepository;
use App\Repositories\ConcreteUserRepository;
use App\Services\ClientMovementService;
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
            return new UserService(new ConcreteUserRepository());
        });
        $this->app->bind(InvoiceServer::class, function (Application $app) {
            return new InvoiceService(new ConcreteInvoiceRepository(), $app->get('redis'));
        });
        $this->app->bind(ClientMovementServer::class, function (Application $app) {
            return new ClientMovementService(new ConcreteClientMovementRepository());
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
            ClientMovementServer::class,
        ];
    }
}
