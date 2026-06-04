<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class CampaignConversionCaptureData extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly string $url,
        public readonly ?string $goalKey = null,
        public readonly ?string $ctaKey = null,
        public readonly ?string $visitId = null,
    ) {}
}
