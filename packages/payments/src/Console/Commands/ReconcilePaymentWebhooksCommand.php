<?php

declare(strict_types=1);

namespace Capell\Payments\Console\Commands;

use Capell\Payments\Actions\ReconcilePaymentWebhooksAction;
use Illuminate\Console\Command;

final class ReconcilePaymentWebhooksCommand extends Command
{
    protected $signature = 'capell:payments:webhooks:reconcile
        {--stale-after=30 : Minutes before a received webhook is considered stale}';

    protected $description = 'Report stored payment webhook processing state and stale received events.';

    public function handle(): int
    {
        $result = ReconcilePaymentWebhooksAction::run(
            staleAfterMinutes: $this->positiveIntOption('stale-after', 30),
        );

        $this->components->info(sprintf(
            'Payment webhook reconciliation. Received: %d, processed: %d, ignored: %d, failed: %d, stale received: %d.',
            $result->received,
            $result->processed,
            $result->ignored,
            $result->failed,
            $result->staleReceived,
        ));

        return $result->failed > 0 || $result->staleReceived > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function positiveIntOption(string $name, int $fallback): int
    {
        $value = $this->option($name);

        if (! is_numeric($value) || (int) $value < 1) {
            return $fallback;
        }

        return (int) $value;
    }
}
