<?php

declare(strict_types=1);

namespace Capell\Inertia\Actions;

use Lorisleiva\Actions\Concerns\AsObject;

final class ResolveInertiaComponentNameAction
{
    use AsObject;

    public const string DefaultPageComponent = 'Capell/Page';

    public function handle(mixed $configuredComponent = null, string $fallback = self::DefaultPageComponent): string
    {
        $component = $configuredComponent ?? config('capell-inertia.page_component', $fallback);

        if (! is_string($component)) {
            return $fallback;
        }

        $component = trim($component);

        return $component !== '' ? $component : $fallback;
    }
}
