<?php

declare(strict_types=1);

use Capell\Contacts\Tests\ContactsTestCase;

require_once __DIR__ . '/autoload.php';

pest()->extend(ContactsTestCase::class)->group('contacts')->in(__DIR__ . '/Feature');
pest()->group('contacts')->in(__DIR__ . '/Unit');
