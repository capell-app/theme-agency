<?php

declare(strict_types=1);

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
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
    $heroBlock = Widget::query()->where('key', 'hero')->firstOrFail();

    capell_expect(array_keys($homeLayout->containers))->toBe(['hero', 'main'])
        ->and($homeLayout->containers['hero']['widgets'])->toBe([
            ['widget_key' => 'hero'],
        ])
        ->and($homeLayout->widgets)->toBe(['hero', 'page-content'])
        ->and($heroBlock->getMeta('height'))->toBe('small')
        ->and($heroBlock->getMeta('color'))->toBe('light')
        ->and($heroBlock->getMeta('content_align'))->toBe('center')
        ->and($heroBlock->getMeta('content_width'))->toBe('balanced')
        ->and($heroBlock->getMeta('media_position'))->toBe('right');

    $homePage = Page::query()
        ->where('layout_id', $homeLayout->id)
        ->with('translation')
        ->firstOrFail();

    capell_expect($homePage->translation->getMeta('hero_title'))->toBe('Start with a clean foundation.')
        ->and($homePage->translation->getMeta('hero'))->toBe('<p>Shape this page around your content, navigation, and publishing workflow.</p>')
        ->and($homePage->translation->content)->toBe('<p>Add the most important details for this page here. Keep it concise, useful, and easy to scan.</p>');
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

    capell_expect(array_keys($homeLayout->containers))->toBe(['hero', 'main'])
        ->and($homeLayout->widgets)->toBe(['hero', 'page-content'])
        ->and(Widget::query()->where('key', 'hero')->count())->toBe(1);
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

    expect($result)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and($homeLayout->containers['hero']['widgets'])->toBe([['widget_key' => 'hero']])
        ->and($homeLayout->containers['main']['widgets'])->toBe([['widget_key' => 'page-content']])
        ->and($page->translation->getMeta('hero_title'))->toBe('Custom headline')
        ->and($page->translation->getMeta('hero'))->toBe('<p>Custom hero copy.</p>')
        ->and($page->translation->content)->toBe('<p>Custom body copy.</p>');
});
