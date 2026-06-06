<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Filament\Settings;

use Capell\Admin\Filament\Contracts\HasSchema;
use Capell\Admin\Filament\Support\HelperText;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

final class LoginAuditSettingsSchema implements HasSchema
{
    public static function make(Schema $configurator): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    HelperText::apply(
                        Toggle::make('show_login_audits')
                            ->label(__('capell-login-audit::settings.show_login_audits')),
                        'capell-login-audit::settings.show_login_audits_helper',
                    ),
                    TextInput::make('retention_days')
                        ->label(__('capell-login-audit::settings.retention_days'))
                        ->helperText(__('capell-login-audit::settings.retention_days_helper'))
                        ->integer()
                        ->minValue(1)
                        ->suffix(__('capell-admin::form.days')),
                    HelperText::apply(
                        Checkbox::make('track_user_ip_addresses')
                            ->label(__('capell-login-audit::settings.track_user_ip_addresses')),
                        'capell-login-audit::settings.track_user_ip_addresses_helper',
                    ),
                    HelperText::apply(
                        Toggle::make('track_admin_activity')
                            ->label(__('capell-login-audit::settings.track_admin_activity')),
                        'capell-login-audit::settings.track_admin_activity_helper',
                    ),
                    TextInput::make('activity_update_grace_seconds')
                        ->label(__('capell-login-audit::settings.activity_update_grace_seconds'))
                        ->helperText(__('capell-login-audit::settings.activity_update_grace_seconds_helper'))
                        ->integer()
                        ->minValue(0)
                        ->suffix(__('capell-login-audit::settings.seconds')),
                    HelperText::apply(
                        Toggle::make('enable_suspicious_detection')
                            ->label(__('capell-login-audit::settings.enable_suspicious_detection')),
                        'capell-login-audit::settings.enable_suspicious_detection_helper',
                    ),
                    TextInput::make('failed_login_threshold')
                        ->label(__('capell-login-audit::settings.failed_login_threshold'))
                        ->helperText(__('capell-login-audit::settings.failed_login_threshold_helper'))
                        ->integer()
                        ->minValue(2),
                    TextInput::make('failed_login_window_minutes')
                        ->label(__('capell-login-audit::settings.failed_login_window_minutes'))
                        ->helperText(__('capell-login-audit::settings.failed_login_window_minutes_helper'))
                        ->integer()
                        ->minValue(1)
                        ->suffix(__('capell-login-audit::settings.minutes')),
                    HelperText::apply(
                        Toggle::make('check_unusual_login_times')
                            ->label(__('capell-login-audit::settings.check_unusual_login_times')),
                        'capell-login-audit::settings.check_unusual_login_times_helper',
                    ),
                    HelperText::apply(
                        Toggle::make('alert_new_devices')
                            ->label(__('capell-login-audit::settings.alert_new_devices')),
                        'capell-login-audit::settings.alert_new_devices_helper',
                    ),
                    HelperText::apply(
                        Toggle::make('alert_failed_logins')
                            ->label(__('capell-login-audit::settings.alert_failed_logins')),
                        'capell-login-audit::settings.alert_failed_logins_helper',
                    ),
                    HelperText::apply(
                        Toggle::make('alert_suspicious_logins')
                            ->label(__('capell-login-audit::settings.alert_suspicious_logins')),
                        'capell-login-audit::settings.alert_suspicious_logins_helper',
                    ),
                    HelperText::apply(
                        Toggle::make('enable_user_resource_bridge')
                            ->label(__('capell-login-audit::settings.enable_user_resource_bridge')),
                        'capell-login-audit::settings.enable_user_resource_bridge_helper',
                    ),
                    TextEntry::make('last_purged_at')
                        ->label(__('capell-login-audit::settings.last_purged_at'))
                        ->state(fn (?string $state): string => $state ?: (string) __('capell-login-audit::settings.never_purged')),
                ]),
        ];
    }
}
