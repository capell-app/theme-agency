<?php

declare(strict_types=1);

namespace Capell\Inertia\Data;

use Spatie\LaravelData\Data;

class InertiaAdapterData extends Data
{
    /**
     * @param  array<string, string>  $npmDependencies
     * @param  array<string, string>  $components
     */
    public function __construct(
        public string $key,
        public string $packageName,
        public array $npmDependencies,
        public string $buildPath,
        public string $entrypoint,
        public array $components,
    ) {}
}
