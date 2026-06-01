<?php

declare(strict_types=1);

namespace Capell\Contacts\Data;

use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ContactSourceRecordData extends Data
{
    /**
     * @param  array<string, mixed>|null  $profile
     * @param  array<string, mixed>|null  $activityPayload
     * @param  list<string>|null  $tags
     */
    public function __construct(
        public int $siteId,
        public string $sourceKey,
        public string $sourceIdentifier,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $displayName = null,
        public ?array $profile = null,
        public ?array $tags = null,
        public ?ContactActivityType $activityType = null,
        public ?string $activitySummary = null,
        public ?array $activityPayload = null,
        public ?string $leadTitle = null,
        public ?LeadStatus $leadStatus = null,
        public ?CarbonInterface $occurredAt = null,
    ) {}
}
