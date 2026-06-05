<?php

declare(strict_types=1);

namespace Capell\Inertia\Support;

use Capell\Inertia\Data\InertiaAdapterData;

class InertiaAdapterRegistry
{
    /** @var array<string, InertiaAdapterData> */
    private array $adapters = [];

    public function register(InertiaAdapterData $adapter): self
    {
        $this->adapters[$adapter->key] = $adapter;

        return $this;
    }

    public function get(string $key): ?InertiaAdapterData
    {
        return $this->adapters[$key] ?? null;
    }

    public function active(): ?InertiaAdapterData
    {
        $key = config('capell-inertia.adapter', 'vue');

        return is_string($key) ? $this->get($key) : null;
    }

    /**
     * @return array<string, InertiaAdapterData>
     */
    public function all(): array
    {
        return $this->adapters;
    }
}
