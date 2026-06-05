<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Support\State\FrontendState;
use Capell\Hero\Data\HeroMediaData;
use Capell\Hero\View\Components\Widget\Hero;
use Capell\LayoutBuilder\Enums\WidgetComponentEnum;
use Capell\LayoutBuilder\Models\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

function renderHeroWidgetHtml(Widget $widget): string
{
    $component = new Hero(
        container: [],
        containerKey: 'main',
        widgetIndex: 0,
        loop: (object) ['first' => true, 'last' => true],
        widget: $widget,
    );

    $rendered = $component->render();

    if ($rendered instanceof View) {
        return $rendered->render();
    }

    return $rendered instanceof Closure ? '' : $rendered;
}

it('renders page translation hero content while ignoring nested page variables', function (): void {
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->defaultMeta()->create();
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Capell'])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Platform Architecture',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Build :title for :site without touching :page.</p>',
                'hero_title' => ':title',
                'slug' => 'platform-architecture',
            ],
        ])
        ->create();

    $page->load('translation');

    $site->load('translation');

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
            'color' => 'light',
            'content_width' => 'balanced',
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroWidgetHtml($widget);

    expect($html)
        ->toContain('Platform Architecture')
        ->toContain('Build Platform Architecture for Capell without touching :page.');
});

it('skips empty hero widgets before exposing public markup', function (): void {
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->defaultMeta()->create();
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Capell'])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Empty Hero',
            'content' => '<p>Body content.</p>',
            'meta' => ['slug' => 'empty-hero'],
        ])
        ->create();

    $page->load('translation');

    $site->load('translation');

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection);
    $widget->setRelation('translation', null);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $component = new Hero(
        container: [],
        containerKey: 'main',
        widgetIndex: 0,
        loop: (object) ['first' => true, 'last' => true],
        widget: $widget,
    );

    expect($component->render())->toBe('');
});

it('renders the inherited theme hero background without public admin metadata', function (): void {
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => '#eaf2ff',
                'accent_color' => '#245f8f',
                'accent_color_alt' => '#8db9dc',
                'overlay_style' => 'grid',
                'overlay_opacity' => '0.24',
            ],
        ],
    ]);
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Capell'])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Hero Background',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Hero copy.</p>',
                'slug' => 'hero-background',
            ],
        ])
        ->create();

    $page->load('translation');

    $site->load('translation');

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
            'color' => 'light',
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroWidgetHtml($widget);

    expect($html)
        ->toContain('hero-background--grid')
        ->toContain('--hero-background-color: #eaf2ff')
        ->toContain('hero-overlay-a-')
        ->not->toContain('capell-hero')
        ->not->toContain('theme_id')
        ->not->toContain('site_id')
        ->not->toContain('widget_id');

    expect($html)->toBe(renderHeroWidgetHtml($widget));
});

it('keeps public hero background identifiers deterministic', function (): void {
    $themePath = dirname(__DIR__, 2);
    $backgroundView = file_get_contents($themePath . '/resources/views/components/hero/background.blade.php');

    expect($backgroundView)
        ->not->toContain('uniqid(')
        ->toContain("hash('xxh128'");
});

it('allows a hero widget to turn the inherited background off', function (): void {
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_background' => [
                'mode' => 'custom',
                'background_color' => '#eaf2ff',
            ],
        ],
    ]);
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Capell'])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Disabled Hero Background',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Hero copy.</p>',
                'slug' => 'disabled-hero-background',
            ],
        ])
        ->create();

    $page->load('translation');

    $site->load('translation');

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
            'hero_background' => ['mode' => 'off'],
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    expect(renderHeroWidgetHtml($widget))->not->toContain('hero-background');
});

it('renders responsive hero media without exposing editor metadata', function (): void {
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_media' => [
                'mode' => 'custom',
                'autoplay' => true,
                'loop' => true,
                'muted' => true,
                'pause_when_out_of_view' => true,
                'preload' => 'metadata',
            ],
        ],
    ]);
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Capell'])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Responsive Hero Media',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Hero copy.</p>',
                'slug' => 'responsive-hero-media',
            ],
        ])
        ->create();

    $theme->setRelation('media', new EloquentCollection([
        Media::factory()
            ->model($theme)
            ->state([
                'collection_name' => HeroMediaData::CollectionDesktopVideo,
                'file_name' => 'hero-desktop.webm',
                'mime_type' => 'video/webm',
            ])
            ->create(),
        Media::factory()
            ->model($theme)
            ->state([
                'collection_name' => HeroMediaData::CollectionMobileImage,
                'file_name' => 'hero-mobile.jpg',
                'mime_type' => 'image/jpeg',
                'custom_properties' => ['width' => 640],
            ])
            ->create(),
    ]));

    $page->load('translation');
    $site->load('translation');
    $site->setRelation('theme', $theme);

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
            'color' => 'light',
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection);
    $widget->setRelation('media', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroWidgetHtml($widget);

    expect($html)
        ->toContain('data-hero-video')
        ->toContain('hero-desktop.webm')
        ->toContain('hero-mobile.jpg')
        ->toContain('hero-mobile.jpg 640w')
        ->toContain('sizes="100vw"')
        ->toContain('data-pause-out-of-view="true"')
        ->not->toContain('capell-hero')
        ->not->toContain('hero_media')
        ->not->toContain('theme_id')
        ->not->toContain('collection_name');
});

it('marks the hero media poster as high fetch priority without exposing editor metadata', function (): void {
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->create([
        'meta' => [
            'hero_media' => [
                'mode' => 'custom',
                'autoplay' => false,
                'loop' => false,
                'muted' => true,
                'pause_when_out_of_view' => true,
                'preload' => 'metadata',
            ],
        ],
    ]);
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Capell'])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Hero Media Poster',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Hero copy.</p>',
                'slug' => 'hero-media-poster',
            ],
        ])
        ->create();

    $theme->setRelation('media', new EloquentCollection([
        Media::factory()
            ->model($theme)
            ->state([
                'collection_name' => HeroMediaData::CollectionDesktopImage,
                'file_name' => 'hero-poster.jpg',
                'mime_type' => 'image/jpeg',
                'custom_properties' => ['width' => 1600],
            ])
            ->create(),
    ]));

    $page->load('translation');
    $site->load('translation');
    $site->setRelation('theme', $theme);

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
            'color' => 'light',
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection);
    $widget->setRelation('media', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroWidgetHtml($widget);

    expect($html)
        ->toContain('hero-poster.jpg')
        ->toContain('hero-poster.jpg 1600w')
        ->toContain('sizes="100vw"')
        ->toContain('fetchpriority="high"')
        ->not->toContain('capell-hero')
        ->not->toContain('hero_media')
        ->not->toContain('theme_id')
        ->not->toContain('collection_name');
});
