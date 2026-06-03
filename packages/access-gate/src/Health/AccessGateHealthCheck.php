<?php

declare(strict_types=1);

namespace Capell\AccessGate\Health;

use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

final class AccessGateHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $service = app(AccessGateDiagnosticsService::class);
        $router = app(Router::class);

        return $service->runAllChecks($router);
    }

    public static function passed(): bool
    {
        $service = app(AccessGateDiagnosticsService::class);
        $router = app(Router::class);

        return $service->allChecksPassed($router);
    }
}
