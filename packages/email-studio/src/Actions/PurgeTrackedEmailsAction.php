<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\TrackedEmailPurgeResultData;
use Capell\EmailStudio\Models\SentEmail;
use Capell\EmailStudio\Models\SentEmailUrlClicked;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

final class PurgeTrackedEmailsAction
{
    use AsAction;

    public function handle(?int $retentionDays = null, bool $dryRun = false): TrackedEmailPurgeResultData
    {
        ApplyMailTrackerSettingsAction::run();

        $resolvedRetentionDays = $retentionDays ?? $this->configuredRetentionDays();

        if ($resolvedRetentionDays < 1) {
            return new TrackedEmailPurgeResultData(
                retentionDays: max(0, $resolvedRetentionDays),
                dryRun: $dryRun,
                matchedEmails: 0,
                deletedClicks: 0,
                deletedEmails: 0,
            );
        }

        $query = $this->expiredEmailQuery(CarbonImmutable::now()->subDays($resolvedRetentionDays));
        $matchedEmails = (clone $query)->count();

        if ($dryRun) {
            return new TrackedEmailPurgeResultData(
                retentionDays: $resolvedRetentionDays,
                dryRun: true,
                matchedEmails: $matchedEmails,
                deletedClicks: 0,
                deletedEmails: 0,
            );
        }

        $deletedClicks = 0;
        $deletedEmails = 0;

        /**
         * @param  EloquentCollection<int, SentEmail>  $emails
         */
        $purgeChunk = function (EloquentCollection $emails) use (&$deletedClicks, &$deletedEmails): void {
            $emailIds = [];

            foreach ($emails as $email) {
                $emailIds[] = $email->id;
            }

            if ($emailIds === []) {
                return;
            }

            $deletedClickRows = SentEmailUrlClicked::query()
                ->whereIn('sent_email_id', $emailIds)
                ->delete();

            if (is_int($deletedClickRows)) {
                $deletedClicks += $deletedClickRows;
            }

            foreach ($emails as $email) {
                if ($email->delete()) {
                    $deletedEmails++;
                }
            }
        };

        $query
            ->select(['id', 'meta'])
            ->orderBy('id')
            ->chunkById(200, $purgeChunk);

        return new TrackedEmailPurgeResultData(
            retentionDays: $resolvedRetentionDays,
            dryRun: false,
            matchedEmails: $matchedEmails,
            deletedClicks: $deletedClicks,
            deletedEmails: $deletedEmails,
        );
    }

    /**
     * @return Builder<SentEmail>
     */
    private function expiredEmailQuery(CarbonImmutable $cutoff): Builder
    {
        return SentEmail::query()
            ->where('created_at', '<', $cutoff);
    }

    private function configuredRetentionDays(): int
    {
        $value = config('mail-tracker.expire-days', 60);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return 60;
    }
}
