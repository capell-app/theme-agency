<?php

declare(strict_types=1);

use Capell\Tests\Packages\PackagesTestCase;

pest()->extend(PackagesTestCase::class)->group('theme-wild-card')->in(__DIR__);
