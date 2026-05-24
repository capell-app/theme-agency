<?php

declare(strict_types=1);

use Capell\Comments\Tests\CommentsTestCase;

pest()->extend(CommentsTestCase::class)->group('comments')->in(__DIR__);
