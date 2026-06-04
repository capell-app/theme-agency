<?php

declare(strict_types=1);

namespace Capell\PublicActions\Contracts;

interface PublicActionWebhookHostResolver
{
    /**
     * @return list<string>
     */
    public function resolve(string $host): array;
}
