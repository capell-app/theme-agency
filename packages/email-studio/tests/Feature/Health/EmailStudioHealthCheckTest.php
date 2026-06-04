<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Health\EmailStudioHealthCheck;
use Capell\EmailStudio\Models\EmailSuppression;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Capell\EmailStudio\Tests\Fixtures\FaultyEmailProviderAdapter;
use Illuminate\Support\Facades\Schema;

it('reports compatible capell api version', function (): void {
    expect(EmailStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs the four declared email studio diagnostics and passes when the slice is installed', function (): void {
    $checks = EmailStudioHealthCheck::runDiagnostics();

    expect($checks)->toHaveCount(4)
        ->and($checks->every(fn (DoctorCheckResultData $check): bool => $check->passed))->toBeTrue()
        ->and($checks->pluck('label')->all())->toBe([
            'Email templates render through typed template actions',
            'Queued email delivery records provider results per recipient',
            'Suppressions are enforced before provider handoff',
            'Provider webhook and inbound reply normalization foundations are present',
        ])
        ->and($checks->every(fn (DoctorCheckResultData $check): bool => $check->remediation === null))->toBeTrue()
        ->and(EmailStudioHealthCheck::passed())->toBeTrue();
});

it('fails the template rendering diagnostic when the templates table is missing', function (): void {
    $missingTable = (new EmailTemplate)->getTable();

    Schema::shouldReceive('hasTable')
        ->andReturnUsing(fn (string $tableName): bool => $tableName !== $missingTable);

    $checks = EmailStudioHealthCheck::runDiagnostics();

    $templateCheck = $checks->firstOrFail(
        fn (DoctorCheckResultData $check): bool => $check->label === 'Email templates render through typed template actions',
    );

    expect($templateCheck->passed)->toBeFalse()
        ->and($templateCheck->message)->toContain('email_templates table')
        ->and($templateCheck->remediation)->not->toBeNull()
        ->and(EmailStudioHealthCheck::passed())->toBeFalse();
});

it('fails the provider delivery diagnostic when no provider adapters are registered', function (): void {
    app()->instance(EmailProviderRegistry::class, new EmailProviderRegistry);

    $checks = EmailStudioHealthCheck::runDiagnostics();

    $providerDeliveryCheck = $checks->firstOrFail(
        fn (DoctorCheckResultData $check): bool => $check->label === 'Queued email delivery records provider results per recipient',
    );

    expect($providerDeliveryCheck->passed)->toBeFalse()
        ->and($providerDeliveryCheck->message)->toContain('at least one registered provider adapter')
        ->and($providerDeliveryCheck->remediation)->not->toBeNull()
        ->and(EmailStudioHealthCheck::passed())->toBeFalse();
});

it('fails the suppressions diagnostic when the suppressions table is missing', function (): void {
    $missingTable = (new EmailSuppression)->getTable();

    Schema::shouldReceive('hasTable')
        ->andReturnUsing(fn (string $tableName): bool => $tableName !== $missingTable);

    $checks = EmailStudioHealthCheck::runDiagnostics();

    $suppressionsCheck = $checks->firstOrFail(
        fn (DoctorCheckResultData $check): bool => $check->label === 'Suppressions are enforced before provider handoff',
    );

    expect($suppressionsCheck->passed)->toBeFalse()
        ->and($suppressionsCheck->message)->toContain('email_suppressions table')
        ->and($suppressionsCheck->remediation)->not->toBeNull()
        ->and(EmailStudioHealthCheck::passed())->toBeFalse();
});

it('fails the provider events diagnostic when registered adapters cannot normalize events and replies', function (): void {
    resolve(EmailProviderRegistry::class)->register(EmailProviderType::Fake, new FaultyEmailProviderAdapter);

    $checks = EmailStudioHealthCheck::runDiagnostics();

    $providerEventsCheck = $checks->firstOrFail(
        fn (DoctorCheckResultData $check): bool => $check->label === 'Provider webhook and inbound reply normalization foundations are present',
    );

    expect($providerEventsCheck->passed)->toBeFalse()
        ->and($providerEventsCheck->message)->toContain('fake provider webhook and reply normalizers')
        ->and($providerEventsCheck->remediation)->not->toBeNull()
        ->and(EmailStudioHealthCheck::passed())->toBeFalse();
});
