<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatVisitorData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $company = null,
        public ?string $topic = null,
        public ?CarbonInterface $preferredCallbackAt = null,
        public bool $processingConsent = false,
        public bool $marketingConsent = false,
    ) {}
}
