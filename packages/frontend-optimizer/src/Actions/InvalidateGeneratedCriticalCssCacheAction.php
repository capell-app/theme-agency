<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Capell\Frontend\Support\Cache\CacheInvalidationRegistry;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class InvalidateGeneratedCriticalCssCacheAction
{
    use AsAction;

    public function handle(FrontendRenderProfile $profile): void
    {
        try {
            if (! app()->bound(CacheInvalidationRegistry::class)) {
                return;
            }

            $registry = app(CacheInvalidationRegistry::class);

            if (! $registry instanceof CacheInvalidationRegistry) {
                return;
            }

            $registry->invalidateChangedModel($profile);
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
