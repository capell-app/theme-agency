<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailBodyPruneResultData;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Models\EmailMessage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class PruneEmailBodiesAction
{
    use AsAction;

    public function handle(?int $retentionDays = null, bool $dryRun = false): EmailBodyPruneResultData
    {
        $resolvedRetentionDays = max(1, $retentionDays ?? (int) config('capell-email-studio.body_retention_days', 90));
        $cutoff = CarbonImmutable::now()->subDays($resolvedRetentionDays);
        $query = $this->retainedBodyQuery($cutoff);
        $matchedMessages = (clone $query)->count();
        $prunedMessages = $dryRun ? 0 : $query->update([
            'rendered_html' => null,
            'rendered_text' => null,
        ]);

        return new EmailBodyPruneResultData(
            retentionDays: $resolvedRetentionDays,
            dryRun: $dryRun,
            matchedMessages: $matchedMessages,
            prunedMessages: $prunedMessages,
        );
    }

    /**
     * @return Builder<EmailMessage>
     */
    private function retainedBodyQuery(CarbonImmutable $cutoff): Builder
    {
        return EmailMessage::query()
            ->whereIn('status', [
                EmailMessageStatus::Sent->value,
                EmailMessageStatus::Failed->value,
                EmailMessageStatus::PartiallyFailed->value,
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('rendered_html')
                    ->orWhereNotNull('rendered_text');
            })
            ->where(function (Builder $query) use ($cutoff): void {
                $query
                    ->where(function (Builder $query) use ($cutoff): void {
                        $query
                            ->whereNotNull('sent_at')
                            ->where('sent_at', '<', $cutoff);
                    })
                    ->orWhere(function (Builder $query) use ($cutoff): void {
                        $query
                            ->whereNull('sent_at')
                            ->whereNotNull('failed_at')
                            ->where('failed_at', '<', $cutoff);
                    })
                    ->orWhere(function (Builder $query) use ($cutoff): void {
                        $query
                            ->whereNull('sent_at')
                            ->whereNull('failed_at')
                            ->where('created_at', '<', $cutoff);
                    });
            });
    }
}
