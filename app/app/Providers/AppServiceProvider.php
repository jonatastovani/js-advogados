<?php

namespace App\Providers;

use App\Models\Auth\PersonalAccessToken;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\View;
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
        // Define a variável acessível em todas as views
        View::share('domainCustomIdentificationClassName', config('tenancy_custom.tenant_type.domain_custom_identification_class_name'));

        // $tenantDados = Tenant::with('dadosUnidade')->find(tenant('id'));
        // $tenantDados = Tenant::find(tenant('id'));
        // $tenantDados = (tenant('id'));
        // view()->share('tenantDados', $tenantDados);

    }
}
