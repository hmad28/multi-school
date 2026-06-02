<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('platform.use_path_routing')) {
            $this->registerPathRoutes();
        } elseif (config('platform.use_subdomain_routing')) {
            $this->registerSubdomainRoutes();
        }

        $this->registerCentralWebRoutes();
    }

    /**
     * Dev & default: /platform/login, /t/demo/login — any host (127.0.0.1, localhost, …).
     */
    protected function registerPathRoutes(): void
    {
        Route::middleware('web')->group(function () {
            Route::prefix('platform')
                ->name('platform.')
                ->group(base_path('routes/platform.php'));

            Route::prefix('t/{tenant}')
                ->where(['tenant' => '[a-z0-9\-]+'])
                ->name('tenant.')
                ->group(base_path('routes/tenant.php'));
        });
    }

    /**
     * Production: admin.platformsekolah.id, {slug}.platformsekolah.id
     */
    protected function registerSubdomainRoutes(): void
    {
        $baseDomain = config('platform.tenant_base_domain');

        Route::middleware('web')
            ->domain(config('platform.admin_domain'))
            ->name('platform.')
            ->group(base_path('routes/platform.php'));

        Route::middleware('web')
            ->domain('{tenant}.'.$baseDomain)
            ->where(['tenant' => '[a-z0-9\-]+'])
            ->name('tenant.')
            ->group(base_path('routes/tenant.php'));
    }

    protected function registerCentralWebRoutes(): void
    {
        foreach (config('platform.central_domains') as $domain) {
            Route::middleware('web')
                ->domain($domain)
                ->group(base_path('routes/web.php'));
        }
    }
}
