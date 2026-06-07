<?php

declare(strict_types=1);

namespace Capell\Deployments\Data;

use Capell\Deployments\Enums\GitProviderType;
use Spatie\LaravelData\Data;

final class PublishComposerChangeResultData extends Data
{
    public function __construct(
        public GitProviderType $provider,
        public ?string $pullRequestUrl = null,
        public ?string $commitSha = null,
        public ?int $pullRequestId = null,
        public bool $dryRun = false,
        public ?string $branchName = null,
    ) {}
}
