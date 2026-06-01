<?php

declare(strict_types=1);

use Capell\SeoSuite\Actions\BuildMarketplaceStructuredDataFreshnessWarningsAction;
use Carbon\CarbonImmutable;

it('warns when marketplace product pricing has no freshness boundary', function (): void {
    $warnings = BuildMarketplaceStructuredDataFreshnessWarningsAction::run([
        '@type' => 'Product',
        'name' => 'Annual membership',
        'offers' => [
            '@type' => 'Offer',
            'price' => '99.00',
            'priceCurrency' => 'GBP',
        ],
    ]);

    expect($warnings)->toContain(__('capell-seo-suite::generic.schema_marketplace_price_valid_until_missing'));
});

it('warns when marketplace product pricing has expired', function (): void {
    $warnings = BuildMarketplaceStructuredDataFreshnessWarningsAction::run(
        [
            '@type' => 'Product',
            'offers' => [
                '@type' => 'Offer',
                'price' => '99.00',
                'priceCurrency' => 'GBP',
                'priceValidUntil' => '2026-01-31',
            ],
        ],
        CarbonImmutable::parse('2026-06-01'),
    );

    expect($warnings)->toContain(__('capell-seo-suite::generic.schema_marketplace_price_valid_until_expired'));
});

it('warns when marketplace ratings are missing count and freshness metadata', function (): void {
    $warnings = BuildMarketplaceStructuredDataFreshnessWarningsAction::run([
        '@type' => 'Product',
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.8',
        ],
    ]);

    expect($warnings)->toContain(
        __('capell-seo-suite::generic.schema_marketplace_rating_count_missing'),
        __('capell-seo-suite::generic.schema_marketplace_rating_date_missing'),
    );
});

it('warns when marketplace ratings have not been refreshed recently', function (): void {
    $warnings = BuildMarketplaceStructuredDataFreshnessWarningsAction::run(
        [
            '@graph' => [
                [
                    '@type' => 'Product',
                    'name' => 'Paid download',
                ],
                [
                    '@type' => 'AggregateRating',
                    'ratingValue' => '4.6',
                    'reviewCount' => 42,
                    'dateModified' => '2024-12-01',
                ],
            ],
        ],
        CarbonImmutable::parse('2026-06-01'),
        365,
    );

    expect($warnings)->toContain(__('capell-seo-suite::generic.schema_marketplace_rating_stale'));
});
