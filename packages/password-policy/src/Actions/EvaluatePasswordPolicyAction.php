<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Data\PasswordPolicyStatusData;
use Capell\PasswordPolicy\Enums\PasswordPolicyLifecycleEvent;
use Capell\PasswordPolicy\Events\PasswordExpired;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;

class EvaluatePasswordPolicyAction
{
    use AsObject;

    public function handle(Model $user): PasswordPolicyStatusData
    {
        $settings = resolve(PasswordPolicySettingsResolver::class)->settings();
        $schema = resolve(RuntimeSchemaState::class);

        if ($settings->forceChangeEnabled && $schema->hasColumn($user->getTable(), 'must_change_password') && (bool) $user->getAttribute('must_change_password')) {
            return new PasswordPolicyStatusData(
                mustChangePassword: true,
                passwordExpired: false,
                reason: 'forced',
            );
        }

        if (! $settings->passwordExpiryEnabled || ! $schema->hasColumn($user->getTable(), 'password_changed_at')) {
            return new PasswordPolicyStatusData(false, false);
        }

        $changedAtValue = $user->getAttribute('password_changed_at');
        $changedAt = $changedAtValue instanceof CarbonInterface
            ? CarbonImmutable::instance($changedAtValue)
            : $this->parseChangedAt($changedAtValue);

        if (! $changedAt instanceof CarbonImmutable) {
            return new PasswordPolicyStatusData(false, false);
        }

        $expiresAt = $changedAt->addDays(max(1, $settings->passwordExpiryDays));
        $passwordExpired = $expiresAt->isPast();

        if ($passwordExpired) {
            NotifyPasswordPolicyLifecycleEventAction::run(
                PasswordPolicyLifecycleEvent::PasswordExpired,
                new PasswordExpired($user, $expiresAt),
            );
        }

        return new PasswordPolicyStatusData(
            mustChangePassword: false,
            passwordExpired: $passwordExpired,
            reason: $passwordExpired ? 'expired' : null,
        );
    }

    private function parseChangedAt(mixed $changedAtValue): ?CarbonImmutable
    {
        if (! is_string($changedAtValue) || $changedAtValue === '') {
            return null;
        }

        return CarbonImmutable::parse($changedAtValue);
    }
}
