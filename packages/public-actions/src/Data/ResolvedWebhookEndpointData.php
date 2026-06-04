<?php

declare(strict_types=1);

namespace Capell\PublicActions\Data;

final readonly class ResolvedWebhookEndpointData
{
    public function __construct(
        public string $url,
        public string $scheme,
        public string $host,
        public int $port,
        public string $address,
    ) {}

    public function curlResolveEntry(): string
    {
        return sprintf('%s:%d:%s', $this->host, $this->port, $this->address);
    }

    public function hostHeader(): string
    {
        if (($this->scheme === 'https' && $this->port === 443) || ($this->scheme === 'http' && $this->port === 80)) {
            return $this->host;
        }

        return sprintf('%s:%d', $this->host, $this->port);
    }
}
