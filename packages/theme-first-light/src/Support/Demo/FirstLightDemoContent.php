<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FirstLight\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the First Light theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (feed-hero / category-tabs
 * / curation-feed / best-of-views / app-website-icons / source-metadata /
 * newsletter) alongside the standard hero/proof/cta — giving every surface a
 * full, individual daily-curation feed site rather than a five-section skeleton.
 */
final class FirstLightDemoContent implements ProvidesThemeDemoContent
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
            title: self::BRAND . ' — One Good Screenshot a Day',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'One good screenshot a day, carefully chosen',
                'Curation Daily collects the best app screens, website details, and icon systems — one calm column, big captures, tiny captions.',
            ),
            renderData: [
                'summary' => 'Curation Daily publishes one carefully chosen app screen, website detail, or icon system every day — big captures, tiny captions, no noise.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'A daily reference feed',
                        'heading' => 'One good screenshot a day, carefully chosen',
                        'summary' => 'The best app screens, website details, and icon systems — published one per day in a single calm column, each with its maker, source, and one short note.',
                        'actions' => [
                            ['label' => 'Browse the latest', 'url' => '#curation-feed', 'style' => 'primary'],
                            ['label' => 'Get the daily email', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Today\'s capture — an onboarding screen worth studying',
                    ],
                    $this->categoryTabsSection(),
                    $this->curationFeedSection(
                        heading: 'This week in the feed',
                        summary: 'Seven days, seven captures — each with its maker, source, platform, and the one thing worth stealing.',
                        media: $media,
                    ),
                    $this->curationFeedGridSection($media),
                    $this->bestOfViewsCarouselSection($media),
                    $this->appWebsiteIconsSection(),
                    $this->proofSection(
                        heading: 'A feed you can actually keep up with',
                        summary: 'One capture a day, every credit intact, and an archive that stays fast.',
                    ),
                    $this->newsletterSection(
                        heading: 'One capture in your inbox, every morning',
                        summary: 'The day\'s pick with its source and a one-line note. No roundups, no sponsors, unsubscribe any time.',
                    ),
                    $this->lightboxViewerSection(),
                    $this->nextItemLightboxCtaSection($media),
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
            title: 'The Archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every capture we have saved',
                'The full archive of daily picks — apps, websites, and icon systems, newest first.',
            ),
            renderData: [
                'summary' => 'The full Curation Daily archive — every app screen, website detail, and icon system we have saved, newest first.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The archive',
                        'heading' => 'Every capture we have saved',
                        'summary' => 'Hundreds of daily picks, browsable by kind, maker, or source — the same calm column all the way down.',
                        'actions' => [
                            ['label' => 'Browse the archive', 'url' => '#content-listing', 'style' => 'primary'],
                            ['label' => 'Get the daily email', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The most recent capture in the archive',
                    ],
                    $this->categoryTabsSection(),
                    $this->curationFeedSection(
                        heading: 'Recently added',
                        summary: 'The newest captures in the archive, each with its maker, source, and note.',
                        media: $media,
                    ),
                    $this->curationFeedGridSection($media, variant: 'compact'),
                    $this->contentListingSection(
                        heading: 'Deeper in the archive',
                        summary: 'Older picks that still hold up — the entries readers keep linking back to.',
                        media: $media,
                    ),
                    $this->bestOfViewsSection($media),
                    $this->lightboxViewerSection(),
                    $this->ctaSection(
                        heading: 'Found something worth keeping?',
                        summary: 'The daily email delivers the next capture before it reaches the archive.',
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
            name: self::BRAND . ' Capture',
            title: 'Driftwork\'s empty states — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Driftwork\'s empty states teach before they ask',
                'A single saved capture with its screenshot, maker, source, platform, and one short note on why it earned a place in the feed.',
            ),
            renderData: [
                'summary' => 'Driftwork\'s empty states, saved to the feed — screenshot, maker, source, platform, and one note on why it matters.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Saved capture · 14 June',
                        'heading' => 'Driftwork\'s empty states teach before they ask',
                        'summary' => 'Every blank screen in this productivity app demonstrates the gesture it wants — the whole product onboards itself one empty state at a time. App, iOS, by Driftwork.',
                        'actions' => [
                            ['label' => 'Visit the source', 'url' => '#source-metadata', 'style' => 'primary'],
                            ['label' => 'Back to the feed', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Driftwork\'s empty state screen, captured on iOS',
                    ],
                    $this->sourceMetadataCreditsSection(),
                    $this->lightboxViewerSection(variant: 'minimal'),
                    $this->curationFeedSection(
                        heading: 'More like this one',
                        summary: 'Other captures filed under onboarding and empty states.',
                        media: $media,
                    ),
                    $this->appWebsiteIconsSection(),
                    $this->nextItemLightboxCtaSection(
                        media: $media,
                        heading: 'One capture like this, every day',
                        summary: 'The daily email delivers the next screen worth studying before it reaches the archive.',
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
                'Send us the screen you can\'t stop thinking about',
                'Submit a capture, suggest a source, or join the daily email — everything sent to the feed gets looked at.',
            ),
            renderData: [
                'summary' => 'Submit a capture, suggest a source, or join the daily email — everything sent to Curation Daily gets looked at.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Submit & subscribe',
                        'heading' => 'Send us the screen you can\'t stop thinking about',
                        'summary' => 'A screenshot and a source link is all it takes. If it earns a place in the feed, it runs with full credit to the maker and to you.',
                        'actions' => [
                            ['label' => 'Join the daily email', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Browse the latest', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'A reader submission being reviewed for the feed',
                    ],
                    $this->newsletterSection(
                        heading: 'One capture in your inbox, every morning',
                        summary: 'The day\'s pick with its source and a one-line note — the same feed, delivered.',
                    ),
                    $this->proofSection(
                        heading: 'What happens to a submission',
                        summary: 'Every capture sent to the feed is opened, considered, and answered.',
                    ),
                    $this->ctaSection(
                        heading: 'Not ready to submit yet?',
                        summary: 'Read along for a while — the feed is the best brief for what belongs in it.',
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
                'Nothing saved under that filter yet',
                'A calm empty state for a filtered feed with no matching captures.',
            ),
            renderData: [
                'summary' => 'No captures match that filter yet — clear it, browse the latest, or suggest the source we are missing.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The archive',
                        'heading' => 'Nothing saved under that filter yet',
                        'summary' => 'No apps, websites, or icon systems match that search. Clear the filter to see the latest, or tell us what we are missing.',
                        'actions' => [
                            ['label' => 'Browse the latest', 'url' => '#curation-feed', 'style' => 'primary'],
                            ['label' => 'Suggest a source', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                    ],
                    $this->categoryTabsSection(),
                    $this->feedHeroSection($media),
                    $this->bestOfViewsSection($media),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Suggest the source and it can join tomorrow\'s feed with full credit.',
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
                'That capture has moved',
                'A not-found page that routes visitors back into the latest feed and the daily email.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the feed.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That capture has moved',
                        'summary' => 'The link is broken or the entry was retired from the archive. The latest feed is one tap away.',
                        'actions' => [
                            ['label' => 'Back to the feed', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse the archive', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                    ],
                    $this->categoryTabsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for a capture?',
                        summary: 'Browse the latest feed, or tell us what you were after and we will dig it out of the archive.',
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
            title: 'Get the daily email — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'The feed, delivered one capture at a time',
                'A focused page inviting readers to receive the daily pick by email.',
            ),
            renderData: [
                'summary' => 'The feed, delivered — one carefully chosen capture in your inbox every morning.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The daily email',
                        'heading' => 'The feed, delivered one capture at a time',
                        'summary' => 'Every morning: one screenshot, its maker and source, and a single line on why it is worth your attention. That is the whole email.',
                        'actions' => [
                            ['label' => 'Subscribe', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Browse the feed first', 'url' => '#curation-feed', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'This morning\'s capture, as it appears in the daily email',
                    ],
                    $this->bestOfViewsSection($media),
                    $this->proofSection(
                        heading: 'Why readers stay subscribed',
                        summary: 'What one email a day actually delivers.',
                    ),
                    $this->ctaSection(
                        heading: 'Tomorrow\'s capture is already queued',
                        summary: 'Subscribe now and it lands in your inbox with the morning coffee.',
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
            'heading' => 'Meanwhile, today in the feed',
            'summary' => 'While you search, this morning\'s capture is already up — Driftwork\'s empty states, credited and annotated.',
            'mediaUrl' => $media['detail'][0],
            'mediaAlt' => 'This morning\'s capture — Driftwork\'s empty states on iOS',
            'notes' => [
                ['title' => 'Maker', 'summary' => 'Driftwork — an independent two-person studio shipping on iOS.'],
                ['title' => 'Why it ran', 'summary' => 'Every blank screen teaches the gesture it wants — restraint doing the onboarding.'],
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
            'kicker' => 'Browse',
            'tabs' => [
                ['title' => 'Best of', 'url' => '#best-of-views', 'summary' => 'The captures readers return to most — apps, website details, and icon systems that keep earning their place.'],
                ['title' => 'Apps', 'url' => '#app-website-icons', 'summary' => 'Onboarding flows, empty states, settings screens, and the small interactions that make software feel considered.'],
                ['title' => 'Websites', 'url' => '#app-website-icons', 'summary' => 'Pricing pages, product sections, portfolios, and docs that solve a layout problem worth remembering.'],
                ['title' => 'Icons', 'url' => '#app-website-icons', 'summary' => 'Icon systems, app icons, favicons, and pictograms with a constraint worth studying.'],
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

        $entries = [
            ['title' => 'Driftwork\'s empty states teach before they ask', 'summary' => 'Every blank screen demonstrates the gesture it wants — the whole app onboards itself one empty state at a time.', 'maker' => 'Driftwork', 'source' => 'driftwork.example', 'meta' => 'App · iOS · 14 Jun', 'tags' => ['Apps', 'Empty states', 'Onboarding']],
            ['title' => 'Verda\'s product page trusts one column', 'summary' => 'No feature grid, no carousel — a single narrow column that reads top to bottom like a good argument.', 'maker' => 'Studio Verda', 'source' => 'verda.example', 'meta' => 'Website · SaaS · 13 Jun', 'tags' => ['Websites', 'Product pages']],
            ['title' => 'Northline draws every icon on a 12px grid', 'summary' => 'One stroke weight, one corner radius, four hundred icons — the constraint is the whole system.', 'maker' => 'Northline', 'source' => 'northline.example', 'meta' => 'Icons · SVG · 12 Jun', 'tags' => ['Icons', 'Systems', 'Open source']],
            ['title' => 'Harbour earns the first tap in three screens', 'summary' => 'The onboarding asks for nothing until it has shown the payoff — a full trip planned before the account exists.', 'maker' => 'Harbour', 'source' => 'harbour.example', 'meta' => 'App · Android · 11 Jun', 'tags' => ['Apps', 'Onboarding']],
            ['title' => 'Lumen prices three tiers without a comparison table', 'summary' => 'Each plan is a sentence, not a checklist — you know which one you are before you scroll.', 'maker' => 'Lumen', 'source' => 'lumen.example', 'meta' => 'Website · Pricing · 10 Jun', 'tags' => ['Websites', 'Pricing']],
            ['title' => 'Foundry ships one mark that survives every size', 'summary' => 'The same shape reads as an app icon, a favicon, and a wordmark — nothing redrawn, only reweighted.', 'maker' => 'Foundry', 'source' => 'foundry.example', 'meta' => 'Icons · Branding · 9 Jun', 'tags' => ['Icons', 'App icons', 'Favicon']],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#entry-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'curation-feed',
            'kicker' => 'Latest',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function bestOfViewsSection(array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['proof'], $media['listing'], $media['hero'])));

        $entries = [
            ['title' => 'Driftwork\'s empty states', 'summary' => 'The capture readers send each other most — onboarding through blank screens.', 'meta' => '18.2k views'],
            ['title' => 'Lumen\'s three-sentence pricing', 'summary' => 'Proof that a pricing page can skip the comparison table entirely.', 'meta' => '14.9k views'],
            ['title' => 'Northline\'s 12px icon grid', 'summary' => 'Four hundred icons from one constraint — the most-saved icon system in the archive.', 'meta' => '11.4k views'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#best-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'best-of-views',
            'kicker' => 'Most viewed',
            'heading' => 'What readers keep coming back to',
            'summary' => 'The three captures with the most repeat visits this month.',
            'button' => 'Browse all best-of picks',
            'buttonUrl' => '#curation-feed',
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function bestOfViewsCarouselSection(array $media): array
    {
        return [
            ...$this->bestOfViewsSection($media),
            'variant' => 'carousel',
            'heading' => 'What readers keep coming back to, in order',
            'summary' => 'Swipe through the most-viewed captures this month — every slide opens in the same lightbox reel as the feed above.',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function curationFeedGridSection(array $media, string $variant = 'default'): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['hero'],
            $media['cta'],
        )));

        $entries = [
            ['title' => 'Driftwork\'s empty states', 'meta' => 'App · iOS'],
            ['title' => 'Verda\'s product page', 'meta' => 'Website · SaaS'],
            ['title' => 'Northline\'s icon grid', 'meta' => 'Icons · SVG'],
            ['title' => 'Harbour\'s onboarding', 'meta' => 'App · Android'],
            ['title' => 'Lumen\'s pricing page', 'meta' => 'Website · Pricing'],
            ['title' => 'Foundry\'s wordmark system', 'meta' => 'Icons · Branding'],
            ['title' => 'Almanac\'s settings screen', 'meta' => 'App · iOS'],
            ['title' => 'Portside\'s docs search', 'meta' => 'Website · Docs'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#entry-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'curation-feed-grid',
            'variant' => $variant,
            'kicker' => 'Contact sheet',
            'heading' => 'Every capture, at a glance',
            'summary' => 'The same reel as the feed above, laid out as a contact sheet — open any capture in the shared lightbox and step through the rest with Next and Previous.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lightboxViewerSection(string $variant = 'default'): array
    {
        return [
            'type' => 'lightbox-carousel-viewer',
            'variant' => $variant,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function nextItemLightboxCtaSection(array $media, ?string $heading = null, ?string $summary = null): array
    {
        return [
            'type' => 'cta',
            'variant' => 'lightbox-preview',
            'kicker' => 'Keep browsing',
            'heading' => $heading ?? 'Tomorrow\'s capture is already queued',
            'summary' => $summary ?? 'Follow along in the feed, or let the daily email bring the next reference to you.',
            'actions' => [
                ['label' => 'Get the daily email', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse the latest', 'url' => '#curation-feed', 'style' => 'secondary'],
            ],
            'nextImage' => $media['cta'][0] ?? $media['hero'][0],
            'nextTitle' => 'Tomorrow\'s capture, queued for the morning edition',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['detail'], $media['contact'])));

        $entries = [
            ['title' => 'A settings screen that reads like a table of contents', 'category' => 'App · iOS', 'summary' => 'Every row is a destination, every destination is one tap deep — filed in May.'],
            ['title' => 'The docs page that answers before you search', 'category' => 'Website · Docs', 'summary' => 'The five most-asked questions sit above the search box, in plain language.'],
            ['title' => 'A favicon set that survives the browser tab', 'category' => 'Icons · Favicon', 'summary' => 'Sixteen pixels, two colours, still unmistakable — filed in April.'],
            ['title' => 'Checkout in a single screen, honestly', 'category' => 'Website · Commerce', 'summary' => 'Address, payment, and confirmation on one page without feeling crowded.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'content-listing',
            'kicker' => 'Archive',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appWebsiteIconsSection(): array
    {
        return [
            'type' => 'app-website-icons',
            'kicker' => 'Apps, websites & icons',
            'heading' => 'Browse by kind',
            'summary' => 'Three views, one card language — pick the shelf you came for.',
            'items' => [
                ['title' => 'Apps', 'summary' => 'Onboarding, empty states, settings, and the interactions that make software feel considered.', 'meta' => '214 saved', 'url' => '#curation-feed'],
                ['title' => 'Websites', 'summary' => 'Pricing pages, product sections, docs, and portfolios that solve a layout problem.', 'meta' => '186 saved', 'url' => '#curation-feed'],
                ['title' => 'Icons', 'summary' => 'Icon systems, app icons, and favicons with a constraint worth studying.', 'meta' => '97 saved', 'url' => '#curation-feed'],
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
            'kicker' => 'Source notes',
            'heading' => 'Every capture, credited',
            'summary' => 'Each entry in the feed carries the same small set of facts — enough to follow the reference home.',
            'items' => [
                ['title' => 'Maker', 'summary' => 'Driftwork — an independent two-person studio; the capture links straight to their release notes.'],
                ['title' => 'Source', 'summary' => 'driftwork.example, captured from the shipping iOS build on 14 June, unedited.'],
                ['title' => 'Filed under', 'summary' => 'Apps · Empty states · Onboarding — three tags, no more, so the archive stays browsable.'],
                ['title' => 'Editor\'s note', 'summary' => 'Saved because every blank screen teaches the gesture it wants — restraint doing the onboarding.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sourceMetadataCreditsSection(): array
    {
        return [
            ...$this->sourceMetadataSection(),
            'variant' => 'credits',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'kicker' => 'Daily email',
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
            'kicker' => 'The feed in numbers',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['value' => '1 / day', 'name' => 'Publishing rhythm', 'label' => 'One capture every morning — never a backlog dump, never a quiet week.'],
                ['value' => '497', 'name' => 'Captures in the archive', 'label' => 'Apps, websites, and icon systems, every one credited to its maker and source.'],
                ['value' => '0', 'name' => 'Sponsored placements', 'label' => 'Nothing in the feed is paid for — a capture earns its place or it does not run.'],
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
            'kicker' => 'Keep browsing',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Get the daily email', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse the latest', 'url' => '#curation-feed', 'style' => 'secondary'],
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
                    ['label' => 'Archive', 'url' => '#content-listing'],
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
                    ['label' => 'Submit a capture', 'url' => '#newsletter'],
                    ['label' => 'Suggest a source', 'url' => '#newsletter'],
                    ['label' => 'Daily email', 'url' => '#newsletter'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'One good screenshot a day — app screens, website details, and icon systems, each credited to its maker and source.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
