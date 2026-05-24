<?php

declare(strict_types=1);

use Capell\FilamentPeek\Tests\FilamentPeekTestCase;

pest()->extend(FilamentPeekTestCase::class)->group('filament-peek')->in(__DIR__);
