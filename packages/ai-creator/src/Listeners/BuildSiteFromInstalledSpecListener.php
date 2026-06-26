<?php

declare(strict_types=1);

namespace Capell\AiCreator\Listeners;

use Capell\AiCreator\Actions\BuildSiteFromSpecFileAction;
use Capell\Core\Events\CapellInstalled;

/**
 * Builds a Capell site from the spec file announced by core's post-install
 * `CapellInstalled` event. Keeps Capell\Core free of any spec/builder
 * knowledge: core dispatches the resolved path, ai-creator interprets it.
 *
 * Errors propagate: a bad spec surfaces as a clear exception on the install
 * command (non-zero exit) but the install itself has already persisted.
 */
final class BuildSiteFromInstalledSpecListener
{
    public function handle(CapellInstalled $event): void
    {
        BuildSiteFromSpecFileAction::run($event->specPath);
    }
}
