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
        Config::set('authentication-log.purge', $purgeDays);
        Config::set('authentication-log.suspicious.enabled', $enableSuspiciousDetection);
        Config::set('authentication-log.suspicious.failed_login_threshold', $threshold);
        Config::set('authentication-log.suspicious.check_unusual_times', $checkUnusualLoginTimes);
    }
}
