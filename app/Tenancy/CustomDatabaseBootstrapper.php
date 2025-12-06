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
        \Log::info("=== CustomDatabaseBootstrapper: BOOTSTRAP METHOD CALLED ===");
        
        // Get the database name - use tenant ID directly
        $databaseName = $tenant->id;
        
        \Log::info("CustomDatabaseBootstrapper: Bootstrapping tenant {$databaseName}");
        
        // Set the tenant database connection
        config([
            'database.connections.tenant.database' => $databaseName,
        ]);
        
        \Log::info("CustomDatabaseBootstrapper: Set database config to {$databaseName}");
        
        // Use the database manager to connect to tenant
        $this->database->connectToTenant($tenant);
        
        \Log::info("CustomDatabaseBootstrapper: Connected to tenant database via DatabaseManager");
        \Log::info("=== CustomDatabaseBootstrapper: BOOTSTRAP COMPLETE ===");
    }

    public function revert()
    {
        // Use the database manager to reconnect to central
        $this->database->reconnectToCentral();
        
        \Log::info("CustomDatabaseBootstrapper: Reverted to central database");
    }
}
