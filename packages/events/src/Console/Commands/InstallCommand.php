<?php

declare(strict_types=1);

namespace Capell\Events\Console\Commands;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Install\ConsoleProgressReporter;
use Capell\Events\Actions\InstallEventsPackageAction;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $description = 'Install events package';

    protected $signature = 'capell:events-install';

    public function handle(): int
    {
        InstallEventsPackageAction::run(CapellCore::getPackage('capell-app/events'), [], new ConsoleProgressReporter($this));

        $this->newLine();
        $this->info('Capell Events installed successfully.');

        return self::SUCCESS;
    }
}
