<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\PreferenceCenterData;
use Capell\Newsletter\Data\PreferenceCenterSegmentData;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolvePreferenceCenterAction
{
    use AsAction;

    public function fromSubscriber(Subscriber $subscriber): PreferenceCenterData
    {
        $subscriber->loadMissing('segments');

        $selectedSegmentIds = $subscriber->segments
            ->pluck('id')
            ->map(static fn (mixed $segmentId): int => (int) $segmentId)
            ->values()
            ->all();

        return new PreferenceCenterData(
            subscriberId: (int) $subscriber->getKey(),
            siteId: (int) $subscriber->site_id,
            email: $subscriber->email,
            status: $subscriber->status,
            canReceiveNewsletter: $subscriber->status->isSendable(),
            segments: $this->segments((int) $subscriber->site_id, array_values($selectedSegmentIds)),
        );
    }

    public function handle(string $token): ?PreferenceCenterData
    {
        /** @var PublicToken|null $publicToken */
        $publicToken = PublicToken::query()
            ->where('type', PublicTokenType::PreferenceCenter)
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $publicToken instanceof PublicToken || ! $publicToken->isUsable()) {
            return null;
        }

        /** @var Subscriber|null $subscriber */
        $subscriber = $publicToken->subscriber()
            ->with('segments')
            ->first();

        if (! $subscriber instanceof Subscriber) {
            return null;
        }

        return $this->fromSubscriber($subscriber);
    }

    /**
     * @param  list<int>  $selectedSegmentIds
     * @return list<PreferenceCenterSegmentData>
     */
    private function segments(int $siteId, array $selectedSegmentIds): array
    {
        /** @var Collection<int, Segment> $segments */
        $segments = Segment::query()
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $preferenceSegments = $segments
            ->map(static fn (Segment $segment): PreferenceCenterSegmentData => new PreferenceCenterSegmentData(
                id: (int) $segment->getKey(),
                name: $segment->name,
                handle: $segment->handle,
                selected: in_array((int) $segment->getKey(), $selectedSegmentIds, true),
            ))
            ->values()
            ->all();

        return array_values($preferenceSegments);
    }
}
