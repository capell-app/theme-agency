<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education\Actions;

use Capell\Core\Actions\SetupPageUrlsAction;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Support\Creator\PageCreator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class RebalanceEducationThemeDemoPagesAction
{
    use AsObject;

    public function handle(): void
    {
        $pages = Page::query()
            ->with(['translations'])
            ->where('meta->theme_demo->theme_key', 'education')
            ->get()
            ->groupBy('site_id');

        foreach ($pages as $siteId => $sitePages) {
            $site = Site::query()->find($siteId);

            if (! $site instanceof Site) {
                continue;
            }

            $homepage = $this->pageForSurface($sitePages, 'homepage');

            if (! $homepage instanceof Page) {
                continue;
            }

            $homepageRenderData = $this->renderData($homepage);
            $this->updatePageRenderData($homepage, $this->shortHomepageRenderData($homepageRenderData));
            $this->ensureProofPage($site, $this->languages(), $homepageRenderData);
            $this->ensureListingPage($site, $this->languages(), $homepageRenderData);
            $this->ensureFeaturePage($site, $this->languages(), $homepageRenderData);
        }
    }

    /**
     * @param  Collection<int, Page>  $pages
     */
    private function pageForSurface(Collection $pages, string $surface): ?Page
    {
        return $pages->first(
            fn (Page $page): bool => data_get($page->meta, 'theme_demo.surface') === $surface,
        );
    }

    /**
     * @return EloquentCollection<int, Language>
     */
    private function languages(): EloquentCollection
    {
        return Language::query()->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function renderData(Page $page): array
    {
        $renderData = data_get($page->meta, 'theme_demo.render_data');

        return is_array($renderData) ? $renderData : [];
    }

    /**
     * @param  array<string, mixed>  $renderData
     * @return array<string, mixed>
     */
    private function shortHomepageRenderData(array $renderData): array
    {
        unset($renderData['gallery'], $renderData['items'], $renderData['proof'], $renderData['spotlight']);

        $features = data_get($renderData, 'features');

        if (is_array($features)) {
            $renderData['features'] = array_slice($features, 0, 2);
        }

        $renderData['summary'] = 'A short Education homepage overview with the full proof and pathway widgets moved to focused route-backed pages.';

        return $renderData;
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @param  array<string, mixed>  $sourceRenderData
     */
    private function ensureProofPage(Site $site, EloquentCollection $languages, array $sourceRenderData): void
    {
        $actions = $this->actions($sourceRenderData);
        $renderData = [
            'summary' => 'Learner proof, outcomes, and pathway widgets moved from the Education homepage into a focused page.',
            'navigation' => data_get($sourceRenderData, 'navigation', []),
            'hero' => [
                'heading' => 'Education learner proof review',
                'summary' => 'Cohort evidence, support signals, and learner pathway cards sit together without lengthening the homepage.',
                'actions' => $actions,
                'mediaUrl' => data_get($sourceRenderData, 'hero.mediaUrl'),
                'mediaAlt' => 'Education learner proof media',
            ],
            'proof' => data_get($sourceRenderData, 'proof', []),
            'cta' => [
                'heading' => 'Choose the right learner pathway',
                'summary' => 'The proof page keeps admissions confidence visible while the homepage stays shorter.',
                'actions' => $actions,
            ],
            'footer' => data_get($sourceRenderData, 'footer', []),
            'image_urls' => data_get($sourceRenderData, 'image_urls', []),
        ];

        $this->upsertPage(
            site: $site,
            languages: $languages,
            name: 'Education Demo Learner Proof',
            title: 'Education Learner Proof',
            slug: 'theme-education-learner-proof',
            surface: 'education-proof-review',
            renderData: $renderData,
            layout: LayoutEnum::Default,
            order: 10,
        );
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @param  array<string, mixed>  $sourceRenderData
     */
    private function ensureListingPage(Site $site, EloquentCollection $languages, array $sourceRenderData): void
    {
        $actions = $this->actions($sourceRenderData);
        $renderData = [
            'navigation' => data_get($sourceRenderData, 'navigation', []),
            'hero' => [
                'heading' => 'Education pathway listing review',
                'summary' => 'Repeated course pathway cards get their own route-backed page so the primary Education pages stay shorter.',
                'actions' => $actions,
                'mediaUrl' => data_get($sourceRenderData, 'hero.mediaUrl'),
                'mediaAlt' => 'Education pathway listing media',
            ],
            'heading' => data_get($sourceRenderData, 'items_heading', 'Course pathways'),
            'summary' => data_get($sourceRenderData, 'items_summary', 'Focused course pathway cards for Education demo pages.'),
            'items' => array_slice($this->arrayData(data_get($sourceRenderData, 'items', [])), 0, 2),
            'cta' => [
                'heading' => 'Compare course pathways',
                'summary' => 'A dedicated listing page preserves pathway card coverage without overloading the homepage.',
                'actions' => $actions,
            ],
            'footer' => data_get($sourceRenderData, 'footer', []),
            'image_urls' => data_get($sourceRenderData, 'image_urls', []),
        ];

        $this->upsertPage(
            site: $site,
            languages: $languages,
            name: 'Education Demo Pathway Listing',
            title: 'Education Pathway Listing',
            slug: 'theme-education-pathway-listing',
            surface: 'education-listing-review',
            renderData: $renderData,
            layout: LayoutEnum::Results,
            order: 11,
        );
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @param  array<string, mixed>  $sourceRenderData
     */
    private function ensureFeaturePage(Site $site, EloquentCollection $languages, array $sourceRenderData): void
    {
        $actions = $this->actions($sourceRenderData);
        $renderData = [
            'summary' => 'Education feature widgets moved from the homepage into a focused page.',
            'navigation' => data_get($sourceRenderData, 'navigation', []),
            'hero' => [
                'heading' => 'Education feature card review',
                'summary' => 'Course discovery, instructor credibility, and admissions support cards get their own route-backed page.',
                'actions' => $actions,
                'mediaUrl' => data_get($sourceRenderData, 'hero.mediaUrl'),
                'mediaAlt' => 'Education feature card media',
            ],
            'features_heading' => data_get($sourceRenderData, 'features_heading'),
            'features_summary' => data_get($sourceRenderData, 'features_summary'),
            'features' => data_get($sourceRenderData, 'features', []),
            'cta' => [
                'heading' => 'Review Education feature cards',
                'summary' => 'A dedicated feature page preserves the card work while the homepage stays shorter.',
                'actions' => $actions,
            ],
            'footer' => data_get($sourceRenderData, 'footer', []),
            'image_urls' => data_get($sourceRenderData, 'image_urls', []),
        ];

        $this->upsertPage(
            site: $site,
            languages: $languages,
            name: 'Education Demo Feature Cards',
            title: 'Education Feature Cards',
            slug: 'theme-education-feature-cards',
            surface: 'education-feature-review',
            renderData: $renderData,
            layout: LayoutEnum::Default,
            order: 12,
        );
    }

    /**
     * @param  array<string, mixed>  $sourceRenderData
     * @return array<int, array<string, string>>
     */
    private function actions(array $sourceRenderData): array
    {
        $actions = data_get($sourceRenderData, 'hero.actions', data_get($sourceRenderData, 'cta.actions', []));

        if (is_array($actions) && $actions !== []) {
            $validActions = [];

            foreach ($actions as $action) {
                if (
                    is_array($action)
                    && is_string($action['label'] ?? null)
                    && is_string($action['url'] ?? null)
                    && is_string($action['style'] ?? null)
                ) {
                    $validActions[] = [
                        'label' => $action['label'],
                        'url' => $action['url'],
                        'style' => $action['style'],
                    ];
                }
            }

            if ($validActions !== []) {
                return $validActions;
            }
        }

        return [
            ['label' => 'Browse courses', 'url' => '#courses', 'style' => 'primary'],
            ['label' => 'Talk to admissions', 'url' => '#admissions', 'style' => 'secondary'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function arrayData(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @param  array<string, mixed>  $renderData
     */
    private function upsertPage(
        Site $site,
        EloquentCollection $languages,
        string $name,
        string $title,
        string $slug,
        string $surface,
        array $renderData,
        LayoutEnum $layout,
        int $order,
    ): void {
        $summary = $this->stringRenderDataValue($renderData, 'summary');

        /** @var Page $page */
        $page = resolve(PageCreator::class)->createPage([
            'name' => $name,
            'type_key' => PageTypeEnum::Default,
            'layout_key' => $layout,
            'visible_from' => now()->subDay()->format('Y-m-d'),
            'meta' => [
                'theme_demo' => [
                    'theme_key' => 'education',
                    'surface' => $surface,
                    'render_data' => $renderData,
                ],
                'robots' => ['noindex' => true],
            ],
            'translations' => $languages
                ->mapWithKeys(fn (Language $language): array => [
                    (string) $language->code => [
                        'title' => $title,
                        'content' => '<h2>' . e($title) . '</h2><p>' . e($summary) . '</p>',
                        'summary' => $summary,
                        'meta' => [
                            'description' => $summary,
                            'hero' => data_get($renderData, 'hero.summary'),
                            'hero_title' => data_get($renderData, 'hero.heading', $title),
                            'label' => $title,
                            'link_text' => 'View preview',
                            'slug' => $slug,
                            'theme_demo' => $renderData,
                        ],
                    ],
                ])
                ->all(),
        ], $site, $languages);

        $page->forceFill(['order' => $order])->save();
        $page->loadMissing(['layout', 'pageUrl.siteDomain', 'site', 'translations', 'type']);

        SetupPageUrlsAction::run($page);
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function stringRenderDataValue(array $renderData, string $key): string
    {
        $value = $renderData[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<string, mixed>  $renderData
     */
    private function updatePageRenderData(Page $page, array $renderData): void
    {
        $page->loadMissing(['site', 'translations']);

        $meta = is_array($page->meta) ? $page->meta : [];
        data_set($meta, 'theme_demo.render_data', $renderData);
        $page->forceFill(['meta' => $meta])->save();

        foreach ($page->translations as $translation) {
            $translationMeta = is_array($translation->meta) ? $translation->meta : [];
            $translationMeta['theme_demo'] = $renderData;
            $translationMeta['description'] = $renderData['summary'] ?? $translationMeta['description'] ?? null;
            $translationMeta['hero'] = data_get($renderData, 'hero.summary', $translationMeta['hero'] ?? null);
            $translationMeta['hero_title'] = data_get($renderData, 'hero.heading', $translationMeta['hero_title'] ?? null);

            $translation->forceFill(['meta' => $translationMeta])->save();
        }
    }
}
