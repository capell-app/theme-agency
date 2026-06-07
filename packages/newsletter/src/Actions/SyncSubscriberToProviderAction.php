<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ProviderInterestData;
use Capell\Newsletter\Data\ProviderSubscriberData;
use Capell\Newsletter\Enums\SyncStatus;
use Capell\Newsletter\Models\ProviderInterestMapping;
use Capell\Newsletter\Models\ProviderSubscriber;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Models\SyncAttempt;
use Capell\Newsletter\Support\Providers\ProviderAdapterRegistry;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class SyncSubscriberToProviderAction
{
    use AsAction;

    public function handle(SyncAttempt $syncAttempt): SyncAttempt
    {
        if (! $this->claimForSync($syncAttempt)) {
            return $syncAttempt->fresh() ?? $syncAttempt;
        }

        $syncAttempt = $syncAttempt->fresh(['subscriber', 'providerAudience', 'providerConnection']) ?? $syncAttempt;

        $subscriber = $syncAttempt->subscriber;
        $audience = $syncAttempt->providerAudience;
        $connection = $syncAttempt->providerConnection;

        if (! $subscriber instanceof Subscriber || $audience === null || $connection === null) {
            return $this->fail($syncAttempt, 'Missing subscriber, audience, or connection.');
        }

        if ($subscriber->status->isGloballySuppressed()) {
            return $this->failPermanently($syncAttempt, 'Subscriber is globally suppressed.');
        }

        try {
            $adapter = resolve(ProviderAdapterRegistry::class)->resolve($connection->provider);
            $result = $adapter->syncSubscriber(
                $connection,
                $audience,
                new ProviderSubscriberData(
                    email: $subscriber->email,
                    status: $subscriber->status,
                    firstName: $subscriber->first_name,
                    lastName: $subscriber->last_name,
                    profile: is_array($subscriber->profile) ? $subscriber->profile : [],
                    interests: $this->interests($subscriber, $audience->getKey()),
                ),
            );
        } catch (Throwable $throwable) {
            return $this->fail($syncAttempt, $throwable->getMessage());
        }

        if (! $result->successful) {
            return $this->fail($syncAttempt, $result->errorMessage ?? 'Provider sync failed.');
        }

        ProviderSubscriber::query()->updateOrCreate([
            'subscriber_id' => $subscriber->getKey(),
            'provider_audience_id' => $audience->getKey(),
        ], [
            'remote_id' => $result->remoteId,
            'remote_status' => $result->remoteStatus,
            'synced_at' => now(),
        ]);

        $syncAttempt->forceFill([
            'sync_status' => SyncStatus::Succeeded,
            'error_message' => null,
            'next_retry_at' => null,
        ])->save();

        return $syncAttempt->refresh();
    }

    private function claimForSync(SyncAttempt $syncAttempt): bool
    {
        return SyncAttempt::query()
            ->whereKey($syncAttempt->getKey())
            ->where('sync_status', SyncStatus::Pending)
            ->update([
                'sync_status' => SyncStatus::Running,
                'attempts' => DB::raw('attempts + 1'),
                'last_attempted_at' => now(),
            ]) === 1;
    }

    /**
     * @return array<int, ProviderInterestData>
     */
    private function interests(Subscriber $subscriber, int $providerAudienceId): array
    {
        $newsletterTagType = config('capell-newsletter.newsletter_tag_type', 'newsletter');
        $tagIds = $subscriber->tags()
            ->where('type', is_string($newsletterTagType) ? $newsletterTagType : 'newsletter')
            ->pluck('tags.id')
            ->map(static fn (mixed $tagId): int => (int) $tagId)
            ->all();

        return ProviderInterestMapping::query()
            ->where('provider_audience_id', $providerAudienceId)
            ->whereIn('tag_id', $tagIds)
            ->get()
            ->map(static fn (ProviderInterestMapping $mapping): ProviderInterestData => new ProviderInterestData(
                tagId: $mapping->tag_id,
                remoteId: $mapping->remote_interest_id,
                remoteType: $mapping->remote_interest_type,
                name: $mapping->remote_name,
            ))
            ->values()
            ->all();
    }

    private function fail(SyncAttempt $syncAttempt, string $errorMessage): SyncAttempt
    {
        $retryMinutes = config('capell-newsletter.sync.retry_minutes', [5, 30, 120]);
        $retryDelay = is_array($retryMinutes)
            ? ($retryMinutes[min($syncAttempt->attempts - 1, count($retryMinutes) - 1)] ?? null)
            : null;

        $syncAttempt->forceFill([
            'sync_status' => $retryDelay === null ? SyncStatus::Exhausted : SyncStatus::RetryScheduled,
            'error_message' => $errorMessage,
            'next_retry_at' => is_numeric($retryDelay) ? now()->addMinutes((int) $retryDelay) : null,
        ])->save();

        return $syncAttempt->refresh();
    }

    private function failPermanently(SyncAttempt $syncAttempt, string $errorMessage): SyncAttempt
    {
        $syncAttempt->forceFill([
            'sync_status' => SyncStatus::Failed,
            'error_message' => $errorMessage,
            'next_retry_at' => null,
        ])->save();

        return $syncAttempt->refresh();
    }
}
