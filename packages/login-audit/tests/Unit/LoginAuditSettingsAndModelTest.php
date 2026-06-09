<?php

declare(strict_types=1);

use Capell\LoginAudit\Actions\ApplyLoginAuditSettingsAction;
use Capell\LoginAudit\Actions\ShouldTrackUserIpAddressesAction;
use Capell\LoginAudit\Filament\Settings\LoginAuditSettingsSchema;
use Capell\LoginAudit\Health\LoginAuditHealthCheck;
use Capell\LoginAudit\Models\LoginAudit;
use Capell\LoginAudit\Settings\LoginAuditSettings;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;

it('falls back safely when login audit settings cannot be resolved', function (): void {
    app()->bind(LoginAuditSettings::class, fn (): never => throw new RuntimeException('Settings unavailable.'));
    Config::set('login-audit.purge', 365);

    ApplyLoginAuditSettingsAction::run();

    expect(config('login-audit.purge'))->toBe(365)
        ->and(ShouldTrackUserIpAddressesAction::run())->toBeTrue();

    app()->forgetInstance(LoginAuditSettings::class);
});

it('clamps retention days to at least one day', function (): void {
    seedLoginAuditSettingsAndModelSetting('show_login_audits', true);
    seedLoginAuditSettingsAndModelSetting('retention_days', 0);
    seedLoginAuditSettingsAndModelSetting('track_user_ip_addresses', true);
    seedLoginAuditSettingsAndModelSetting('track_admin_activity', true);
    seedLoginAuditSettingsAndModelSetting('activity_update_grace_seconds', 60);
    seedLoginAuditSettingsAndModelSetting('last_purged_at', null);
    seedLoginAuditSettingsAndModelSetting('enable_user_resource_bridge', true);

    Config::set('login-audit.purge', 365);
    Config::set('authentication-log.purge', 365);

    ApplyLoginAuditSettingsAction::run();

    expect(config('login-audit.purge'))->toBe(1)
        ->and(config('authentication-log.purge'))->toBe(1);
});

it('declares settings metadata health compatibility and immutable audit date casts', function (): void {
    $audit = LoginAudit::factory()->create([
        'login_at' => now(),
        'logout_at' => now(),
        'last_seen_at' => now(),
    ]);

    expect(LoginAuditSettings::group())->toBe('login_audit')
        ->and(LoginAuditSettings::schema())->toBe(LoginAuditSettingsSchema::class)
        ->and(resolve(LoginAuditSettings::class)->track_admin_activity)->toBeTrue()
        ->and(resolve(LoginAuditSettings::class)->activity_update_grace_seconds)->toBe(60)
        ->and(LoginAuditHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($audit->login_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($audit->logout_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($audit->last_seen_at)->toBeInstanceOf(DateTimeImmutable::class);
});

it('builds the login audit settings schema controls', function (): void {
    $components = LoginAuditSettingsSchema::make(Schema::make());

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Grid::class);

    $childComponents = rawLoginAuditSettingsChildComponents($components[0]);

    expect($childComponents)
        ->toHaveCount(15)
        ->and(array_map(
            static fn (object $component): string => $component::class,
            $childComponents,
        ))->toBe([
            Toggle::class,
            TextInput::class,
            Checkbox::class,
            Toggle::class,
            TextInput::class,
            Toggle::class,
            Toggle::class,
            TextInput::class,
            TextInput::class,
            Toggle::class,
            Toggle::class,
            Toggle::class,
            Toggle::class,
            Toggle::class,
            TextEntry::class,
        ]);
});

function seedLoginAuditSettingsAndModelSetting(string $settingName, mixed $value): void
{
    /** @var SettingsMigrator $settingsMigrator */
    $settingsMigrator = resolve(SettingsMigrator::class);

    foreach (defaultLoginAuditSettingsAndModelSettings() as $defaultSettingName => $defaultValue) {
        $defaultSettingKey = 'login_audit.' . $defaultSettingName;

        if (! $settingsMigrator->exists($defaultSettingKey)) {
            $settingsMigrator->add($defaultSettingKey, $defaultValue);
        }
    }

    $settingKey = 'login_audit.' . $settingName;

    if ($settingsMigrator->exists($settingKey)) {
        $settingsMigrator->update($settingKey, fn (): mixed => $value);
    } else {
        $settingsMigrator->add($settingKey, $value);
    }

    app()->forgetInstance(LoginAuditSettings::class);
}

/**
 * @return array<string, mixed>
 */
function defaultLoginAuditSettingsAndModelSettings(): array
{
    return [
        'show_login_audits' => true,
        'retention_days' => 90,
        'track_user_ip_addresses' => true,
        'track_admin_activity' => true,
        'activity_update_grace_seconds' => 60,
        'enable_suspicious_detection' => true,
        'enable_geo_location' => false,
        'failed_login_threshold' => 5,
        'failed_login_window_minutes' => 60,
        'check_unusual_login_times' => false,
        'alert_new_devices' => true,
        'alert_failed_logins' => false,
        'alert_suspicious_logins' => true,
        'last_purged_at' => null,
        'enable_user_resource_bridge' => true,
    ];
}

/**
 * @return array<int, object>
 */
function rawLoginAuditSettingsChildComponents(Grid $grid): array
{
    $reflectionProperty = new ReflectionProperty($grid, 'childComponents');
    $childComponents = $reflectionProperty->getValue($grid);

    return $childComponents['default'] ?? [];
}
