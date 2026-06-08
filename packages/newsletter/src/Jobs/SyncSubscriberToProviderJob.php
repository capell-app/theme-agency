<?php

declare(strict_types=1);

namespace Capell\Newsletter\Jobs;

use Capell\Newsletter\Actions\SyncSubscriberToProviderAction;
use Capell\Newsletter\Models\SyncAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSubscriberToProviderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public SyncAttempt $syncAttempt,
    ) {
        $this->afterCommit();

        $queue = config('capell-newsletter.sync.queue');

        if (is_string($queue) && $queue !== '') {
            $this->onQueue($queue);
        }
    }

    public function handle(): void
    {
        SyncSubscriberToProviderAction::run($this->syncAttempt->refresh());
    }
}
