<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OnePageShowcase\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the One Page Showcase theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature gallery renderers (showcase-hero / category-tabs
 * / one-page-grid / templates-sections / tools-sponsors / build-resources / newsletter)
 * alongside the standard hero/proof/cta — giving every surface a full, individual
 * showcase site rather than the shared skeleton.
 *
 * Chrome payloads carry BOTH the contract keys (`brandName` / `columns`) and the
 * theme blade keys (`brand` / `items`) so navigation and footer render identically
 * under the contract harness and the live theme renderers.
 */
final class OnePageShowcaseDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'OnePage Gallery';

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
            title: self::BRAND . ' — Curated one-page websites',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A gallery of one-pagers worth shipping',
                'OnePage Gallery curates the best single-page websites, templates, sections, and the tools their makers used to build them.',
            ),
            renderData: [
                'summary' => 'A warm, spacious gallery of curated one-page websites, the templates behind them, and the build tools their makers swear by.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'One Page Showcase',
                        heading: 'A showcase of one-pagers worth shipping',
                        summary: 'Browse hundreds of curated single-page sites, copy the templates that power them, and grab the exact tools their makers used. New work lands every week.',
                        primaryLabel: 'Browse the gallery',
                        primaryUrl: '#one-page-grid',
                        secondaryLabel: 'Submit a one-pager',
                        secondaryUrl: '#newsletter',
                        mediaUrl: $media['hero'][0] ?? null,
                        mediaAlt: 'A wall of curated one-page websites',
                    ),
                    $this->showcaseHeroSection(
                        heading: 'This week\'s featured one-pagers',
                        summary: 'Hand-picked launches that nail the single-scroll story — strong hero, honest proof, one clear call to action.',
                        media: $media,
                    ),
                    $this->categoryTabsSection(
                        heading: 'Browse by what you are building',
                        summary: 'Filter the gallery by the kind of one-pager you need, from solo launches to product waitlists.',
                    ),
                    $this->onePageGridSection(
                        heading: 'Fresh from the gallery',
                        summary: 'The newest one-pagers added by the community, with a note on why each one earned its place.',
                        media: $media,
                    ),
                    $this->templatesSectionsSection(
                        heading: 'Templates & sections to remix',
                        summary: 'Start from a proven layout instead of a blank canvas. Every template ships the sections that made the original work.',
                        media: $media,
                    ),
                    $this->toolsSponsorsSection(
                        heading: 'Tools the makers actually used',
                        summary: 'The builders, hosts, and helpers behind the one-pagers in the gallery.',
                    ),
                    $this->buildResourcesSection(
                        heading: 'Build resources & guides',
                        summary: 'Short, practical reads on shipping a single-page site that converts.',
                    ),
                    $this->proofSection(
                        heading: 'A gallery makers keep coming back to',
                        summary: 'What the showcase looks like after three years of curation.',
                    ),
                    $this->newsletterSection(
                        heading: 'Submit your one-pager',
                        summary: 'Built something worth showing? Send it in and join the weekly drop of the best new single-page sites.',
                    ),
                    $this->ctaSection(
                        heading: 'Start browsing the gallery',
                        summary: 'Open the full collection of curated one-pagers and find the layout your next launch deserves.',
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
            name: self::BRAND . ' Gallery',
            title: 'The gallery — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every curated one-pager in one place',
                'The full, scannable archive of single-page websites, filtered by category and sorted newest first.',
            ),
            renderData: [
                'summary' => 'The full archive of curated one-page websites — spacious cards, compact tags, and a category filter that keeps discovery legible.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The gallery',
                        heading: 'An archive of one-pagers built to be scanned',
                        summary: 'Hundreds of curated single-page sites, organised by category and intent. Filter, scan, and open the ones that fit your brief.',
                        primaryLabel: 'Submit a one-pager',
                        primaryUrl: '/#newsletter',
                        secondaryLabel: 'Browse templates',
                        secondaryUrl: '/#templates-sections',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0] ?? null,
                        mediaAlt: 'A grid of curated one-page websites',
                    ),
                    $this->categoryTabsSection(
                        heading: 'Filter the gallery',
                        summary: 'Spacious cards and compact tags keep the archive legible across every category.',
                    ),
                    $this->onePageGridSection(
                        heading: 'All one-pagers, newest first',
                        summary: 'Browse the complete collection. Each card carries the category, the maker, and why we added it.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Older entries and community submissions still worth a look.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Found a layout that fits?',
                        summary: 'Open the template behind any one-pager and start your own single-scroll story.',
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
            name: self::BRAND . ' Showcase',
            title: 'Harbor — a launch one-pager — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Harbor — a launch one-pager that converts',
                'A close look at a single-page launch site: the sections it uses, the tools behind it, and why it works.',
            ),
            renderData: [
                'summary' => 'A close look at the Harbor launch one-pager — the sections it leans on, the tools that built it, and the numbers it moved.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Showcase',
                        heading: 'Harbor — a launch one-pager that converts',
                        summary: 'A solo founder shipped this single-page launch site in a weekend. Here is the anatomy of the scroll, section by section.',
                        primaryLabel: 'Use this template',
                        primaryUrl: '#templates-sections',
                        secondaryLabel: 'Back to the gallery',
                        secondaryUrl: '/#one-page-grid',
                        mediaUrl: $media['detail'][0] ?? null,
                        mediaAlt: 'The Harbor launch one-pager',
                    ),
                    $this->showcaseHeroSection(
                        heading: 'How the Harbor scroll is built',
                        summary: 'Each block earns its place: a hero that states the promise, proof that backs it, and one undeniable call to action.',
                        media: $media,
                    ),
                    $this->templatesSectionsSection(
                        heading: 'The sections Harbor uses',
                        summary: 'Remix the exact blocks behind this one-pager for your own launch.',
                        media: $media,
                        url: '/#templates-sections',
                    ),
                    $this->toolsSponsorsSection(
                        heading: 'What built Harbor',
                        summary: 'The stack the maker reached for, from first draft to live launch.',
                    ),
                    $this->proofSection(
                        heading: 'What the launch moved',
                        summary: 'The numbers from Harbor\'s first thirty days.',
                    ),
                    $this->ctaSection(
                        heading: 'Build your own launch one-pager',
                        summary: 'Start from the Harbor template and ship a single-scroll site that does one job well.',
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
            title: 'Submit a one-pager — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit your one-pager',
                'Send in a single-page site you built or admire — every submission is reviewed by a real curator.',
            ),
            renderData: [
                'summary' => 'Send in a single-page site worth showing. Every submission is reviewed by a curator, and the best join the weekly gallery drop.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit',
                        heading: 'Reach the gallery through one warm path',
                        summary: 'Share the link, tell us what makes it work, and we will take it from there. We review every submission within two working days.',
                        primaryLabel: 'Send your link',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'See the gallery',
                        secondaryUrl: '#one-page-grid',
                        mediaUrl: $media['contact'][0] ?? null,
                        mediaAlt: 'Submitting a one-pager to the gallery',
                    ),
                    $this->newsletterSection(
                        heading: 'Send us your one-pager',
                        summary: 'Drop the URL and a sentence on why it earns a spot. A curator reads every one.',
                    ),
                    $this->proofSection(
                        heading: 'What happens after you submit',
                        summary: 'How a submission becomes a featured entry.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to browse first?',
                        summary: 'See the kind of work that gets featured before you send yours in.',
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
                'No one-pagers match that filter yet',
                'A graceful empty state for a gallery filter with no matching single-page sites.',
            ),
            renderData: [
                'summary' => 'No one-pagers match that filter yet — but the gallery can still point you somewhere worth a scroll.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Gallery',
                        heading: 'No one-pagers match that filter — yet',
                        summary: 'Nothing has landed in this category so far. Clear the filter to see everything, or tell us what you are hunting for.',
                        primaryLabel: 'View the whole gallery',
                        primaryUrl: '/#one-page-grid',
                        secondaryLabel: 'Submit a one-pager',
                        secondaryUrl: '/#newsletter',
                        mediaUrl: $media['listing'][0] ?? null,
                        mediaAlt: 'An empty gallery filter',
                    ),
                    $this->categoryTabsSection(
                        heading: 'Try another category',
                        summary: 'Jump to a category with fresh one-pagers waiting.',
                    ),
                    $this->toolsSponsorsSection(
                        heading: 'While you are here',
                        summary: 'The build tools makers reach for most across the gallery.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the brief and we will surface the closest one-pagers from the archive.',
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
                'This page scrolled off',
                'A not-found page that routes visitors back into the gallery and submission paths.',
            ),
            renderData: [
                'summary' => 'That page scrolled off — here is the way back into the gallery.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This page scrolled off the gallery',
                        summary: 'The link is broken or the one-pager has moved. Head back to the collection, or submit a site of your own.',
                        primaryLabel: 'Back to home',
                        primaryUrl: '/',
                        secondaryLabel: 'Browse the gallery',
                        secondaryUrl: '/#one-page-grid',
                        mediaUrl: $media['hero'][0] ?? null,
                        mediaAlt: 'A missing page in the gallery',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us which one-pager you wanted and we will point you to it.',
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
            name: self::BRAND . ' Join',
            title: 'Join the weekly drop — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Get the best one-pagers every week',
                'A focused conversion page inviting makers to join the weekly gallery drop.',
            ),
            renderData: [
                'summary' => 'Get the best new one-pagers, templates, and build tools in your inbox every week.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Join the drop',
                        heading: 'The best one-pagers, in your inbox weekly',
                        summary: 'One email a week: the freshest single-page sites, the templates behind them, and the tools that built them. No noise.',
                        primaryLabel: 'Join the weekly drop',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Browse the gallery',
                        secondaryUrl: '/#one-page-grid',
                        mediaUrl: $media['cta'][0] ?? null,
                        mediaAlt: 'The weekly gallery drop',
                    ),
                    $this->proofSection(
                        heading: 'Why makers subscribe',
                        summary: 'What the weekly drop has built over three years.',
                    ),
                    $this->newsletterSection(
                        heading: 'Join the weekly drop',
                        summary: 'Drop your email and get the next edition of curated one-pagers this week.',
                    ),
                    $this->ctaSection(
                        heading: 'One scroll away',
                        summary: 'Open the gallery and find the one-pager your next launch deserves.',
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
        string $mediaAlt,
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
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function showcaseHeroSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['hero'], $media['detail'], $media['cta'])));

        $entries = [
            ['title' => 'Harbor', 'summary' => 'A weekend launch one-pager for a solo founder — promise, proof, one button.'],
            ['title' => 'Northpine Studio', 'summary' => 'A single-scroll portfolio that lets three case studies carry the whole story.'],
            ['title' => 'Cadence', 'summary' => 'A waitlist one-pager that turned a vague idea into 4,000 early signups.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '/#one-page-grid',
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'] . ' one-pager',
            ];
        }

        return [
            'type' => 'showcase-hero',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryTabsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'category-tabs',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Launch pages', 'summary' => 'Single-scroll sites built to ship a product on day one.', 'url' => '/#one-page-grid'],
                ['title' => 'Portfolios', 'summary' => 'One-pagers that let the work do the talking.', 'url' => '/#one-page-grid'],
                ['title' => 'Waitlists', 'summary' => 'Pre-launch pages tuned to capture early interest.', 'url' => '/#one-page-grid'],
                ['title' => 'Events', 'summary' => 'Single-page sites for conferences, meetups, and drops.', 'url' => '/#one-page-grid'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function onePageGridSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $entries = [
            ['title' => 'Harbor', 'meta' => 'Launch page', 'summary' => 'A solo founder\'s weekend launch site — one promise, one button, and proof in between.'],
            ['title' => 'Northpine Studio', 'meta' => 'Portfolio', 'summary' => 'A single-scroll portfolio that lets three case studies do the convincing.'],
            ['title' => 'Cadence', 'meta' => 'Waitlist', 'summary' => 'A pre-launch page that turned a one-line idea into four thousand signups.'],
            ['title' => 'Field Notes Conf', 'meta' => 'Event', 'summary' => 'A one-page conference site with the schedule, speakers, and tickets in one scroll.'],
            ['title' => 'Mossback Coffee', 'meta' => 'Local business', 'summary' => 'A neighbourhood roaster\'s single-page site, menu and map included.'],
            ['title' => 'Relay', 'meta' => 'Launch page', 'summary' => 'A developer-tool launch page that ships its docs link above the fold.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#one-page-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'] . ' one-pager',
            ];
        }

        return [
            'type' => 'one-page-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function templatesSectionsSection(string $heading, string $summary, array $media, string $url = '#one-page-grid'): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Launch starter', 'summary' => 'Hero, feature trio, proof, and a closing CTA — the bones of a converting launch page.'],
            ['title' => 'Portfolio scroll', 'summary' => 'A case-study-first layout that puts three projects front and centre.'],
            ['title' => 'Waitlist capture', 'summary' => 'A focused single-field signup with proof and a clear promise above the fold.'],
            ['title' => 'Event single-page', 'summary' => 'Schedule, speakers, venue, and tickets stacked into one clean scroll.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$entry,
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'] . ' template preview',
            ];
        }

        return [
            'type' => 'templates-sections',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Browse all templates',
            'url' => $url,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toolsSponsorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'tools-sponsors',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Framer', 'meta' => 'Builder', 'summary' => 'The no-code builder behind a third of the launch pages in the gallery.'],
                ['title' => 'Cal.com', 'meta' => 'Booking', 'summary' => 'Drop-in scheduling that turns a one-pager into a booking funnel.'],
                ['title' => 'Plausible', 'meta' => 'Analytics', 'summary' => 'Lightweight, privacy-first analytics makers trust on single-page sites.'],
                ['title' => 'Resend', 'meta' => 'Email', 'summary' => 'The email layer powering waitlist confirmations across the gallery.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResourcesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'build-resources',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Anatomy of a converting one-pager', 'summary' => 'The five blocks every strong single-page site shares, and why order matters.'],
                ['title' => 'Writing a hero that earns the scroll', 'summary' => 'How to state one promise clearly enough that nobody bounces.'],
                ['title' => 'One CTA, said three ways', 'summary' => 'Repeating a single call to action without nagging the reader.'],
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
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['value' => '600+', 'label' => 'One-pagers curated since 2022'],
                ['value' => 'Weekly', 'label' => 'Fresh drop of the best new work'],
                ['value' => '2 days', 'label' => 'Review time on every submission'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Glasshouse', 'category' => 'Portfolio', 'summary' => 'An architecture studio\'s single-scroll site that lets the buildings carry it.'],
            ['title' => 'Tideline', 'category' => 'Launch page', 'summary' => 'A surf-forecast app launch page with the download button always in reach.'],
            ['title' => 'Quarter', 'category' => 'Waitlist', 'summary' => 'A budgeting-tool waitlist that grew to ten thousand before launch day.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
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
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Browse the gallery',
            'url' => '/#one-page-grid',
            'actions' => [
                ['label' => 'Browse the gallery', 'url' => '/#one-page-grid', 'style' => 'primary'],
                ['label' => 'Submit a one-pager', 'url' => '/#newsletter', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        $items = [
            ['label' => 'One-pagers', 'url' => '/#one-page-grid'],
            ['label' => 'Templates', 'url' => '/#templates-sections'],
            ['label' => 'Tools', 'url' => '/#tools-sponsors'],
            ['label' => 'Resources', 'url' => '/#build-resources'],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => $items,
            'consultationUrl' => '/#newsletter',
            'ctaLabel' => 'Submit a one-pager',
            'ctaUrl' => '/#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Gallery',
                'heading' => 'Gallery',
                'links' => [
                    ['label' => 'All one-pagers', 'url' => '/#one-page-grid'],
                    ['label' => 'Categories', 'url' => '/#category-tabs'],
                    ['label' => 'This week', 'url' => '/#showcase-hero'],
                ],
            ],
            [
                'title' => 'Build',
                'heading' => 'Build',
                'links' => [
                    ['label' => 'Templates', 'url' => '/#templates-sections'],
                    ['label' => 'Tools', 'url' => '/#tools-sponsors'],
                    ['label' => 'Resources', 'url' => '/#build-resources'],
                ],
            ],
            [
                'title' => 'Connect',
                'heading' => 'Connect',
                'links' => [
                    ['label' => 'Submit a one-pager', 'url' => '/#newsletter'],
                    ['label' => 'Weekly drop', 'url' => '/#newsletter'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A curated gallery of the best one-page websites, the templates behind them, and the tools that built them.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
