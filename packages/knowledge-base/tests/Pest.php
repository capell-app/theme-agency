<?php

declare(strict_types=1);

use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;

require_once __DIR__ . '/KnowledgeBaseTestCase.php';

pest()->extend(KnowledgeBaseTestCase::class)->group('knowledge-base')->in(__DIR__);
