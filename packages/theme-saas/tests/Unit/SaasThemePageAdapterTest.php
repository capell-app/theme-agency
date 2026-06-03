<?php

declare(strict_types=1);

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\Core\ThemeStudio\Contracts\ThemeRuntimeSettings;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\CapellFrontendContext;
use Capell\ThemeStudio\Saas\Tests\Fixtures\SaasThemeAdapterContextReader;
use Capell\ThemeStudio\Saas\ThemeStudio\Adapters\SaasThemePageAdapter;

beforeEach(function (): void {
    app()->instance(ThemeRuntimeSettings::class, new class implements ThemeRuntimeSettings
    {
        public function activeTheme(): string
        {
            return 'saas';
        }

        public function activePreset(): string
        {
            return 'default';
        }

        public function brandProfile(): BrandProfileData
        {
            return new BrandProfileData(primaryColor: '#123456');
        }

        public function themeOverrides(): array
        {
            return [];
        }
    });
});

it('adapts explicit SaaS render data into theme page sections navigation and footer', function (): void {
    $translation = new Translation([
        'title' => 'Growth Platform',
        'content' => '<p>Portable CMS content summary.</p>',
    ]);
    $page = saasThemeAdapterPage([
        'name' => 'Fallback name',
        'meta' => [
            'theme_demo' => [
                'render_data' => [
                    'hero' => [
                        'heading' => 'Launch faster',
                        'eyebrow' => 'SaaS CMS',
                        'actions' => [['label' => 'Start', 'url' => '/start']],
                    ],
                    'features_heading' => 'Modules',
                    'features' => [
                        ['name' => 'Publishing', 'summary' => 'Governed pages'],
                        ['title' => 'Analytics', 'description' => 'Clear signals'],
                    ],
                    'spotlight' => [
                        ['title' => 'Case study', 'summary' => 'A story'],
                    ],
                    'gallery' => [
                        'heading' => 'Screens',
                        'items' => [['title' => 'Dashboard']],
                    ],
                    'items' => [
                        'heading' => 'Resources',
                        'items' => [['title' => 'Guide']],
                    ],
                    'proof' => [
                        ['metric' => '42%', 'name' => 'Faster launches'],
                    ],
                    'cta' => [
                        'heading' => 'Book a walkthrough',
                        'actions' => [['label' => 'Contact', 'url' => '/contact']],
                    ],
                    'navigation' => [
                        ['label' => 'Product', 'url' => '#product'],
                    ],
                    'footer' => [
                        ['heading' => 'Explore', 'links' => [['label' => 'Product', 'url' => '#product']]],
                    ],
                ],
            ],
        ],
    ], $translation);

    saasThemeBindFrontendContext($page, saasThemeAdapterSite(['name' => 'Capell SaaS']));

    $themePage = (new SaasThemePageAdapter)->currentPage();

    expect($themePage->title)->toBe('Growth Platform')
        ->and($themePage->brand->primaryColor)->toBe('#123456')
        ->and($themePage->navigation?->brandName)->toBe('Capell SaaS')
        ->and($themePage->footer?->brandName)->toBe('Capell SaaS')
        ->and($themePage->sections)->toHaveCount(7)
        ->and($themePage->sections[0])->toBeInstanceOf(HeroSectionData::class)
        ->and($themePage->sections[1])->toBeInstanceOf(FeatureSectionData::class)
        ->and($themePage->sections[2])->toBeInstanceOf(ContentListingSectionData::class)
        ->and($themePage->sections[5])->toBeInstanceOf(ProofSectionData::class)
        ->and($themePage->sections[6])->toBeInstanceOf(CtaSectionData::class);
});

it('uses the premium landing fallback for immersive SaaS pages without render data', function (): void {
    $translation = new Translation([
        'title' => 'Premium Landing',
        'content' => '<p>A focused conversion page for software teams.</p>',
    ]);
    $page = saasThemeAdapterPage([
        'name' => 'Premium Landing',
        'meta' => ['hero_style' => 'immersive'],
    ], $translation);

    saasThemeBindFrontendContext($page, saasThemeAdapterSite(['name' => 'Capell']));

    $themePage = (new SaasThemePageAdapter)->currentPage();

    expect($themePage->sections)->toHaveCount(5)
        ->and($themePage->sections[0])->toBeInstanceOf(HeroSectionData::class)
        ->and($themePage->sections[1])->toBeInstanceOf(ProofSectionData::class)
        ->and($themePage->sections[2])->toBeInstanceOf(FeatureSectionData::class)
        ->and($themePage->sections[3])->toBeInstanceOf(ContentListingSectionData::class)
        ->and($themePage->sections[4])->toBeInstanceOf(CtaSectionData::class);
});

it('falls back to a single hero for ordinary pages without SaaS render data', function (): void {
    $translation = new Translation([
        'title' => '',
        'content' => '<p>Fallback summary content for a plain page.</p>',
    ]);
    $page = saasThemeAdapterPage(['name' => 'Plain Page'], $translation);

    saasThemeBindFrontendContext($page, null);

    $themePage = (new SaasThemePageAdapter)->currentPage();

    expect($themePage->title)->toBe('Plain Page')
        ->and($themePage->sections)->toHaveCount(1)
        ->and($themePage->sections[0])->toBeInstanceOf(HeroSectionData::class)
        ->and($themePage->navigation?->brandName)->toBe('Capell');
});

/**
 * @param  array<string, mixed>  $attributes
 */
function saasThemeAdapterPage(array $attributes, Translation $translation): Page
{
    $page = new Page;
    $page->forceFill($attributes);
    $page->setRelation('translation', $translation);

    return $page;
}

function saasThemeBindFrontendContext(?Pageable $page, ?Site $site): void
{
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(
        new SaasThemeAdapterContextReader($page, $site),
    ));
}

/**
 * @param  array<string, mixed>  $attributes
 */
function saasThemeAdapterSite(array $attributes): Site
{
    $site = new Site;
    $site->forceFill($attributes);

    return $site;
}
