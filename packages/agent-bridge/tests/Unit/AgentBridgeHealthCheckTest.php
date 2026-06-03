<?php

declare(strict_types=1);

use Capell\AgentBridge\Health\AgentBridgeHealthCheck;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;

it('returns a compatible capell api version', function (): void {
    expect(AgentBridgeHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs diagnostics and returns a collection of check results', function (): void {
    $results = AgentBridgeHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(5)
        ->and($results->every(fn (mixed $check): bool => $check instanceof DoctorCheckResultData))->toBeTrue();
});

it('checks that the laravel mcp package is available', function (): void {
    $results = AgentBridgeHealthCheck::runDiagnostics();

    $mcpCheck = $results->first(fn (DoctorCheckResultData $check): bool => $check->label === 'Laravel MCP package');

    expect($mcpCheck)->not->toBeNull()
        ->and($mcpCheck->passed)->toBeTrue()
        ->and($mcpCheck->message)->toContain('installed and available');
});

it('checks that agent bridge database tables exist', function (): void {
    $results = AgentBridgeHealthCheck::runDiagnostics();

    $tableCheck = $results->first(fn (DoctorCheckResultData $check): bool => $check->label === 'Agent Bridge database tables');

    expect($tableCheck)->not->toBeNull()
        ->and($tableCheck->passed)->toBeTrue()
        ->and($tableCheck->message)->toContain('All required Agent Bridge tables are present');
});

it('checks that the capability registry is resolvable with capabilities', function (): void {
    $results = AgentBridgeHealthCheck::runDiagnostics();

    $registryCheck = $results->first(fn (DoctorCheckResultData $check): bool => $check->label === 'Agent Bridge capability registry');

    expect($registryCheck)->not->toBeNull()
        ->and($registryCheck->passed)->toBeTrue()
        ->and($registryCheck->message)->toContain('capability(ies) registered');
});

it('checks route configuration status', function (): void {
    $results = AgentBridgeHealthCheck::runDiagnostics();

    $routeCheck = $results->first(fn (DoctorCheckResultData $check): bool => $check->label === 'Agent Bridge MCP routes');

    expect($routeCheck)->not->toBeNull()
        ->and($routeCheck->passed)->toBeTrue()
        ->and($routeCheck->message)->toContain('site');
});

it('reports failed routes check when all routes are disabled', function (): void {
    config()->set('capell-agent-bridge.routes.site', null);
    config()->set('capell-agent-bridge.routes.knowledge', null);

    $results = AgentBridgeHealthCheck::runDiagnostics();

    $routeCheck = $results->first(fn (DoctorCheckResultData $check): bool => $check->label === 'Agent Bridge MCP routes');

    expect($routeCheck)->not->toBeNull()
        ->and($routeCheck->passed)->toBeFalse()
        ->and($routeCheck->message)->toContain('No MCP server routes are enabled');
});

it('returns overall passed status based on all checks', function (): void {
    expect(AgentBridgeHealthCheck::passed())->toBeTrue();
});
