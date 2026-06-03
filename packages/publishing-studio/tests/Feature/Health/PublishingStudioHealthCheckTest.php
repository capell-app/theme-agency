<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PublishingStudio\Checks\AccessibilityCheck;
use Capell\PublishingStudio\Health\PublishingStudioHealthCheck;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(PublishingStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = PublishingStudioHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(2)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when storage tables exist and a publish check is configured', function (): void {
    Config::set('capell.publishing-studio.publish_checks', [AccessibilityCheck::class]);

    $check = new PublishingStudioHealthCheck;

    expect($check->missingTables())->toBe([])
        ->and($check->resolvablePublishCheckCount())->toBe(1)
        ->and(PublishingStudioHealthCheck::passed())->toBeTrue();
});

it('fails the storage table check when a workflow table is missing', function (): void {
    Config::set('capell.publishing-studio.publish_checks', [AccessibilityCheck::class]);

    Schema::drop('publishing_scheduler_events');

    $check = new PublishingStudioHealthCheck;

    expect($check->missingTables())->toContain('publishing_scheduler_events')
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(PublishingStudioHealthCheck::passed())->toBeFalse();
});

it('fails the publish-readiness check when no checks are configured', function (): void {
    Config::set('capell.publishing-studio.publish_checks', []);

    $check = new PublishingStudioHealthCheck;

    expect($check->resolvablePublishCheckCount())->toBe(0)
        ->and($check->publishChecksResolvableCheck()->passed)->toBeFalse()
        ->and(PublishingStudioHealthCheck::passed())->toBeFalse();
});

it('ignores configured publish checks that are not real PublishCheck classes', function (): void {
    Config::set('capell.publishing-studio.publish_checks', [
        'Capell\\PublishingStudio\\Checks\\DoesNotExist',
        AccessibilityCheck::class,
    ]);

    $check = new PublishingStudioHealthCheck;

    expect($check->resolvablePublishCheckCount())->toBe(1);
});
