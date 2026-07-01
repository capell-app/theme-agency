<?php

declare(strict_types=1);

use Awcodes\Curator\CuratorServiceProvider;
use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Tests\Support\Concerns\CreatesAdminUser;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(CreatesAdminUser::class);

it('saves page details from the admin edit form with all package providers booted', function (): void {
    expect(CapellCore::getInstalledPackages()->keys()->all())
        ->toContain(
            'capell-app/admin',
            'capell-app/blog',
            'capell-app/campaign-studio',
            'capell-app/content-sections',
            'capell-app/frontend-authoring',
            'capell-app/navigation',
            'capell-app/publishing-studio',
            'capell-app/seo-suite',
        );

    $this->registerAndMigrateSettings(
        [
            '2026_05_10_190871_01_create_ai-orchestrator_settings',
        ],
        __DIR__ . '/../../../packages/ai-orchestrator/database/settings',
    );

    $this->registerAndMigrateSettings(
        [
            '2026_05_10_190871_03_create_seo_suite_settings',
        ],
        __DIR__ . '/../../../packages/seo-suite/database/settings',
    );

    $this->app->register(CuratorServiceProvider::class);

    test()->actingAsAdmin();

    $language = Language::factory()->default()->create([
        'name' => 'English',
        'code' => 'en',
        'locale' => 'en',
    ]);

    $theme = Theme::factory()->default()->create();
    $layout = Layout::factory()->default()->create();
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language)
        ->create(['name' => 'All Packages Site']);

    $page = Page::factory()
        ->site($site)
        ->layout($layout)
        ->withTranslations($language, [
            'content' => '<p>Original admin details content.</p>',
            'meta' => [
                'description' => 'Original admin details description.',
                'summary' => 'Original admin details summary.',
            ],
            'title' => 'Original Admin Details',
        ], slug: 'original-admin-details')
        ->create(['name' => 'Original Admin Details']);

    get(PageResource::getUrl('edit', ['record' => $page]))
        ->assertOk()
        ->assertSee('Original Admin Details');

    livewire(EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSchemaStateSet([
            'name' => 'Original Admin Details',
            'layout_id' => $layout->getKey(),
            'site_id' => $site->getKey(),
        ])
        ->fillForm([
            'name' => 'Updated Admin Details',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh())
        ->name->toBe('Updated Admin Details')
        ->layout_id->toBe($layout->getKey())
        ->site_id->toBe($site->getKey());
});
