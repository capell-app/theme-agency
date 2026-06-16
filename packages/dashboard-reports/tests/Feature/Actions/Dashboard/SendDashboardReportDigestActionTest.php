<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\DashboardReports\Actions\Dashboard\SendDashboardReportDigestAction;
use Capell\DashboardReports\Notifications\DashboardReportDigestNotification;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(DashboardReportsTestCase::class, CreatesAdminUser::class);

beforeEach(function (): void {
    Role::findOrCreate(config('capell.roles.super_admin', 'super_admin'));
});

it('sends dashboard report digests as each recipient actor', function (): void {
    Notification::fake();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-08 12:00:00'));

    $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'), [
        'email' => 'editor@example.test',
    ]);

    Page::factory()->published(CarbonImmutable::parse('2026-05-03 09:00:00'))->create();
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-06 09:00:00'),
    ]);

    $result = SendDashboardReportDigestAction::run(
        ['editor@example.test', 'missing@example.test'],
        CarbonImmutable::parse('2026-05-01 00:00:00'),
        CarbonImmutable::parse('2026-05-08 23:59:59'),
        90,
    );

    expect($result)->toBe([
        'sent' => 1,
        'skipped' => 1,
    ])->and(auth()->user())->toBeNull();

    Notification::assertSentOnDemand(
        DashboardReportDigestNotification::class,
        function (DashboardReportDigestNotification $notification): bool {
            return $notification->toMail(new class {})->subject === __('capell-dashboard-reports::dashboard.digest_subject');
        },
    );
});
