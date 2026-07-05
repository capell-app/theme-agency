<?php

declare(strict_types=1);

use Capell\Tests\Packages\PackagesTestCase;

pest()->extend(PackagesTestCase::class)->group('theme-quiet-type')->in(__DIR__);
