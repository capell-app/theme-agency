<?php

declare(strict_types=1);
use Capell\Tests\Support\PackageTestDatabaseGuard;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/Support/PackageTestDatabaseGuard.php';
require_once __DIR__ . '/Support/GlobalTestHelpers.php';

PackageTestDatabaseGuard::assertEnvironmentIsSafe();
