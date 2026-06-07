<?php

declare(strict_types=1);

use Capell\Admin\Actions\CreatePageAction as AdminCreatePageAction;
use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Filament\Actions\Page\CreatePageAction;
use Capell\Admin\Filament\Configurators\Pages\DefaultPageConfigurator;
use Capell\Admin\Filament\Plugin\CapellAdminPlugin;
use Capell\Admin\Filament\Resources\Pages\Pages\CreatePage;
use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Enums\SeoCheckModeEnum;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    test()->registerAndMigrateSettings([
        '2026_05_10_190871_01_create_ai-orchestrator_settings',
        '2026_05_10_190871_03_create_seo_suite_settings',
        '2026_06_07_000001_add_seo_authoring_quality_gate_settings',
    ], dirname(__DIR__, 3) . '/database/settings');

    CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::configurator(
        class: DefaultPageConfigurator::class,
        group: 'Pages',
        name: 'default',
    ));
    $registerConfigurators = new ReflectionMethod(CapellAdminPlugin::class, 'registerConfigurators');
    $registerConfigurators->invoke(CapellAdminPlugin::make());
    Blueprint::factory()->page()->default()->create();

    test()->actingAsAdmin();
    seoSuiteFeatureQualityGateSettings();
});

function seoSuiteFeatureQualityGateSettings(array $overrides = []): SeoSuiteSettings
{
    $settings = resolve(SeoSuiteSettings::class);
    $settings->seo_authoring_strict_meta_enabled = true;
    $settings->seo_meta_description_min_length = 65;
    $settings->seo_meta_description_max_length = 200;
    $settings->seo_authoring_require_title_in_description = false;
    $settings->seo_quality_gate_meta_title_mode = SeoCheckModeEnum::Ignored->value;

    foreach ($overrides as $key => $value) {
        $settings->{$key} = $value;
    }

    app()->instance(SeoSuiteSettings::class, $settings);

    return $settings;
}

it('blocks regular page creation when meta description is too short', function (): void {
    $language = Language::factory()->createOne();
    $site = Site::factory()->recycle($language)->withTranslations()->create();
    $type = Blueprint::factory()->page()->create();
    $translationKey = (string) Str::uuid();

    Livewire::test(CreatePage::class)
        ->assertSuccessful()
        ->set('data.translations', [])
        ->fillForm([
            'name' => 'Strict SEO page',
            'site_id' => $site->id,
            'blueprint_id' => $type->id,
            'translations' => [
                $translationKey => [
                    'language_id' => $language->id,
                    'title' => 'Strict SEO page',
                    'meta' => [
                        'description' => 'Too short.',
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

it('blocks page edits when meta description is missing', function (): void {
    $language = Language::factory()->createOne();
    $page = Page::factory()
        ->recycle($language)
        ->withTranslations($language, ['meta' => ['description' => str_repeat('a', 90)]])
        ->create();

    $translationKey = sprintf('record-%d', $page->translation->id);

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertSuccessful()
        ->fillForm([
            sprintf('translations.%s.meta.description', $translationKey) => '',
        ])
        ->call('save')
        ->assertHasFormErrors([
            sprintf('translations.%s.meta.description', $translationKey),
        ]);
});

it('blocks modal page creation when strict metadata fails', function (): void {
    $page = Page::factory()->withTranslations()->create();
    $language = $page->site->language;
    $type = Blueprint::factory()->page()->create();
    $translationKey = (string) Str::uuid();

    Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertSuccessful()
        ->mountAction(TestAction::make(CreatePageAction::class))
        ->set('mountedActions.0.data.translations', [])
        ->fillForm([
            'blueprint_id' => $type->id,
            'name' => 'Modal SEO page',
            'parent_id' => null,
            'translations' => [
                $translationKey => [
                    'language_id' => $language->id,
                    'title' => 'Modal SEO page',
                    'meta' => [
                        'slug' => 'modal-seo-page',
                    ],
                ],
            ],
        ])
        ->callMountedAction()
        ->assertHasFormErrors();
});

it('blocks the admin create page action when strict metadata fails', function (): void {
    $language = Language::factory()->createOne();
    $site = Site::factory()->recycle($language)->create();

    expect(fn (): Page => AdminCreatePageAction::run([
        'site_id' => $site->id,
        'name' => 'Action SEO page',
        'blueprint_id' => Blueprint::factory()->page()->create()->id,
        'layout_id' => Layout::factory()->createOne()->id,
        'translations' => [
            [
                'language_id' => $language->id,
                'title' => 'Action SEO page',
                'content' => '',
                'meta' => [
                    'description' => 'Too short.',
                ],
            ],
        ],
    ]))->toThrow(ValidationException::class);
});

it('persists validated admin create page action metadata', function (): void {
    $language = Language::factory()->createOne();
    $site = Site::factory()->recycle($language)->create();

    $page = AdminCreatePageAction::run([
        'site_id' => $site->id,
        'name' => 'Action SEO page',
        'blueprint_id' => Blueprint::factory()->page()->create()->id,
        'layout_id' => Layout::factory()->createOne()->id,
        'translations' => [
            [
                'language_id' => $language->id,
                'title' => 'Action SEO page',
                'content' => '',
                'meta' => [
                    'description' => 'This is a useful meta description for the action-created page in strict SEO mode.',
                ],
            ],
        ],
    ]);

    $translation = $page->translations()->first();

    expect($translation)->toBeInstanceOf(Translation::class)
        ->and($translation->meta['description'] ?? null)
        ->toBe('This is a useful meta description for the action-created page in strict SEO mode.');
});
