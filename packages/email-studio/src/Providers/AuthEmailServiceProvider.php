<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Providers;

use Capell\EmailStudio\Actions\BuildAuthEmailMailMessageAction;
use Capell\EmailStudio\Actions\RegisterAuthEmailTemplatesAction;
use Capell\EmailStudio\Actions\SendEmailAction;
use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelData\DataCollection;
use Throwable;

class AuthEmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/capell-email-studio.php', 'capell-email-studio');

        if (! $this->app->bound(EmailTemplateRegistry::class)) {
            $this->app->singleton(EmailTemplateRegistry::class);
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', EmailStudioServiceProvider::$name);
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', EmailStudioServiceProvider::$name);

        RegisterAuthEmailTemplatesAction::run();

        VerifyEmail::toMailUsing(fn (mixed $notifiable, string $url): MailMessage => is_object($notifiable)
            ? $this->verificationMail($notifiable, $url)
            : $this->defaultVerificationMail($url));
        ResetPassword::toMailUsing(fn (mixed $notifiable, string $token): MailMessage => is_object($notifiable)
            ? $this->passwordResetMail($notifiable, $token)
            : $this->defaultPasswordResetMail(new class {}, $token));

        Event::listen(Registered::class, fn (Registered $event): null => $this->sendLifecycleEmail('auth_send_welcome', 'auth.welcome', $event->user, [
            'action_url' => $this->loginUrl(),
        ]));
        Event::listen(Verified::class, fn (Verified $event): null => $this->sendLifecycleEmail('auth_send_verified', 'auth.verified', $event->user));
        Event::listen(Login::class, fn (Login $event): null => $this->sendLifecycleEmail('auth_send_login', 'auth.login', $event->user, [
            'login_time' => now()->toDateTimeString(),
            'ip_address' => request()->ip() ?? '',
        ]));
        Event::listen(PasswordReset::class, fn (PasswordReset $event): null => $this->sendLifecycleEmail('auth_send_password_reset_success', 'auth.password-reset-success', $event->user));
        Event::listen(Lockout::class, function (Lockout $event): null {
            $email = $event->request->input('email');

            if (! is_string($email) || $email === '') {
                return null;
            }

            return $this->sendAddressedLifecycleEmail('auth_send_lockout', 'auth.lockout', $email, [
                'email' => $email,
                'ip_address' => $event->request->ip() ?? '',
            ]);
        });
    }

    private function verificationMail(object $notifiable, string $url): MailMessage
    {
        if (! $this->enabled('auth_replace_verification')) {
            return $this->defaultVerificationMail($url);
        }

        try {
            return BuildAuthEmailMailMessageAction::run('auth.verify-email', [
                'name' => $this->notifiableName($notifiable),
                'email' => $this->notifiableEmail($notifiable),
                'action_url' => $url,
                'expires_minutes' => config('auth.verification.expire', 60),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->defaultVerificationMail($url);
        }
    }

    private function passwordResetMail(object $notifiable, string $token): MailMessage
    {
        if (! $this->enabled('auth_replace_password_reset')) {
            return $this->defaultPasswordResetMail($notifiable, $token);
        }

        try {
            return BuildAuthEmailMailMessageAction::run('auth.reset-password', [
                'name' => $this->notifiableName($notifiable),
                'email' => $this->notifiableEmail($notifiable),
                'action_url' => $this->passwordResetUrl($notifiable, $token),
                'expires_minutes' => $this->passwordResetExpiryMinutes(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->defaultPasswordResetMail($notifiable, $token);
        }
    }

    private function defaultVerificationMail(string $url): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->line(Lang::get('Please click the button below to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    private function defaultPasswordResetMail(object $notifiable, string $token): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            ->action(Lang::get('Reset Password'), $this->passwordResetUrl($notifiable, $token))
            ->line(Lang::get('This password reset link will expire in :count minutes.', [
                'count' => $this->passwordResetExpiryMinutes(),
            ]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }

    private function passwordResetUrl(object $notifiable, string $token): string
    {
        if (ResetPassword::$createUrlCallback !== null) {
            return (string) call_user_func(ResetPassword::$createUrlCallback, $notifiable, $token);
        }

        return url(route('password.reset', [
            'token' => $token,
            'email' => $this->notifiableEmail($notifiable),
        ], false));
    }

    private function passwordResetExpiryMinutes(): int
    {
        $broker = config('auth.defaults.passwords');
        $broker = is_string($broker) && $broker !== '' ? $broker : 'users';
        $minutes = config('auth.passwords.' . $broker . '.expire', 60);

        return is_numeric($minutes) ? (int) $minutes : 60;
    }

    private function loginUrl(): string
    {
        if (app('router')->has('login')) {
            return url(route('login', absolute: false));
        }

        return url('/login');
    }

    /**
     * @param  array<string, mixed>  $extraVariables
     */
    private function sendLifecycleEmail(string $setting, string $templateKey, object $notifiable, array $extraVariables = []): null
    {
        $email = $this->notifiableEmail($notifiable);

        if ($email === '') {
            return null;
        }

        return $this->sendAddressedLifecycleEmail($setting, $templateKey, $email, [
            'id' => $notifiable instanceof Model ? $notifiable->getKey() : null,
            'name' => $this->notifiableName($notifiable),
            'email' => $email,
            ...$extraVariables,
        ]);
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function sendAddressedLifecycleEmail(string $setting, string $templateKey, string $email, array $variables): null
    {
        if (! $this->enabled($setting)) {
            return null;
        }

        try {
            SendEmailAction::run(new SendEmailData(
                templateKey: $templateKey,
                to: new DataCollection(EmailAddressData::class, [new EmailAddressData($email)]),
                cc: new DataCollection(EmailAddressData::class, []),
                bcc: new DataCollection(EmailAddressData::class, []),
                siteId: null,
                siteScopeKey: 'global',
                emailProfileId: null,
                variables: $variables,
                headers: new DataCollection(EmailHeaderData::class, []),
                triggeredByType: 'auth',
                triggeredById: $this->notifiableId($variables),
                queue: true,
            ));
        } catch (Throwable $throwable) {
            report($throwable);
        }

        return null;
    }

    private function enabled(string $setting): bool
    {
        try {
            if ($this->app->bound(EmailStudioSettings::class)) {
                /** @var EmailStudioSettings $settings */
                $settings = resolve(EmailStudioSettings::class);

                return (bool) $settings->{$setting};
            }
        } catch (Throwable) {
            return (bool) config('capell-email-studio.auth.' . str($setting)->after('auth_')->toString(), false);
        }

        return (bool) config('capell-email-studio.auth.' . str($setting)->after('auth_')->toString(), false);
    }

    private function notifiableName(object $notifiable): string
    {
        foreach (['name', 'email'] as $attribute) {
            if ($notifiable instanceof Model) {
                $value = $notifiable->getAttribute($attribute);
            } else {
                $value = $notifiable->{$attribute} ?? null;
            }

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function notifiableEmail(object $notifiable): string
    {
        if ($notifiable instanceof MustVerifyEmail) {
            $email = $notifiable->getEmailForVerification();

            return is_string($email) ? $email : '';
        }

        if (method_exists($notifiable, 'getEmailForPasswordReset')) {
            $email = $notifiable->getEmailForPasswordReset();

            return is_string($email) ? $email : '';
        }

        if ($notifiable instanceof Model) {
            $email = $notifiable->getAttribute('email');

            return is_string($email) ? $email : '';
        }

        $email = $notifiable->email ?? null;

        return is_string($email) ? $email : '';
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function notifiableId(array $variables): ?int
    {
        $id = $variables['id'] ?? null;

        return is_numeric($id) ? (int) $id : null;
    }
}
