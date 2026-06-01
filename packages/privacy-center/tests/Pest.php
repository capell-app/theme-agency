<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;

require_once __DIR__ . '/PrivacyCenterTestCase.php';

pest()->extend(PrivacyCenterTestCase::class)->group('privacy-center')->in(__DIR__);
