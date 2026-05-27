<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Console\Commands;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Install\ConsoleProgressReporter;
use Capell\SeoSuite\Actions\InstallSeoSuitePackageAction;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'capell:seo-suite-install';

    public function handle(): int
    {
        InstallSeoSuitePackageAction::run(CapellCore::getPackage('capell-app/seo-suite'), [], new ConsoleProgressReporter($this));

        $this->newLine();
        $this->info('Capell SEO Suite installed successfully.');

        return Command::SUCCESS;
    }
}
