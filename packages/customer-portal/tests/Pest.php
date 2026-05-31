<?php

declare(strict_types=1);

use Capell\CustomerPortal\Tests\CustomerPortalTestCase;

require_once __DIR__ . '/autoload.php';

pest()->extend(CustomerPortalTestCase::class)->group('customer-portal')->in(__DIR__ . '/Feature');
pest()->group('customer-portal')->in(__DIR__ . '/Unit');
