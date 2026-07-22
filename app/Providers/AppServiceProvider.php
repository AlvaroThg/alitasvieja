<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // En producción todo va por HTTPS (detrás de Cloudflare/Traefik).
        // Forzar el esquema garantiza que TODA URL generada —incluido el
        // endpoint de Livewire— sea https y no rompa por mixed content.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
