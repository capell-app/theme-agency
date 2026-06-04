<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners\Concerns;

use Closure;
use Throwable;

trait RunsQueuedContactSourceSync
{
    public int $tries = 1;

    /**
     * @param  Closure(): mixed  $callback
     */
    private function runQueuedContactSourceSync(Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
