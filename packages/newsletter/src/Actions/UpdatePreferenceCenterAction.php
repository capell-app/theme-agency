<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\PreferenceCenterData;
use Capell\Newsletter\Data\PreferenceCenterUpdateData;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePreferenceCenterAction
{
    use AsAction;

    public function handle(string $token, PreferenceCenterUpdateData $data): ?PreferenceCenterData
    {
        return DB::transaction(function () use ($token, $data): ?PreferenceCenterData {
            /** @var PublicToken|null $publicToken */
            $publicToken = PublicToken::query()
                ->where('type', PublicTokenType::PreferenceCenter)
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $publicToken instanceof PublicToken || ! $publicToken->isUsable()) {
                return null;
            }

            /** @var Subscriber|null $subscriber */
            $subscriber = $publicToken->subscriber()
                ->lockForUpdate()
                ->first();

            if (! $subscriber instanceof Subscriber) {
                return null;
            }

            $subscriber->segments()->sync($this->validSegmentIds((int) $subscriber->site_id, $data->segmentHandles));
            $subscriber->unsetRelation('segments');

            return ResolvePreferenceCenterAction::make()->fromSubscriber($subscriber);
        });
    }

    /**
     * @param  list<string>  $segmentHandles
     * @return list<int>
     */
    private function validSegmentIds(int $siteId, array $segmentHandles): array
    {
        $validSegmentIds = Segment::query()
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->whereIn('handle', $segmentHandles)
            ->pluck('id')
            ->map(static fn (mixed $segmentId): int => (int) $segmentId)
            ->values()
            ->all();

        return array_values($validSegmentIds);
    }
}
