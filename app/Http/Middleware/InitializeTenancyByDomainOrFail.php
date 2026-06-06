<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;

class InitializeTenancyByDomainOrFail
{
    protected $tenancy;

    protected $resolver;

    public function __construct(Tenancy $tenancy, DomainTenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        try {
            // Get host and strip port if present (e.g., demo2.localhost:8000 -> demo2.localhost)
            $host = $request->getHost();
            $domain = explode(':', $host)[0];

            // Check if this is a central domain
            if (in_array($domain, config('tenancy.central_domains'))) {
                return $next($request);
            }

            Log::info("Attempting to resolve tenant for domain: {$domain}");

            // Resolve and initialize the tenant
            $tenant = $this->resolver->resolve($domain);
            $this->tenancy->initialize($tenant);

            Log::info("Tenant initialized successfully: {$tenant->id}");

            // Manually invoke the database bootstrapper
            $bootstrapper = app(DatabaseTenancyBootstrapper::class);
            $bootstrapper->bootstrap($tenant);

            Log::info("Database bootstrapper completed for tenant: {$tenant->id}");

            return $next($request);
        } catch (TenantCouldNotBeIdentifiedOnDomainException $e) {
            Log::error("Tenant could not be identified on domain: {$domain}", [
                'host' => $request->getHost(),
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            abort(404, "Tenant could not be identified on domain {$domain}. Please check your subdomain.");
        }
    }
}
