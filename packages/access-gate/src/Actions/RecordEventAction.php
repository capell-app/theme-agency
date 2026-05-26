<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Enums\EventType;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordEventAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        EventType|string $type,
        ?Area $area = null,
        ?Registration $registration = null,
        ?Grant $grant = null,
        ?ClaimToken $claimToken = null,
        ?BrowserToken $browserToken = null,
        ?int $userId = null,
        array $payload = [],
        array $metadata = [],
        ?Model $subject = null,
    ): Event {
        return Event::query()->create([
            'access_area_id' => $this->accessAreaId($area, $registration, $grant, $claimToken, $browserToken),
            'registration_id' => $registration?->getKey(),
            'grant_id' => $grant?->getKey(),
            'claim_token_id' => $claimToken?->getKey(),
            'browser_token_id' => $browserToken?->getKey(),
            'user_id' => $userId,
            'type' => $type instanceof EventType ? $type : $type,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'payload' => $payload,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    private function accessAreaId(
        ?Area $area,
        ?Registration $registration,
        ?Grant $grant,
        ?ClaimToken $claimToken,
        ?BrowserToken $browserToken,
    ): int|string|null {
        if ($area instanceof Area) {
            return $area->getKey();
        }

        if ($registration instanceof Registration) {
            return $registration->access_area_id;
        }

        if ($grant instanceof Grant) {
            return $grant->access_area_id;
        }

        if ($claimToken instanceof ClaimToken) {
            return $claimToken->access_area_id;
        }

        return $browserToken?->access_area_id;
    }
}
