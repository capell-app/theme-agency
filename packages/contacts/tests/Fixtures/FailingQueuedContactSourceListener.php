<?php

declare(strict_types=1);

namespace Capell\Contacts\Tests\Fixtures;

use Capell\Contacts\Listeners\Concerns\RunsQueuedContactSourceSync;
use RuntimeException;

final class FailingQueuedContactSourceListener
{
    use RunsQueuedContactSourceSync;

    public function handle(): void
    {
        $this->runQueuedContactSourceSync(static function (): never {
            throw new RuntimeException('Fixture source sync failure.');
        });
    }
}
