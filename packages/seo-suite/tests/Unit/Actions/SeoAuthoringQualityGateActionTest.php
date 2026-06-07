<?php

declare(strict_types=1);

use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\BuildSeoAuthoringQualityGateReadinessReportAction;
use Capell\SeoSuite\Actions\BuildSeoAuthoringQualityGateResultsAction;
use Capell\SeoSuite\Enums\SeoCheckModeEnum;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Illuminate\Support\Str;

function seoSuiteQualityGateSettings(array $overrides = []): SeoSuiteSettings
{
    $settings = resolve(SeoSuiteSettings::class);
    $settings->seo_quality_gate_meta_title_mode = SeoCheckModeEnum::Ignored->value;

    foreach ($overrides as $key => $value) {
        $settings->{$key} = $value;
    }

    app()->instance(SeoSuiteSettings::class, $settings);

    return $settings;
}

it('returns no authoring quality gate results while strict mode is disabled', function (): void {
    seoSuiteQualityGateSettings([
        'seo_authoring_strict_meta_enabled' => false,
    ]);

    $results = BuildSeoAuthoringQualityGateResultsAction::run([
        'translations' => [
            'en' => [
                'title' => 'About Capell',
                'meta' => [],
            ],
        ],
    ]);

    expect($results)->toBe([]);
});

it('validates meta description presence and configured length using stripped text', function (): void {
    seoSuiteQualityGateSettings([
        'seo_authoring_strict_meta_enabled' => true,
        'seo_meta_description_min_length' => 65,
        'seo_meta_description_max_length' => 200,
    ]);

    $results = BuildSeoAuthoringQualityGateResultsAction::run([
        'translations' => [
            'missing' => [
                'title' => 'Missing',
                'meta' => [],
            ],
            'short' => [
                'title' => 'Short',
                'meta' => ['description' => '<p>Tiny.</p>'],
            ],
            'long' => [
                'title' => 'Long',
                'meta' => ['description' => str_repeat('a', 201)],
            ],
            'valid' => [
                'title' => 'Valid',
                'meta' => ['description' => '<p>' . str_repeat('a', 90) . '</p>'],
            ],
        ],
    ]);

    expect($results)
        ->toHaveCount(3)
        ->and(collect($results)->pluck('fieldPath')->all())
        ->toBe([
            'translations.missing.meta.description',
            'translations.short.meta.description',
            'translations.long.meta.description',
        ])
        ->and(collect($results)->every(fn (mixed $result): bool => $result->blocks()))
        ->toBeTrue();
});

it('can warn instead of block when the meta description gate is configured as warning', function (): void {
    seoSuiteQualityGateSettings([
        'seo_authoring_strict_meta_enabled' => true,
        'seo_quality_gate_meta_description_mode' => SeoCheckModeEnum::Warning->value,
    ]);

    $results = BuildSeoAuthoringQualityGateResultsAction::run([
        'translations' => [
            'en' => [
                'title' => 'About Capell',
                'meta' => [],
            ],
        ],
    ]);

    expect($results)->toHaveCount(1)
        ->and($results[0]->warns())->toBeTrue()
        ->and($results[0]->blocks())->toBeFalse();
});

it('can require the page title inside the meta description', function (): void {
    seoSuiteQualityGateSettings([
        'seo_authoring_strict_meta_enabled' => true,
        'seo_authoring_require_title_in_description' => true,
        'seo_meta_description_min_length' => 10,
        'seo_meta_description_max_length' => 200,
    ]);

    $results = BuildSeoAuthoringQualityGateResultsAction::run([
        'translations' => [
            'en' => [
                'title' => 'Capell SEO',
                'meta' => ['description' => 'A useful description that intentionally omits the exact title.'],
            ],
        ],
    ]);

    expect($results)->toHaveCount(1)
        ->and($results[0]->message)->toBe(__('capell-seo-suite::generic.seo_authoring_meta_description_missing_title'));
});

it('allows page types to override strict metadata thresholds', function (): void {
    seoSuiteQualityGateSettings([
        'seo_authoring_strict_meta_enabled' => true,
        'seo_meta_description_min_length' => 65,
        'seo_meta_description_max_length' => 200,
    ]);

    $blueprint = Blueprint::factory()
        ->page()
        ->create([
            'admin' => [
                'seo_quality_gates' => [
                    'meta_description' => [
                        'min_length' => 10,
                        'max_length' => 20,
                    ],
                ],
            ],
        ]);

    $results = BuildSeoAuthoringQualityGateResultsAction::run([
        'blueprint_id' => $blueprint->getKey(),
        'translations' => [
            'en' => [
                'title' => 'Capell SEO',
                'meta' => ['description' => Str::of('Compact page copy')->toString()],
            ],
        ],
    ]);

    expect($results)->toBe([]);
});

it('reports pages that would fail strict quality gates before rollout', function (): void {
    seoSuiteQualityGateSettings([
        'seo_authoring_strict_meta_enabled' => false,
        'seo_meta_description_min_length' => 65,
        'seo_meta_description_max_length' => 200,
    ]);

    $language = Language::factory()->create();
    $site = Site::factory()->language($language)->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, ['meta' => []])
        ->create();

    $rows = BuildSeoAuthoringQualityGateReadinessReportAction::run(siteId: (int) $site->getKey());

    expect($rows)->toHaveCount(1)
        ->and($rows[0]->pageId)->toBe((int) $page->getKey())
        ->and($rows[0]->messages)->toContain(__('capell-seo-suite::generic.seo_authoring_meta_description_required'));
});
