<?php

declare(strict_types=1);

namespace Capell\PublicActions\Tests\Fakes;

use Capell\PublicActions\Contracts\PublicActionWebhookHostResolver;

final class FakePublicActionWebhookHostResolver implements PublicActionWebhookHostResolver
{
    /**
     * @param  array<string, list<string>>  $addressesByHost
     */
    public function __construct(
        private array $addressesByHost = [],
    ) {}

    /**
     * @param  list<string>  $addresses
     */
    public function set(string $host, array $addresses): void
    {
        $this->addressesByHost[strtolower($host)] = $addresses;
    }

    /**
     * @return list<string>
     */
    public function resolve(string $host): array
    {
        return $this->addressesByHost[strtolower($host)] ?? ['93.184.216.34'];
    }
}
