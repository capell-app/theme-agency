<?php

declare(strict_types=1);

namespace Capell\Inertia\Actions;

use Lorisleiva\Actions\Concerns\AsObject;

final class ResolveInertiaAdapterKeyAction
{
    use AsObject;

    public const string DefaultAdapter = 'vue';

    public function handle(mixed $configuredAdapter = null): string
    {
        $adapter = $configuredAdapter ?? config('capell-inertia.adapter', self::DefaultAdapter);

        if (! is_string($adapter)) {
            return self::DefaultAdapter;
        }

        $adapter = trim($adapter);

        return $adapter !== '' ? $adapter : self::DefaultAdapter;
    }
}
