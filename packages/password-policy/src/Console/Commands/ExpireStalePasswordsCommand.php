<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Console\Commands;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Actions\MarkUserForPasswordChangeAction;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class ExpireStalePasswordsCommand extends Command
{
    protected $signature = 'capell:password-policy:expire-stale
        {--days= : Override the configured password-expiry age in days}
        {--dry-run : Report matching users without marking them}';

    protected $description = 'Mark users with stale passwords as requiring a password change.';

    public function handle(): int
    {
        $days = $this->positiveIntegerOption('days')
            ?? resolve(PasswordPolicySettingsResolver::class)->settings()->passwordExpiryDays;

        if ($days < 1 || ! $this->hasRequiredColumns()) {
            $this->components->error((string) __('capell-password-policy::commands.expire_stale_unavailable'));

            return self::FAILURE;
        }

        $cutoff = CarbonImmutable::now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');
        $matched = 0;

        $this->userModel()::query()
            ->where('must_change_password', false)
            ->whereNotNull('password_changed_at')
            ->where('password_changed_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function (EloquentCollection $users) use ($dryRun, &$matched): void {
                foreach ($users as $user) {
                    $matched++;

                    if (! $dryRun) {
                        MarkUserForPasswordChangeAction::run($user);
                    }
                }
            });

        $this->components->info((string) __('capell-password-policy::commands.expire_stale_complete', [
            'count' => $matched,
        ]));

        return self::SUCCESS;
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

        throw new RuntimeException('Password Policy commands require a configured Eloquent users provider model.');
    }

    private function positiveIntegerOption(string $name): ?int
    {
        $option = $this->option($name);

        if ($option === null || $option === '') {
            return null;
        }

        if (! is_string($option) && ! is_int($option)) {
            return 0;
        }

        $value = (string) $option;

        return ctype_digit($value) ? (int) $value : 0;
    }
}
