<?php

namespace App\Providers;

use App\Listeners\AttachUserActivitiesToLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (App::environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        Event::listen(Login::class, AttachUserActivitiesToLogin::class);

    }
}
