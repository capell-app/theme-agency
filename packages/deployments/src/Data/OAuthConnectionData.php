<?php

declare(strict_types=1);

namespace Capell\Deployments\Data;

use Capell\Deployments\Enums\GitProviderType;
use Spatie\LaravelData\Data;

final class OAuthConnectionData extends Data
{
    public function __construct(
        public GitProviderType $provider,
        public string $repoOwner,
        public string $repoName,
    ) {}
}
