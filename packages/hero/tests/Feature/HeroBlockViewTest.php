<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Support\State\FrontendState;
use Capell\Hero\Data\HeroMediaData;
use Capell\Hero\View\Components\Block\Hero;
use Capell\LayoutBuilder\Enums\BlockComponentEnum;
use Capell\LayoutBuilder\Models\Widget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

function renderHeroBlockHtml(Widget $block): string
{
    $component = new Hero(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: (object) ['first' => true, 'last' => true],
        block: $block,
    );

    $rendered = $component->render();

    return is_string($rendered) ? $rendered : $rendered->render();
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

    $block = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => BlockComponentEnum::Hero->value,
            'color' => 'light',
            'content_width' => 'balanced',
        ],
    ]);
    $block->setRelation('assets', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroBlockHtml($block);

    expect($html)
        ->toContain('Platform Architecture')
        ->toContain('Build Platform Architecture for Capell without touching :page.');
});

it('skips empty hero blocks before exposing public markup', function (): void {
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

    $block = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => BlockComponentEnum::Hero->value,
        ],
    ]);
    $block->setRelation('assets', new EloquentCollection);
    $block->setRelation('translation', null);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $component = new Hero(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: (object) ['first' => true, 'last' => true],
        block: $block,
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

    $block = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => BlockComponentEnum::Hero->value,
            'color' => 'light',
        ],
    ]);
    $block->setRelation('assets', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroBlockHtml($block);

    expect($html)
        ->toContain('hero-background--grid')
        ->toContain('--hero-background-color: #eaf2ff')
        ->toContain('hero-overlay-a-')
        ->not->toContain('capell-hero')
        ->not->toContain('theme_id')
        ->not->toContain('site_id')
        ->not->toContain('block_id');

    expect($html)->toBe(renderHeroBlockHtml($block));
});

it('keeps public hero background identifiers deterministic', function (): void {
    $themePath = dirname(__DIR__, 2);
    $backgroundView = file_get_contents($themePath . '/resources/views/components/hero/background.blade.php');

    expect($backgroundView)
        ->not->toContain('uniqid(')
        ->toContain("hash('xxh128'");
});

it('allows a hero block to turn the inherited background off', function (): void {
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

    $block = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => BlockComponentEnum::Hero->value,
            'hero_background' => ['mode' => 'off'],
        ],
    ]);
    $block->setRelation('assets', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    expect(renderHeroBlockHtml($block))->not->toContain('hero-background');
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
            ])
            ->create(),
    ]));

    $page->load('translation');
    $site->load('translation');
    $site->setRelation('theme', $theme);

    $block = Widget::factory()->create([
        'key' => 'hero',
        'meta' => [
            'component' => BlockComponentEnum::Hero->value,
            'color' => 'light',
        ],
    ]);
    $block->setRelation('assets', new EloquentCollection);
    $block->setRelation('media', new EloquentCollection);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withPage($page);

    $html = renderHeroBlockHtml($block);

    expect($html)
        ->toContain('data-hero-video')
        ->toContain('hero-desktop.webm')
        ->toContain('hero-mobile.jpg')
        ->toContain('data-pause-out-of-view="true"')
        ->not->toContain('capell-hero')
        ->not->toContain('hero_media')
        ->not->toContain('theme_id')
        ->not->toContain('collection_name');
});
