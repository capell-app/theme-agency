<?php

declare(strict_types=1);

use Capell\LoginAudit\Actions\ApplyLoginAuditSettingsAction;
use Capell\LoginAudit\Actions\RecordLoginAuditPurgeAction;
use Capell\LoginAudit\Models\LoginAudit;
use Capell\LoginAudit\Settings\LoginAuditSettings;
use Carbon\CarbonImmutable;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog as VendorLoginAudit;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

function seedLoginAuditSetting(string $settingName, mixed $value): void
{
    /** @var SettingsMigrator $settingsMigrator */
    $settingsMigrator = resolve(SettingsMigrator::class);
    $settingKey = 'login_audit.' . $settingName;

    if ($settingsMigrator->exists($settingKey)) {
        $settingsMigrator->update($settingKey, fn (): mixed => $value);

        return;
    }

    $settingsMigrator->add($settingKey, $value);
}

it('uses retention days settings for the purge command configuration', function (): void {
    seedLoginAuditSetting('show_login_audits', true);
    seedLoginAuditSetting('retention_days', 42);
    seedLoginAuditSetting('track_user_ip_addresses', true);
    seedLoginAuditSetting('track_admin_activity', true);
    seedLoginAuditSetting('activity_update_grace_seconds', 60);
    seedLoginAuditSetting('last_purged_at', null);

    config()->set('login-audit.purge', 365);
    config()->set('authentication-log.purge', 365);

    ApplyLoginAuditSettingsAction::run();

    expect(config('login-audit.purge'))->toBe(42)
        ->and(config('authentication-log.purge'))->toBe(42);
});

it('records the last successful login audit purge timestamp in settings', function (): void {
    seedLoginAuditSetting('show_login_audits', true);
    seedLoginAuditSetting('retention_days', 90);
    seedLoginAuditSetting('track_user_ip_addresses', true);
    seedLoginAuditSetting('track_admin_activity', true);
    seedLoginAuditSetting('activity_update_grace_seconds', 60);
    seedLoginAuditSetting('last_purged_at', null);
    seedLoginAuditSetting('enable_user_resource_bridge', true);

    $purgedAt = CarbonImmutable::parse('2026-06-04 09:15:00', 'UTC');

    RecordLoginAuditPurgeAction::run($purgedAt);

    app()->forgetInstance(LoginAuditSettings::class);

    expect(resolve(LoginAuditSettings::class)->last_purged_at)->toBe($purgedAt->toIso8601String());
});

it('removes stored ip addresses when tracking is disabled', function (): void {
    seedLoginAuditSetting('show_login_audits', true);
    seedLoginAuditSetting('retention_days', 90);
    seedLoginAuditSetting('track_user_ip_addresses', false);
    seedLoginAuditSetting('track_admin_activity', true);
    seedLoginAuditSetting('activity_update_grace_seconds', 60);
    seedLoginAuditSetting('last_purged_at', null);

    app()->forgetInstance(LoginAuditSettings::class);

    $authenticationLog = LoginAudit::factory()->create([
        'ip_address' => '203.0.113.10',
    ]);

    expect($authenticationLog->refresh()->ip_address)->toBeNull();
});

it('removes vendor-created ip addresses when tracking is disabled', function (): void {
    seedLoginAuditSetting('show_login_audits', true);
    seedLoginAuditSetting('retention_days', 90);
    seedLoginAuditSetting('track_user_ip_addresses', false);
    seedLoginAuditSetting('track_admin_activity', true);
    seedLoginAuditSetting('activity_update_grace_seconds', 60);
    seedLoginAuditSetting('last_purged_at', null);

    app()->forgetInstance(LoginAuditSettings::class);

    $authenticationLog = new VendorLoginAudit;
    $authenticationLog->forceFill([
        'authenticatable_type' => 'user',
        'authenticatable_id' => 999_999,
        'ip_address' => '203.0.113.10',
        'user_agent' => 'Capell Test Browser',
        'login_at' => now(),
        'login_successful' => true,
    ]);
    $authenticationLog->save();

    expect($authenticationLog->refresh()->ip_address)->toBeNull();
});
