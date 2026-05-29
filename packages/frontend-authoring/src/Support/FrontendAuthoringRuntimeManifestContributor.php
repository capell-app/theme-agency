<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Support;

use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Contracts\FrontendRuntimeManifestContributor;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\FrontendAuthoring\Providers\FrontendAuthoringServiceProvider;

final class FrontendAuthoringRuntimeManifestContributor implements FrontendRuntimeManifestContributor
{
    public function contribute(FrontendContextReader $context, FrontendRuntimeManifestData $manifest): void
    {
        if (
            ! (bool) config('capell-frontend-authoring.enabled', true)
            || ! CapellCore::isPackageInstalled(FrontendAuthoringServiceProvider::$packageName)
        ) {
            return;
        }

        $manifest->usesBeacon = true;
        $manifest->modules['frontend-authoring'] = true;
    }
}
