<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Newsletter\Health\NewsletterHealthCheck;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(NewsletterHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning four check results', function (): void {
    $results = NewsletterHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the listener, storage tables, and segment evaluation are healthy', function (): void {
    $check = new NewsletterHealthCheck;

    expect($check->formSubmissionListenerRegistered())->toBeTrue()
        ->and($check->segmentEvaluatesToBuilder())->toBeTrue()
        ->and($check->missingTables(['newsletter_subscribers', 'newsletter_consent_events', 'newsletter_sync_attempts', 'newsletter_processed_webhook_events', 'newsletter_segments']))->toBe([])
        ->and(NewsletterHealthCheck::passed())->toBeTrue()
        ->and(NewsletterHealthCheck::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('reports the consent table as missing for the form subscription check', function (): void {
    Schema::shouldReceive('hasTable')
        ->with('newsletter_subscribers')->andReturnTrue();
    Schema::shouldReceive('hasTable')
        ->with('newsletter_consent_events')->andReturnFalse();

    $check = new NewsletterHealthCheck;
    $result = $check->formSubscriptionCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('newsletter_consent_events');
});

it('reports the sync attempt table as missing for the provider sync retry check', function (): void {
    Schema::shouldReceive('hasTable')
        ->with('newsletter_sync_attempts')->andReturnFalse();

    $check = new NewsletterHealthCheck;
    $result = $check->providerSyncRetryCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('newsletter_sync_attempts')
        ->and($result->remediation)->not->toBeNull();
});

it('reports the idempotency table as missing for the provider webhook check', function (): void {
    Schema::shouldReceive('hasTable')
        ->with('newsletter_processed_webhook_events')->andReturnFalse();

    $check = new NewsletterHealthCheck;
    $result = $check->providerWebhookCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('newsletter_processed_webhook_events');
});

it('fails the aggregate check when a required table is missing', function (): void {
    Schema::shouldReceive('hasTable')->andReturnFalse();

    expect(NewsletterHealthCheck::passed())->toBeFalse();
});
