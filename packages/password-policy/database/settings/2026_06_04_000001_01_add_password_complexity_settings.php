<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('password_policy.minimum_password_length')) {
            $this->migrator->add('password_policy.minimum_password_length', 8);
        }

        if (! $this->migrator->exists('password_policy.require_mixed_case')) {
            $this->migrator->add('password_policy.require_mixed_case', false);
        }

        if (! $this->migrator->exists('password_policy.require_numbers')) {
            $this->migrator->add('password_policy.require_numbers', false);
        }

        if (! $this->migrator->exists('password_policy.require_symbols')) {
            $this->migrator->add('password_policy.require_symbols', false);
        }
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('password_policy.minimum_password_length');
        $this->migrator->deleteIfExists('password_policy.require_mixed_case');
        $this->migrator->deleteIfExists('password_policy.require_numbers');
        $this->migrator->deleteIfExists('password_policy.require_symbols');
    }
};
