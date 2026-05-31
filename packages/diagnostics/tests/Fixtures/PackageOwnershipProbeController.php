<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures;

final class PackageOwnershipProbeController
{
    public function __invoke(): string
    {
        return 'ok';
    }
}
