<?php

declare(strict_types=1);

namespace Capell\Comments\Console\Commands;

use Capell\Comments\Actions\InstallCommentsPackageAction;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Install\ConsoleProgressReporter;
use Illuminate\Console\Command;

final class InstallCommentsCommand extends Command
{
    protected $signature = 'capell-comments:install';

    protected $description = 'Publish Comments migrations, settings migrations, and config.';

    public function handle(): int
    {
        InstallCommentsPackageAction::run(CapellCore::getPackage('capell-app/comments'), [], new ConsoleProgressReporter($this));

        return self::SUCCESS;
    }
}
