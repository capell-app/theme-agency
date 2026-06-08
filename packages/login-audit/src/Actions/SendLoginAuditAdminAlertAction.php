<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Capell\Admin\Actions\Notifications\ResolveAdminNotificationRecipientsAction;
use Capell\LoginAudit\Filament\Resources\LoginAudits\LoginAuditResource;
use Capell\LoginAudit\Models\LoginAudit;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class SendLoginAuditAdminAlertAction
{
    use AsAction;

    public const string NOTIFICATION_GROUP = 'login_audit_security_alerts';

    public function handle(LoginAudit $loginAudit, string $alertType): void
    {
        $recipients = ResolveAdminNotificationRecipientsAction::run(self::NOTIFICATION_GROUP);

        foreach ($recipients as $recipient) {
            if (! $recipient instanceof Model) {
                continue;
            }

            if (! $recipient instanceof Authenticatable) {
                continue;
            }

            $this->sendFilamentNotification($loginAudit, $alertType, $recipient);
        }
    }

    private function sendFilamentNotification(LoginAudit $loginAudit, string $alertType, Authenticatable&Model $recipient): void
    {
        $notification = Notification::make('login-audit-' . $alertType . '-' . $loginAudit->getKey())
            ->title($this->title($alertType))
            ->body($this->body($loginAudit))
            ->icon(Heroicon::OutlinedShieldExclamation)
            ->danger()
            ->persistent()
            ->actions([
                Action::make('viewLoginAudit')
                    ->label(__('capell-login-audit::settings.view_login_audit'))
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->link()
                    ->close()
                    ->url(LoginAuditResource::getUrl()),
            ]);

        try {
            $notification->broadcast($recipient);

            if (Schema::hasTable('notifications')) {
                $notification->sendToDatabase($recipient);
            }
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }

    private function title(string $alertType): string
    {
        return match ($alertType) {
            'new_device' => (string) __('capell-login-audit::settings.alert_new_device_title'),
            'failed_login' => (string) __('capell-login-audit::settings.alert_failed_login_title'),
            'suspicious_login' => (string) __('capell-login-audit::settings.alert_suspicious_login_title'),
            default => (string) __('capell-login-audit::settings.alert_login_audit_title'),
        };
    }

    private function body(LoginAudit $loginAudit): string
    {
        return (string) __('capell-login-audit::settings.alert_body', [
            'id' => $loginAudit->getKey(),
            'ip' => $loginAudit->ip_address ?: __('capell-admin::generic.missing'),
            'time' => $loginAudit->login_at?->toIso8601String() ?? __('capell-admin::generic.missing'),
        ]);
    }
}
