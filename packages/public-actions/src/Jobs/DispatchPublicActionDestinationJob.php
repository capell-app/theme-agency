<?php

declare(strict_types=1);

namespace Capell\PublicActions\Jobs;

use Capell\PublicActions\Actions\DispatchPublicActionDestinationAction;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class DispatchPublicActionDestinationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public PublicActionDestination $destination,
        public PublicActionSubmission $submission,
    ) {
        $this->onQueue(config('capell-public-actions.queue', 'default'));
    }

    public function handle(DispatchPublicActionDestinationAction $dispatchDestination): void
    {
        $result = $dispatchDestination->handle($this->destination, $this->submission);

        if ($result->dispatchStatus === PublicActionDispatchStatus::Retryable && $this->attempts() < $this->tries) {
            $this->release($this->retryDelaySeconds());
        }
    }

    private function retryDelaySeconds(): int
    {
        $delay = config('capell-public-actions.dispatch_retry_seconds', 60);

        return is_numeric($delay) && (int) $delay > 0 ? (int) $delay : 60;
    }
}
