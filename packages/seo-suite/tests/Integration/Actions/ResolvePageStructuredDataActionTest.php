<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\LanguageFactory;
use Capell\Core\Database\Factories\PageFactory;
use Capell\Core\Database\Factories\SiteFactory;
use Capell\SeoSuite\Actions\ResolvePageStructuredDataAction;

it('builds structured data from page and translation records', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()
        ->recycle($language)
        ->language($language)
        ->withTranslations($language, [
            'title' => 'Capell',
            'meta' => ['description' => 'A CMS toolkit for Laravel apps.'],
        ], [
            'domain' => 'capell.test',
            'scheme' => 'https',
            'path' => null,
            'default' => true,
        ])
        ->meta([
            'business_name' => 'Capell',
            'structured_data' => [
                [
                    '@type' => 'SoftwareApplication',
                    'name' => 'Capell',
                    'url' => '/',
                    'sameAs' => ['https://docs.capell.app'],
                ],
            ],
        ])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Features',
            'meta' => [
                'description' => 'Compare Capell CMS features.',
                'structured_data' => [
                    [
                        '@type' => 'ItemList',
                        'name' => 'Feature catalogue',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Pages'],
                        ],
                    ],
                ],
            ],
        ])
        ->create();

    $schemas = ResolvePageStructuredDataAction::run($page->refresh(), $site->refresh(), $language);

    expect(collect($schemas)->pluck('@type')->all())
        ->toContain('Organization', 'SoftwareApplication', 'WebPage', 'BreadcrumbList', 'ItemList')
        ->and(collect($schemas)->firstWhere('@type', 'SoftwareApplication')['url'])
        ->toBe('https://capell.test/')
        ->and(collect($schemas)->firstWhere('@type', 'ItemList')['itemListElement'])
        ->toHaveCount(1);
});
