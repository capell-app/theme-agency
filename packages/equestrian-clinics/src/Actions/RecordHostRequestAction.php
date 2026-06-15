<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianHostRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianHostRequest run(array<string, mixed> $payload)
 */
final class RecordHostRequestAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): EquestrianHostRequest
    {
        /** @var EquestrianHostRequest $hostRequest */
        $hostRequest = EquestrianHostRequest::query()->create([
            'site_id' => $payload['site_id'] ?? null,
            'requester_name' => $payload['requester_name'],
            'requester_email' => $payload['requester_email'],
            'requester_phone' => $payload['requester_phone'] ?? null,
            'venue_name' => $payload['venue_name'] ?? null,
            'postal_code' => $payload['postal_code'] ?? null,
            'preferred_region' => $payload['preferred_region'] ?? null,
            'lesson_type' => $payload['lesson_type'] ?? null,
            'skill_tier' => $payload['skill_tier'] ?? null,
            'expected_riders' => $payload['expected_riders'] ?? null,
            'message' => $payload['message'] ?? null,
            'payload' => $payload,
        ]);

        return $hostRequest;
    }
}
