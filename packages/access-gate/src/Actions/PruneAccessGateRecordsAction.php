<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Data\PrunedAccessGateRecordsData;
use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Enums\ClaimTokenStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class PruneAccessGateRecordsAction
{
    use AsAction;

    /**
     * @param  array<string, int|null>  $retentionDays
     */
    public function handle(array $retentionDays = [], bool $dryRun = false): PrunedAccessGateRecordsData
    {
        $browserTokens = $this->countOrDelete($this->staleBrowserTokensQuery($retentionDays), $dryRun);
        $claimTokens = $this->countOrDelete($this->staleClaimTokensQuery($retentionDays), $dryRun);
        $registrations = $this->countOrDelete($this->staleRegistrationsQuery($retentionDays), $dryRun);
        $events = $this->countOrDelete($this->staleEventsQuery($retentionDays), $dryRun);

        return new PrunedAccessGateRecordsData(
            browserTokens: $browserTokens,
            claimTokens: $claimTokens,
            registrations: $registrations,
            events: $events,
        );
    }

    /**
     * @return Builder<BrowserToken>
     */
    private function staleBrowserTokensQuery(array $retentionDays): Builder
    {
        $cutoff = now()->subDays($this->retentionDays($retentionDays, 'browser_tokens'));

        return BrowserToken::query()
            ->where(function (Builder $query) use ($cutoff): void {
                $query
                    ->whereIn('status', [
                        BrowserTokenStatus::Expired->value,
                        BrowserTokenStatus::Revoked->value,
                    ])
                    ->where('updated_at', '<', $cutoff);
            })
            ->orWhere(function (Builder $query) use ($cutoff): void {
                $query
                    ->where('status', BrowserTokenStatus::Active->value)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', $cutoff);
            });
    }

    /**
     * @return Builder<ClaimToken>
     */
    private function staleClaimTokensQuery(array $retentionDays): Builder
    {
        $cutoff = now()->subDays($this->retentionDays($retentionDays, 'claim_tokens'));

        return ClaimToken::query()
            ->where(function (Builder $query) use ($cutoff): void {
                $query
                    ->whereIn('status', [
                        ClaimTokenStatus::Claimed->value,
                        ClaimTokenStatus::Expired->value,
                        ClaimTokenStatus::Revoked->value,
                    ])
                    ->where('updated_at', '<', $cutoff);
            })
            ->orWhere(function (Builder $query) use ($cutoff): void {
                $query
                    ->where('status', ClaimTokenStatus::Active->value)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', $cutoff);
            });
    }

    /**
     * @return Builder<Registration>
     */
    private function staleRegistrationsQuery(array $retentionDays): Builder
    {
        $cutoff = now()->subDays($this->retentionDays($retentionDays, 'registrations'));

        return Registration::query()
            ->where('status', RegistrationStatus::Expired->value)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', $cutoff);
    }

    /**
     * @return Builder<Event>
     */
    private function staleEventsQuery(array $retentionDays): Builder
    {
        $cutoff = now()->subDays($this->retentionDays($retentionDays, 'events'));

        return Event::query()
            ->where('occurred_at', '<', $cutoff);
    }

    /**
     * @param  array<string, int|null>  $retentionDays
     */
    private function retentionDays(array $retentionDays, string $key): int
    {
        $configured = $retentionDays[$key] ?? config("access-gate.pruning.retention_days.{$key}");

        if (is_numeric($configured)) {
            return max(0, (int) $configured);
        }

        return match ($key) {
            'events' => 365,
            default => 90,
        };
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     */
    private function countOrDelete(Builder $query, bool $dryRun): int
    {
        if ($dryRun) {
            return $query->count();
        }

        return $query->delete();
    }
}
