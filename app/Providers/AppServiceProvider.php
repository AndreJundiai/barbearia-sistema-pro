<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        \Carbon\Carbon::setLocale(config('app.locale', 'pt_BR'));

        View::share('last_deploy', now());

        // Configurações da barbearia disponíveis em todas as views como $shop
        View::composer('*', fn ($view) => $view->with('shop', Setting::all_values()));
    }
}
