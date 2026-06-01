<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static list<string> run(array $schema, ?CarbonImmutable $today = null, int $ratingFreshnessDays = 365)
 */
final class BuildMarketplaceStructuredDataFreshnessWarningsAction
{
    use AsAction;

    private const int DefaultRatingFreshnessDays = 365;

    /**
     * @param  array<string, mixed>  $schema
     * @return list<string>
     */
    public function handle(
        array $schema,
        ?CarbonImmutable $today = null,
        int $ratingFreshnessDays = self::DefaultRatingFreshnessDays,
    ): array {
        $today ??= CarbonImmutable::today();
        $warnings = [];

        foreach ($this->flattenSchemas($schema) as $item) {
            $schemaTypes = $this->schemaTypes($item);

            if (array_intersect($schemaTypes, ['Product', 'Offer']) !== []) {
                array_push($warnings, ...$this->pricingWarnings($item, $today));
            }

            if (in_array('AggregateRating', $schemaTypes, true)) {
                array_push($warnings, ...$this->ratingWarnings($item, $today, $ratingFreshnessDays));
            }
        }

        return array_values(array_unique($warnings));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    private function pricingWarnings(array $item, CarbonImmutable $today): array
    {
        if (! $this->hasFilledValue($item, 'price')) {
            return [];
        }

        $validUntil = $this->stringValue($item['priceValidUntil'] ?? null);

        if ($validUntil === null) {
            return [__('capell-seo-suite::generic.schema_marketplace_price_valid_until_missing')];
        }

        $validUntilDate = $this->dateValue($validUntil);

        if (! $validUntilDate instanceof CarbonImmutable) {
            return [__('capell-seo-suite::generic.schema_marketplace_price_valid_until_invalid')];
        }

        if ($validUntilDate->lt($today)) {
            return [__('capell-seo-suite::generic.schema_marketplace_price_valid_until_expired')];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    private function ratingWarnings(array $item, CarbonImmutable $today, int $ratingFreshnessDays): array
    {
        if (! $this->hasFilledValue($item, 'ratingValue')) {
            return [];
        }

        $warnings = [];

        if (! $this->hasFilledValue($item, 'reviewCount') && ! $this->hasFilledValue($item, 'ratingCount')) {
            $warnings[] = __('capell-seo-suite::generic.schema_marketplace_rating_count_missing');
        }

        $updatedAt = $this->dateValue($this->stringValue($item['dateModified'] ?? $item['datePublished'] ?? null));

        if (! $updatedAt instanceof CarbonImmutable) {
            $warnings[] = __('capell-seo-suite::generic.schema_marketplace_rating_date_missing');

            return $warnings;
        }

        if ($updatedAt->lt($today->subDays(max(1, $ratingFreshnessDays)))) {
            $warnings[] = __('capell-seo-suite::generic.schema_marketplace_rating_stale');
        }

        return $warnings;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return list<array<string, mixed>>
     */
    private function flattenSchemas(array $schema): array
    {
        $items = [];
        $schemas = Arr::isAssoc($schema) ? [$schema] : $schema;

        foreach ($schemas as $item) {
            if (! is_array($item)) {
                continue;
            }

            /** @var array<string, mixed> $item */
            $items[] = $item;

            foreach (['@graph', 'offers', 'aggregateRating', 'review'] as $nestedKey) {
                $nested = $item[$nestedKey] ?? null;

                if (is_array($nested)) {
                    array_push($items, ...$this->flattenSchemas($nested));
                }
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    private function schemaTypes(array $item): array
    {
        $type = $item['@type'] ?? null;

        if (is_string($type) && trim($type) !== '') {
            return [trim($type)];
        }

        if (! is_array($type)) {
            return [];
        }

        return array_values(array_filter(
            array_map($this->stringValue(...), $type),
            fn (?string $value): bool => $value !== null,
        ));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function hasFilledValue(array $item, string $key): bool
    {
        return $this->stringValue($item[$key] ?? null) !== null;
    }

    private function stringValue(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    private function dateValue(?string $value): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
