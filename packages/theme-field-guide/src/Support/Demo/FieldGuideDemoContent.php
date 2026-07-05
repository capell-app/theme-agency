<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FieldGuide\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Field Guide theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (filter-hero /
 * taxonomy-navigation / editor-picks / latest-designs / blog-mission /
 * faq-archives / newsletter) alongside the standard hero/proof/cta. Every
 * capture card carries a real photograph from the role-grouped theme media
 * plus type/style/colour/industry facet tags, so each surface reads as a
 * dense, image-first reference library rather than the shared skeleton.
 */
final class FieldGuideDemoContent implements ProvidesThemeDemoContent
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
            title: self::BRAND . ' — A Filterable Reference Library of Web Captures',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Every capture tagged four ways',
                'Galleria Index files screenshots of live sites under type, style, colour, and industry — stack the filters and the reference you half-remember surfaces in seconds.',
            ),
            renderData: [
                'summary' => 'Galleria Index is a filterable reference library of web capture screenshots, filed by type, style, colour, and industry.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Reference library',
                        heading: 'Every capture tagged four ways, so the search you repeat takes seconds',
                        summary: 'Galleria Index files screenshots of live sites under type, style, colour, and industry. Stack the filters, save the view, and get back to the reference you half-remember.',
                        primaryLabel: 'Browse the index',
                        primaryUrl: '#latest-designs',
                        secondaryLabel: 'Open the filters',
                        secondaryUrl: '#taxonomy-navigation',
                        mediaUrl: $media['hero'][0] ?? null,
                        mediaAlt: 'Most-saved capture this week: a dark fintech pricing page',
                    ),
                    $this->filterHeroSection(),
                    $this->taxonomyNavigationSection('#latest-designs'),
                    $this->editorPicksSection($media, $this->pagePath($themeKey, 'detail')),
                    $this->latestDesignsSection($media, $this->pagePath($themeKey, 'detail'), $this->pagePath($themeKey, 'directory')),
                    $this->blogMissionSection(),
                    $this->faqArchivesSection(),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'The week in captures, once',
                        summary: 'Every Friday: the new intake batch, the pinned picks, and one tag worth exploring. Nothing else.',
                    ),
                    $this->ctaSection(
                        heading: 'Seen a site the index should hold?',
                        summary: 'Submissions go through the same intake as everything else — captured, tagged four ways, and filed with its source.',
                        primaryLabel: 'Submit a site',
                        primaryUrl: $this->pagePath($themeKey, 'contact'),
                        secondaryLabel: 'Browse the index',
                        secondaryUrl: '#latest-designs',
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
            title: 'The archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full archive, filtered your way',
                'Twelve thousand captures behind four facets — the grid stays tight so you can compare a dozen references without scrolling.',
            ),
            renderData: [
                'summary' => 'The full Galleria Index archive — every capture behind the type, style, colour, and industry filter board.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'Twelve thousand captures behind four facets',
                        summary: 'Start from a facet or a keyword. Counts on every chip show where the archive runs deep before you commit to a filter stack.',
                        primaryLabel: 'Open the filter board',
                        primaryUrl: '#taxonomy-navigation',
                        secondaryLabel: 'Newest captures',
                        secondaryUrl: '#latest-designs',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0] ?? null,
                        mediaAlt: 'Archive view filtered to editorial ecommerce captures',
                    ),
                    $this->filterHeroSection(),
                    $this->taxonomyNavigationSection('#latest-designs'),
                    $this->contentListingSection(
                        heading: 'Captures matching this view',
                        summary: 'A saved view: dark palettes across fintech and SaaS, newest first.',
                        media: $media,
                        detailUrl: $this->pagePath($themeKey, 'detail'),
                    ),
                    $this->latestDesignsSection($media, $this->pagePath($themeKey, 'detail'), '#content-listing'),
                    $this->ctaSection(
                        heading: 'Save this filter stack as a view',
                        summary: 'Any combination of chips can be kept and revisited — saved views update as new captures match.',
                        primaryLabel: 'Submit a site',
                        primaryUrl: $this->pagePath($themeKey, 'contact'),
                        secondaryLabel: 'Newest captures',
                        secondaryUrl: '#latest-designs',
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
            name: self::BRAND . ' Capture Detail',
            title: 'Capture #12401: fintech pricing page — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Capture #12401 — a pricing page that earns its density',
                'One capture with its full facet record: type, style, colour, industry, source domain, and the curator\'s intake note.',
            ),
            renderData: [
                'summary' => 'Capture #12401 — a fintech pricing page filed under pricing, minimal, dark, and fintech, with the curator\'s intake note.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Capture #12401',
                        heading: 'A pricing page that earns its density',
                        summary: 'Filed under pricing, minimal, dark, and fintech. Intake note: three tiers, one anchor, and a comparison table that stays readable at every breakpoint.',
                        primaryLabel: 'More like this',
                        primaryUrl: '#content-listing',
                        secondaryLabel: 'Back to the archive',
                        secondaryUrl: $this->pagePath($themeKey, 'directory'),
                        mediaUrl: $media['detail'][0] ?? null,
                        mediaAlt: 'Capture #12401: dark fintech pricing page',
                    ),
                    $this->contentListingSection(
                        heading: 'Captures sharing these tags',
                        summary: 'Same filter stack — pricing, minimal, dark, fintech — ranked by saves.',
                        media: $media,
                        detailUrl: $this->pagePath($themeKey, 'detail'),
                    ),
                    $this->editorPicksSection($media, $this->pagePath($themeKey, 'detail')),
                    $this->taxonomyNavigationSection('#content-listing'),
                    $this->ctaSection(
                        heading: 'Keep this filter stack',
                        summary: 'Save the view and new captures matching pricing, minimal, dark, and fintech will file themselves into it.',
                        primaryLabel: 'Submit a site',
                        primaryUrl: $this->pagePath($themeKey, 'contact'),
                        secondaryLabel: 'Browse the archive',
                        secondaryUrl: $this->pagePath($themeKey, 'directory'),
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
            title: 'Submit a site — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit a site to the index',
                'Send a URL and a curator will capture it, file it under all four facets, and queue it for the next weekly intake batch.',
            ),
            renderData: [
                'summary' => 'Submit a URL for intake, subscribe to the Friday digest, or read the archive rules first.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Intake desk',
                        heading: 'Send a URL, we do the filing',
                        summary: 'A curator captures the page, checks all four facets by hand, records the source, and queues it for the Friday batch. You get a note either way.',
                        primaryLabel: 'Submit a site',
                        primaryUrl: '#cta',
                        secondaryLabel: 'Read the archive rules',
                        secondaryUrl: '#faq-archives',
                        mediaUrl: $media['contact'][0] ?? null,
                        mediaAlt: 'The intake queue at the curation desk',
                    ),
                    $this->faqArchivesSection(),
                    $this->newsletterSection(
                        heading: 'Watch the intake batches land',
                        summary: 'The Friday digest lists every capture that entered the index that week, tagged and counted.',
                    ),
                    $this->ctaSection(
                        heading: 'Your reference could be someone\'s answer',
                        summary: 'Submit the site you keep showing people — intake takes a minute and the archive keeps the credit with the source.',
                        primaryLabel: 'Subscribe to the digest',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Browse the index',
                        secondaryUrl: $this->pagePath($themeKey),
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
            title: 'No matching captures — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No captures match this filter stack',
                'A graceful empty state for a filtered archive with no matching entries — remove a chip or widen a facet.',
            ),
            renderData: [
                'summary' => 'No captures match that filter stack yet — remove a chip, widen a facet, or clear the view.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Filtered view',
                        heading: 'This filter stack came back empty',
                        summary: 'Nothing is filed under this exact combination yet. Remove the narrowest chip, widen a facet, or clear the view to browse the full index.',
                        primaryLabel: 'Clear the view',
                        primaryUrl: $this->pagePath($themeKey, 'directory'),
                        secondaryLabel: 'Open the filter board',
                        secondaryUrl: '#taxonomy-navigation',
                        mediaUrl: null,
                        mediaAlt: null,
                    ),
                    $this->contentListingSection(
                        heading: 'Nothing filed under this stack yet',
                        summary: null,
                        media: $media,
                        detailUrl: $this->pagePath($themeKey, 'detail'),
                        items: [],
                    ),
                    $this->taxonomyNavigationSection($this->pagePath($themeKey, 'directory') . '#content-listing'),
                    $this->ctaSection(
                        heading: 'Want to be told when this stack fills up?',
                        summary: 'Save the empty view — the moment a capture matches, it appears there and in your Friday digest.',
                        primaryLabel: 'Get the Friday digest',
                        primaryUrl: $this->pagePath($themeKey, 'cta'),
                        secondaryLabel: 'Browse the full index',
                        secondaryUrl: $this->pagePath($themeKey, 'directory'),
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
                'This capture has been retired',
                'A not-found page that routes visitors back into the filter board and the latest intake batch.',
            ),
            renderData: [
                'summary' => 'That page has moved, or the capture was retired when its source went offline — here is the way back in.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This capture has been retired',
                        summary: 'The link is broken, or the source went offline and the capture was archived with a note. The rest of the index is where you left it.',
                        primaryLabel: 'Back to the index',
                        primaryUrl: $this->pagePath($themeKey),
                        secondaryLabel: 'Newest captures',
                        secondaryUrl: $this->pagePath($themeKey) . '#latest-designs',
                        mediaUrl: null,
                        mediaAlt: null,
                    ),
                    $this->taxonomyNavigationSection($this->pagePath($themeKey) . '#latest-designs'),
                    $this->ctaSection(
                        heading: 'Looking for a capture you saved?',
                        summary: 'Search by keyword or source domain — retired captures keep their record even after the screenshot is archived.',
                        primaryLabel: 'Search the index',
                        primaryUrl: $this->pagePath($themeKey) . '#filter-hero',
                        secondaryLabel: 'Submit a site',
                        secondaryUrl: $this->pagePath($themeKey, 'contact'),
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
            name: self::BRAND . ' Subscribe',
            title: 'Follow the intake — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Follow the index as it grows',
                'A focused conversion page for the Friday digest — every new capture, pick, and tag, once a week.',
            ),
            renderData: [
                'summary' => 'Follow the index as it grows — the Friday digest lists every new capture, pinned pick, and tag worth exploring.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The Friday digest',
                        heading: 'Follow the index as it grows',
                        summary: 'One email a week: the full intake batch, the desk\'s pinned picks, and one tag worth exploring. The archive does the remembering.',
                        primaryLabel: 'Subscribe',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Browse first',
                        secondaryUrl: $this->pagePath($themeKey, 'directory'),
                        mediaUrl: $media['cta'][0] ?? null,
                        mediaAlt: 'A week of intake captures laid out for the digest',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'The week in captures, once',
                        summary: 'Every Friday: the new intake batch, the pinned picks, and one tag worth exploring. Nothing else.',
                    ),
                    $this->ctaSection(
                        heading: 'Or send the index something first',
                        summary: 'Submit the site you keep showing people — it goes through intake and lands in the same Friday digest.',
                        primaryLabel: 'Submit a site',
                        primaryUrl: $this->pagePath($themeKey, 'contact'),
                        secondaryLabel: 'Browse the index',
                        secondaryUrl: $this->pagePath($themeKey, 'directory'),
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
            'kicker' => $eyebrow,
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
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    private function pagePath(string $themeKey, string $surfaceSuffix = ''): string
    {
        return '/theme-' . $themeKey . ($surfaceSuffix === '' ? '' : '-' . $surfaceSuffix);
    }

    /**
     * @return array<string, mixed>
     */
    private function filterHeroSection(): array
    {
        return [
            'type' => 'filter-hero',
            'heading' => 'Type what you remember — we file the rest',
            'summary' => 'Keyword search runs across titles, tags, and source domains. Combine it with any facet chip to narrow twelve thousand captures to a shortlist.',
            'items' => [
                ['label' => 'Docs', 'count' => '388', 'facet' => 'type', 'url' => '#taxonomy-navigation'],
                ['label' => 'Monochrome', 'count' => '241', 'facet' => 'colour', 'url' => '#taxonomy-navigation'],
                ['label' => 'Playful', 'count' => '199', 'facet' => 'style', 'url' => '#taxonomy-navigation'],
                ['label' => 'Health', 'count' => '354', 'facet' => 'industry', 'url' => '#taxonomy-navigation'],
                ['label' => 'Onboarding', 'count' => '167', 'facet' => 'type', 'url' => '#taxonomy-navigation'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function taxonomyNavigationSection(string $chipUrl): array
    {
        $withChipUrls = fn (array $chips): array => array_map(
            fn (array $chip): array => [...$chip, 'url' => $chipUrl],
            $chips,
        );

        return [
            'type' => 'taxonomy-navigation',
            'heading' => 'Four facets, one board',
            'summary' => 'Every capture is filed under all four facets at intake. Chips show live counts, so you can see where the archive runs deep before you commit to a filter.',
            'items' => [
                [
                    'title' => 'Type',
                    'facet' => 'type',
                    'summary' => 'What the page is for.',
                    'chips' => $withChipUrls([
                        ['label' => 'Landing page', 'count' => '1,204'],
                        ['label' => 'Pricing', 'count' => '743'],
                        ['label' => 'Docs', 'count' => '388'],
                        ['label' => 'Blog', 'count' => '652'],
                        ['label' => 'Onboarding', 'count' => '167'],
                        ['label' => 'Changelog', 'count' => '94'],
                    ]),
                ],
                [
                    'title' => 'Style',
                    'facet' => 'style',
                    'summary' => 'How it carries itself.',
                    'chips' => $withChipUrls([
                        ['label' => 'Minimal', 'count' => '1,038'],
                        ['label' => 'Brutalist', 'count' => '317'],
                        ['label' => 'Editorial', 'count' => '265'],
                        ['label' => 'Playful', 'count' => '199'],
                        ['label' => 'Retro', 'count' => '142'],
                        ['label' => 'Motion-led', 'count' => '176'],
                    ]),
                ],
                [
                    'title' => 'Colour',
                    'facet' => 'colour',
                    'summary' => 'The palette that leads.',
                    'chips' => $withChipUrls([
                        ['label' => 'Dark', 'count' => '892'],
                        ['label' => 'Monochrome', 'count' => '241'],
                        ['label' => 'Pastel', 'count' => '203'],
                        ['label' => 'Warm neutral', 'count' => '318'],
                        ['label' => 'Neon', 'count' => '87'],
                        ['label' => 'High contrast', 'count' => '264'],
                    ]),
                ],
                [
                    'title' => 'Industry',
                    'facet' => 'industry',
                    'summary' => 'Who shipped it.',
                    'chips' => $withChipUrls([
                        ['label' => 'Fintech', 'count' => '486'],
                        ['label' => 'SaaS', 'count' => '1,167'],
                        ['label' => 'Ecommerce', 'count' => '731'],
                        ['label' => 'Health', 'count' => '354'],
                        ['label' => 'Agency', 'count' => '412'],
                        ['label' => 'Nonprofit', 'count' => '128'],
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function editorPicksSection(array $media, string $detailUrl): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['contact'])));

        $picks = [
            [
                'title' => 'Fintech pricing with one anchor tier',
                'summary' => 'Three tiers, one anchor, and a comparison table that stays readable at every breakpoint — the intake note called it the calmest pricing page of the quarter.',
                'meta' => 'ledgerline.example · saved 214 times',
                'tags' => [
                    ['label' => 'Pricing', 'facet' => 'type'],
                    ['label' => 'Minimal', 'facet' => 'style'],
                    ['label' => 'Dark', 'facet' => 'colour'],
                    ['label' => 'Fintech', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Ecommerce catalogue that merchandises with type',
                'summary' => 'No lifestyle photography above the fold — the catalogue sells with a price grid and a single warm neutral palette.',
                'meta' => 'formandstock.example · saved 178 times',
                'tags' => [
                    ['label' => 'Landing page', 'facet' => 'type'],
                    ['label' => 'Editorial', 'facet' => 'style'],
                    ['label' => 'Warm neutral', 'facet' => 'colour'],
                    ['label' => 'Ecommerce', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Agency site that treats the case study as a feed',
                'summary' => 'Work is filed reverse-chronologically like a changelog — each project entry carries its own tags and a one-line brief.',
                'meta' => 'studiofell.example · saved 131 times',
                'tags' => [
                    ['label' => 'Blog', 'facet' => 'type'],
                    ['label' => 'Motion-led', 'facet' => 'style'],
                    ['label' => 'High contrast', 'facet' => 'colour'],
                    ['label' => 'Agency', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Docs hub with a search-first front door',
                'summary' => 'The homepage is a search field and six counted categories — the same pattern this index uses, which is exactly why the desk pinned it.',
                'meta' => 'plainmanual.example · saved 96 times',
                'tags' => [
                    ['label' => 'Docs', 'facet' => 'type'],
                    ['label' => 'Minimal', 'facet' => 'style'],
                    ['label' => 'Monochrome', 'facet' => 'colour'],
                    ['label' => 'SaaS', 'facet' => 'industry'],
                ],
            ],
        ];

        $items = [];

        foreach ($picks as $index => $pick) {
            $items[] = [
                ...$pick,
                'url' => $detailUrl,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $pick['title'],
            ];
        }

        return [
            'type' => 'editor-picks',
            'heading' => 'Pinned by the curation desk this week',
            'summary' => 'A short stack of captures the desk kept coming back to — each one filed with full tags and a note on why it earned the pin.',
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function latestDesignsSection(array $media, string $detailUrl, string $buttonUrl): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['hero'], $media['cta'])));

        $captures = [
            [
                'title' => 'Health onboarding in four quiet steps',
                'meta' => 'today',
                'tags' => [
                    ['label' => 'Onboarding', 'facet' => 'type'],
                    ['label' => 'Pastel', 'facet' => 'colour'],
                    ['label' => 'Health', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Brutalist changelog for a build tool',
                'meta' => 'today',
                'tags' => [
                    ['label' => 'Changelog', 'facet' => 'type'],
                    ['label' => 'Brutalist', 'facet' => 'style'],
                    ['label' => 'SaaS', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Nonprofit annual report as a single page',
                'meta' => '2 days ago',
                'tags' => [
                    ['label' => 'Landing page', 'facet' => 'type'],
                    ['label' => 'Editorial', 'facet' => 'style'],
                    ['label' => 'Nonprofit', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Neon portfolio with a keyboard-first index',
                'meta' => '3 days ago',
                'tags' => [
                    ['label' => 'Landing page', 'facet' => 'type'],
                    ['label' => 'Neon', 'facet' => 'colour'],
                    ['label' => 'Agency', 'facet' => 'industry'],
                ],
            ],
            [
                'title' => 'Retro blog with a hand-set masthead',
                'meta' => '4 days ago',
                'tags' => [
                    ['label' => 'Blog', 'facet' => 'type'],
                    ['label' => 'Retro', 'facet' => 'style'],
                    ['label' => 'Warm neutral', 'facet' => 'colour'],
                ],
            ],
            [
                'title' => 'Ecommerce size guide that reads like docs',
                'meta' => '5 days ago',
                'tags' => [
                    ['label' => 'Docs', 'facet' => 'type'],
                    ['label' => 'Minimal', 'facet' => 'style'],
                    ['label' => 'Ecommerce', 'facet' => 'industry'],
                ],
            ],
        ];

        $items = [];

        foreach ($captures as $index => $capture) {
            $items[] = [
                ...$capture,
                'url' => $detailUrl,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $capture['title'],
            ];
        }

        return [
            'type' => 'latest-designs',
            'heading' => 'Fresh into the index',
            'summary' => 'Newest captures first, tagged at intake. The grid stays tight on purpose — compare a dozen references without scrolling.',
            'url' => $buttonUrl,
            'label' => 'View all latest',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blogMissionSection(): array
    {
        return [
            'type' => 'blog-mission',
            'heading' => 'Curation notes',
            'summary' => 'A reference is only useful if you can find it again. We tag every capture four ways at intake so the archive answers questions instead of collecting screenshots.',
            'items' => [
                ['meta' => 'Intake', 'title' => 'Every capture is filed by hand', 'summary' => 'No auto-scraping. A curator captures the page, checks the tags, and records the source before anything is published.'],
                ['meta' => 'Notes', 'title' => 'Teardowns and tag guides', 'summary' => 'Short write-ups on what a tag actually means, plus teardowns of captures that keep getting saved.'],
                ['meta' => 'Retirement', 'title' => 'Dead sites leave the index', 'summary' => 'Captures are re-checked quarterly. When a source goes offline, the capture is archived with a note, not silently kept.'],
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
            'heading' => 'How the archive works',
            'summary' => 'The rules the curation desk works to — intake, tagging, retirement, and what happens to your submission.',
            'items' => [
                ['title' => 'How does a site get into the index?', 'summary' => 'Submit a URL. A curator captures it, files it under all four facets, records the source, and queues it for the next weekly batch.'],
                ['title' => 'What do the four facets mean?', 'summary' => 'Type is what the page is for, style is how it carries itself, colour is the palette that leads, and industry is who shipped it. Every capture gets one or more tags in each.'],
                ['title' => 'How fresh are the counts?', 'summary' => 'Tag counts are recalculated on every intake batch, so the numbers on the filter board reflect the live archive.'],
                ['title' => 'Can I keep a filter combination?', 'summary' => 'Yes — any stack of chips can be saved as a view and revisited from the topbar. Saved views update as new captures match.'],
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
            'heading' => 'The archive, counted',
            'items' => [
                ['value' => '12,482', 'label' => 'Captures in the index, each filed under all four facets with its source domain on record.'],
                ['value' => '96', 'label' => 'Tags across type, style, colour, and industry — counts recalculated on every intake batch.'],
                ['value' => 'Fri', 'label' => 'Intake day. New captures land in one weekly batch, so the digest and the counts move together.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @param  list<array<string, mixed>>|null  $items
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, ?string $summary, array $media, string $detailUrl, ?array $items = null): array
    {
        if ($items === null) {
            $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

            $entries = [
                [
                    'title' => 'SaaS dashboard that survives real data',
                    'category' => 'grainmetrics.example',
                    'summary' => 'Dense tables, sticky filters, and a legend that never leaves the viewport.',
                    'tags' => [
                        ['label' => 'Landing page', 'facet' => 'type'],
                        ['label' => 'Dark', 'facet' => 'colour'],
                        ['label' => 'SaaS', 'facet' => 'industry'],
                    ],
                ],
                [
                    'title' => 'Editorial portfolio set entirely in one serif',
                    'category' => 'annaleighton.example',
                    'summary' => 'Case studies read like magazine features, with captures inline as figures.',
                    'tags' => [
                        ['label' => 'Blog', 'facet' => 'type'],
                        ['label' => 'Editorial', 'facet' => 'style'],
                        ['label' => 'Monochrome', 'facet' => 'colour'],
                    ],
                ],
                [
                    'title' => 'Pastel onboarding for a sleep app',
                    'category' => 'lullhealth.example',
                    'summary' => 'Four steps, one illustration style, and a progress bar that behaves.',
                    'tags' => [
                        ['label' => 'Onboarding', 'facet' => 'type'],
                        ['label' => 'Pastel', 'facet' => 'colour'],
                        ['label' => 'Health', 'facet' => 'industry'],
                    ],
                ],
                [
                    'title' => 'Fintech docs with runnable examples',
                    'category' => 'ledgerline.example',
                    'summary' => 'Every endpoint page pairs the reference table with a live request panel.',
                    'tags' => [
                        ['label' => 'Docs', 'facet' => 'type'],
                        ['label' => 'Minimal', 'facet' => 'style'],
                        ['label' => 'Fintech', 'facet' => 'industry'],
                    ],
                ],
            ];

            $items = [];

            foreach ($entries as $index => $entry) {
                $image = $images[$index % max(count($images), 1)] ?? null;
                $items[] = [
                    ...$entry,
                    'url' => $detailUrl,
                    'image' => $image,
                    'imageUrl' => $image,
                    'imageAlt' => $entry['title'],
                ];
            }
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
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
            'email_label' => 'Email address',
            'button' => 'Subscribe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(
        string $heading,
        string $summary,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
    ): array {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'url' => $primaryUrl,
            'label' => $primaryLabel,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(string $themeKey): array
    {
        $homePath = $this->pagePath($themeKey);

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Types', 'url' => $homePath . '#taxonomy-navigation'],
                ['label' => 'Styles', 'url' => $homePath . '#taxonomy-navigation'],
                ['label' => 'Colours', 'url' => $homePath . '#taxonomy-navigation'],
                ['label' => 'Industries', 'url' => $homePath . '#taxonomy-navigation'],
                ['label' => 'Editor picks', 'url' => $homePath . '#editor-picks'],
                ['label' => 'Latest', 'url' => $homePath . '#latest-designs'],
            ],
            'ctaLabel' => 'Submit a site',
            'ctaUrl' => $this->pagePath($themeKey, 'contact'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(string $themeKey): array
    {
        $homePath = $this->pagePath($themeKey);

        $columns = [
            [
                'heading' => 'Facets',
                'title' => 'Facets',
                'links' => [
                    ['label' => 'Types', 'url' => $homePath . '#taxonomy-navigation'],
                    ['label' => 'Styles', 'url' => $homePath . '#taxonomy-navigation'],
                    ['label' => 'Colours', 'url' => $homePath . '#taxonomy-navigation'],
                    ['label' => 'Industries', 'url' => $homePath . '#taxonomy-navigation'],
                ],
            ],
            [
                'heading' => 'Library',
                'title' => 'Library',
                'links' => [
                    ['label' => 'Editor picks', 'url' => $homePath . '#editor-picks'],
                    ['label' => 'Latest captures', 'url' => $homePath . '#latest-designs'],
                    ['label' => 'Archive FAQ', 'url' => $homePath . '#faq-archives'],
                    ['label' => 'Curation notes', 'url' => $homePath . '#blog-mission'],
                ],
            ],
            [
                'heading' => 'Contribute',
                'title' => 'Contribute',
                'links' => [
                    ['label' => 'Submit a site', 'url' => $this->pagePath($themeKey, 'contact')],
                    ['label' => 'Weekly digest', 'url' => $this->pagePath($themeKey, 'cta')],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A filterable reference library of web capture screenshots, filed by type, style, colour, and industry.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
