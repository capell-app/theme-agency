<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PublicActions\Health\PublicActionsHealthCheck;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(PublicActionsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning four check results', function (): void {
    $results = PublicActionsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when tables, adapter, presets, and form builder bridge are present', function (): void {
    $results = PublicActionsHealthCheck::runDiagnostics();

    expect(PublicActionsHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the dispatch check when a storage table is missing', function (): void {
    Schema::drop('public_action_dispatch_attempts');

    $check = new PublicActionsHealthCheck;

    expect($check->missingTables())->toContain('public_action_dispatch_attempts')
        ->and($check->webhookDispatchCheck()->passed)->toBeFalse()
        ->and(PublicActionsHealthCheck::passed())->toBeFalse();
});

it('fails the security check when private webhook hosts are allowed', function (): void {
    Config::set('capell-public-actions.allow_private_webhook_urls', true);

    $check = new PublicActionsHealthCheck;

    expect($check->webhookSecurityCheck()->passed)->toBeFalse()
        ->and(PublicActionsHealthCheck::passed())->toBeFalse();
});

it('fails the security check when insecure webhook urls are allowed', function (): void {
    Config::set('capell-public-actions.allow_insecure_webhook_urls', true);

    $check = new PublicActionsHealthCheck;

    expect($check->webhookSecurityCheck()->passed)->toBeFalse();
});

it('fails the provider preset check when a preset is removed', function (): void {
    Config::set('capell-public-actions.adapters.presets', [
        'generic' => ['adapter' => 'http_webhook', 'method' => 'POST', 'expects_json' => true],
    ]);

    $check = new PublicActionsHealthCheck;

    expect($check->missingPresetKeys())->toContain('zapier')
        ->and($check->providerPresetsCheck()->passed)->toBeFalse();
});

it('confirms the http webhook adapter and all presets resolve', function (): void {
    $check = new PublicActionsHealthCheck;

    expect($check->hasHttpWebhookAdapter())->toBeTrue()
        ->and($check->missingPresetKeys())->toBe([]);
});
