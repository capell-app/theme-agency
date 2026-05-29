<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;

final class DiscoverQueueMonitorQueuesAction extends Action
{
    /**
     * @return list<string>
     */
    public function handle(): array
    {
        $queues = array_filter(
            Arr::wrap(config('capell-diagnostics.queue_monitor.queues', ['default'])),
            static fn (mixed $queue): bool => is_string($queue) && $queue !== '',
        );

        foreach (Arr::wrap(config('capell-diagnostics.queue_monitor.queue_config_paths', [])) as $path) {
            if (! is_string($path)) {
                continue;
            }
            if ($path === '') {
                continue;
            }
            $queue = config($path);

            if (is_string($queue) && $queue !== '') {
                $queues[] = $queue;
            }
        }

        $defaultQueue = config('queue.connections.' . config('queue.default') . '.queue');

        if (is_string($defaultQueue) && $defaultQueue !== '') {
            $queues[] = $defaultQueue;
        }

        return array_values(array_unique($queues));
    }
}
