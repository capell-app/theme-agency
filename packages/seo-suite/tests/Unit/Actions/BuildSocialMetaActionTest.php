<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Actions\BuildSocialMetaAction;
use Capell\SeoSuite\Enums\OpenGraphTypeEnum;
use Carbon\CarbonImmutable;

it('builds social metadata from explicit social fields and article schema data', function (): void {
    $language = Language::factory()->create(['code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, [
            'title' => '<strong>Capell Site</strong>',
            'meta' => ['title_after_text' => 'Ignored suffix'],
        ], siteDomainData: ['domain' => 'example.test', 'scheme' => 'https', 'path' => null, 'default' => true])
        ->create(['meta' => ['twitter' => '@capell']]);
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Fallback page title',
            'meta' => [
                'title' => 'Meta page title',
                'description' => '<p>Meta description</p>',
                'social_title' => 'Social title',
                'social_description' => '<p>Social description</p>',
            ],
        ])
        ->create([
            'visible_from' => CarbonImmutable::parse('2026-01-02 03:04:05'),
            'updated_at' => CarbonImmutable::parse('2026-01-03 03:04:05'),
        ]);

    $pageUrl = PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->state(['url' => '/social-page'])
        ->create();

    $page->type->forceFill(['meta' => ['schema' => ['type' => 'NewsArticle']]])->save();
    $page->refresh()->load(['translation', 'type']);
    $page->setRelation('pageUrl', $pageUrl->load('siteDomain'));
    $siteTranslation = new Translation;
    $siteTranslation->forceFill([
        'title' => '<strong>Capell Site</strong>',
        'meta' => ['title_after_text' => 'Ignored suffix'],
    ]);
    $site->refresh();
    $site->setRelation('translation', $siteTranslation);

    $social = BuildSocialMetaAction::run($page, $site, $language);

    expect($social->title)->toBe('Social title')
        ->and($social->description)->toBe('Social description')
        ->and($social->ogType)->toBe(OpenGraphTypeEnum::Article)
        ->and($social->url)->toBe('https://example.test/social-page')
        ->and($social->siteName)->toBe('Capell Site')
        ->and($social->twitterHandle)->toBe('@capell')
        ->and($social->articlePublishedTime)->toBe('2026-01-02T03:04:05+00:00')
        ->and($social->articleModifiedTime)->toBe('2026-01-03T03:04:05+00:00')
        ->and($social->articleAuthor)->toBeNull();
});

it('falls back to meta title and page title for website social metadata', function (): void {
    config()->set('capell-frontend.meta_title_seperator', ' | ');

    $language = Language::factory()->create(['code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, [
            'title' => 'Site Name',
            'meta' => ['title_after_text' => 'Suffix'],
        ])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Visible page title',
            'content' => '<p>Body description fallback.</p>',
            'meta' => [],
        ])
        ->create();

    $page->refresh()->load(['translation', 'type']);
    $page->setRelation('translation', $page->translations()->where('language_id', $language->getKey())->first());
    $siteTranslation = new Translation;
    $siteTranslation->forceFill([
        'title' => 'Site Name',
        'meta' => ['title_after_text' => 'Suffix'],
    ]);
    $site->refresh();
    $site->setRelation('translation', $siteTranslation);

    $social = BuildSocialMetaAction::run($page, $site, $language);

    expect($social->title)->toBe('Visible page title')
        ->and($social->description)->toBe('Body description fallback.')
        ->and($social->ogType)->toBe(OpenGraphTypeEnum::Website)
        ->and($social->articlePublishedTime)->toBeNull()
        ->and($social->articleModifiedTime)->toBeNull()
        ->and($social->articleAuthor)->toBeNull();

    $page->translation->forceFill(['meta' => ['title' => 'Meta title wins']]);

    $metaTitleSocial = BuildSocialMetaAction::run($page, $site, $language);

    expect($metaTitleSocial->title)->toBe('Meta title wins');
});

it('resolves template tokens such as :site in the social title', function (): void {
    $language = Language::factory()->create(['code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, ['title' => 'Capell Ruby'])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Home',
            'meta' => ['title' => ':site'],
        ])
        ->create();

    $page->refresh()->load(['translation', 'type']);
    $page->setRelation('translation', $page->translations()->where('language_id', $language->getKey())->first());
    $page->translation->forceFill(['meta' => ['title' => ':site']]);
    $siteTranslation = new Translation;
    $siteTranslation->forceFill(['title' => 'Capell Ruby']);
    $site->refresh();
    $site->setRelation('translation', $siteTranslation);

    $social = BuildSocialMetaAction::run($page, $site, $language);

    expect($social->title)->toBe('Capell Ruby');
});

it('maps schema types to open graph types and labels each case', function (): void {
    expect(OpenGraphTypeEnum::fromSchemaType(null))->toBe(OpenGraphTypeEnum::Website)
        ->and(OpenGraphTypeEnum::fromSchemaType('Product'))->toBe(OpenGraphTypeEnum::Product)
        ->and(OpenGraphTypeEnum::fromSchemaType('Person'))->toBe(OpenGraphTypeEnum::Profile)
        ->and(OpenGraphTypeEnum::fromSchemaType('Report'))->toBe(OpenGraphTypeEnum::Article)
        ->and(OpenGraphTypeEnum::fromSchemaType('Unknown'))->toBe(OpenGraphTypeEnum::Website)
        ->and(OpenGraphTypeEnum::Article->isArticle())->toBeTrue()
        ->and(OpenGraphTypeEnum::Website->isArticle())->toBeFalse()
        ->and(OpenGraphTypeEnum::Website->getLabel())->toBe(__('capell::generic.og_type_website'))
        ->and(OpenGraphTypeEnum::Article->getLabel())->toBe(__('capell::generic.og_type_article'))
        ->and(OpenGraphTypeEnum::Product->getLabel())->toBe(__('capell::generic.og_type_product'))
        ->and(OpenGraphTypeEnum::Profile->getLabel())->toBe(__('capell::generic.og_type_profile'));
});
