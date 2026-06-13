<?php

declare(strict_types=1);

use Capell\LiveChat\Tests\LiveChatTestCase;

pest()->extend(LiveChatTestCase::class)->group('live-chat')->in(__DIR__);
