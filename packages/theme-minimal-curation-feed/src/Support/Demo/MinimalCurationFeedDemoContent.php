<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalCurationFeed\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Minimal Curation Feed theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (feed-hero / category-tabs
 * / curation-feed / best-of-views / app-website-icons / source-metadata /
 * newsletter) alongside the standard hero/proof/cta — giving every surface a
 * full, individual daily-curation feed site rather than a five-section skeleton.
 */
final class MinimalCurationFeedDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Curation Daily';

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
            title: self::BRAND . ' — Daily Curated Screenshots & References',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A daily feed of curated screenshots and references',
                'Curation Daily is a calm white feed for daily curated links, screenshots, apps, websites, icons, and product references.',
            ),
            renderData: [
                'summary' => 'Curation Daily is a calm daily feed for curated links, screenshots, apps, websites, icons, and product references with category tabs, search, and a newsletter signup.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Minimal curation feed',
                        'heading' => 'A daily feed of curated screenshots and references',
                        'summary' => 'A calm white feed for daily curated links, screenshots, apps, websites, icons, and product references with category tabs, search, a live update indicator, and a compact newsletter signup.',
                        'actions' => [
                            ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'primary'],
                            ['label' => 'Join newsletter', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Curation Daily live feed',
                    ],
                    $this->feedHeroSection($media),
                    $this->categoryTabsSection(),
                    $this->curationFeedSection(
                        heading: 'Today\'s picks, with just enough metadata',
                        summary: 'Airy but compact cards for screenshot, title, maker, source, rating, platform, topic tags, and saved state.',
                        media: $media,
                    ),
                    $this->appWebsiteIconsSection(),
                    $this->sourceMetadataSection(),
                    $this->proofSection(
                        heading: 'Why the feed stays calm',
                        summary: 'A live update indicator, compact cards, and tiny labels keep browsing fast.',
                    ),
                    $this->newsletterSection(
                        heading: 'Send useful references without clutter',
                        summary: 'A compact signup for daily links, weekly best-of lists, apps, websites, icons, and product references.',
                    ),
                    $this->ctaSection(
                        heading: 'Launch a calm curation feed that stays fast',
                        summary: 'Use the theme for daily curated links, screenshots, apps, websites, icons, and product reference libraries.',
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
            name: self::BRAND . ' Latest',
            title: 'Latest feed — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The latest feed, archived and legible',
                'An archive view for latest, best of, apps, websites, icons, makers, sources, and topic tags.',
            ),
            renderData: [
                'summary' => 'An archive view for latest, best of, apps, websites, icons, makers, sources, and topic tags — fast and legible.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Latest references',
                        'heading' => 'The latest feed, archived and legible',
                        'summary' => 'Daily curated links, screenshots, apps, websites, icons, product references, and best-of lists kept fast with white pages, hairline borders, and tiny labels.',
                        'actions' => [
                            ['label' => 'Join newsletter', 'url' => '#newsletter', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Latest curated feed archive',
                    ],
                    $this->categoryTabsSection(),
                    $this->curationFeedSection(
                        heading: 'Screenshot cards with just enough metadata',
                        summary: 'Compact cards for screenshot, title, maker, source, rating, platform, and saved state.',
                        media: $media,
                    ),
                    $this->bestOfViewsSection(),
                    $this->ctaSection(
                        heading: 'Want this archive on your own feed?',
                        summary: 'Use the theme for daily curated links, apps, websites, icons, and product reference libraries.',
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
            name: self::BRAND . ' Reference',
            title: 'Productivity app with excellent empty states — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Productivity app with excellent empty states',
                'A feed card for screenshot, maker, source, rating, platform, and compact topic tags.',
            ),
            renderData: [
                'summary' => 'A single saved reference with screenshot, maker, source, rating, platform, topic tags, and a short editorial note.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Saved reference',
                        'heading' => 'Productivity app with excellent empty states',
                        'summary' => 'A screenshot entry with title, maker, source, rating, platform, category, topic tags, and one short note — App, iOS, 4.8 rating.',
                        'actions' => [
                            ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Saved app reference screenshot',
                    ],
                    $this->sourceMetadataSection(),
                    $this->appWebsiteIconsSection(),
                    $this->curationFeedSection(
                        heading: 'More from this maker and topic',
                        summary: 'Related screenshot cards with the same minimal metadata language.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Save references like this in your own feed',
                        summary: 'Screenshot, title, maker, source, rating, platform, tags, and saved state are all supported.',
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
            title: 'Submit & subscribe — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Stay close to the feed in one calm step',
                'A non-submitting newsletter, link submission, and source suggestion prompt that feels native to the feed.',
            ),
            renderData: [
                'summary' => 'Subscribe to daily picks, suggest a source, or submit a link — without clutter and without leaving the feed.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Weekly picks',
                        'heading' => 'Stay close to the feed in one calm step',
                        'summary' => 'Subscribe for daily links and weekly best-of lists, suggest a source, or submit a link — a tiny signup block that keeps the feed fast.',
                        'actions' => [
                            ['label' => 'Subscribe', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Newsletter and link submission',
                    ],
                    $this->newsletterSection(
                        heading: 'Send useful references without clutter',
                        summary: 'A compact signup module for daily links, weekly best-of lists, apps, websites, icons, and product references.',
                    ),
                    $this->proofSection(
                        heading: 'What you can expect',
                        summary: 'How the feed treats your inbox and your attention.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to start your own feed?',
                        summary: 'Use the theme for daily curated links, screenshots, apps, websites, icons, and product reference libraries.',
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
            title: 'No matches — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing in the feed for that filter yet',
                'A calm empty state for a filtered curation feed with no matching references.',
            ),
            renderData: [
                'summary' => 'No screenshots, apps, websites, or icons match that filter yet — clear it or browse the latest feed.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Search across the whole curated feed',
                        'heading' => 'Nothing in the feed for that filter yet',
                        'summary' => 'No screenshots, apps, websites, icons, makers, or sources match that search. Clear the filter to see the latest, or suggest a source to add.',
                        'actions' => [
                            ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'primary'],
                            ['label' => 'Suggest a source', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                    ],
                    $this->categoryTabsSection(),
                    $this->appWebsiteIconsSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific reference?',
                        summary: 'Suggest a source and it can join the latest, best of, apps, websites, or icons views.',
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
                'That reference has moved',
                'A not-found page that routes visitors back into the latest feed and newsletter.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the feed.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That reference has moved',
                        'summary' => 'The link is broken or the entry has been unsaved. Head back to the latest feed, or join the newsletter for daily picks.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                    ],
                    $this->categoryTabsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for a reference?',
                        summary: 'Browse the latest feed or suggest a source to add to apps, websites, or icons.',
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
            name: self::BRAND . ' Start',
            title: 'Start your feed — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Launch a calm curation feed that stays fast',
                'A focused page inviting makers and curators to start their own minimal feed.',
            ),
            renderData: [
                'summary' => 'Launch a calm curation feed that stays fast — daily links, screenshots, apps, websites, icons, and product references.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Feed ready',
                        'heading' => 'Launch a calm curation feed that stays fast',
                        'summary' => 'Use the theme for daily curated links, screenshots, apps, websites, icons, and product reference libraries — best of, latest, apps, websites, and icons in one minimal card language.',
                        'actions' => [
                            ['label' => 'Start with Minimal Curation Feed', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Curation Daily feed ready to launch',
                    ],
                    $this->bestOfViewsSection(),
                    $this->proofSection(
                        heading: 'Built to stay calm and quick',
                        summary: 'White pages, hairline borders, tiny labels, and simple metadata.',
                    ),
                    $this->ctaSection(
                        heading: 'One signup away from a fresh feed',
                        summary: 'Daily picks, best-of lists, apps, websites, icons, and product references keep the feed fresh.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function feedHeroSection(array $media): array
    {
        return [
            'type' => 'feed-hero',
            'kicker' => 'Live feed',
            'heading' => 'Open with one useful sentence and immediate discovery',
            'summary' => 'A short description, a live update indicator, category tabs, a search input, and a compact newsletter signup — updated daily.',
            'mediaUrl' => $media['hero'][0] ?? null,
            'mediaAlt' => 'Live curation feed hero',
            'notes' => [
                ['title' => 'Updated daily', 'summary' => 'A small live indicator shows freshness without turning the page into a dashboard.'],
                ['title' => 'Search and tabs stay close', 'summary' => 'Latest, best of, apps, websites, and icons remain one tap away on mobile and desktop.'],
                ['title' => 'Newsletter without a heavy CTA', 'summary' => 'A tiny signup block supports daily or weekly picks while keeping the feed fast.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryTabsSection(): array
    {
        return [
            'type' => 'category-tabs',
            'kicker' => 'Category tabs',
            'heading' => 'Keep the navigation tiny and predictable',
            'summary' => 'Latest, best of, apps, websites, and icons stay one tap away in a tiny, predictable tab row.',
            'tabs' => [
                ['title' => 'Best of', 'summary' => 'Handpicked collections for useful apps, homepage patterns, icon sets, product details, and reference boards.'],
                ['title' => 'Apps', 'summary' => 'Mobile apps, desktop tools, AI products, productivity utilities, design tools, and developer apps.'],
                ['title' => 'Websites', 'summary' => 'Product pages, portfolios, launch pages, docs, ecommerce pages, dashboards, and marketing references.'],
                ['title' => 'Icons', 'summary' => 'Icon systems, app icons, favicons, pictograms, UI symbols, and small visual references.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function curationFeedSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $cards = [
            ['title' => 'Productivity app with excellent empty states', 'summary' => 'A feed card for screenshot, maker, source, rating, platform, and compact topic tags.', 'maker' => 'Driftwork', 'source' => 'driftwork.example', 'meta' => 'App. iOS. 4.8 rating.', 'tags' => ['Apps', 'Productivity', 'iOS']],
            ['title' => 'Website reference with calm product sections', 'summary' => 'A screenshot entry with title, maker, source, platform, category, and one short note.', 'maker' => 'Studio Verda', 'source' => 'verda.example', 'meta' => 'Website. SaaS. Editor pick.', 'tags' => ['Websites', 'SaaS', 'Landing']],
            ['title' => 'Icon set with useful visual constraints', 'summary' => 'A compact card for icon preview, maker, source, license note, platform, and tags.', 'maker' => 'Northline', 'source' => 'northline.example', 'meta' => 'Icons. SVG. Saved.', 'tags' => ['Icons', 'SVG', 'Open source']],
            ['title' => 'Onboarding flow that earns the first tap', 'summary' => 'A screenshot entry with maker, source, platform, rating, and a short editorial note.', 'maker' => 'Harbour', 'source' => 'harbour.example', 'meta' => 'App. Android. 4.6 rating.', 'tags' => ['Apps', 'Onboarding', 'Android']],
            ['title' => 'Pricing page that stays honest and scannable', 'summary' => 'A website card with title, maker, source, category, platform, and saved state.', 'maker' => 'Lumen', 'source' => 'lumen.example', 'meta' => 'Website. Pricing. Saved.', 'tags' => ['Websites', 'Pricing', 'Marketing']],
            ['title' => 'Favicon and app-icon system done right', 'summary' => 'An icon card for app icon, favicon, maker, source, license, and small tags.', 'maker' => 'Foundry', 'source' => 'foundry.example', 'meta' => 'Icons. App icon. Editor pick.', 'tags' => ['Icons', 'Branding', 'Favicon']],
        ];

        $items = [];

        foreach ($cards as $index => $card) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$card,
                'url' => '#entry-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $card['title'],
            ];
        }

        return [
            'type' => 'curation-feed',
            'kicker' => 'Curation feed',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bestOfViewsSection(): array
    {
        return [
            'type' => 'best-of-views',
            'kicker' => 'Best-of views',
            'heading' => 'Give evergreen collections a quieter rhythm',
            'summary' => 'Best-of modules collect apps, websites, icons, product details, references, and editor picks without breaking the feed.',
            'button' => 'Browse best of',
            'buttonUrl' => '#best-of-views',
            'items' => [
                ['title' => 'Best apps this month', 'summary' => 'Apps grouped by category, platform, maker, rating, source, and editor note.'],
                ['title' => 'Best website details', 'summary' => 'Pricing sections, signup flows, empty states, icon treatments, product cards, and navigation details.'],
                ['title' => 'Best icon systems', 'summary' => 'Icon systems, app icons, symbols, and downloadable references worth keeping.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appWebsiteIconsSection(): array
    {
        return [
            'type' => 'app-website-icons',
            'kicker' => 'Apps, websites, and icons',
            'heading' => 'Separate common views without changing the feed model',
            'summary' => 'Apps, websites, and icons each get a tuned view while sharing one minimal card language.',
            'items' => [
                ['title' => 'Apps view', 'summary' => 'Compact app cards with platform labels, maker names, rating, source, and topic tags.', 'meta' => 'Apps'],
                ['title' => 'Websites view', 'summary' => 'Screenshot-first website cards with source, maker, category, and a short note.', 'meta' => 'Websites'],
                ['title' => 'Icons view', 'summary' => 'Small visual cards for icon systems, app icons, symbols, and downloadable references.', 'meta' => 'Icons'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sourceMetadataSection(): array
    {
        return [
            'type' => 'source-metadata',
            'kicker' => 'Source metadata',
            'heading' => 'Keep every reference useful without overexplaining',
            'summary' => 'Source links, makers, platform, rating, topic tags, category, date, saved state, and short editorial notes.',
            'items' => [
                ['title' => 'Maker and source', 'summary' => 'Show who made the reference, where it came from, platform, category, and why it was saved.'],
                ['title' => 'Rating and topic tags', 'summary' => 'Rating, platform, topic tags, and source labels help visitors scan without making the card dense.'],
                ['title' => 'Infinite-feed friendly archives', 'summary' => 'Archives for latest, best of, apps, websites, icons, topics, sources, makers, and saved views stay fast.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'kicker' => 'Weekly picks',
            'heading' => $heading,
            'summary' => $summary,
            'emailLabel' => 'Email address',
            'button' => 'Subscribe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'kicker' => 'Feed proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['metric' => 'Daily', 'name' => 'Live update indicator', 'quote' => 'Compact cards and category tabs keep the feed current without noise.'],
                ['metric' => '5 views', 'name' => 'Best of, latest, apps, websites, icons', 'quote' => 'All supported with the same minimal card language.'],
                ['metric' => 'Fast', 'name' => 'Calm and quick to browse', 'quote' => 'White pages, hairline borders, tiny labels, and simple metadata.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'kicker' => 'Feed ready',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Start with Minimal Curation Feed', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse latest', 'url' => '#curation-feed', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        $items = [
            ['label' => 'Latest', 'url' => '#curation-feed'],
            ['label' => 'Best of', 'url' => '#best-of-views'],
            ['label' => 'Apps & icons', 'url' => '#app-website-icons'],
            ['label' => 'Newsletter', 'url' => '#newsletter'],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => $items,
            'consultationUrl' => '#newsletter',
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Feed',
                'title' => 'Feed',
                'links' => [
                    ['label' => 'Latest', 'url' => '#curation-feed'],
                    ['label' => 'Best of', 'url' => '#best-of-views'],
                    ['label' => 'Archive', 'url' => '#curation-feed'],
                ],
            ],
            [
                'heading' => 'Browse',
                'title' => 'Browse',
                'links' => [
                    ['label' => 'Apps', 'url' => '#app-website-icons'],
                    ['label' => 'Websites', 'url' => '#app-website-icons'],
                    ['label' => 'Icons', 'url' => '#app-website-icons'],
                ],
            ],
            [
                'heading' => 'Contribute',
                'title' => 'Contribute',
                'links' => [
                    ['label' => 'Submit a link', 'url' => '#newsletter'],
                    ['label' => 'Suggest a source', 'url' => '#newsletter'],
                    ['label' => 'Newsletter', 'url' => '#newsletter'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A calm daily feed of curated screenshots, apps, websites, icons, and product references.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
