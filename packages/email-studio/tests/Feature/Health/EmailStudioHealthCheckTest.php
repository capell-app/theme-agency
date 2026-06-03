<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\EmailStudio\Health\EmailStudioHealthCheck;
use Capell\EmailStudio\Models\EmailTemplate;
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
            'Provider webhook events and inbound replies normalize into local records',
        ])
        ->and($checks->every(fn (DoctorCheckResultData $check): bool => $check->remediation === null))->toBeTrue()
        ->and(EmailStudioHealthCheck::passed())->toBeTrue();
});

it('fails the template rendering diagnostic when the templates table is missing', function (): void {
    Schema::drop((new EmailTemplate)->getTable());

    $checks = EmailStudioHealthCheck::runDiagnostics();

    $templateCheck = $checks->firstOrFail(
        fn (DoctorCheckResultData $check): bool => $check->label === 'Email templates render through typed template actions',
    );

    expect($templateCheck->passed)->toBeFalse()
        ->and($templateCheck->message)->toContain('email_templates table')
        ->and($templateCheck->remediation)->not->toBeNull()
        ->and(EmailStudioHealthCheck::passed())->toBeFalse();
});
