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
use Capell\LayoutBuilder\Models\WidgetAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

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

it('sanitizes author-provided page hero html before public rendering', function (): void {
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
            'title' => 'Safe Hero',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Trusted <strong>formatting</strong>.</p><script>alert("xss")</script><img src=x onerror="alert(1)"><a href="javascript:alert(2)">Unsafe link</a>',
                'hero_title' => 'Safe Hero',
                'slug' => 'safe-hero',
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
        ->toContain('<strong>formatting</strong>')
        ->toContain('Safe Hero')
        ->not->toContain('<script')
        ->not->toContain('onerror')
        ->not->toContain('javascript:');
});

it('renders hydrated page hero state without database queries', function (): void {
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
            'title' => 'Query Safe Hero',
            'content' => '<p>Body content.</p>',
            'meta' => [
                'hero' => '<p>Preloaded hero copy.</p>',
                'hero_title' => 'Query Safe Hero',
                'slug' => 'query-safe-hero',
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
    $widget->setRelation('media', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    DB::flushQueryLog();
    DB::enableQueryLog();

    try {
        $html = renderHeroWidgetHtml($widget);
        $queries = DB::getQueryLog();
    } finally {
        DB::disableQueryLog();
    }

    expect($html)
        ->toContain('Query Safe Hero')
        ->toContain('Preloaded hero copy')
        ->and($queries)->toBe([]);
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
        ->toContain('data-hero-video-toggle')
        ->toContain('Pause hero video')
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
                'hero_title' => 'Hero Media Poster',
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
        ->toContain('alt="Hero Media Poster"')
        ->not->toContain('capell-hero')
        ->not->toContain('hero_media')
        ->not->toContain('theme_id')
        ->not->toContain('collection_name');
});

it('renders multi-slide carousel data attributes from prepared slide state', function (): void {
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
            'title' => 'Carousel Host',
            'content' => '<p>Body content.</p>',
            'meta' => ['slug' => 'carousel-host'],
        ])
        ->create();

    $firstSlide = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'First feature',
            'content' => '<p>First feature copy.</p>',
            'meta' => ['slug' => 'first-feature'],
        ])
        ->create();

    $secondSlide = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Second feature',
            'content' => '<p>Second feature copy.</p>',
            'meta' => ['slug' => 'second-feature'],
        ])
        ->create();

    $page->load('translation');
    $site->load('translation');
    $firstSlide->load('translation');
    $secondSlide->load('translation');
    $firstSlide->setRelation('pageUrl', null);
    $secondSlide->setRelation('pageUrl', null);

    $firstAsset = new WidgetAsset;
    $firstAsset->setRelation('asset', $firstSlide);
    $firstAsset->setRelation('media', new EloquentCollection);

    $secondAsset = new WidgetAsset;
    $secondAsset->setRelation('asset', $secondSlide);
    $secondAsset->setRelation('media', new EloquentCollection);

    $widget = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => WidgetComponentEnum::Hero->value,
            'carousel_align' => 'start',
            'carousel_arrows' => true,
            'carousel_auto_play' => false,
            'carousel_auto_delay' => 5000,
            'carousel_disable_on_interaction' => false,
            'carousel_drag' => false,
            'carousel_effect' => 'fade',
            'carousel_loop' => false,
            'carousel_pagination' => true,
            'carousel_pause_on_hover' => false,
            'carousel_rewind' => true,
            'carousel_speed' => 450,
            'carousel_touch' => false,
            'carousel_wheel' => false,
            'color' => 'light',
        ],
    ]);
    $widget->setRelation('assets', new EloquentCollection([$firstAsset, $secondAsset]));

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroWidgetHtml($widget);

    expect($html)
        ->toContain('data-carousel="1"')
        ->toContain('data-carousel-align="start"')
        ->toContain('data-carousel-autoplay="0"')
        ->toContain('data-carousel-autoplay-delay="5000"')
        ->toContain('data-carousel-disable-on-interaction="0"')
        ->toContain('data-carousel-drag="0"')
        ->toContain('data-carousel-effect="fade"')
        ->toContain('data-carousel-loop="0"')
        ->toContain('data-carousel-navigation="1"')
        ->toContain('data-carousel-pagination="1"')
        ->toContain('data-carousel-pause-on-hover="0"')
        ->toContain('data-carousel-rewind="1"')
        ->toContain('data-carousel-speed="450"')
        ->toContain('data-carousel-touch="0"')
        ->toContain('data-carousel-wheel="0"')
        ->not->toContain('data-auto=')
        ->not->toContain('data-loop=')
        ->not->toContain('data-delay=')
        ->not->toContain('data-align=')
        ->not->toContain('data-drag=')
        ->not->toContain('data-wheel=')
        ->not->toContain('data-fade=')
        ->toContain('swiper-button-prev')
        ->toContain('swiper-pagination')
        ->toContain('First feature')
        ->toContain('Second feature')
        ->not->toContain('capell-hero')
        ->not->toContain('widget_id');
});

it('ships a safe default widget fallback view for base widget subclasses', function (): void {
    expect(view()->exists('capell-hero::components.widget.default'))->toBeTrue();
});
