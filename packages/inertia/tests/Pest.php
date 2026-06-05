<?php

declare(strict_types=1);

use Capell\Inertia\Tests\InertiaTestCase;

require_once __DIR__ . '/InertiaTestCase.php';

pest()->extend(InertiaTestCase::class)->group('inertia')->in(__DIR__);
