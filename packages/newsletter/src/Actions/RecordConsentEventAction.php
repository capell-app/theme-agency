<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\ConsentEvent;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\Subscriber;
use Lorisleiva\Actions\Concerns\AsAction;

class RecordConsentEventAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        Subscriber $subscriber,
        ConsentEventType $eventType,
        ?ConsentEvidenceData $evidence = null,
        ?SubscriberStatus $status = null,
        ?ProviderConnection $providerConnection = null,
        array $metadata = [],
    ): ConsentEvent {
        $metadata = $this->metadataWithUtmAttribution($metadata, $evidence);

        return ConsentEvent::query()->create([
            'subscriber_id' => $subscriber->getKey(),
            'site_id' => $subscriber->site_id,
            'event_type' => $eventType,
            'subscriber_status' => $status ?? $subscriber->status,
            'source_type' => $evidence?->sourceType,
            'source_id' => $evidence?->sourceId,
            'provider_connection_id' => $providerConnection?->getKey(),
            'evidence' => $evidence?->toArray(),
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function metadataWithUtmAttribution(array $metadata, ?ConsentEvidenceData $evidence): array
    {
        if (array_key_exists('utm_attribution', $metadata)) {
            return $metadata;
        }

        $attribution = ResolveUtmAttributionAction::run($evidence);

        if ($attribution === null) {
            return $metadata;
        }

        return array_merge($metadata, [
            'utm_attribution' => $attribution->toMetadataArray(),
        ]);
    }
}
