<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\KnowledgeBase\Health\KnowledgeBaseHealthCheck;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__, 2) . '/KnowledgeBaseTestCase.php';

uses(KnowledgeBaseTestCase::class);

it('passes when package storage and code surfaces are discoverable', function (): void {
    $diagnostics = KnowledgeBaseHealthCheck::runDiagnostics();

    expect(KnowledgeBaseHealthCheck::passed())->toBeTrue()
        ->and($diagnostics)->toHaveCount(5)
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and($diagnostics->pluck('label')->all())->toBe([
            'Knowledge Base storage tables',
            'Knowledge Base models',
            'Knowledge Base actions',
            'Knowledge Base admin resources',
            'Knowledge Base providers',
        ]);
});

it('fails the storage tables check when a required table is missing', function (): void {
    Schema::drop('knowledge_base_articles');

    $diagnostics = KnowledgeBaseHealthCheck::runDiagnostics();
    $storageResult = $diagnostics->firstOrFail(
        static fn (DoctorCheckResultData $result): bool => $result->label === 'Knowledge Base storage tables',
    );

    expect(KnowledgeBaseHealthCheck::passed())->toBeFalse()
        ->and($storageResult->passed)->toBeFalse()
        ->and($storageResult->message)->toContain('knowledge_base_articles')
        ->and($storageResult->remediation)->not->toBeNull();
});

it('reports discoverable domain actions, admin resources, and providers', function (): void {
    $check = new KnowledgeBaseHealthCheck;

    expect($check->actionsDiscoverableCheck()->passed)->toBeTrue()
        ->and($check->actionsDiscoverableCheck()->message)->toBe(__('capell-knowledge-base::generic.health.actions.passed'))
        ->and($check->adminResourcesDiscoverableCheck()->passed)->toBeTrue()
        ->and($check->adminResourcesDiscoverableCheck()->message)->toBe(__('capell-knowledge-base::generic.health.admin_resources.passed'))
        ->and($check->providersDiscoverableCheck()->passed)->toBeTrue()
        ->and($check->providersDiscoverableCheck()->message)->toBe(__('capell-knowledge-base::generic.health.providers.passed'));
});
