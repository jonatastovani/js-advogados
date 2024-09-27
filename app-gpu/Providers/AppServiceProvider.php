<?php

namespace App\Providers;

use App\Models\Auth\PersonalAccessToken;
use App\Models\Auth\Tenant;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        
        // $tenantDados = Tenant::with('dadosUnidade')->find(tenant('id'));
        // $tenantDados = Tenant::find(tenant('id'));
        // $tenantDados = (tenant('id'));
        // view()->share('tenantDados', $tenantDados);

    }
}
