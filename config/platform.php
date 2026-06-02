<?php

return [

    'name' => env('PLATFORM_NAME', 'Platform Sekolah'),

    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'trial_days' => (int) env('PLATFORM_TRIAL_DAYS', 14),

    'tenant_base_domain' => env('TENANT_BASE_DOMAIN', 'platformsekolah.test'),

    'admin_domain' => env('ADMIN_DOMAIN', 'admin.platformsekolah.test'),

    'central_domains' => array_filter(array_map(
        'trim',
        explode(',', env('CENTRAL_DOMAINS', 'platformsekolah.test,localhost,127.0.0.1')),
    )),

    'marketing_url' => env('MARKETING_URL', 'http://127.0.0.1:4321'),

    /*
    |--------------------------------------------------------------------------
    | Path routing (/platform/*, /t/{slug}/*)
    |--------------------------------------------------------------------------
    | Default ON — works on http://127.0.0.1:PORT tanpa edit hosts.
    | Production: set PLATFORM_PATH_ROUTING=false, PLATFORM_SUBDOMAIN_ROUTING=true
    */
    'use_path_routing' => filter_var(
        env('PLATFORM_PATH_ROUTING', true),
        FILTER_VALIDATE_BOOL,
    ),

    'use_subdomain_routing' => filter_var(
        env('PLATFORM_SUBDOMAIN_ROUTING', false),
        FILTER_VALIDATE_BOOL,
    ),

];
