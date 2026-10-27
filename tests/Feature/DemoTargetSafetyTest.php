<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Core\Models\Theme;
use Capell\ThemeAgency\Console\Commands\DemoCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Lang;

beforeEach(function (): void {
    Artisan::registerCommand(resolve(DemoCommand::class));
});

it('refuses an unscoped demo before changing existing site data even with force', function (bool $force): void {
    $language = Language::factory()->english()->create(['default' => true]);
    $site = Site::factory()->withTranslations($language)->create([
        'name' => 'Existing publication', 'language_id' => $language->id, 'default' => true, 'status' => false,
    ]);
    SiteDomain::factory()->site($site)->language($language)->create(['domain' => 'publication.test']);
    $before = [$site->refresh()->getAttributes(), $site->translations()->get()->map->getAttributes()->all(), $site->siteDomains()->get()->map->getAttributes()->all()];
    $themes = Theme::query()->get()->map->getAttributes()->all();
    $languages = Language::query()->get()->map->getAttributes()->all();
    $pages = Page::query()->get()->map->getAttributes()->all();

    expect(Lang::has('capell-theme-foundation::demo.target_required'))->toBeTrue();
    expect(fn (): int => Artisan::call('capell:theme-agency-demo', $force ? ['--force' => true] : []))
        ->toThrow(InvalidArgumentException::class, __('capell-theme-foundation::demo.target_required'));

    expect([$site->refresh()->getAttributes(), $site->translations()->get()->map->getAttributes()->all(), $site->siteDomains()->get()->map->getAttributes()->all()])->toBe($before)
        ->and(Theme::query()->get()->map->getAttributes()->all())->toBe($themes)
        ->and(Language::query()->get()->map->getAttributes()->all())->toBe($languages)
        ->and(Page::query()->get()->map->getAttributes()->all())->toBe($pages);
})->with([false, true]);

it('seeds a named disposable target and preserves its configuration on an ordinary repeat', function (): void {
    expect(Artisan::call('capell:theme-agency-demo', ['--sites' => 'Disposable', '--url' => 'https://demo.test']))->toBe(0);
    $site = Site::query()->where('name', 'Disposable')->sole();
    $site->update(['status' => false, 'theme_id' => Theme::factory()->create()->id]);
    $site->translations()->firstOrFail()->update(['content' => '<p>Authored content</p>', 'meta' => ['description' => 'Authored metadata']]);
    $site->siteDomains()->firstOrFail()->update(['domain' => 'authored.test']);
    $before = [$site->refresh()->getAttributes(), $site->translations()->get()->map->getAttributes()->all(), $site->siteDomains()->get()->map->getAttributes()->all()];
    $pageIds = $site->pages()->orderBy('id')->pluck('id')->all();
    expect($pageIds)->not->toBeEmpty();

    expect(Artisan::call('capell:theme-agency-demo', ['--sites' => 'Disposable', '--url' => 'https://changed.test']))->toBe(0);
    expect([$site->refresh()->getAttributes(), $site->translations()->get()->map->getAttributes()->all(), $site->siteDomains()->get()->map->getAttributes()->all()])->toBe($before)
        ->and($site->pages()->orderBy('id')->pluck('id')->all())->toBe($pageIds);

    expect(Artisan::call('capell:theme-agency-demo', ['--sites' => 'Disposable', '--url' => 'https://reviewed.test', '--force' => true]))->toBe(0);
    expect((bool) $site->refresh()->status)->toBeTrue()
        ->and($site->theme?->key)->toBe('agency')
        ->and($site->siteDomains()->sole()->domain)->toBe('reviewed.test')
        ->and($site->pages()->orderBy('id')->pluck('id')->all())->toBe($pageIds);
    $this->assertNotSame('<p>Authored content</p>', $site->translations()->sole()->content);
});

it('allows initial unscoped setup on an empty installation', function (): void {
    expect(Site::query()->exists())->toBeFalse();
    expect(Artisan::call('capell:theme-agency-demo'))->toBe(0);

    $site = Site::query()->default()->sole();
    expect($site->theme?->key)->toBe('agency')
        ->and($site->pages()->count())->toBeGreaterThan(0);
});

it('seeds a new named target without changing the existing default site', function (): void {
    $language = Language::factory()->english()->create(['default' => true]);
    $site = Site::factory()->withTranslations($language)->create([
        'name' => 'Existing default', 'language_id' => $language->id, 'default' => true, 'status' => false,
    ]);
    SiteDomain::factory()->site($site)->language($language)->create(['domain' => 'original.test']);
    $before = [$site->refresh()->getAttributes(), $site->translations()->get()->map->getAttributes()->all(), $site->siteDomains()->get()->map->getAttributes()->all()];

    expect(Artisan::call('capell:theme-agency-demo', ['--sites' => 'Disposable target', '--url' => 'https://demo.test']))->toBe(0);
    expect([$site->refresh()->getAttributes(), $site->translations()->get()->map->getAttributes()->all(), $site->siteDomains()->get()->map->getAttributes()->all()])->toBe($before)
        ->and(Site::query()->where('name', 'Disposable target')->sole()->pages()->count())->toBeGreaterThan(0)
        ->and(Site::query()->default()->sole()->id)->toBe($site->id);
});

it('keeps language domains stable when repeating a deliberate multi-site overwrite', function (): void {
    $options = ['--sites' => 'First demo,Second demo', '--languages' => 'en,fr', '--url' => 'https://demo.test', '--force' => true];
    expect(Artisan::call('capell:theme-agency-demo', $options))->toBe(0);
    $columns = ['id', 'site_id', 'language_id', 'scheme', 'domain', 'path', 'default', 'status'];
    $domains = SiteDomain::query()->orderBy('id')->get($columns)->map->getAttributes()->all();
    expect($domains)->toHaveCount(4);

    expect(Artisan::call('capell:theme-agency-demo', $options))->toBe(0);
    expect(SiteDomain::query()->orderBy('id')->get($columns)->map->getAttributes()->all())->toBe($domains);
});
