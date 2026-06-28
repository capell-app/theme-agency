<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumPortfolioCollection\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Premium Portfolio Collection theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature editorial renderers (featured-portfolios /
 * filter-taxonomies / portfolio-grid / awarded-profiles / creator-directory /
 * education-upsell / newsletter) alongside the standard hero/proof/cta — giving
 * every surface a full, curated gallery site rather than a sparse skeleton.
 */
final class PremiumPortfolioCollectionDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Index Gallery';

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
            title: self::BRAND . ' — A Curated Index of Award-Winning Portfolios',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A curated index of the work worth studying',
                'Index Gallery is an editor-led showcase of award-winning portfolios across product, brand, motion, and craft.',
            ),
            renderData: [
                'summary' => 'Index Gallery is an editor-led collection of award-winning portfolios. Browse featured work, filter by discipline, and follow the creators setting the bar.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'The portfolios worth studying, in one curated index',
                        'summary' => 'Every entry is hand-picked and award-vetted. Browse featured collections, filter by craft, and follow the designers, studios, and engineers shaping standout work.',
                        'primary_label' => 'Browse the collection',
                        'primary_url' => '#portfolios',
                        'secondary_label' => 'See this year\'s winners',
                        'secondary_url' => '#winners',
                        'header_image_url' => $media['hero'][0],
                        'header_image_alt' => 'Editorial spread of featured portfolio covers',
                        'notes' => [
                            'Hand-picked by working editors, not an open submission feed.',
                            'Each entry carries creator metadata, awards, and a collection note.',
                            'New featured work lands every Thursday morning.',
                        ],
                    ],
                    $this->featuredPortfoliosSection(),
                    $this->filterTaxonomiesSection(),
                    $this->portfolioGridSection(),
                    $this->awardedProfilesSection(),
                    $this->creatorDirectorySection(),
                    $this->educationUpsellSection(),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Made something worth indexing?',
                        summary: 'Submit a portfolio for editorial review. We read every submission and reply within five working days.',
                        label: 'Submit your work',
                        url: '#submit',
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
            name: self::BRAND . ' Collection',
            title: 'The Collection — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full collection, filtered your way',
                'Browse the complete index of featured portfolios across product design, brand, motion, and engineering craft.',
            ),
            renderData: [
                'summary' => 'The full index of featured portfolios. Filter by discipline, award, or year, and open any entry for the collection note and creator profile.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Every featured portfolio, filtered your way',
                        'summary' => 'Sixty-plus curated collections across product, brand, motion, and craft. Narrow by discipline and award, or open an entry for the full editorial note.',
                        'primary_label' => 'Clear filters',
                        'primary_url' => '#portfolios',
                        'secondary_label' => 'Sort by latest',
                        'secondary_url' => '#latest',
                        'header_image_url' => $media['listing'][0] ?? $media['hero'][0],
                        'header_image_alt' => 'Grid of curated portfolio thumbnails',
                    ],
                    $this->filterTaxonomiesSection(),
                    $this->portfolioGridSection(),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want the weekly shortlist instead?',
                        summary: 'Subscribers get the five most notable new portfolios each Thursday, with the editor\'s note on why each one made the cut.',
                        label: 'Get the weekly shortlist',
                        url: '#subscribe',
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
            name: self::BRAND . ' Portfolio',
            title: 'Marlow Reade — Portfolio — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Marlow Reade — a portfolio built around the work',
                'How an independent product designer turned five shipped projects into a portfolio that reads like a case-study magazine.',
            ),
            renderData: [
                'summary' => 'A featured portfolio entry: Marlow Reade, independent product designer, with the collection note, awarded work, and the studios that hire them.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Marlow Reade — a portfolio built around the work',
                        'summary' => 'An independent product designer whose case studies read like a magazine. Five shipped projects, two industry awards, and a through-line of clarity under constraint.',
                        'primary_label' => 'Visit the live portfolio',
                        'primary_url' => '#live',
                        'secondary_label' => 'Back to the collection',
                        'secondary_url' => '#portfolios',
                        'header_image_url' => $media['detail'][0],
                        'header_image_alt' => 'Cover image from the Marlow Reade portfolio',
                        'notes' => [
                            'Discipline — Product design & design systems.',
                            'Recognition — D&AD Wood Pencil, Awwwards Site of the Day.',
                            'Based in Lisbon, working with teams across Europe.',
                        ],
                    ],
                    $this->awardedProfilesSection(
                        heading: 'Awarded work in this portfolio',
                        summary: 'The three projects that earned this entry its place in the index.',
                    ),
                    $this->featuredPortfoliosSection(
                        heading: 'Why the editors picked it',
                        summary: 'What this portfolio does that most do not.',
                    ),
                    $this->creatorDirectorySection(
                        heading: 'Studios that have hired Marlow',
                        summary: 'A short list of the teams behind the shipped work.',
                    ),
                    $this->ctaSection(
                        heading: 'Following Marlow\'s work?',
                        summary: 'Subscribe to the index and we will tell you when this creator publishes a new project or moves studios.',
                        label: 'Follow this creator',
                        url: '#follow',
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
            title: 'Submit your portfolio — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit your portfolio for review',
                'Send us a link. Our editors read every submission and reply within five working days with a decision and notes.',
            ),
            renderData: [
                'summary' => 'Submit a portfolio for editorial review. We read every link, weigh it against the collection, and reply within five working days.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Submit your portfolio for review',
                        'summary' => 'No fees, no open feed. Send a link and our editors read every submission, weigh it against the live collection, and reply within five working days.',
                        'primary_label' => 'Email the editors',
                        'primary_url' => 'mailto:editors@indexgallery.example',
                        'secondary_label' => 'Read the criteria',
                        'secondary_url' => '#criteria',
                        'header_image_url' => $media['contact'][0],
                        'header_image_alt' => 'The Index Gallery editorial desk',
                    ],
                    $this->featuredPortfoliosSection(
                        heading: 'What we look for',
                        summary: 'Three things every featured portfolio gets right.',
                    ),
                    $this->newsletterSection(),
                    $this->proofSection(
                        heading: 'How review works',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to submit?',
                        summary: 'Send the link and a one-line note on what you are most proud of. We take it from there.',
                        label: 'Send your portfolio',
                        url: 'mailto:editors@indexgallery.example',
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
            name: self::BRAND . ' No Matches',
            title: 'No matching portfolios — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No portfolios match that filter — yet',
                'A graceful empty state for a filtered collection with no matching entries.',
            ),
            renderData: [
                'summary' => 'No portfolios match that combination of filters yet — but the index can still point you somewhere worth looking.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'No portfolios match that filter — yet',
                        'summary' => 'Nothing in the index fits that exact combination of discipline and award. Clear a filter to widen the search, or browse this week\'s featured picks below.',
                        'primary_label' => 'Clear all filters',
                        'primary_url' => '#portfolios',
                        'secondary_label' => 'Browse featured',
                        'secondary_url' => '#featured',
                    ],
                    $this->filterTaxonomiesSection(
                        heading: 'Try a broader discipline',
                    ),
                    $this->featuredPortfoliosSection(
                        heading: 'While you are here',
                        summary: 'Three featured portfolios the editors keep returning to.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the discipline or studio you have in mind and we will surface the closest entries from the archive.',
                        label: 'Ask the editors',
                        url: 'mailto:editors@indexgallery.example',
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
                'That entry is no longer in the index',
                'A not-found page that routes visitors back into the collection and featured work.',
            ),
            renderData: [
                'summary' => 'That page has moved or the portfolio left the index — here is the way back to the work.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'That entry is no longer in the index',
                        'summary' => 'The link is broken or the portfolio has been retired from the collection. Head back to the featured work, or browse the full index.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Browse the collection',
                        'secondary_url' => '#portfolios',
                    ],
                    $this->featuredPortfoliosSection(
                        heading: 'Featured instead',
                        summary: 'Three entries the editors are championing this week.',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for a particular portfolio?',
                        summary: 'Tell us the creator or studio and we will point you to the right entry, or let you know if it has moved on.',
                        label: 'Ask the editors',
                        url: 'mailto:editors@indexgallery.example',
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
            name: self::BRAND . ' Membership',
            title: 'Join the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Get the work worth studying, every week',
                'A focused conversion page inviting readers to join the membership and follow the creators they care about.',
            ),
            renderData: [
                'summary' => 'Join Index Gallery membership for the weekly shortlist, full creator profiles, and early access to award announcements.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Get the work worth studying, every week',
                        'summary' => 'Membership unlocks the full creator profiles, the weekly editor\'s shortlist, and early access to award announcements — for the price of a coffee a month.',
                        'primary_label' => 'Start membership',
                        'primary_url' => '#join',
                        'secondary_label' => 'See what\'s included',
                        'secondary_url' => '#included',
                        'header_image_url' => $media['cta'][0],
                        'header_image_alt' => 'Index Gallery membership preview',
                    ],
                    $this->educationUpsellSection(),
                    $this->proofSection(
                        heading: 'Why readers join',
                    ),
                    $this->ctaSection(
                        heading: 'One membership, the whole index',
                        summary: 'Cancel anytime. Keep every saved portfolio and creator you follow if you do.',
                        label: 'Start membership',
                        url: '#join',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function featuredPortfoliosSection(
        string $heading = 'This week\'s featured collections',
        string $summary = 'Three portfolios our editors are championing right now, across product, brand, and motion.',
    ): array {
        return [
            'type' => 'featured-portfolios',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Halden Studio — product design', 'summary' => 'A systems-first portfolio that shows the thinking behind every shipped screen, not just the final frame.'],
                ['title' => 'Ren Okabe — brand & type', 'summary' => 'Identity work for cultural institutions, presented as a quiet, confident editorial sequence.'],
                ['title' => 'Field Notes Motion — animation', 'summary' => 'A motion reel that earns its loops, with breakdowns of the rigs and timing behind each cut.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filterTaxonomiesSection(
        string $heading = 'Browse by discipline',
    ): array {
        return [
            'type' => 'filter-taxonomies',
            'heading' => $heading,
            'items' => [
                ['title' => 'Product design', 'summary' => 'App and web interfaces, design systems, and the case studies behind them.'],
                ['title' => 'Brand & identity', 'summary' => 'Logos, type, and visual systems for studios, startups, and cultural work.'],
                ['title' => 'Motion & 3D', 'summary' => 'Reels, rigs, and rendered work with the breakdowns that explain them.'],
                ['title' => 'Engineering & craft', 'summary' => 'Creative developers and the interaction work that lives in the browser.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function portfolioGridSection(
        string $heading = 'Recently added to the index',
        string $summary = 'The latest entries to clear editorial review, newest first.',
    ): array {
        return [
            'type' => 'portfolio-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Lume — design systems lead', 'summary' => 'A portfolio organised around tokens and outcomes rather than screenshots.', 'meta' => 'Product design', 'care_note' => 'Added 2 days ago'],
                ['title' => 'Atelier Voss — brand', 'summary' => 'Heritage identity work for a third-generation furniture maker.', 'meta' => 'Brand & identity', 'care_note' => 'Added 4 days ago'],
                ['title' => 'Nori Tanaka — interaction', 'summary' => 'WebGL experiments that ship as production marketing sites.', 'meta' => 'Engineering & craft', 'care_note' => 'Added 6 days ago'],
                ['title' => 'Studio Bask — motion', 'summary' => 'Broadcast-grade title sequences for independent film.', 'meta' => 'Motion & 3D', 'care_note' => 'Added last week'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function awardedProfilesSection(
        string $heading = 'This year\'s award winners',
        string $summary = 'Portfolios recognised by D&AD, Awwwards, and the FWA, now indexed in full.',
    ): array {
        return [
            'type' => 'awarded-profiles',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'See all winners',
            'url' => '#winners',
            'items' => [
                ['title' => 'Meridian rebrand — D&AD Yellow Pencil', 'summary' => 'A fintech identity that scaled from pitch deck to product without losing its nerve.'],
                ['title' => 'Northwind platform — Awwwards Site of the Year', 'summary' => 'A renewables tool field engineers actually enjoy opening every morning.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function creatorDirectorySection(
        string $heading = 'Creators worth following',
        string $summary = 'The designers, studios, and developers whose new work we surface the moment it lands.',
    ): array {
        return [
            'type' => 'creator-directory',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Product design', 'title' => 'Halden Studio', 'summary' => 'A two-person studio shipping systems work for health and finance teams.'],
                ['meta' => 'Brand & type', 'title' => 'Ren Okabe', 'summary' => 'Independent identity designer for museums, festivals, and small presses.'],
                ['meta' => 'Motion & 3D', 'title' => 'Field Notes Motion', 'summary' => 'A motion collective with a reputation for breakdowns as good as the work.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function educationUpsellSection(): array
    {
        return [
            'type' => 'education-upsell',
            'heading' => 'Learn from the portfolios you admire',
            'summary' => 'Members get the long-form interviews and process teardowns behind the featured work.',
            'items' => [
                ['title' => 'Process teardowns', 'summary' => 'Step-by-step breakdowns of how a featured project moved from brief to launch.'],
                ['title' => 'Creator interviews', 'summary' => 'Honest conversations about pricing, rejection, and the projects that did not work out.'],
                ['title' => 'Portfolio clinics', 'summary' => 'Monthly live reviews where editors critique reader submissions on the record.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(): array
    {
        return [
            'type' => 'newsletter',
            'heading' => 'The Thursday shortlist',
            'summary' => 'Five notable new portfolios in your inbox every week, with the editor\'s note on why each one earned its place.',
            'action' => '#subscribe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(
        string $heading = 'A collection readers trust',
    ): array {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'items' => [
                ['value' => '420+', 'label' => 'Portfolios indexed since 2019'],
                ['value' => '5 days', 'label' => 'Average review turnaround'],
                ['value' => '38k', 'label' => 'Readers on the Thursday shortlist'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Verda — architecture practice', 'category' => 'Brand & identity', 'summary' => 'An editorial portfolio that lets the buildings carry the page.'],
            ['title' => 'Kindred — community app', 'category' => 'Product design', 'summary' => 'A case study on designing trust into a peer lending product.'],
            ['title' => 'Foundry — design system', 'category' => 'Engineering & craft', 'summary' => 'How one component library unified four product teams on a shared language.'],
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
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, string $label, string $url): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'label' => $label,
            'url' => $url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Collection', 'url' => '#portfolios'],
                ['label' => 'Winners', 'url' => '#winners'],
                ['label' => 'Creators', 'url' => '#creators'],
                ['label' => 'Learn', 'url' => '#learn'],
                ['label' => 'Submit', 'url' => '#submit'],
            ],
            'ctaLabel' => 'Join membership',
            'ctaUrl' => '#join',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'An editor-led index of award-winning portfolios. Curated weekly, member-supported.',
            'columns' => [
                [
                    'heading' => 'Browse',
                    'title' => 'Browse',
                    'links' => [
                        ['label' => 'The collection', 'url' => '#portfolios'],
                        ['label' => 'Award winners', 'url' => '#winners'],
                        ['label' => 'Creators', 'url' => '#creators'],
                        ['label' => 'Latest entries', 'url' => '#latest'],
                    ],
                ],
                [
                    'heading' => 'Members',
                    'title' => 'Members',
                    'links' => [
                        ['label' => 'Join membership', 'url' => '#join'],
                        ['label' => 'Process teardowns', 'url' => '#learn'],
                        ['label' => 'Portfolio clinics', 'url' => '#clinics'],
                        ['label' => 'The Thursday shortlist', 'url' => '#subscribe'],
                    ],
                ],
                [
                    'heading' => 'Index Gallery',
                    'title' => 'Index Gallery',
                    'links' => [
                        ['label' => 'Submit your work', 'url' => '#submit'],
                        ['label' => 'Editorial criteria', 'url' => '#criteria'],
                        ['label' => 'editors@indexgallery.example', 'url' => 'mailto:editors@indexgallery.example'],
                    ],
                ],
            ],
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
