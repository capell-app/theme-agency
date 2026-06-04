<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Jobs;

use Capell\EmailStudio\Actions\DeliverEmailMessageAction;
use Capell\EmailStudio\Actions\MarkEmailMessageDeliveryFailedAction;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 4;

    public int $timeout = 60;

    public function __construct(
        public int $emailMessageId,
    ) {}

    public function handle(): void
    {
        DeliverEmailMessageAction::run($this->emailMessageId);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(2)->toImmutable();
    }

    public function failed(Throwable $exception): void
    {
        MarkEmailMessageDeliveryFailedAction::run($this->emailMessageId, $exception->getMessage());
    }
}
