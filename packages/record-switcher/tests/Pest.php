<?php

declare(strict_types=1);

use Capell\RecordSwitcher\Tests\RecordSwitcherTestCase;

pest()->extend(RecordSwitcherTestCase::class)->group('record-switcher')->in(__DIR__);
