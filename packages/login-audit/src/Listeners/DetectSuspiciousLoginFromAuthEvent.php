<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Listeners;

use Capell\LoginAudit\Actions\DetectSuspiciousLoginAction;
use Capell\LoginAudit\Actions\SendLoginAuditAdminAlertAction;
use Capell\LoginAudit\Models\LoginAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class DetectSuspiciousLoginFromAuthEvent
{
    public function handle(Failed|Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof Authenticatable || ! $user instanceof Model) {
            return;
        }

        $loginAudit = LoginAudit::query()
            ->where('authenticatable_type', $user->getMorphClass())
            ->where('authenticatable_id', $user->getKey())
            ->where('login_successful', $event instanceof Login)
            ->where('login_at', '>=', now()->subMinutes(5))
            ->latest('login_at')
            ->latest('id')
            ->first();

        if (! $loginAudit instanceof LoginAudit) {
            return;
        }

        $isNewDevice = $this->isNewDevice($loginAudit);
        $loginAudit = DetectSuspiciousLoginAction::run($loginAudit);

        if ($loginAudit->is_suspicious && (bool) config('login-audit.admin_alerts.suspicious_logins', true)) {
            SendLoginAuditAdminAlertAction::run($loginAudit, 'suspicious_login');

            return;
        }

        if ($event instanceof Failed && (bool) config('login-audit.admin_alerts.failed_logins', false)) {
            SendLoginAuditAdminAlertAction::run($loginAudit, 'failed_login');

            return;
        }

        if ($event instanceof Login && $isNewDevice && (bool) config('login-audit.admin_alerts.new_devices', true)) {
            SendLoginAuditAdminAlertAction::run($loginAudit, 'new_device');
        }
    }

    private function isNewDevice(LoginAudit $loginAudit): bool
    {
        $deviceId = $loginAudit->getAttribute('device_id');

        if (! $loginAudit->login_successful || blank($deviceId)) {
            return false;
        }

        return ! LoginAudit::query()
            ->where('authenticatable_type', $loginAudit->authenticatable_type)
            ->where('authenticatable_id', $loginAudit->authenticatable_id)
            ->where('login_successful', true)
            ->where('device_id', $deviceId)
            ->whereKeyNot($loginAudit->getKey())
            ->exists();
    }
}
