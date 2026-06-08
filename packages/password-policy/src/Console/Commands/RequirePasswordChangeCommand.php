<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Console\Commands;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Actions\MarkUserForPasswordChangeAction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class RequirePasswordChangeCommand extends Command
{
    protected $signature = 'capell:password-policy:require-change
        {--user-id= : Mark one user by ID}
        {--all : Mark every user}
        {--dry-run : Report matching users without marking them}';

    protected $description = 'Mark users as requiring a password change.';

    public function handle(): int
    {
        $userId = $this->positiveIntegerOption('user-id');
        $all = (bool) $this->option('all');

        if ($userId === 0 || (! $all && $userId === null) || ($all && $userId !== null)) {
            $this->components->error((string) __('capell-password-policy::commands.require_change_scope_required'));

            return self::FAILURE;
        }

        if (! resolve(RuntimeSchemaState::class)->hasColumn('users', 'must_change_password')) {
            $this->components->error((string) __('capell-password-policy::commands.require_change_unavailable'));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $marked = 0;
        $query = $this->userModel()::query()
            ->when($userId !== null, static fn (Builder $query): Builder => $query->whereKey($userId))
            ->orderBy('id');

        $query->chunkById(100, function (EloquentCollection $users) use ($dryRun, &$marked): void {
            foreach ($users as $user) {
                $marked++;

                if (! $dryRun) {
                    MarkUserForPasswordChangeAction::run($user);
                }
            }
        });

        $this->components->info((string) __('capell-password-policy::commands.require_change_complete', [
            'count' => $marked,
        ]));

        return self::SUCCESS;
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

        return ctype_digit($value) && (int) $value > 0 ? (int) $value : 0;
    }
}
