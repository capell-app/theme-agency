<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support;

use Capell\PublicActions\Contracts\PublicActionDestinationAdapter;
use InvalidArgumentException;

final class PublicActionDestinationAdapterRegistry
{
    /** @var array<string, PublicActionDestinationAdapter|class-string> */
    private array $adapters = [];

    /**
     * @param  PublicActionDestinationAdapter|class-string  $adapter
     */
    public function register(string $key, PublicActionDestinationAdapter|string $adapter): void
    {
        $resolvedAdapter = is_string($adapter) ? resolve($adapter) : $adapter;

        throw_unless($resolvedAdapter instanceof PublicActionDestinationAdapter, InvalidArgumentException::class, 'Public action destination adapters must implement PublicActionDestinationAdapter.');

        $this->adapters[$key] = $adapter;
    }

    public function resolve(string $key): ?PublicActionDestinationAdapter
    {
        $adapter = $this->adapters[$key] ?? null;

        if ($adapter === null) {
            return null;
        }

        return is_string($adapter) ? resolve($adapter) : $adapter;
    }

    /**
     * @return array<string, PublicActionDestinationAdapter>
     */
    public function all(): array
    {
        return collect($this->adapters)
            ->mapWithKeys(fn (PublicActionDestinationAdapter|string $adapter, string $key): array => [$key => is_string($adapter) ? resolve($adapter) : $adapter])
            ->all();
    }
}
