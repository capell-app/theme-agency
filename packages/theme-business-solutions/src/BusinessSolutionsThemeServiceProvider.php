<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BusinessSolutions;

use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\BusinessSolutions\Rendering\BusinessSolutionSectionRenderer;
use Capell\ThemeStudio\BusinessSolutions\Rendering\BusinessSolutionThemeRenderer;
use Illuminate\Support\ServiceProvider;
use Override;

class BusinessSolutionsThemeServiceProvider extends ServiceProvider
{
    public const string PACKAGE_NAME = 'capell-app/theme-business-solutions';

    /** @var array<int, string> */
    private const array SectionKeys = [
        'navigation',
        'hero',
        'features',
        'proof',
        'content-listing',
        'cta',
        'footer',
    ];

    /**
     * @return array<int, ThemeDefinitionData>
     */
    public static function definitions(): array
    {
        return array_map(
            self::definitionForProfile(...),
            self::profiles(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function profiles(): array
    {
        return [
            [
                'key' => 'business-legal',
                'name' => 'Juris',
                'industry' => 'Legal & Compliance',
                'layout' => 'form-hero',
                'navigation' => 'sidebar',
                'modern' => false,
                'description' => 'Trust-heavy legal and compliance layout with a consultation panel, practice-area navigation, and proof-first hierarchy.',
                'presetName' => 'Juris & Co',
                'presetDescription' => 'Navy and gold legal theme from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-legal.jpg',
                'tags' => ['Legal', 'Compliance', 'Professional'],
                'bestFit' => ['Law firms', 'Compliance advisors', 'Risk consultants'],
                'values' => [
                    'primaryColor' => '#102a43',
                    'accentColor' => '#b7791f',
                    'neutralColor' => '#111827',
                    'surfaceColor' => '#f8fafc',
                    'foregroundColor' => '#111827',
                    'headingFont' => 'playfair',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'bordered',
                    'navigationStyle' => 'prominent',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'natural',
                    'radius' => 'sm',
                    'headingScale' => 'balanced',
                    'cardDensity' => 'comfortable',
                ],
            ],
            [
                'key' => 'business-healthcare',
                'name' => 'Careline',
                'industry' => 'Healthcare Clinic',
                'layout' => 'appointment-hero',
                'navigation' => 'utility',
                'modern' => true,
                'description' => 'Patient-centric clinic theme with an appointment-first hero, emergency utility bar, and certification proof strip.',
                'presetName' => 'Careline Clinic',
                'presetDescription' => 'Emerald healthcare direction from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-healthcare.jpg',
                'tags' => ['Healthcare', 'Appointments', 'Accessible'],
                'bestFit' => ['Clinics', 'Dental practices', 'Wellness providers'],
                'values' => [
                    'primaryColor' => '#047857',
                    'accentColor' => '#0ea5e9',
                    'neutralColor' => '#134e4a',
                    'surfaceColor' => '#f0fdfa',
                    'foregroundColor' => '#10201d',
                    'headingFont' => 'manrope',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'elevated',
                    'navigationStyle' => 'prominent',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'natural',
                    'radius' => 'lg',
                    'headingScale' => 'balanced',
                    'cardDensity' => 'comfortable',
                ],
            ],
            [
                'key' => 'business-financial',
                'name' => 'Ledger',
                'industry' => 'Financial Advisory',
                'layout' => 'ticker-hero',
                'navigation' => 'centered',
                'modern' => false,
                'description' => 'Financial advisory theme with centered positioning, market-pulse cues, AUM metrics, and service-grid proof.',
                'presetName' => 'Ledger Advisory',
                'presetDescription' => 'Charcoal and silver finance theme from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-financial.jpg',
                'tags' => ['Finance', 'Advisory', 'Metrics'],
                'bestFit' => ['Financial advisors', 'Accountants', 'Investment consultants'],
                'values' => [
                    'primaryColor' => '#1f2937',
                    'accentColor' => '#64748b',
                    'neutralColor' => '#0f172a',
                    'surfaceColor' => '#f8fafc',
                    'foregroundColor' => '#0f172a',
                    'headingFont' => 'playfair',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'bordered',
                    'navigationStyle' => 'standard',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'natural',
                    'radius' => 'none',
                    'headingScale' => 'compact',
                    'cardDensity' => 'compact',
                ],
            ],
            [
                'key' => 'business-real-estate',
                'name' => 'Habitat',
                'industry' => 'Real Estate Brokerage',
                'layout' => 'search-hero',
                'navigation' => 'transparent',
                'modern' => true,
                'description' => 'Modern brokerage theme with image-led search, featured listing strips, and an agent profile module.',
                'presetName' => 'Habitat Realty',
                'presetDescription' => 'Modern real-estate search layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-real-estate.jpg',
                'tags' => ['Real estate', 'Search', 'Listings'],
                'bestFit' => ['Brokerages', 'Property managers', 'Developers'],
                'values' => [
                    'primaryColor' => '#0f766e',
                    'accentColor' => '#f97316',
                    'neutralColor' => '#164e63',
                    'surfaceColor' => '#f7fee7',
                    'foregroundColor' => '#172554',
                    'headingFont' => 'manrope',
                    'bodyFont' => 'inter',
                    'spacing' => 'spacious',
                    'cardStyle' => 'layered',
                    'navigationStyle' => 'minimal',
                    'layoutPresentation' => 'immersive',
                    'motionIntensity' => 'expressive',
                    'mediaTreatment' => 'framed',
                    'radius' => 'xl',
                    'headingScale' => 'expressive',
                    'cardDensity' => 'comfortable',
                ],
            ],
            [
                'key' => 'business-education',
                'name' => 'Pathway',
                'industry' => 'Education & Training',
                'layout' => 'program-finder',
                'navigation' => 'tiered',
                'modern' => true,
                'description' => 'Training and education theme with program finder navigation, success counters, and modular course discovery.',
                'presetName' => 'Pathway Learning',
                'presetDescription' => 'Bright education layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-education.jpg',
                'tags' => ['Education', 'Programs', 'Training'],
                'bestFit' => ['Training providers', 'Schools', 'Course businesses'],
                'values' => [
                    'primaryColor' => '#2563eb',
                    'accentColor' => '#f59e0b',
                    'neutralColor' => '#1e3a8a',
                    'surfaceColor' => '#eff6ff',
                    'foregroundColor' => '#172554',
                    'headingFont' => 'sora',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'elevated',
                    'navigationStyle' => 'prominent',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'expressive',
                    'mediaTreatment' => 'framed',
                    'radius' => 'lg',
                    'headingScale' => 'balanced',
                    'cardDensity' => 'comfortable',
                ],
            ],
            [
                'key' => 'business-hospitality',
                'name' => 'Reserve',
                'industry' => 'Events & Hospitality',
                'layout' => 'booking-hero',
                'navigation' => 'transparent',
                'modern' => true,
                'description' => 'Elegant hospitality and events theme with a full-bleed hero, booking bar, and guest-review rhythm.',
                'presetName' => 'Reserve House',
                'presetDescription' => 'Modern hospitality layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-hospitality.jpg',
                'tags' => ['Hospitality', 'Booking', 'Events'],
                'bestFit' => ['Hotels', 'Restaurants', 'Venues'],
                'values' => [
                    'primaryColor' => '#7c2d12',
                    'accentColor' => '#d97706',
                    'neutralColor' => '#2c1810',
                    'surfaceColor' => '#fff7ed',
                    'foregroundColor' => '#2c1810',
                    'headingFont' => 'playfair',
                    'bodyFont' => 'inter',
                    'spacing' => 'spacious',
                    'cardStyle' => 'layered',
                    'navigationStyle' => 'minimal',
                    'layoutPresentation' => 'immersive',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'framed',
                    'radius' => 'xl',
                    'headingScale' => 'expressive',
                    'cardDensity' => 'spacious',
                ],
            ],
            [
                'key' => 'business-consultancy',
                'name' => 'Vector',
                'industry' => 'B2B Services Consultancy',
                'layout' => 'split-modern',
                'navigation' => 'minimal',
                'modern' => true,
                'description' => 'Modern B2B services theme with split hero, client proof, case-study strips, and sharp conversion hierarchy.',
                'presetName' => 'Vector Services',
                'presetDescription' => 'Modern consultancy direction from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-consultancy.jpg',
                'tags' => ['Consultancy', 'B2B', 'Modern'],
                'bestFit' => ['Consultants', 'Agencies', 'Managed services'],
                'values' => [
                    'primaryColor' => '#4f46e5',
                    'accentColor' => '#06b6d4',
                    'neutralColor' => '#111827',
                    'surfaceColor' => '#f8fafc',
                    'foregroundColor' => '#111827',
                    'headingFont' => 'sora',
                    'bodyFont' => 'inter',
                    'spacing' => 'spacious',
                    'cardStyle' => 'layered',
                    'navigationStyle' => 'minimal',
                    'layoutPresentation' => 'immersive',
                    'motionIntensity' => 'expressive',
                    'mediaTreatment' => 'framed',
                    'radius' => 'lg',
                    'headingScale' => 'expressive',
                    'cardDensity' => 'comfortable',
                    'overlayTreatment' => 'strong',
                ],
            ],
            [
                'key' => 'business-nonprofit',
                'name' => 'Impact',
                'industry' => 'Nonprofit Impact',
                'layout' => 'story-impact',
                'navigation' => 'donation',
                'modern' => false,
                'description' => 'Warm nonprofit theme with donation-first CTAs, impact tracker sections, and image-led storytelling.',
                'presetName' => 'Impact Fund',
                'presetDescription' => 'Impact and donation layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-nonprofit.jpg',
                'tags' => ['Nonprofit', 'Impact', 'Fundraising'],
                'bestFit' => ['Charities', 'Foundations', 'Community groups'],
                'values' => [
                    'primaryColor' => '#b45309',
                    'accentColor' => '#15803d',
                    'neutralColor' => '#292524',
                    'surfaceColor' => '#fffbeb',
                    'foregroundColor' => '#292524',
                    'headingFont' => 'playfair',
                    'bodyFont' => 'inter',
                    'spacing' => 'spacious',
                    'cardStyle' => 'subtle',
                    'navigationStyle' => 'prominent',
                    'layoutPresentation' => 'editorial',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'natural',
                    'radius' => 'lg',
                    'headingScale' => 'expressive',
                    'cardDensity' => 'spacious',
                ],
            ],
            [
                'key' => 'business-manufacturing',
                'name' => 'Forge',
                'industry' => 'Manufacturing Supplier',
                'layout' => 'spec-hero',
                'navigation' => 'industrial',
                'modern' => false,
                'description' => 'Industrial supplier theme with technical specification hierarchy, quote-first actions, and compliance proof.',
                'presetName' => 'Forge Supply',
                'presetDescription' => 'Industrial manufacturing layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-manufacturing.jpg',
                'tags' => ['Manufacturing', 'Industrial', 'B2B'],
                'bestFit' => ['Manufacturers', 'Suppliers', 'Industrial services'],
                'values' => [
                    'primaryColor' => '#1e3a8a',
                    'accentColor' => '#f97316',
                    'neutralColor' => '#1f2937',
                    'surfaceColor' => '#f3f4f6',
                    'foregroundColor' => '#111827',
                    'headingFont' => 'manrope',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'bordered',
                    'navigationStyle' => 'standard',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'natural',
                    'radius' => 'none',
                    'headingScale' => 'compact',
                    'cardDensity' => 'compact',
                ],
            ],
            [
                'key' => 'business-government',
                'name' => 'Civic',
                'industry' => 'Local Government/Civic Services',
                'layout' => 'service-directory',
                'navigation' => 'utility',
                'modern' => false,
                'description' => 'Accessible civic theme with service directory search, news ticker, and official-service structure.',
                'presetName' => 'Civic Services',
                'presetDescription' => 'Accessible government layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-government.jpg',
                'tags' => ['Government', 'Civic', 'Accessible'],
                'bestFit' => ['Councils', 'Public services', 'Civic portals'],
                'values' => [
                    'primaryColor' => '#075985',
                    'accentColor' => '#ca8a04',
                    'neutralColor' => '#0f172a',
                    'surfaceColor' => '#f8fafc',
                    'foregroundColor' => '#0f172a',
                    'headingFont' => 'inter',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'bordered',
                    'navigationStyle' => 'prominent',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'natural',
                    'radius' => 'none',
                    'headingScale' => 'compact',
                    'cardDensity' => 'comfortable',
                ],
            ],
            [
                'key' => 'business-recruiting',
                'name' => 'Talent',
                'industry' => 'Recruiting/HR Agency',
                'layout' => 'job-search',
                'navigation' => 'toggle',
                'modern' => true,
                'description' => 'Modern recruiting theme with job-search hero, candidate/employer toggle, and company-card modules.',
                'presetName' => 'Talent Desk',
                'presetDescription' => 'Modern recruiting layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-recruiting.jpg',
                'tags' => ['Recruiting', 'HR', 'Modern'],
                'bestFit' => ['Recruiters', 'Staffing agencies', 'HR platforms'],
                'values' => [
                    'primaryColor' => '#7c3aed',
                    'accentColor' => '#14b8a6',
                    'neutralColor' => '#111827',
                    'surfaceColor' => '#faf5ff',
                    'foregroundColor' => '#111827',
                    'headingFont' => 'sora',
                    'bodyFont' => 'inter',
                    'spacing' => 'spacious',
                    'cardStyle' => 'elevated',
                    'navigationStyle' => 'prominent',
                    'layoutPresentation' => 'immersive',
                    'motionIntensity' => 'expressive',
                    'mediaTreatment' => 'framed',
                    'radius' => 'xl',
                    'headingScale' => 'expressive',
                    'cardDensity' => 'comfortable',
                ],
            ],
            [
                'key' => 'business-commerce',
                'name' => 'Catalog',
                'industry' => 'Ecommerce/B2B Catalog',
                'layout' => 'catalog-grid',
                'navigation' => 'commerce',
                'modern' => true,
                'description' => 'Modern B2B commerce theme with utility navigation, category sidebar, hero deal, and product-grid rhythm.',
                'presetName' => 'Catalog Pro',
                'presetDescription' => 'Modern ecommerce and B2B catalog layout from the Stitch business-solution board.',
                'previewImage' => '/vendor/capell/themes/business-commerce.jpg',
                'tags' => ['Commerce', 'Catalog', 'Modern'],
                'bestFit' => ['B2B commerce', 'Wholesalers', 'Product catalogs'],
                'values' => [
                    'primaryColor' => '#dc2626',
                    'accentColor' => '#0f172a',
                    'neutralColor' => '#111827',
                    'surfaceColor' => '#ffffff',
                    'foregroundColor' => '#111827',
                    'headingFont' => 'manrope',
                    'bodyFont' => 'inter',
                    'spacing' => 'balanced',
                    'cardStyle' => 'bordered',
                    'navigationStyle' => 'standard',
                    'layoutPresentation' => 'structured',
                    'motionIntensity' => 'subtle',
                    'mediaTreatment' => 'framed',
                    'radius' => 'md',
                    'headingScale' => 'balanced',
                    'cardDensity' => 'compact',
                ],
            ],
        ];
    }

    #[Override]
    public function register(): void {}

    public function boot(ThemeRegistry $registry): void
    {
        if (! CapellCore::isPackageInstalled(self::PACKAGE_NAME)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-business-solutions');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-business-solutions');

        foreach (self::profiles() as $profile) {
            $sectionRenderers = $this->sectionRenderers($profile);
            $themeKey = (string) $profile['key'];

            $registry->register(
                definition: self::definitionForProfile($profile),
                themeRenderer: new BusinessSolutionThemeRenderer(
                    themeKey: $themeKey,
                    layoutView: 'capell-theme-business-solutions::page',
                    sectionRenderers: $sectionRenderers,
                    profile: $profile,
                ),
                sectionRenderers: array_values($sectionRenderers),
            );
        }
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private static function definitionForProfile(array $profile): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: (string) $profile['key'],
            name: (string) $profile['name'],
            description: (string) $profile['description'],
            package: self::PACKAGE_NAME,
            previewImage: (string) $profile['previewImage'],
            tags: $profile['tags'],
            bestFit: $profile['bestFit'],
            includedSections: self::SectionKeys,
            presets: [
                new ThemePresetData(
                    key: 'default',
                    name: (string) $profile['presetName'],
                    description: (string) $profile['presetDescription'],
                    previewImage: (string) $profile['previewImage'],
                    values: $profile['values'],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/business-solutions.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'layout' => $profile['layout'],
                'industry' => $profile['industry'],
                'modern' => $profile['modern'],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, BusinessSolutionSectionRenderer>
     */
    private function sectionRenderers(array $profile): array
    {
        $themeKey = (string) $profile['key'];

        return collect(self::SectionKeys)
            ->mapWithKeys(fn (string $sectionKey): array => [
                $sectionKey => new BusinessSolutionSectionRenderer(
                    themeKey: $themeKey,
                    sectionKey: $sectionKey,
                    view: 'capell-theme-business-solutions::sections.' . $sectionKey,
                    profile: $profile,
                ),
            ])
            ->all();
    }
}
