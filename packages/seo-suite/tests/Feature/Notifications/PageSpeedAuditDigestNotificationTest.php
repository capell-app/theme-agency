<?php

declare(strict_types=1);

use Capell\Admin\Actions\Notifications\ResolveAdminNotificationRecipientsAction;
use Capell\Admin\Actions\Notifications\SaveAdminNotificationSubscriptionsAction;
use Capell\Admin\Support\Notifications\AdminNotificationGroupRegistry;
use Capell\Core\Database\Factories\UserFactory;
use Capell\SeoSuite\Actions\SendPageSpeedAuditDigestAction;
use Capell\SeoSuite\Data\PageSpeedAuditDigestFindingData;
use Capell\SeoSuite\Data\PageSpeedAuditSummaryData;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Capell\SeoSuite\Notifications\PageSpeedAuditDigestNotification;
use Capell\SeoSuite\Providers\SeoSuiteServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

it('registers the SEO Suite PageSpeed notification group', function (): void {
    $group = resolve(AdminNotificationGroupRegistry::class)
        ->get(SendPageSpeedAuditDigestAction::NOTIFICATION_GROUP);

    expect($group?->key)->toBe(SendPageSpeedAuditDigestAction::NOTIFICATION_GROUP)
        ->and($group?->label)->toBe(__('capell-seo-suite::generic.pagespeed_digest_group_label'));
});

it('resolves PageSpeed digest recipients through admin notification subscriptions', function (): void {
    Role::findOrCreate('super_admin');

    $superAdmin = UserFactory::new()->create()->assignRole('super_admin');
    $developer = UserFactory::new()->create();

    SaveAdminNotificationSubscriptionsAction::run($superAdmin, []);
    SaveAdminNotificationSubscriptionsAction::run($developer, [SendPageSpeedAuditDigestAction::NOTIFICATION_GROUP]);

    $recipients = ResolveAdminNotificationRecipientsAction::run(SendPageSpeedAuditDigestAction::NOTIFICATION_GROUP);

    expect($recipients->pluck('id')->all())
        ->toContain($developer->getKey())
        ->not->toContain($superAdmin->getKey());
});

it('resolves default PageSpeed digest recipients through the user role relation query', function (): void {
    $role = Role::findOrCreate('super_admin');
    Role::findOrCreate('editor');

    $superAdmin = UserFactory::new()->create()->assignRole('super_admin');
    $siteScopedAdmin = UserFactory::new()->create();
    $editor = UserFactory::new()->create()->assignRole('editor');
    $unassignedUser = UserFactory::new()->create();

    DB::table('model_has_roles')->insert([
        'role_id' => $role->getKey(),
        'model_type' => $siteScopedAdmin->getMorphClass(),
        'model_id' => $siteScopedAdmin->getKey(),
        'team_id' => 123,
    ]);

    $provider = app()->getProvider(SeoSuiteServiceProvider::class);
    $method = new ReflectionMethod(SeoSuiteServiceProvider::class, 'defaultPageSpeedDigestRecipients');

    DB::flushQueryLog();
    DB::enableQueryLog();

    $recipients = $method->invoke($provider);

    $queries = collect(DB::getQueryLog());
    DB::disableQueryLog();

    $userQueries = $queries
        ->pluck('query')
        ->filter(fn (string $query): bool => str_contains($query, 'from "users"') || str_contains($query, 'from `users`'))
        ->values();

    expect($recipients->pluck('id')->all())
        ->toContain($superAdmin->getKey())
        ->not->toContain($siteScopedAdmin->getKey(), $editor->getKey(), $unassignedUser->getKey())
        ->and($userQueries)->toHaveCount(1)
        ->and(strtolower($userQueries->first() ?? ''))->toContain('exists', 'model_has_roles', 'roles');
});

it('sends PageSpeed digest mail notifications only to subscribed recipients', function (): void {
    Notification::fake();
    Role::findOrCreate('super_admin');

    $superAdmin = UserFactory::new()->create()->assignRole('super_admin');
    $developer = UserFactory::new()->create();

    SaveAdminNotificationSubscriptionsAction::run($superAdmin, []);
    SaveAdminNotificationSubscriptionsAction::run($developer, [SendPageSpeedAuditDigestAction::NOTIFICATION_GROUP]);

    SendPageSpeedAuditDigestAction::run(new PageSpeedAuditSummaryData(
        run: PageSpeedAuditRun::query()->create([
            'trigger' => 'scheduled',
            'status' => 'succeeded',
            'target_count' => 2,
            'success_count' => 2,
            'failure_count' => 0,
            'notification_status' => 'pending',
            'started_at' => now(),
            'completed_at' => now(),
        ]),
        auditedPages: 1,
        successfulResults: 2,
        failedResults: 0,
        poorResults: 0,
    ));

    Notification::assertSentTo($developer, PageSpeedAuditDigestNotification::class);
    Notification::assertNotSentTo($superAdmin, PageSpeedAuditDigestNotification::class);
});

it('includes worst scores and drops in the PageSpeed digest mail', function (): void {
    $notification = new PageSpeedAuditDigestNotification(new PageSpeedAuditSummaryData(
        run: PageSpeedAuditRun::query()->create([
            'trigger' => 'scheduled',
            'status' => 'succeeded',
            'target_count' => 1,
            'success_count' => 1,
            'failure_count' => 0,
            'started_at' => now(),
            'completed_at' => now(),
        ]),
        auditedPages: 1,
        successfulResults: 1,
        failedResults: 0,
        poorResults: 1,
        worstMobileResults: [
            new PageSpeedAuditDigestFindingData('https://example.test/slow', PageSpeedStrategyEnum::Mobile, 41),
        ],
        biggestDrops: [
            new PageSpeedAuditDigestFindingData('https://example.test/slow', PageSpeedStrategyEnum::Mobile, 41, 90, 49),
        ],
        belowThresholdResults: [
            new PageSpeedAuditDigestFindingData('https://example.test/slow', PageSpeedStrategyEnum::Mobile, 41),
        ],
    ));

    $message = $notification->toMail(UserFactory::new()->create());

    expect($message->introLines)->toContain(
        __('capell-seo-suite::generic.pagespeed_digest_worst_mobile', [
            'items' => 'Mobile https://example.test/slow: 41',
        ]),
        __('capell-seo-suite::generic.pagespeed_digest_biggest_drops', [
            'items' => 'Mobile https://example.test/slow: 41 (down 49 from 90)',
        ]),
    );
});
