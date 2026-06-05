<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Saas\ThemeStudio\Adapters;

use Capell\Core\Actions\Content\ExtractTextContentAction;
use Capell\Core\Models\Translation;
use Capell\Core\ThemeStudio\Contracts\ThemePageAdapter;
use Capell\Core\ThemeStudio\Contracts\ThemeRuntimeSettings;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Frontend\Facades\Frontend;
use Illuminate\Database\Eloquent\Model;

final class SaasThemePageAdapter implements ThemePageAdapter
{
    public function currentPage(): ThemePageData
    {
        $page = Frontend::page();
        $translation = $page instanceof Model && $page->relationLoaded('translation') ? $page->translation : null;
        $brand = resolve(ThemeRuntimeSettings::class)->brandProfile();
        $title = $this->titleFrom($translation, $page->name ?? 'Untitled page');
        $renderData = $this->renderData($page, $translation);
        $navigation = $this->navigationFrom($renderData) ?? $this->defaultNavigation();

        if ($renderData !== []) {
            return new ThemePageData(
                title: $title,
                brand: $brand,
                sections: $this->sectionsFrom($renderData, $title, $translation),
                navigation: $navigation,
                footer: $this->footerFrom($renderData, $navigation),
            );
        }

        if ($this->shouldRenderPremiumLanding($page)) {
            return new ThemePageData(
                title: $title,
                brand: $brand,
                sections: $this->premiumLandingSections($title, $translation),
                navigation: $navigation,
                footer: $this->defaultFooter($navigation),
            );
        }

        return new ThemePageData(
            title: $title,
            brand: $brand,
            sections: [$this->fallbackHero($title, $translation)],
            navigation: $navigation,
            footer: $this->defaultFooter($navigation),
        );
    }

    /**
     * @return array<int, ThemeSection>
     */
    /**
     * @param  array<string, mixed>  $renderData
     * @return array<int, ThemeSection>
     */
    private function sectionsFrom(array $renderData, string $title, ?Translation $translation): array
    {
        $sections = [];
        $hero = data_get($renderData, 'hero');

        if (is_array($hero)) {
            $sections[] = HeroSectionData::from([
                'heading' => data_get($hero, 'heading', $title),
                'eyebrow' => data_get($hero, 'eyebrow'),
                'summary' => data_get($hero, 'summary', $this->summaryFrom($translation?->content)),
                'actions' => data_get($hero, 'actions', []),
                'mediaUrl' => data_get($hero, 'mediaUrl'),
                'mediaAlt' => data_get($hero, 'mediaAlt'),
            ]);
        }

        $features = $this->featuresFrom($renderData);

        if ($features instanceof FeatureSectionData) {
            $sections[] = $features;
        }

        $spotlight = $this->contentListingFrom(
            renderData: $renderData,
            key: 'spotlight',
            defaultHeading: __('capell-theme-saas::generic.theme_spotlight_heading'),
            variant: 'spotlight',
        );

        if ($spotlight instanceof ContentListingSectionData) {
            $sections[] = $spotlight;
        }

        $gallery = $this->contentListingFrom(
            renderData: $renderData,
            key: 'gallery',
            defaultHeading: __('capell-theme-saas::generic.gallery_heading'),
            variant: 'gallery',
        );

        if ($gallery instanceof ContentListingSectionData) {
            $sections[] = $gallery;
        }

        $items = $this->contentListingFrom(
            renderData: $renderData,
            key: 'items',
            defaultHeading: __('capell-theme-saas::generic.browse_entries_heading'),
        );

        if ($items instanceof ContentListingSectionData) {
            $sections[] = $items;
        }

        $proof = $this->proofFrom($renderData);

        if ($proof instanceof ProofSectionData) {
            $sections[] = $proof;
        }

        $cta = data_get($renderData, 'cta');

        if (is_array($cta)) {
            $sections[] = CtaSectionData::from($cta);
        }

        return $sections !== [] ? $sections : [$this->fallbackHero($title, $translation)];
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function featuresFrom(array $renderData): ?FeatureSectionData
    {
        $features = data_get($renderData, 'features');

        if (! is_array($features) || $features === []) {
            return null;
        }

        return FeatureSectionData::from([
            'heading' => data_get($renderData, 'features_heading', __('capell-theme-saas::generic.featured_modules_heading')),
            'summary' => data_get($renderData, 'features_summary', data_get($renderData, 'summary')),
            'features' => collect($features)
                ->filter(fn (mixed $feature): bool => is_array($feature))
                ->map(fn (array $feature): array => [
                    'title' => (string) data_get($feature, 'title', data_get($feature, 'name', __('capell-theme-saas::generic.feature_label'))),
                    'description' => (string) data_get($feature, 'description', data_get($feature, 'summary', '')),
                    'image' => data_get($feature, 'image', data_get($feature, 'imageUrl')),
                    'type' => data_get($feature, 'type'),
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function contentListingFrom(array $renderData, string $key, string $defaultHeading, ?string $variant = null): ?ContentListingSectionData
    {
        $source = data_get($renderData, $key);

        if (! is_array($source)) {
            return null;
        }

        $sourceIsList = array_is_list($source);
        $items = $sourceIsList ? $source : data_get($source, 'items', []);

        if (! is_array($items) || $items === []) {
            return null;
        }

        return ContentListingSectionData::from([
            'heading' => $sourceIsList
                ? data_get($renderData, $key . '_heading', data_get($renderData, 'heading', $defaultHeading))
                : data_get($source, 'heading', $defaultHeading),
            'summary' => $sourceIsList
                ? data_get($renderData, $key . '_summary', data_get($renderData, 'summary'))
                : data_get($source, 'summary'),
            'items' => $items,
            'variant' => $sourceIsList ? data_get($renderData, $key . '_variant', $variant) : data_get($source, 'variant', $variant),
        ]);
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function proofFrom(array $renderData): ?ProofSectionData
    {
        $proof = data_get($renderData, 'proof');

        if (! is_array($proof)) {
            return null;
        }

        $items = array_is_list($proof) ? $proof : data_get($proof, 'items', []);

        if (! is_array($items) || $items === []) {
            return null;
        }

        return ProofSectionData::from([
            'heading' => array_is_list($proof)
                ? __('capell-theme-saas::generic.proof_points_heading')
                : data_get($proof, 'heading', __('capell-theme-saas::generic.proof_points_heading')),
            'summary' => array_is_list($proof) ? null : data_get($proof, 'summary'),
            'items' => $items,
        ]);
    }

    /**
     * @return array<int, ThemeSection>
     */
    private function premiumLandingSections(string $title, ?Translation $translation): array
    {
        $summary = $this->summaryFrom($translation?->content)
            ?? __('capell-theme-saas::generic.premium_landing_summary');

        return [
            new HeroSectionData(
                heading: $title,
                eyebrow: __('capell-theme-saas::generic.premium_landing_eyebrow'),
                summary: $summary,
                actions: [
                    ['label' => __('capell-theme-saas::generic.start_free_label'), 'url' => '/contact', 'style' => 'primary'],
                    ['label' => __('capell-theme-saas::generic.view_demo_label'), 'url' => '/resources', 'style' => 'secondary'],
                ],
            ),
            new ProofSectionData(
                heading: __('capell-theme-saas::generic.premium_proof_heading'),
                summary: __('capell-theme-saas::generic.premium_proof_summary'),
                items: [
                    ['metric' => '2.5k+', 'name' => __('capell-theme-saas::generic.teams_onboarded_label')],
                    ['metric' => '42%', 'name' => __('capell-theme-saas::generic.faster_page_launches_label')],
                    ['metric' => '99.9%', 'name' => __('capell-theme-saas::generic.cache_safe_output_label')],
                ],
            ),
            new FeatureSectionData(
                heading: __('capell-theme-saas::generic.premium_features_heading'),
                summary: __('capell-theme-saas::generic.premium_features_summary'),
                features: [
                    ['title' => __('capell-theme-saas::generic.live_content_model_title'), 'description' => __('capell-theme-saas::generic.live_content_model_description')],
                    ['title' => __('capell-theme-saas::generic.growth_dashboard_title'), 'description' => __('capell-theme-saas::generic.growth_dashboard_description')],
                    ['title' => __('capell-theme-saas::generic.theme_owned_rendering_title'), 'description' => __('capell-theme-saas::generic.theme_owned_rendering_description')],
                ],
            ),
            new ContentListingSectionData(
                heading: __('capell-theme-saas::generic.premium_content_heading'),
                summary: __('capell-theme-saas::generic.premium_content_summary'),
                items: [
                    ['title' => __('capell-theme-saas::generic.implementation_title'), 'summary' => __('capell-theme-saas::generic.implementation_summary'), 'url' => '/implementation', 'type' => __('capell-theme-saas::generic.delivery_label')],
                    ['title' => __('capell-theme-saas::generic.resources_title'), 'summary' => __('capell-theme-saas::generic.resources_summary'), 'url' => '/resources', 'type' => __('capell-theme-saas::generic.learning_label')],
                    ['title' => __('capell-theme-saas::generic.pricing_title'), 'summary' => __('capell-theme-saas::generic.pricing_summary'), 'url' => '/pricing', 'type' => __('capell-theme-saas::generic.commercial_label')],
                ],
            ),
            new CtaSectionData(
                heading: __('capell-theme-saas::generic.premium_cta_heading'),
                summary: __('capell-theme-saas::generic.premium_cta_summary'),
                actions: [
                    ['label' => __('capell-theme-saas::generic.contact_team_label'), 'url' => '/contact', 'style' => 'primary'],
                    ['label' => __('capell-theme-saas::generic.browse_services_label'), 'url' => '/services', 'style' => 'secondary'],
                ],
            ),
        ];
    }

    private function fallbackHero(string $title, ?Translation $translation): HeroSectionData
    {
        return HeroSectionData::from([
            'heading' => $title,
            'summary' => $this->summaryFrom($translation?->content),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function renderData(mixed $page, ?Translation $translation): array
    {
        $pageRenderData = $page instanceof Model ? data_get($page->getAttribute('meta'), 'theme_demo.render_data') : null;

        if (is_array($pageRenderData)) {
            return $pageRenderData;
        }

        $translationRenderData = data_get($translation?->meta, 'theme_demo');

        return is_array($translationRenderData) ? $translationRenderData : [];
    }

    private function shouldRenderPremiumLanding(mixed $page): bool
    {
        if (! $page instanceof Model) {
            return false;
        }

        return data_get($page->getAttribute('meta'), 'hero_style') === 'immersive';
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function navigationFrom(array $renderData): ?NavigationData
    {
        $navigation = data_get($renderData, 'navigation');

        if (! is_array($navigation)) {
            return null;
        }

        if (array_is_list($navigation)) {
            return new NavigationData(
                brandName: $this->defaultNavigation()->brandName,
                items: $navigation,
            );
        }

        return NavigationData::from($navigation);
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function footerFrom(array $renderData, NavigationData $navigation): FooterData
    {
        $footer = data_get($renderData, 'footer');

        if (is_array($footer) && array_is_list($footer)) {
            return new FooterData(
                brandName: $navigation->brandName,
                columns: $footer,
            );
        }

        if (is_array($footer)) {
            return FooterData::from($footer);
        }

        return $this->defaultFooter($navigation);
    }

    private function defaultNavigation(): NavigationData
    {
        $site = Frontend::site();

        return NavigationData::from([
            'brandName' => $site->title ?? $site->name ?? 'Capell',
            'items' => [
                ['label' => __('capell-theme-saas::generic.product_label'), 'url' => '#content'],
                ['label' => __('capell-theme-saas::generic.growth_label'), 'url' => '#gallery'],
                ['label' => __('capell-theme-saas::generic.team_label'), 'url' => '#footer'],
            ],
            'ctaLabel' => __('capell-theme-saas::generic.get_started_label'),
            'ctaUrl' => '/contact',
        ]);
    }

    private function defaultFooter(NavigationData $navigation): FooterData
    {
        return FooterData::from([
            'brandName' => $navigation->brandName,
            'summary' => __('capell-theme-saas::generic.default_footer_summary'),
            'columns' => [
                [
                    'heading' => __('capell-theme-saas::generic.explore_label'),
                    'links' => $navigation->items !== [] ? $navigation->items : [['label' => __('capell-theme-saas::generic.home_label'), 'url' => '/']],
                ],
                [
                    'heading' => 'Capell',
                    'links' => [
                        ['label' => __('capell-theme-saas::generic.content_model_label'), 'url' => '#content'],
                        ['label' => __('capell-theme-saas::generic.media_library_label'), 'url' => '#gallery'],
                    ],
                ],
            ],
        ]);
    }

    private function titleFrom(?Translation $translation, string $fallback): string
    {
        $title = $translation?->title;

        return is_string($title) && $title !== '' ? $title : $fallback;
    }

    private function summaryFrom(mixed $content): ?string
    {
        $summary = ExtractTextContentAction::run($content, 40);

        return $summary === '' ? null : $summary;
    }
}
