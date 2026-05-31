<?php

declare(strict_types=1);

use Capell\Bookings\Tests\BookingsTestCase;

pest()->extend(BookingsTestCase::class)->group('bookings')->in(__DIR__);
