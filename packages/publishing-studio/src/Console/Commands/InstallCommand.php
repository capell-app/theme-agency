<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Console\Commands;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Install\ConsoleProgressReporter;
use Capell\PublishingStudio\Actions\InstallPublishingStudioPackageAction;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /** @var string */
    protected $description = 'Install publishing-studio package';

    /** @var string */
    protected $signature = 'capell:publishing-studio-install';

    public function handle(): int
    {
        InstallPublishingStudioPackageAction::run(CapellCore::getPackage('capell-app/publishing-studio'), [], new ConsoleProgressReporter($this));

        $this->newLine();
        $this->info('Capell PublishingStudio installed successfully.');

        return self::SUCCESS;
    }
}
