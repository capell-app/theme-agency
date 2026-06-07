<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Tests\Fixtures;

use Capell\Frontend\Data\FrontendAssetRequirementData;

final class HintedFrontendAssetRequirementData extends FrontendAssetRequirementData
{
    public function __construct(
        string $handle,
        string $kind,
        string $source,
        ?string $buildPath = null,
        public ?bool $criticalEligible = null,
        public ?string $frontendOptimizerLoadingStrategy = null,
        public ?string $packageName = null,
        public ?string $resourceAs = null,
        public ?string $resourceType = null,
        public ?string $crossorigin = null,
        public ?string $fetchpriority = null,
    ) {
        parent::__construct(
            handle: $handle,
            kind: $kind,
            source: $source,
            buildPath: $buildPath,
        );
    }
}
