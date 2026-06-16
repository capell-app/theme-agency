<?php

declare(strict_types=1);

namespace Capell\Inertia\Actions;

use Lorisleiva\Actions\Concerns\AsObject;

final class ResolveInertiaRootViewAction
{
    use AsObject;

    public const string DefaultRootView = 'capell-inertia::app';

    public function handle(mixed $configuredRootView = null): string
    {
        $rootView = $configuredRootView ?? config('capell-inertia.root_view', self::DefaultRootView);

        if (! is_string($rootView)) {
            return self::DefaultRootView;
        }

        $rootView = trim($rootView);

        return $rootView !== '' ? $rootView : self::DefaultRootView;
    }
}
