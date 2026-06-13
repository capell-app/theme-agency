<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\SiteMetaSchemaAction;

function makeSeoSuiteSiteMetaAction(Site $site, Language $language): SiteMetaSchemaAction
{
    $action = new SiteMetaSchemaAction;

    $siteDomainProperty = new ReflectionProperty(SiteMetaSchemaAction::class, 'site_domain');
    $siteDomainProperty->setValue($action, 'https://example.test');

    $siteProperty = new ReflectionProperty(SiteMetaSchemaAction::class, 'site');
    $siteProperty->setValue($action, $site);

    $languageProperty = new ReflectionProperty(SiteMetaSchemaAction::class, 'language');
    $languageProperty->setValue($action, $language);

    return $action;
}

it('builds rich site contact points with areas, languages, hours, options, and social profiles', function (): void {
    $english = Language::factory()->create(['name' => 'English', 'code' => 'en']);
    $french = Language::factory()->create(['name' => 'French', 'code' => 'fr']);
    $site = Site::factory()
        ->language($english)
        ->withTranslations([$english, $french])
        ->create();
    $site->load('translations.language');

    $action = makeSeoSuiteSiteMetaAction($site, $english);
    $method = new ReflectionMethod(SiteMetaSchemaAction::class, 'contactPoint');

    $contactPoint = $method->invoke(
        $action,
        [
            ['type' => 'Country', 'name' => 'United Kingdom', 'url' => 'https://example.test/uk'],
            ['name' => 'Remote'],
        ],
        'support@example.test',
        null,
        [$english->getKey(), $french->getKey(), 9999],
        'Editorial support',
        [
            [
                'days' => ['monday', 'public_holidays'],
                'date_from' => '2026-01-01',
                'date_until' => '2026-12-31',
                'open_time' => '09:00',
                'close_time' => '17:00',
            ],
        ],
        ['HearingImpairedSupported'],
        '+441234567890',
        [
            ['url' => 'https://linkedin.test/company/example'],
        ],
        'customer support',
    );

    expect($contactPoint)->toMatchArray([
        '@type' => 'ContactPoint',
        'name' => 'Editorial support',
        'email' => 'support@example.test',
        'telephone' => '+441234567890',
        'contactType' => 'customer support',
        'contactOption' => ['HearingImpairedSupported'],
        'sameAs' => ['https://linkedin.test/company/example'],
        'availableLanguage' => ['English', 'French'],
    ])
        ->and($contactPoint['areaServed'])->toBe([
            ['@type' => 'Country', 'name' => 'United Kingdom', '@id' => 'https://example.test/uk'],
            'Remote',
        ])
        ->and($contactPoint['hoursAvailable'][0])->toMatchArray([
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => [
                'https://schema.org/Monday',
                'https://schema.org/PublicHolidays',
            ],
            'validFrom' => '2026-01-01',
            'validThrough' => '2026-12-31',
            'opens' => '09:00',
            'closes' => '17:00',
        ]);
});

it('maps schema weekdays to canonical schema.org URLs with a safe extension fallback', function (): void {
    $language = Language::factory()->create(['name' => 'English', 'code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language)
        ->create();

    $action = makeSeoSuiteSiteMetaAction($site, $language);
    $method = new ReflectionMethod(SiteMetaSchemaAction::class, 'getSchemaDay');

    expect($method->invoke($action, 'monday'))->toBe('https://schema.org/Monday')
        ->and($method->invoke($action, 'public_holidays'))->toBe('https://schema.org/PublicHolidays')
        ->and($method->invoke($action, 'seasonal'))->toBe('https://schema.org/Seasonal');
});

it('builds site schema contact points through process when contacts are present', function (): void {
    $language = Language::factory()->create(['name' => 'English', 'code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language)
        ->create();
    $site->load('translations.language');

    $action = makeSeoSuiteSiteMetaAction($site, $language);
    $method = new ReflectionMethod(SiteMetaSchemaAction::class, 'process');

    $schema = $method->invoke(
        $action,
        'LocalBusiness',
        'Example Studio',
        'Example',
        [['name' => 'Worldwide']],
        [
            [
                'name' => 'Sales',
                'email' => 'sales@example.test',
                'phone' => '+441234567890',
                'type' => 'sales',
                'languages' => [$language->getKey()],
                'socialLinks' => [
                    ['url' => 'https://linkedin.test/company/example'],
                ],
            ],
        ],
        ['GBP'],
        'A publishing studio.',
        'hello@example.test',
        null,
        null,
        [],
        ['Card'],
        '+440000000000',
        '$$',
        [['url' => 'https://github.test/example']],
    );

    expect($schema)->toMatchArray([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        '@id' => 'https://example.test/#Organization',
        'name' => 'Example Studio',
        'alternateName' => 'Example',
        'translation' => 'A publishing studio.',
        'email' => 'hello@example.test',
        'telephone' => '+440000000000',
        'priceRange' => '$$',
        'paymentAccepted' => ['Card'],
        'currenciesAccepted' => ['GBP'],
        'sameAs' => ['https://github.test/example'],
        'areaServed' => ['Worldwide'],
    ])
        ->and($schema['contactPoint'][0])->toMatchArray([
            '@type' => 'ContactPoint',
            'name' => 'Sales',
            'email' => 'sales@example.test',
            'telephone' => '+441234567890',
            'contactType' => 'sales',
            'availableLanguage' => ['English'],
            'sameAs' => ['https://linkedin.test/company/example'],
        ]);
});
