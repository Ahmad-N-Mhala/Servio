<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Database\DatabaseManager;

class CustomDatabaseBootstrapper implements TenancyBootstrapper
{
    protected $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    public function bootstrap(Tenant $tenant)
    {
        $databaseName = $tenant->id;

        config([
            'database.connections.tenant.database' => $databaseName,
        ]);

        $this->database->connectToTenant($tenant);
    }

    public function revert()
    {
        $this->database->reconnectToCentral();
    }
}
