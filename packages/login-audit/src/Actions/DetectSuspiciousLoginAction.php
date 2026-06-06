<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Capell\LoginAudit\Models\LoginAudit;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class DetectSuspiciousLoginAction
{
    use AsAction;

    public function handle(LoginAudit $loginAudit): LoginAudit
    {
        if (! (bool) config('login-audit.suspicious.enabled', true)) {
            return $loginAudit;
        }

        $findings = [
            ...$this->multipleFailedLogins($loginAudit),
            ...$this->successfulLoginAfterFailedDeviceAttempt($loginAudit),
            ...$this->rapidLocationChange($loginAudit),
            ...$this->unusualLoginTime($loginAudit),
        ];

        if ($findings === []) {
            return $loginAudit;
        }

        $loginAudit->forceFill([
            'is_suspicious' => true,
            'suspicious_reason' => json_encode($findings, JSON_THROW_ON_ERROR),
        ])->save();

        return $loginAudit->refresh();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function multipleFailedLogins(LoginAudit $loginAudit): array
    {
        if ($loginAudit->login_successful) {
            return [];
        }

        $threshold = max(2, (int) config('login-audit.suspicious.failed_login_threshold', 5));
        $windowMinutes = max(1, (int) config('login-audit.suspicious.failed_login_window_minutes', 60));
        $failedAttempts = $this->actorQuery($loginAudit)
            ->where('login_successful', false)
            ->where('login_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($failedAttempts < $threshold) {
            return [];
        }

        return [[
            'type' => 'multiple_failed_logins',
            'count' => $failedAttempts,
            'window_minutes' => $windowMinutes,
            'message' => "{$failedAttempts} failed login attempts in {$windowMinutes} minutes",
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function successfulLoginAfterFailedDeviceAttempt(LoginAudit $loginAudit): array
    {
        $deviceId = $loginAudit->getAttribute('device_id');

        if (! $loginAudit->login_successful || blank($deviceId)) {
            return [];
        }

        $windowHours = max(1, (int) config('login-audit.suspicious.failed_device_window_hours', 24));
        $hadRecentFailedAttempt = $this->actorQuery($loginAudit)
            ->where('login_successful', false)
            ->where('device_id', $deviceId)
            ->where('login_at', '>=', now()->subHours($windowHours))
            ->exists();

        if (! $hadRecentFailedAttempt) {
            return [];
        }

        return [[
            'type' => 'successful_login_after_failed_device_attempt',
            'device_id' => $deviceId,
            'window_hours' => $windowHours,
            'message' => 'Successful login followed recent failed attempts from the same device',
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rapidLocationChange(LoginAudit $loginAudit): array
    {
        $location = $loginAudit->location;

        if (! is_array($location) || blank($location['country'] ?? null)) {
            return [];
        }

        $windowMinutes = max(1, (int) config('login-audit.suspicious.location_window_minutes', 60));
        $countries = $this->actorQuery($loginAudit)
            ->where('login_successful', true)
            ->where('login_at', '>=', now()->subMinutes($windowMinutes))
            ->whereNotNull('location')
            ->get()
            ->pluck('location.country')
            ->filter()
            ->unique()
            ->values();

        if ($countries->count() < 2) {
            return [];
        }

        return [[
            'type' => 'rapid_location_change',
            'countries' => $countries->all(),
            'window_minutes' => $windowMinutes,
            'message' => 'Login from multiple countries within the configured window',
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function unusualLoginTime(LoginAudit $loginAudit): array
    {
        if (! (bool) config('login-audit.suspicious.check_unusual_times', false)) {
            return [];
        }

        $loginHour = $loginAudit->login_at?->hour;
        $usualHours = config('login-audit.suspicious.usual_hours', [9, 10, 11, 12, 13, 14, 15, 16, 17]);

        if ($loginHour === null || ! is_array($usualHours) || in_array($loginHour, $usualHours, true)) {
            return [];
        }

        return [[
            'type' => 'unusual_login_time',
            'hour' => $loginHour,
            'message' => "Login at unusual time: {$loginHour}:00",
        ]];
    }

    /**
     * @return Builder<LoginAudit>
     */
    private function actorQuery(LoginAudit $loginAudit): Builder
    {
        return LoginAudit::query()
            ->where('authenticatable_type', $loginAudit->authenticatable_type)
            ->where('authenticatable_id', $loginAudit->authenticatable_id);
    }
}
