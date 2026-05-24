<?php

declare(strict_types=1);

namespace Capell\Events\Console\Commands;

use Capell\Admin\Actions\AssignPermissionsToRole;
use Capell\Events\Actions\InstallPackageAction;
use Capell\Events\Enums\ResourceEnum;
use Capell\Events\Support\EventModelRegistrar;
use Filament\Facades\Filament;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $description = 'Install events package';

    protected $signature = 'capell:events-install';

    public function handle(): int
    {
        EventModelRegistrar::register();

        Filament::getDefaultPanel()
            ->resources(array_map(fn (ResourceEnum $resourceEnum): string => $resourceEnum->value, ResourceEnum::cases()));

        AssignPermissionsToRole::run(resources: array_map(fn (ResourceEnum $resourceEnum): string => $resourceEnum->value, ResourceEnum::cases()));

        $this->publishMigrations();

        $this->call('migrate', ['--force' => true]);

        InstallPackageAction::run();

        $this->callSilent('filament:assets');

        $this->newLine();
        $this->info('Capell Events installed successfully.');

        return self::SUCCESS;
    }

    private function publishMigrations(): void
    {
        $migrations = [
            __DIR__ . '/../../../database/migrations/2026_05_10_190848_01_create_event_venues_table.php',
            __DIR__ . '/../../../database/migrations/2026_05_10_190848_02_create_events_table.php',
            __DIR__ . '/../../../database/migrations/2026_05_10_190848_03_create_event_occurrences_table.php',
            __DIR__ . '/../../../database/migrations/2026_05_10_190848_04_create_event_registrations_table.php',
            __DIR__ . '/../../../database/migrations/2026_05_10_190848_05_create_event_notification_logs_table.php',
        ];

        $this->call('capell:publish-migrations', ['--items' => $migrations]);
    }
}
