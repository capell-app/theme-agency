<?php

declare(strict_types=1);

namespace Capell\Payments\Jobs;

use Capell\Payments\Actions\ProcessStripeWebhookEventAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessStripeWebhookEventJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $webhookEventId,
    ) {}

    public function handle(): void
    {
        ProcessStripeWebhookEventAction::run($this->webhookEventId);
    }

    public function uniqueId(): string
    {
        return (string) $this->webhookEventId;
    }
}
