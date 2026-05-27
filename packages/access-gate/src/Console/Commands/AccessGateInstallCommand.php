<?php

declare(strict_types=1);

namespace Capell\AccessGate\Console\Commands;

use Capell\AccessGate\Actions\InstallAccessGatePackageAction;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Install\ConsoleProgressReporter;
use Illuminate\Console\Command;

final class AccessGateInstallCommand extends Command
{
    protected $signature = 'capell:access-gate-install';

    protected $description = 'Install Access Gate publishables, run migrations, and create the paused default area.';

    public function handle(): int
    {
        InstallAccessGatePackageAction::run(CapellCore::getPackage('capell-app/access-gate'), [], new ConsoleProgressReporter($this));

        return self::SUCCESS;
    }
}
