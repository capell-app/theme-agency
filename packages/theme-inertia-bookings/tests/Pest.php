<?php

declare(strict_types=1);

use Capell\Tests\Packages\PackagesTestCase;

pest()->extend(PackagesTestCase::class)->group('theme-inertia-bookings')->in(__DIR__);
