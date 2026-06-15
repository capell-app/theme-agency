<?php

declare(strict_types=1);

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Translation;
use Capell\Core\Support\Creator\LayoutCreator;
use Capell\Hero\Actions\InstallHeroLayoutDefaultsAction;
use Capell\LayoutBuilder\Models\Widget;

it('installs compact natural home hero defaults', function (): void {
    resolve(LayoutCreator::class)->setup();

    $homeLayout = Layout::query()
        ->where('key', LayoutEnum::Home->value)
        ->firstOrFail();

    $homeLayout->update(['containers' => []]);

    Page::factory()
        ->layout($homeLayout)
        ->withTranslations(data: [
            'content' => '<p>Welcome to Capell</p>',
            'meta' => ['hero' => '<p>Welcome to Capell</p>'],
        ])
        ->create(['name' => 'Home']);

    Widget::query()->where('key', 'hero')->delete();

    test()->artisan('capell:hero-setup')->assertSuccessful();

    $homeLayout = Layout::query()->where('key', LayoutEnum::Home->value)->firstOrFail();
    $heroWidget = Widget::query()->where('key', 'hero')->firstOrFail();
    $containers = $homeLayout->containers ?? [];

    capell_expect(array_keys($containers))->toBe(['hero', 'main'])
        ->and(data_get($containers, 'hero.widgets'))->toBe([
            ['widget_key' => 'hero'],
        ])
        ->and($homeLayout->widgets)->toBe(['hero', 'page-content'])
        ->and($heroWidget->getMeta('height'))->toBe('small')
        ->and($heroWidget->getMeta('color'))->toBe('light')
        ->and($heroWidget->getMeta('content_align'))->toBe('center')
        ->and($heroWidget->getMeta('content_width'))->toBe('balanced')
        ->and($heroWidget->getMeta('media_position'))->toBe('right');

    $homePage = Page::query()
        ->where('layout_id', $homeLayout->id)
        ->with('translation')
        ->firstOrFail();
    $translation = $homePage->translation;

    throw_unless($translation instanceof Translation, RuntimeException::class, 'Expected home page translation to be created.');

    capell_expect($translation->getMeta('hero_title'))->toBe('Start with a clean foundation.')
        ->and($translation->getMeta('hero'))->toBe('<p>Shape this page around your content, navigation, and publishing workflow.</p>')
        ->and($translation->content)->toBe('<p>Add the most important details for this page here. Keep it concise, useful, and easy to scan.</p>');
});

it('does not duplicate hero defaults on repeated setup', function (): void {
    resolve(LayoutCreator::class)->setup();

    Layout::query()
        ->where('key', LayoutEnum::Home->value)
        ->firstOrFail()
        ->update(['containers' => []]);

    Widget::query()->where('key', 'hero')->delete();

    test()->artisan('capell:hero-setup')->assertSuccessful();
    test()->artisan('capell:hero-setup')->assertSuccessful();

    $homeLayout = Layout::query()->where('key', LayoutEnum::Home->value)->firstOrFail();
    $containers = $homeLayout->containers ?? [];

    capell_expect(array_keys($containers))->toBe(['hero', 'main'])
        ->and($homeLayout->widgets)->toBe(['hero', 'page-content'])
        ->and(Widget::query()->where('key', 'hero')->count())->toBe(1);
});

it('repairs page content below an existing hero container', function (): void {
    resolve(LayoutCreator::class)->setup();

    $homeLayout = Layout::query()
        ->where('key', LayoutEnum::Home->value)
        ->firstOrFail();

    Widget::query()->where('key', 'hero')->delete();

    $homeLayout->update([
        'containers' => [
            'hero' => [
                'widgets' => [
                    ['widget_key' => 'hero'],
                ],
            ],
        ],
    ]);

    $result = InstallHeroLayoutDefaultsAction::run();

    $homeLayout->refresh();
    $containers = $homeLayout->containers ?? [];

    expect($result)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and(array_keys($containers))->toBe(['hero', 'main'])
        ->and($containers['main']['widgets'] ?? null)->toBe([
            ['widget_key' => 'page-content'],
        ])
        ->and($homeLayout->widgets)->toBe(['hero', 'page-content']);
});

it('installs hero defaults when home layout containers are null', function (): void {
    resolve(LayoutCreator::class)->setup();

    Layout::query()
        ->where('key', LayoutEnum::Home->value)
        ->firstOrFail()
        ->update(['containers' => null]);

    Widget::query()->where('key', 'hero')->delete();

    test()->artisan('capell:hero-setup')->assertSuccessful();

    $homeLayout = Layout::query()->where('key', LayoutEnum::Home->value)->firstOrFail();
    $containers = $homeLayout->containers ?? [];

    capell_expect(array_keys($containers))->toBe(['hero', 'main'])
        ->and($homeLayout->widgets)->toBe(['hero', 'page-content']);
});

it('force updates an existing hero container without replacing custom home copy', function (): void {
    resolve(LayoutCreator::class)->setup();

    $homeLayout = Layout::query()
        ->where('key', LayoutEnum::Home->value)
        ->firstOrFail();

    Widget::factory()->create(['key' => 'legacy-hero']);
    Widget::factory()->create(['key' => 'custom-body']);

    $homeLayout->update([
        'containers' => [
            'hero' => [
                'meta' => ['container' => 'narrow'],
                'widgets' => [['widget_key' => 'legacy-hero']],
            ],
            'main' => [
                'meta' => ['container' => 'content'],
                'widgets' => [['widget_key' => 'custom-body']],
            ],
        ],
    ]);

    $page = Page::factory()
        ->layout($homeLayout)
        ->withTranslations(data: [
            'content' => '<p>Custom body copy.</p>',
            'meta' => [
                'hero_title' => 'Custom headline',
                'hero' => '<p>Custom hero copy.</p>',
            ],
        ])
        ->create(['name' => 'Home']);

    app()->bind(LayoutCreator::class, fn (): LayoutCreator => new class extends LayoutCreator
    {
        public function setup(): void {}
    });

    $result = InstallHeroLayoutDefaultsAction::run(force: true);

    $homeLayout->refresh();
    $page->load('translation');
    $containers = $homeLayout->containers ?? [];
    $translation = $page->translation;

    throw_unless($translation instanceof Translation, RuntimeException::class, 'Expected custom home page translation to be loaded.');

    expect($result)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and(data_get($containers, 'hero.widgets'))->toBe([['widget_key' => 'hero']])
        ->and(data_get($containers, 'main.widgets'))->toBe([['widget_key' => 'page-content']])
        ->and($translation->getMeta('hero_title'))->toBe('Custom headline')
        ->and($translation->getMeta('hero'))->toBe('<p>Custom hero copy.</p>')
        ->and($translation->content)->toBe('<p>Custom body copy.</p>');
});
