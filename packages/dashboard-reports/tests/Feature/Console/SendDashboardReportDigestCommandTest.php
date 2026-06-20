<?php

declare(strict_types=1);

use Capell\DashboardReports\Notifications\DashboardReportDigestNotification;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(DashboardReportsTestCase::class, CreatesAdminUser::class);

function commandSuperAdminRole(): string
{
    $superAdminRole = config('capell.roles.super_admin', 'super_admin');

    return is_string($superAdminRole) ? $superAdminRole : 'super_admin';
}

beforeEach(function (): void {
    Role::findOrCreate(commandSuperAdminRole());
});

it('sends dashboard report digests to configured recipients from the command', function (): void {
    Notification::fake();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-08 12:00:00'));

    $this->createUserWithRole(commandSuperAdminRole(), [
        'email' => 'owner@example.test',
    ]);

    config()->set('capell-dashboard-reports.digest_recipients', ['owner@example.test']);

    $this
        ->artisan('capell:dashboard-reports:send-digest', [
            '--from' => '2026-05-01',
            '--to' => '2026-05-08',
        ])
        ->assertSuccessful();

    Notification::assertSentOnDemand(DashboardReportDigestNotification::class);
});

it('does not fail when no digest recipients are configured', function (): void {
    config()->set('capell-dashboard-reports.digest_recipients', []);

    $this
        ->artisan('capell:dashboard-reports:send-digest')
        ->assertSuccessful();
});
