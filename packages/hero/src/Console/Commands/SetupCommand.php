<?php

declare(strict_types=1);

namespace Capell\Hero\Console\Commands;

use Capell\Hero\Actions\InstallHeroLayoutDefaultsAction;
use Illuminate\Console\Command;

final class SetupCommand extends Command
{
    protected $signature = 'capell:hero-setup {--force : Rebuild Hero-managed home layout defaults}';

    protected $description = 'Install Hero widget and home layout defaults.';

    public function handle(): int
    {
        $result = InstallHeroLayoutDefaultsAction::run((bool) $this->option('force'));

        $this->components->info(sprintf(
            'Hero setup complete: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
