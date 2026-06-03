<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\CustomerPortal\Health\CustomerPortalHealthCheck;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Tests\CustomerPortalTestCase;
use Illuminate\Support\Facades\Config;

uses(CustomerPortalTestCase::class);

it('passes when the required tables and models are present', function (): void {
    expect(CustomerPortalHealthCheck::passed())->toBeTrue();

    $diagnostics = CustomerPortalHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(2)
        ->and($diagnostics->every(fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails when a required table is missing', function (): void {
    Config::set('capell-customer-portal.tables.support_requests', 'portal_support_requests_missing');

    expect(CustomerPortalHealthCheck::passed())->toBeFalse();

    $tableCheck = CustomerPortalHealthCheck::runDiagnostics()->first();

    expect($tableCheck?->passed)->toBeFalse()
        ->and($tableCheck?->message)->toContain('portal_support_requests_missing');
});

it('reports both diagnostics with descriptive labels', function (): void {
    $labels = CustomerPortalHealthCheck::runDiagnostics()
        ->map(fn (DoctorCheckResultData $result): string => $result->label)
        ->all();

    expect($labels)->toContain('Customer Portal database tables')
        ->and($labels)->toContain('Customer Portal models')
        ->and((new PortalAccount)->getTable())->toBe('portal_accounts');
});
