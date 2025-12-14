<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Propiedad;
use App\Policies\PropiedadPolicy;

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
        // 1. REGISTRO EXPLÍCITO: Conectamos el Modelo con su Policy
        Gate::policy(Propiedad::class, PropiedadPolicy::class);

        // 2. MODO DIOS GLOBAL (Red de seguridad)
        Gate::before(function ($user, $ability) {
            if ($user->email === 'admin@admin.com') {
                return true;
            }
        });
    }
}