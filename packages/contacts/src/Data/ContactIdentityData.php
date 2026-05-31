<?php

declare(strict_types=1);

namespace Capell\Contacts\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class ContactIdentityData extends Data
{
    /**
     * @param  array<string, mixed>|null  $profile
     */
    public function __construct(
        public int $siteId,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $displayName = null,
        public ?array $profile = null,
        public ?CarbonInterface $seenAt = null,
    ) {}
}
