<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Capell\LoginAudit\Settings\LoginAuditSettings;
use Illuminate\Support\Facades\Config;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ApplyLoginAuditSettingsAction
{
    use AsAction;

    public function handle(): void
    {
        try {
            /** @var LoginAuditSettings $settings */
            $settings = resolve(LoginAuditSettings::class);
            $retentionDays = $settings->retention_days;
            $enableSuspiciousDetection = $settings->enable_suspicious_detection;
            $failedLoginThreshold = $settings->failed_login_threshold;
            $failedLoginWindowMinutes = $settings->failed_login_window_minutes;
            $checkUnusualLoginTimes = $settings->check_unusual_login_times;
            $alertNewDevices = $settings->alert_new_devices;
            $alertFailedLogins = $settings->alert_failed_logins;
            $alertSuspiciousLogins = $settings->alert_suspicious_logins;
        } catch (Throwable) {
            return;
        }

        $purgeDays = max(1, $retentionDays);
        $threshold = max(2, $failedLoginThreshold);
        $windowMinutes = max(1, $failedLoginWindowMinutes);

        Config::set('login-audit.purge', $purgeDays);
        Config::set('login-audit.suspicious.enabled', $enableSuspiciousDetection);
        Config::set('login-audit.suspicious.failed_login_threshold', $threshold);
        Config::set('login-audit.suspicious.failed_login_window_minutes', $windowMinutes);
        Config::set('login-audit.suspicious.check_unusual_times', $checkUnusualLoginTimes);
        Config::set('login-audit.admin_alerts.new_devices', $alertNewDevices);
        Config::set('login-audit.admin_alerts.failed_logins', $alertFailedLogins);
        Config::set('login-audit.admin_alerts.suspicious_logins', $alertSuspiciousLogins);
        Config::set('login-audit.notifications.new-device.enabled', $alertNewDevices);
        Config::set('login-audit.notifications.failed-login.enabled', $alertFailedLogins);
        Config::set('login-audit.notifications.suspicious-activity.enabled', $alertSuspiciousLogins);
        Config::set('authentication-log.purge', $purgeDays);
        Config::set('authentication-log.suspicious.enabled', $enableSuspiciousDetection);
        Config::set('authentication-log.suspicious.failed_login_threshold', $threshold);
        Config::set('authentication-log.suspicious.check_unusual_times', $checkUnusualLoginTimes);
        Config::set('authentication-log.notifications.new-device.enabled', $alertNewDevices);
        Config::set('authentication-log.notifications.failed-login.enabled', $alertFailedLogins);
        Config::set('authentication-log.notifications.suspicious-activity.enabled', $alertSuspiciousLogins);
    }
}
