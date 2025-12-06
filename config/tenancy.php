<?php

declare(strict_types=1);

return [
    'tenant_model' => \App\Models\Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,

    'domain_model' => \Stancl\Tenancy\Database\Models\Domain::class,

    'central_domains' => [
        '127.0.0.1',
        'localhost',
        env('CENTRAL_DOMAIN', 'restaurfy.test'),
    ],

    'bootstrappers' => [
        Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\RedisTenancyBootstrapper::class,
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'pgsql'),

        'template_tenant_connection' => 'pgsql',

        'prefix_base' => 'restaurfy_tenant_',

        'suffix_base' => '',

        'managers' => [
            'pgsql' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
            'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
        ],
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base' => 'tenant',
        'root_override' => [
            'local' => '%storage_path%/app/',
            'public' => '%storage_path%/app/public/',
        ],
        'suffix_storage_path' => true,
        'asset_helper_tenancy' => true,
    ],

    'redis' => [
        'prefix_base' => 'tenant',
        'prefixed_connections' => [
            'default',
        ],
    ],

    'features' => [
        Stancl\Tenancy\Features\UserImpersonation::class,
        Stancl\Tenancy\Features\TenantConfig::class,
        Stancl\Tenancy\Features\CrossDomainRedirect::class,
        Stancl\Tenancy\Features\ViteBundler::class,
    ],

    'home_url' => '/dashboard',

    'impersonation' => [
        'enabled' => true,
        'session_key' => 'impersonated_by',
        'abort_403' => false,
    ],

    'migration_parameters' => [
        '--force' => true,
        '--path' => [database_path('migrations/tenant')],
    ],

    'seeders' => [
        'class' => DatabaseSeeder::class,
        'parameters' => [],
    ],

    'queue_database_creation' => false,

    'custom_database_actions' => [
        'create' => null,
        'delete' => null,
    ],

    'database_managers' => [
        'pgsql' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
        'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
    ],

    'storage_to_config_mapping' => [
        'url' => 'filesystem.disks.local.url',
        'path' => 'filesystem.disks.local.root',
    ],

    'tenant_route_namespace' => 'App\Http\Controllers\Tenant',

    'exempt_domains' => [
        // 'localhost',
    ],

    'database_fallback_connection' => null,

    'redis_tenancy' => true,

    'redis_prefix_base' => 'tenant',

    'unique_id_generator' => null,

    'global_middleware' => [
        Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    ],

    'middleware_groups' => [
        'web' => [
            // Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ],
    ],

    'middleware_priority' => [
        Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
    ],

    'tenant_middleware' => [
        'universal' => [],
        'web' => [
            Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        ],
        'api' => [
            Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        ],
    ],

    'bootstrapper_map' => [
        'database' => Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        'cache' => Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        'filesystem' => Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        'queue' => Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
        'redis' => Stancl\Tenancy\Bootstrappers\RedisTenancyBootstrapper::class,
    ],

    'tenant_route_model_binding' => true,
];

