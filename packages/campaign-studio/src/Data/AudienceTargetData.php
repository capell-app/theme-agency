<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class AudienceTargetData extends Data
{
    public function __construct(
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $utmTerm = null,
        public ?string $utmContent = null,
    ) {}

    public static function fromUrl(?string $url): self
    {
        if (! is_string($url) || trim($url) === '') {
            return new self;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return new self;
        }

        $parameters = [];
        parse_str($query, $parameters);

        return new self(
            utmSource: self::nullableString($parameters['utm_source'] ?? null),
            utmMedium: self::nullableString($parameters['utm_medium'] ?? null),
            utmCampaign: self::nullableString($parameters['utm_campaign'] ?? null),
            utmTerm: self::nullableString($parameters['utm_term'] ?? null),
            utmContent: self::nullableString($parameters['utm_content'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmedValue = trim($value);

        return $trimmedValue === '' ? null : $trimmedValue;
    }
}
