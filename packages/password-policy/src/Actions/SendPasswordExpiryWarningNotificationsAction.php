<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Notifications\PasswordExpiryWarningNotification;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

/**
 * @method static int run(?CarbonImmutable $now = null, ?int $days = null, int $chunkSize = 100)
 */
final class SendPasswordExpiryWarningNotificationsAction
{
    use AsObject;

    public function handle(?CarbonImmutable $now = null, ?int $days = null, int $chunkSize = 100): int
    {
        $settings = resolve(PasswordPolicySettingsResolver::class)->settings();

        if (! $settings->passwordExpiryEnabled || ! $settings->passwordExpiryWarningNotificationsEnabled) {
            return 0;
        }

        if (! $this->hasRequiredColumns()) {
            return 0;
        }

        $now ??= CarbonImmutable::now();
        $warningDays = max(1, $days ?? $settings->passwordExpiryWarningDays);
        $expiryDays = max(1, $settings->passwordExpiryDays);
        $windowStart = $now->subDays($expiryDays);
        $windowEnd = $now->addDays($warningDays)->subDays($expiryDays);
        $sent = 0;

        if ($windowEnd->lt($windowStart)) {
            return 0;
        }

        $this->userModel()::query()
            ->where('must_change_password', false)
            ->whereNotNull('password_changed_at')
            ->where('password_changed_at', '>', $windowStart)
            ->where('password_changed_at', '<=', $windowEnd)
            ->orderBy('id')
            ->chunkById(max(1, $chunkSize), function (EloquentCollection $users) use ($expiryDays, $now, &$sent): void {
                foreach ($users as $user) {
                    if (! method_exists($user, 'notify')) {
                        continue;
                    }

                    $changedAt = $this->changedAt($user);

                    if (! $changedAt instanceof CarbonImmutable) {
                        continue;
                    }

                    $expiresAt = $changedAt->addDays($expiryDays);
                    $daysRemaining = max(0, (int) $now->startOfDay()->diffInDays($expiresAt->startOfDay(), false));

                    $user->notify(new PasswordExpiryWarningNotification($expiresAt, $daysRemaining));
                    $sent++;
                }
            });

        return $sent;
    }

    private function hasRequiredColumns(): bool
    {
        $schema = resolve(RuntimeSchemaState::class);

        return $schema->hasTable('users')
            && $schema->hasColumn('users', 'must_change_password')
            && $schema->hasColumn('users', 'password_changed_at');
    }

    /**
     * @return class-string<Model>
     */
    private function userModel(): string
    {
        $configured = config('auth.providers.users.model');

        if (is_string($configured) && is_subclass_of($configured, Model::class)) {
            return $configured;
        }

        throw new RuntimeException('Password Policy expiry warnings require a configured Eloquent users provider model.');
    }

    private function changedAt(Model $user): ?CarbonImmutable
    {
        $changedAt = $user->getAttribute('password_changed_at');

        if ($changedAt instanceof CarbonInterface) {
            return CarbonImmutable::instance($changedAt);
        }

        if (is_string($changedAt) && $changedAt !== '') {
            return CarbonImmutable::parse($changedAt);
        }

        return null;
    }
}
