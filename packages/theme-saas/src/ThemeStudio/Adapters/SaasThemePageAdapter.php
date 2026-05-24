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
        $title = $this->titleFrom($translation, $page?->name ?? 'Untitled page');
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

        $proof = data_get($renderData, 'proof');

        if (is_array($proof) && data_get($proof, 'items') !== null) {
            $sections[] = ProofSectionData::from([
                'heading' => data_get($proof, 'heading', 'Proof points'),
                'summary' => data_get($proof, 'summary'),
                'items' => data_get($proof, 'items', []),
            ]);
        }

        $features = data_get($renderData, 'features');

        if (is_array($features) && $features !== []) {
            $sections[] = FeatureSectionData::from([
                'heading' => data_get($renderData, 'features_heading', 'Featured modules'),
                'summary' => data_get($renderData, 'features_summary', data_get($renderData, 'summary')),
                'features' => collect($features)
                    ->filter(fn (mixed $feature): bool => is_array($feature))
                    ->map(fn (array $feature): array => [
                        'title' => (string) data_get($feature, 'title', data_get($feature, 'name', 'Feature')),
                        'description' => (string) data_get($feature, 'description', data_get($feature, 'summary', '')),
                    ])
                    ->values()
                    ->all(),
            ]);
        }

        $items = data_get($renderData, 'items');

        if (is_array($items) && $items !== []) {
            $sections[] = ContentListingSectionData::from([
                'heading' => data_get($renderData, 'heading', 'Browse entries'),
                'summary' => data_get($renderData, 'summary'),
                'items' => $items,
            ]);
        }

        $cta = data_get($renderData, 'cta');

        if (is_array($cta)) {
            $sections[] = CtaSectionData::from($cta);
        }

        return $sections !== [] ? $sections : [$this->fallbackHero($title, $translation)];
    }

    /**
     * @return array<int, ThemeSection>
     */
    private function premiumLandingSections(string $title, ?Translation $translation): array
    {
        $summary = $this->summaryFrom($translation?->content)
            ?? 'A product-led landing page for teams that need CMS structure, growth pages, and governed publishing to move together.';

        return [
            new HeroSectionData(
                heading: $title,
                eyebrow: 'Engineered for momentum',
                summary: $summary,
                actions: [
                    ['label' => 'Start free', 'url' => '/contact', 'style' => 'primary'],
                    ['label' => 'View demo', 'url' => '/resources', 'style' => 'secondary'],
                ],
            ),
            new ProofSectionData(
                heading: 'Built for high-velocity publishing teams',
                summary: 'Compact proof modules keep the first screen commercially sharp without storing presentation markup in page content.',
                items: [
                    ['metric' => '2.5k+', 'name' => 'Teams onboarded'],
                    ['metric' => '42%', 'name' => 'Faster page launches'],
                    ['metric' => '99.9%', 'name' => 'Cache-safe output'],
                ],
            ),
            new FeatureSectionData(
                heading: 'Everything a premium landing page needs',
                summary: 'The page keeps editable content portable while the active premium theme owns the conversion presentation.',
                features: [
                    ['title' => 'Live content model', 'description' => 'Typed pages, layouts, and blocks keep campaign surfaces consistent.'],
                    ['title' => 'Growth dashboard feel', 'description' => 'Structured cards and metrics make the public page feel product-led.'],
                    ['title' => 'Theme-owned rendering', 'description' => 'Velocity carries the premium landing rhythm without moving it into Foundation.'],
                ],
            ),
            new ContentListingSectionData(
                heading: 'Route visitors into the right next step',
                summary: 'Use the landing page to connect services, resources, pricing, and contact without a separate template stack.',
                items: [
                    ['title' => 'Implementation', 'summary' => 'Plan a CMS rollout with clear technical ownership.', 'url' => '/implementation', 'type' => 'Delivery'],
                    ['title' => 'Resources', 'summary' => 'Read practical guides for Capell page and package architecture.', 'url' => '/resources', 'type' => 'Learning'],
                    ['title' => 'Pricing', 'summary' => 'Match the build scope to the right commercial path.', 'url' => '/pricing', 'type' => 'Commercial'],
                ],
            ),
            new CtaSectionData(
                heading: 'Launch the next landing page with the premium theme',
                summary: 'Foundation stays boring; Velocity carries the opinionated conversion layout.',
                actions: [
                    ['label' => 'Contact team', 'url' => '/contact', 'style' => 'primary'],
                    ['label' => 'Browse services', 'url' => '/services', 'style' => 'secondary'],
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

        return NavigationData::from($navigation);
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function footerFrom(array $renderData, NavigationData $navigation): FooterData
    {
        $footer = data_get($renderData, 'footer');

        if (is_array($footer)) {
            return FooterData::from($footer);
        }

        return $this->defaultFooter($navigation);
    }

    private function defaultNavigation(): NavigationData
    {
        $site = Frontend::site();

        return NavigationData::from([
            'brandName' => $site?->title ?? $site?->name ?? 'Capell',
            'items' => [
                ['label' => 'Product', 'url' => '#content'],
                ['label' => 'Growth', 'url' => '#gallery'],
                ['label' => 'Team', 'url' => '#footer'],
            ],
            'ctaLabel' => 'Get Started',
            'ctaUrl' => '/contact',
        ]);
    }

    private function defaultFooter(NavigationData $navigation): FooterData
    {
        return FooterData::from([
            'brandName' => $navigation->brandName,
            'summary' => 'A compact Capell site assembled from reusable content, media, and premium theme sections.',
            'columns' => [
                [
                    'heading' => 'Explore',
                    'links' => $navigation->items !== [] ? $navigation->items : [['label' => 'Home', 'url' => '/']],
                ],
                [
                    'heading' => 'Capell',
                    'links' => [
                        ['label' => 'Content model', 'url' => '#content'],
                        ['label' => 'Media library', 'url' => '#gallery'],
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
