<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FilterGallery\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Filter Gallery theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (filter-hero /
 * taxonomy-navigation / editor-picks / latest-designs / blog-mission /
 * faq-archives / newsletter) alongside the standard hero/proof/cta — giving
 * every surface a full inspiration-library site rather than the shared
 * five-section skeleton.
 */
final class FilterGalleryDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Galleria Index';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return [
            $this->homepage($themeKey, $media),
            $this->directory($themeKey, $media),
            $this->detail($themeKey, $media),
            $this->contact($themeKey, $media),
            $this->empty($themeKey, $media),
            $this->notFound($themeKey, $media),
            $this->cta($themeKey, $media),
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Filterable Design Inspiration Library',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A filter-rich inspiration library',
                'Galleria Index is a browsable archive of thousands of designs, searchable by industry, style, color, platform, and technology.',
            ),
            renderData: [
                'summary' => 'Galleria Index is a filterable inspiration library. Search thousands of curated designs by industry, style, color, platform, and technology.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Filter-rich inspiration library',
                        heading: 'Search thousands of designs by industry, style, color, platform, and technology',
                        summary: 'A dense, scalable gallery for taxonomy filters, selected-filter chips, saved views, editor picks, and latest designs — built for discovery-led browsing at scale.',
                        primaryLabel: 'Browse the gallery',
                        primaryUrl: '#latest-designs',
                        secondaryLabel: 'Explore filters',
                        secondaryUrl: '#industries',
                        mediaUrl: $media['hero'][0] ?? null,
                        mediaAlt: 'Filter Gallery inspiration grid',
                    ),
                    $this->filterHeroSection(
                        heading: 'Put search and selected filters above the grid',
                        summary: 'Prominent keyword search, saved views, removable selected-filter chips, and quick routes into the highest-volume categories.',
                    ),
                    $this->taxonomyNavigationSection(),
                    $this->editorPicksSection(),
                    $this->latestDesignsSection(),
                    $this->blogMissionSection(),
                    $this->faqArchivesSection(),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Send fresh designs, saved views, and category roundups',
                        summary: 'A weekly digest of editor picks, latest designs, new taxonomies, and filter guides.',
                    ),
                    $this->ctaSection(
                        heading: 'Launch a filter-rich inspiration archive that can scale',
                        summary: 'Start browsing, save a view, or submit a design for the next editor-picks roundup.',
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Archive',
            title: 'Design archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A filter archive built to be scanned',
                'Dense taxonomy navigation and a consistent thumbnail grid keep large libraries legible.',
            ),
            renderData: [
                'summary' => 'Editor picks, latest designs, category pages, saved views, and taxonomy archives across the whole library.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Design archive',
                        heading: 'A filter archive built to be scanned',
                        summary: 'Dense taxonomy navigation and a consistent thumbnail grid keep thousands of entries legible — filter by industry, style, color, platform, or technology.',
                        primaryLabel: 'Open filters',
                        primaryUrl: '#industries',
                        secondaryLabel: 'View latest',
                        secondaryUrl: '#latest-designs',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0] ?? null,
                        mediaAlt: 'Filter Gallery archive grid',
                    ),
                    $this->taxonomyNavigationSection(),
                    $this->contentListingSection(
                        heading: 'Editor picks, latest designs, and taxonomy archives',
                        media: $media,
                    ),
                    $this->latestDesignsSection(),
                    $this->ctaSection(
                        heading: 'Save a view for the searches you repeat',
                        summary: 'Track competitor categories, campaign references, technology stacks, or visual styles in one saved view.',
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Design Detail',
            title: 'Fintech homepage — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Fintech homepage with crisp trust hierarchy',
                'A single design entry with its industry, style, color, platform, and technology metadata.',
            ),
            renderData: [
                'summary' => 'A single design entry with full taxonomy metadata, a consistent thumbnail, and related editor picks.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Design detail',
                        heading: 'Fintech homepage with crisp trust hierarchy',
                        summary: 'Industry: Fintech. Style: Minimal. Platform: React. A selected card for industry, style, color, platform, technology, content type, source, and quick save.',
                        primaryLabel: 'Visit design',
                        primaryUrl: '#visit',
                        secondaryLabel: 'Save to view',
                        secondaryUrl: '#saved-views',
                        mediaUrl: $media['detail'][0] ?? null,
                        mediaAlt: 'Fintech homepage design thumbnail',
                    ),
                    $this->editorPicksSection(),
                    $this->taxonomyNavigationSection(),
                    $this->contentListingSection(
                        heading: 'Related designs in the same taxonomy',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Find more designs like this one',
                        summary: 'Filter by the same industry, style, color, platform, or technology to keep browsing.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Submit',
            title: 'Submit a design — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit a design to the library',
                'Convert interest through one confident path — newsletter signup and a submission CTA.',
            ),
            renderData: [
                'summary' => 'Submit a design, subscribe to the weekly digest, or ask about taxonomy and submission rules.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit & subscribe',
                        heading: 'Convert interest through one confident path',
                        summary: 'Subscribe to the weekly digest or submit a design for review. We confirm submissions and publish accepted entries with full taxonomy metadata.',
                        primaryLabel: 'Submit a design',
                        primaryUrl: '#submit',
                        secondaryLabel: 'Read submission rules',
                        secondaryUrl: '#faq',
                        mediaUrl: $media['contact'][0] ?? null,
                        mediaAlt: 'Submit a design to the gallery',
                    ),
                    $this->faqArchivesSection(),
                    $this->newsletterSection(
                        heading: 'Get fresh designs in your inbox each week',
                        summary: 'Editor picks, latest designs, new categories, and filter guides — one focused email.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a design worth featuring?',
                        summary: 'Submit your work and we will review it against our thumbnail, taxonomy, and quality rules.',
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Results',
            title: 'No matching designs — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No designs match those filters yet',
                'A graceful empty state for a filtered archive with no matching entries.',
            ),
            renderData: [
                'summary' => 'No designs match that filter combination yet — clear a chip or browse a broader taxonomy.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Search results',
                        heading: 'No designs match those filters yet',
                        summary: 'Remove a selected-filter chip, widen the taxonomy, or clear all filters to see the full library again.',
                        primaryLabel: 'Clear all filters',
                        primaryUrl: '#latest-designs',
                        secondaryLabel: 'Browse industries',
                        secondaryUrl: '#industries',
                        mediaUrl: null,
                        mediaAlt: null,
                    ),
                    $this->contentListingSection(
                        heading: 'Nothing to show for this filter combination',
                        media: $media,
                        items: [],
                    ),
                    $this->taxonomyNavigationSection(),
                    $this->ctaSection(
                        heading: 'Try a broader taxonomy',
                        summary: 'Start from an industry or style and narrow down with chips from there.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function notFound(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'Page not found',
                'A not-found page that routes visitors back into the gallery and its filters.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the library.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That design has been filtered out',
                        summary: 'The link is broken or the entry was removed. Head back to the latest designs, or start a new filter from an industry or style.',
                        primaryLabel: 'Back to home',
                        primaryUrl: '/',
                        secondaryLabel: 'View latest designs',
                        secondaryUrl: '#latest-designs',
                        mediaUrl: null,
                        mediaAlt: null,
                    ),
                    $this->taxonomyNavigationSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific design?',
                        summary: 'Search by keyword or pick a taxonomy to find your way back.',
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Get Started',
            title: 'Launch your library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Launch a filter-rich inspiration archive',
                'A focused conversion page inviting teams to start their own scalable design library.',
            ),
            renderData: [
                'summary' => 'Launch a filter-rich inspiration archive that scales to thousands of curated designs.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Library ready',
                        heading: 'Launch a filter-rich inspiration archive that can scale',
                        summary: 'Dense filters, prominent search, saved views, editor picks, latest designs, category mega menus, FAQ, and pagination — ready for thousands of entries.',
                        primaryLabel: 'Start with Filter Gallery',
                        primaryUrl: '#submit',
                        secondaryLabel: 'Browse the gallery',
                        secondaryUrl: '#latest-designs',
                        mediaUrl: $media['cta'][0] ?? null,
                        mediaAlt: 'Filter Gallery library overview',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Stay close to the library as it grows',
                        summary: 'A weekly digest of new designs, taxonomies, and saved-view ideas.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Subscribe, save a view, or submit your first design to get started.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
        ?string $mediaUrl,
        ?string $mediaAlt,
    ): array {
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => $primaryLabel,
            'primary_url' => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
            'notes' => [
                'Filter hero, taxonomy navigation, editor picks, latest designs, and listing sections stay modular.',
                'Cards carry thumbnail, title, industry, style, color, platform, technology, saved state, and quick actions.',
                'Weekly editor picks, saved views, and new taxonomy collections keep the library useful.',
            ],
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filterHeroSection(string $heading, string $summary): array
    {
        return [
            'type' => 'filter-hero',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Selected filters stay visible', 'summary' => 'Active industry, style, color, platform, content type, and technology filters appear as compact removable chips.'],
                ['title' => 'Saved views for repeated research', 'summary' => 'Teams track competitor categories, campaign references, technology stacks, or visual styles in a saved view.'],
                ['title' => 'Search that feels useful at scale', 'summary' => 'Keyword search pairs with expandable filter groups, result counts, sort, pagination, and quick reset actions.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function taxonomyNavigationSection(): array
    {
        return [
            'type' => 'taxonomy-navigation',
            'heading' => 'Make dense categories scannable without feeling like admin',
            'items' => [
                ['title' => 'Industry and content type', 'summary' => 'SaaS, ecommerce, fintech, health, education, agency, portfolio, nonprofit, pricing, blog, docs, and landing pages.'],
                ['title' => 'Style and color', 'summary' => 'Minimal, bold, editorial, playful, brutalist, dark, light, monochrome, colorful, pastel, neon, and neutral systems.'],
                ['title' => 'Platform and technology', 'summary' => 'Webflow, Shopify, Framer, WordPress, Laravel, React, Vue, static sites, CMS pages, AI tools, and analytics stacks.'],
                ['title' => 'Alphabetized mega menus', 'summary' => 'Long category lists stay practical with alphabetized grouping, counts, popular links, and compact mobile drawers.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editorPicksSection(): array
    {
        return [
            'type' => 'editor-picks',
            'heading' => 'Consistent thumbnail cards with clear labels and quick actions',
            'summary' => 'Thumbnail-led cards with stable aspect ratios, labels, quick save, visit buttons, and source metadata — enough context to compare many entries quickly.',
            'items' => [
                ['title' => 'Fintech homepage with crisp trust hierarchy', 'summary' => 'A selected card for industry, style, color, platform, technology, content type, source, and quick save.', 'meta' => 'Fintech. Minimal. React. Saved view.'],
                ['title' => 'Ecommerce product page with strong merchandising', 'summary' => 'A card for color palette, platform, content type, commerce stack, thumbnail, and visit action.', 'meta' => 'Ecommerce. Shopify. Warm neutral.'],
                ['title' => 'Agency site with motion-led case study flow', 'summary' => 'A card for industry, style, motion, platform, technology tags, and editor note.', 'meta' => 'Agency. Motion. Webflow.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function latestDesignsSection(): array
    {
        return [
            'type' => 'latest-designs',
            'heading' => 'Keep fresh entries dense, comparable, and paginated',
            'summary' => 'Compact cards with result counts, pagination, sort order, selected chips, and quick actions for high-volume browsing.',
            'url' => '#latest-designs',
            'label' => 'View latest',
            'items' => [
                ['title' => 'High-density grid rows', 'summary' => 'Stable thumbnail ratios, source labels, industry and style tags, color swatches, platform labels, and save buttons.'],
                ['title' => 'Saved-view workflows', 'summary' => 'Return to filter combinations such as AI SaaS, dark fintech, Shopify product pages, or editorial portfolios.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blogMissionSection(): array
    {
        return [
            'type' => 'blog-mission',
            'heading' => 'Explain the curation without slowing down browsing',
            'items' => [
                ['meta' => 'Mission', 'title' => 'Why the library exists', 'summary' => 'Compact editorial modules cover why the archive exists, how picks are reviewed, and what good submissions include.'],
                ['meta' => 'Blog', 'title' => 'Trends, roundups, and filter guides', 'summary' => 'Trend posts, category roundups, filter guides, design teardowns, and workflow notes sit beside the archive.'],
                ['meta' => 'Menus', 'title' => 'Mega-menu support', 'summary' => 'Alphabetized category groups, popular taxonomies, saved views, and mobile drawers handle broad navigation.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function faqArchivesSection(): array
    {
        return [
            'type' => 'faq-archives',
            'heading' => 'Support thousands of entries with calm archive patterns',
            'summary' => 'Archive-ready modules for FAQ, selected filters, result counts, pagination, category pages, saved views, and submission rules.',
            'items' => [
                ['title' => 'FAQ for browsing and submissions', 'summary' => 'Explains taxonomy rules, submission quality, thumbnail requirements, saved views, moderation, and update cadence.'],
                ['title' => 'Archive pages by taxonomy', 'summary' => 'Dedicated archives for industry, style, color, platform, content type, technology, editor picks, latest, and saved views.'],
                ['title' => 'Pagination that stays useful', 'summary' => 'Result counts, page ranges, sort controls, selected chips, reset actions, and quick filters keep large archives approachable.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'heading' => 'Built to stay browsable at scale',
            'items' => [
                ['value' => '12k+', 'label' => 'Design entries stay browsable through search, filters, saved views, selected chips, and pagination.'],
                ['value' => '6 axes', 'label' => 'Industry, style, color, platform, content type, and technology taxonomies structure the archive.'],
                ['value' => 'Fast', 'label' => 'Consistent thumbnails, compact metadata, and quick actions keep the interface practical without feeling like admin.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @param  list<array<string, mixed>>|null  $items
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, array $media, ?array $items = null): array
    {
        if ($items === null) {
            $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

            $entries = [
                ['title' => 'SaaS dashboard with dense data layout', 'category' => 'SaaS. Dark. React.', 'summary' => 'A product dashboard entry tagged by industry, style, color, platform, and technology.'],
                ['title' => 'Editorial portfolio with generous type', 'category' => 'Portfolio. Editorial. Framer.', 'summary' => 'A portfolio entry showing thumbnail, labels, source, and quick save metadata.'],
                ['title' => 'Pastel landing page for a wellbeing app', 'category' => 'Health. Pastel. Webflow.', 'summary' => 'A landing-page entry with colour swatch, platform label, and content-type tag.'],
            ];

            $items = [];

            foreach ($entries as $index => $entry) {
                $image = $images[$index % max(count($images), 1)] ?? null;
                $items[] = [
                    ...$entry,
                    'url' => '#design-' . ($index + 1),
                    'image' => $image,
                    'imageUrl' => $image,
                ];
            }
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'url' => '#submit',
            'label' => 'Start with Filter Gallery',
            'actions' => [
                ['label' => 'Start with Filter Gallery', 'url' => '#submit', 'style' => 'primary'],
                ['label' => 'Browse the gallery', 'url' => '#latest-designs', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Industries', 'url' => '#industries'],
                ['label' => 'Styles', 'url' => '#styles'],
                ['label' => 'Colors', 'url' => '#colors'],
                ['label' => 'Editor picks', 'url' => '#editor-picks'],
                ['label' => 'Latest designs', 'url' => '#latest-designs'],
            ],
            'ctaLabel' => 'Submit a design',
            'ctaUrl' => '#submit',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Browse',
                'title' => 'Browse',
                'links' => [
                    ['label' => 'Industries', 'url' => '#industries'],
                    ['label' => 'Styles', 'url' => '#styles'],
                    ['label' => 'Colors', 'url' => '#colors'],
                    ['label' => 'Platforms', 'url' => '#platforms'],
                ],
            ],
            [
                'heading' => 'Library',
                'title' => 'Library',
                'links' => [
                    ['label' => 'Editor picks', 'url' => '#editor-picks'],
                    ['label' => 'Latest designs', 'url' => '#latest-designs'],
                    ['label' => 'Saved views', 'url' => '#saved-views'],
                    ['label' => 'Technology', 'url' => '#technology'],
                ],
            ],
            [
                'heading' => 'Contribute',
                'title' => 'Contribute',
                'links' => [
                    ['label' => 'Submit a design', 'url' => '#submit'],
                    ['label' => 'Newsletter', 'url' => '#newsletter'],
                ],
            ],
        ];

        return [
            'brandName' => self::BRAND,
            'summary' => 'A filterable inspiration library for thousands of curated designs.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
