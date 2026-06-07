<?php

declare(strict_types=1);

namespace Capell\Payments\Console\Commands;

use Capell\Payments\Actions\ReprocessPaymentWebhookEventsAction;
use Illuminate\Console\Command;

final class ReprocessPaymentWebhookEventsCommand extends Command
{
    protected $signature = 'capell:payments:webhooks:reprocess
        {--event-id= : Reprocess one stored webhook event id}
        {--limit=100 : Maximum failed webhook events to reprocess}';

    protected $description = 'Reprocess failed stored payment webhook events.';

    public function handle(): int
    {
        $eventId = $this->positiveIntOption('event-id');
        $limit = $this->positiveIntOption('limit') ?? 100;

        $result = ReprocessPaymentWebhookEventsAction::run($eventId, $limit);

        $this->components->info(sprintf(
            'Payment webhook reprocess complete. Processed: %d, failed: %d.',
            $result->processed,
            $result->failed,
        ));

        return $result->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function positiveIntOption(string $name): ?int
    {
        $value = $this->option($name);

        if (! is_numeric($value) || (int) $value < 1) {
            return null;
        }

        return (int) $value;
    }
}
