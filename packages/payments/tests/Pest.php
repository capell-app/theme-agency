<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Capell\Payments\Tests\TestCase;

pest()->extend(TestCase::class)->group('payments')->in(__DIR__);
