<?php

declare(strict_types=1);

namespace Capell\Tests\Fixtures\PhpStan;

use Capell\PublishingStudio\Concerns\ScopedToActiveContext;
use Capell\Tests\AbstractTestCase;
use Capell\Tests\Fixtures\Concerns\HasAssertWorkspaceDraftable;
use Capell\Tests\Support\Concerns\TestingFrontend;
use Capell\Tests\Support\Concerns\TestingFrontendWithVite;

final class TraitUsageFixture extends AbstractTestCase
{
    use HasAssertWorkspaceDraftable;
    use ScopedToActiveContext;
    use TestingFrontend;
    use TestingFrontendWithVite;

    protected function getPackageServiceName(): string
    {
        return 'phpstan-trait-usage-fixture';
    }
}
