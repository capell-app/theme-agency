<?php

declare(strict_types=1);

use Capell\UrlManager\Tests\UrlManagerTestCase;

pest()->extend(UrlManagerTestCase::class)->group('url-manager')->in(__DIR__);
uses(UrlManagerTestCase::class)->group('url-manager')->in(__DIR__);
