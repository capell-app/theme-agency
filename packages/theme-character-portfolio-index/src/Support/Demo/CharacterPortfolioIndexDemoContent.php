<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CharacterPortfolioIndex\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Character Portfolio Index theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature editorial renderers (showcase-headline /
 * category-tabs / curated-grid / standout-notes / related-recommendations /
 * creator-summary / newsletter) alongside the standard hero/proof/cta — giving
 * every surface a full, individual portfolio-index site rather than the shared
 * five-section skeleton. Every card that ships with a URL carries a real
 * photograph sourced from ThemeDemoMedia, and every action anchor resolves to
 * a section id that actually renders on that surface (or a real page path).
 */
final class CharacterPortfolioIndexDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Index & Co.';

    private const string EDITORS_EMAIL = 'editors@index-and-co.example';

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
            title: self::BRAND . ' — A curated index of portfolios',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A curated index of portfolios worth bookmarking',
                'Index & Co. is an editorial directory of personal, studio, design, development, and video portfolios — read with standout notes and creator context.',
            ),
            renderData: [
                'summary' => 'Index & Co. is an editorial index of the portfolios we keep coming back to, across personal sites, studios, design, development, and motion work.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Character Portfolio Index',
                        heading: 'A curated index of portfolios worth bookmarking',
                        summary: 'An editorial directory of personal, studio, design, development, and video portfolios — each entry carries a standout note, a creator summary, and related work worth a look.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'A designer reviewing a portfolio layout at a studio desk',
                        primaryLabel: 'Browse portfolios',
                        primaryUrl: '#curated-grid',
                        secondaryLabel: 'Join the index',
                        secondaryUrl: '#newsletter',
                        notes: [
                            'Hand-picked, never auto-scraped — every entry earns its place.',
                            'Standout notes call out the one detail that made each portfolio stick.',
                            'New entries land every Thursday in the subscriber digest.',
                        ],
                    ),
                    $this->showcaseHeadlineSection(
                        heading: 'A featured portfolio that reads like an editorial spread',
                        summary: 'The week\'s standout pairs curated work with creator context, so you can size up the maker at a glance.',
                        media: $media,
                    ),
                    $this->categoryTabsSection(
                        heading: 'Browse the index by discipline',
                        summary: 'Personal sites, studios, design, development, and motion — the categories we sort every entry into.',
                    ),
                    $this->curatedGridSection(
                        heading: 'This week\'s curated portfolios',
                        summary: 'Three entries earning a place in the index right now, with the detail that earned each one a feature.',
                        media: $media,
                        withUrls: true,
                    ),
                    $this->standoutNotesSection(
                        heading: 'Standout notes from the editors',
                        summary: 'The small craft decisions we flagged while curating this week — the reasons these portfolios stop a scroll.',
                        media: $media,
                        url: '#curated-grid',
                    ),
                    $this->relatedRecommendationsSection(
                        heading: 'Related portfolios you might have missed',
                        summary: 'If a featured entry caught your eye, these sit in the same neighbourhood.',
                        media: $media,
                    ),
                    $this->creatorSummarySection(
                        heading: 'The creators behind this week\'s index',
                        summary: 'A short read on the makers — who they are, what they ship, and where to find more.',
                        media: $media,
                    ),
                    $this->proofSection(
                        heading: 'A directory built on judgement',
                        summary: 'How the index has grown since we started curating.',
                    ),
                    $this->newsletterSection(
                        heading: 'Get the curated index in your inbox',
                        summary: 'One email a week: the new entries, the standout notes, and the creators worth following.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a portfolio worth indexing?',
                        summary: 'Submit your own work or nominate a portfolio you admire. We review every entry by hand.',
                        primaryUrl: '#newsletter',
                        secondaryUrl: '#curated-grid',
                        secondaryLabel: 'See what gets featured',
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
            name: self::BRAND . ' Browse',
            title: 'Browse the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse the full portfolio index',
                'A scannable listing of every curated portfolio, sorted by discipline, medium, and creator.',
            ),
            renderData: [
                'summary' => 'The full index, built to be scanned — filter by discipline and read the standout note before you click through.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Browse the index',
                        heading: 'Every curated portfolio, in one scannable index',
                        summary: 'Sort by discipline, medium, or creator. Each card keeps the standout note and creator context legible before you ever leave the page.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'A scannable grid of portfolio thumbnails laid out on a light table',
                        primaryLabel: 'Jump to the grid',
                        primaryUrl: '#curated-grid',
                        secondaryLabel: 'Join the index',
                        secondaryUrl: '#newsletter',
                        notes: [
                            'Filters stay sticky as you scan — discipline, medium, and recency.',
                            'Every card carries the editor\'s standout note up front.',
                        ],
                    ),
                    $this->categoryTabsSection(
                        heading: 'Filter the index by discipline',
                        summary: 'Category tabs keep disciplines, mediums, and creators legible without losing the editorial frame.',
                    ),
                    $this->curatedGridSection(
                        heading: 'Curated portfolios, newest first',
                        summary: 'The current shortlist, each with the detail that earned it a place in the index.',
                        media: $media,
                        withUrls: true,
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Older entries and honourable mentions, still worth a click.',
                        media: $media,
                    ),
                    $this->newsletterSection(
                        heading: 'Get new entries before they hit the archive',
                        summary: 'Subscribe and every Thursday\'s curated additions land in your inbox first.',
                    ),
                    $this->ctaSection(
                        heading: 'Spotted a gap in the index?',
                        summary: 'Nominate a portfolio we have missed — we review every suggestion by hand.',
                        primaryUrl: '#newsletter',
                        secondaryUrl: '#curated-grid',
                        secondaryLabel: 'See the current shortlist',
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
            name: self::BRAND . ' Entry',
            title: 'Mara Quinn — Portfolio entry — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Mara Quinn — a motion portfolio that earns the loop',
                'Why we featured Mara Quinn\'s motion-design portfolio: a single looping reel that says everything before you scroll.',
            ),
            renderData: [
                'summary' => 'A full entry for one featured portfolio — the standout note, the creator summary, and the related work we lined up beside it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Featured entry',
                        heading: 'Mara Quinn — a motion portfolio that earns the loop',
                        summary: 'A motion designer whose entire portfolio is one looping reel and three lines of copy. We featured it for the restraint as much as the craft.',
                        media: $media,
                        mediaKey: 'detail',
                        mediaAlt: 'A still frame from Mara Quinn\'s looping motion reel',
                        primaryLabel: 'Read the standout note',
                        primaryUrl: '#standout-notes',
                        secondaryLabel: 'About the creator',
                        secondaryUrl: '#creator-summary',
                        notes: [
                            'Discipline: motion design and direction.',
                            'Standout: a single autoplaying reel doing all the talking.',
                        ],
                    ),
                    $this->standoutNotesSection(
                        heading: 'Why this entry made the index',
                        summary: 'The editorial read on Mara Quinn\'s portfolio — the choices that turned a reel into a statement.',
                        media: $media,
                        url: '#creator-summary',
                    ),
                    $this->creatorSummarySection(
                        heading: 'About the creator',
                        summary: 'Who Mara is, what she ships, and where her work has run.',
                        media: $media,
                    ),
                    $this->relatedRecommendationsSection(
                        heading: 'Portfolios in the same vein',
                        summary: 'If Mara\'s reel landed, these motion and direction entries sit close by.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want your portfolio read like this?',
                        summary: 'Submit your work and an editor will give it the same close read.',
                        primaryUrl: 'mailto:' . self::EDITORS_EMAIL,
                        primaryLabel: 'Submit your portfolio',
                        secondaryUrl: '#related-recommendations',
                        secondaryLabel: 'See similar entries',
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
            title: 'Submit a portfolio — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit a portfolio to the index',
                'Send us your own portfolio or nominate one you admire — every submission gets a human review.',
            ),
            renderData: [
                'summary' => 'Submit your portfolio or nominate one you admire. There is one curated path in, and a person reads every entry.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit',
                        heading: 'Join the index through one curated path',
                        summary: 'No forms with twenty fields. Email the URL, the discipline, and why it stands out — an editor takes it from there, usually within a week.',
                        media: $media,
                        mediaKey: 'contact',
                        mediaAlt: 'An editor reviewing a submitted portfolio on a laptop',
                        primaryLabel: 'Email the editors',
                        primaryUrl: 'mailto:' . self::EDITORS_EMAIL,
                        secondaryLabel: 'Subscribe instead',
                        secondaryUrl: '#newsletter',
                        notes: [
                            'One reviewer reads every submission — no auto-rejections.',
                            'Nominate someone else\'s work just as easily as your own.',
                        ],
                    ),
                    $this->newsletterSection(
                        heading: 'Subscribe, then submit',
                        summary: 'Join the weekly index and reply to any issue with the portfolio you want considered.',
                    ),
                    $this->proofSection(
                        heading: 'What submitting looks like',
                        summary: 'The numbers behind how we review and feature entries.',
                    ),
                    $this->creatorSummarySection(
                        heading: 'Who we tend to feature',
                        summary: 'The kinds of makers the index keeps coming back to.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Ready to be considered?',
                        summary: 'Send the link and a line on why it stands out. We reply to every submission.',
                        primaryUrl: 'mailto:' . self::EDITORS_EMAIL,
                        primaryLabel: 'Email the editors',
                        secondaryUrl: '#newsletter',
                        secondaryLabel: 'Subscribe to the index',
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
            title: 'No matches — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No portfolios match that filter yet',
                'A graceful empty state for a filtered index view with no matching entries.',
            ),
            renderData: [
                'summary' => 'No entries match that filter yet — but the index can still point you somewhere worth a look.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Index search',
                        heading: 'No portfolios match that filter — yet',
                        summary: 'We have not indexed work that fits this combination. Clear the filter to see everything, or browse by discipline instead.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: null,
                        primaryLabel: 'Clear filters',
                        primaryUrl: '/theme-' . $themeKey . '-directory',
                        secondaryLabel: 'Browse by discipline',
                        secondaryUrl: '#category-tabs',
                        notes: [
                            'Filters narrowed the index to zero entries.',
                            'Widen the discipline or recency window to see more.',
                        ],
                    ),
                    $this->contentListingSection(
                        heading: 'Nothing here right now',
                        summary: 'When entries match this filter they will appear here, newest first.',
                        media: $media,
                        items: [],
                    ),
                    $this->categoryTabsSection(
                        heading: 'Try another discipline',
                        summary: 'The categories with the most entries waiting to be browsed.',
                    ),
                    $this->ctaSection(
                        heading: 'Hunting for something specific?',
                        summary: 'Email us the discipline or maker and we will point you to the closest entries.',
                        primaryUrl: 'mailto:' . self::EDITORS_EMAIL,
                        primaryLabel: 'Ask the editors',
                        secondaryUrl: '#category-tabs',
                        secondaryLabel: 'Browse categories',
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
                'That entry has moved',
                'A not-found page that routes visitors back into the curated index and submission paths.',
            ),
            renderData: [
                'summary' => 'That entry has moved or was never indexed — here is the way back into the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That entry slipped out of the index',
                        summary: 'The link is broken or the portfolio was pulled. Head back home, or browse the full index by discipline.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: null,
                        primaryLabel: 'Back to home',
                        primaryUrl: '/',
                        secondaryLabel: 'Browse the index',
                        secondaryUrl: '/theme-' . $themeKey . '-directory',
                        notes: [
                            'Bookmarks to pulled entries land here.',
                            'The full index always has a fresh shortlist.',
                        ],
                    ),
                    $this->categoryTabsSection(
                        heading: 'Find your way back by discipline',
                        summary: 'Pick a category and pick up the index where you left off.',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for an entry?',
                        summary: 'Email us which portfolio you were after and we will track down the new link.',
                        primaryUrl: 'mailto:' . self::EDITORS_EMAIL,
                        primaryLabel: 'Ask the editors',
                        secondaryUrl: '/',
                        secondaryLabel: 'Back to home',
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
            title: 'Join the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Get the curated portfolio index every week',
                'A focused conversion page inviting visitors to subscribe and submit to the index.',
            ),
            renderData: [
                'summary' => 'Get the curated portfolio index every week, and put your own work in front of the editors.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Join the index',
                        heading: 'Get the curated portfolio index every week',
                        summary: 'One email, every Thursday: the new entries, the standout notes, and the creators worth following. Free, and easy to leave.',
                        media: $media,
                        mediaKey: 'cta',
                        mediaAlt: 'A weekly digest email open beside a stack of portfolio printouts',
                        primaryLabel: 'Subscribe now',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Why readers stay',
                        secondaryUrl: '#proof',
                        notes: [
                            'No more than one email a week — ever.',
                            'Reply to any issue to submit or nominate a portfolio.',
                        ],
                    ),
                    $this->proofSection(
                        heading: 'Why readers stay subscribed',
                        summary: 'The numbers behind the weekly index.',
                    ),
                    $this->newsletterSection(
                        heading: 'Join thousands of curators and makers',
                        summary: 'Subscribe once and the curated index lands in your inbox every Thursday.',
                    ),
                    $this->ctaSection(
                        heading: 'One subscribe away',
                        summary: 'Add your email and the next curated index is on its way.',
                        primaryUrl: '#newsletter',
                        secondaryUrl: '/',
                        secondaryLabel: 'Back to home',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @param  list<string>  $notes
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        array $media,
        string $mediaKey,
        ?string $mediaAlt,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
        array $notes,
    ): array {
        $mediaUrl = $media[$mediaKey][0] ?? $media['hero'][0];

        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'kicker' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $mediaAlt,
            'notes' => $notes,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function showcaseHeadlineSection(string $heading, string $summary, array $media): array
    {
        return [
            'type' => 'showcase-headline',
            'heading' => $heading,
            'summary' => $summary,
            'mediaUrl' => $media['detail'][0] ?? $media['hero'][0],
            'mediaAlt' => 'A close crop of Studio Lichen\'s case-study layout',
            'items' => [
                ['title' => 'Featured: Studio Lichen', 'summary' => 'A design studio portfolio that opens with the work, not the manifesto — three case studies, no scroll-jacking.'],
                ['title' => 'The standout detail', 'summary' => 'Every project page loads under a second and reads top-to-bottom without a single modal.'],
                ['title' => 'Why it leads this week', 'summary' => 'It treats the portfolio as the product, and it shows in every interaction.'],
            ],
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
                ['title' => 'Personal sites', 'summary' => 'Solo makers, writers, and engineers running their own corner of the web.'],
                ['title' => 'Studios & agencies', 'summary' => 'Multi-discipline teams with case studies that hold up to scrutiny.'],
                ['title' => 'Design & development', 'summary' => 'Product designers, front-end engineers, and the craft in between.'],
                ['title' => 'Motion & video', 'summary' => 'Reels, direction, and editing portfolios that earn their autoplay.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function curatedGridSection(string $heading, string $summary, array $media, bool $withUrls = false): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $entries = [
            [
                'title' => 'Studio Lichen',
                'summary' => 'A four-person design studio whose portfolio is three deep case studies and nothing else — confident, fast, and quietly opinionated.',
                'meta' => 'Studio · Design',
                'care_note' => 'Standout: 0.9s load',
            ],
            [
                'title' => 'Devon Park',
                'summary' => 'A front-end engineer\'s personal site that ships a live component playground instead of screenshots — proof in the interaction.',
                'meta' => 'Personal · Development',
                'care_note' => 'Standout: live demos',
            ],
            [
                'title' => 'Mara Quinn',
                'summary' => 'A motion designer whose entire portfolio is one looping reel and three lines of copy — restraint as a flex.',
                'meta' => 'Personal · Motion',
                'care_note' => 'Standout: single reel',
            ],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'image' => $image,
                'imageAlt' => $entry['title'] . ' portfolio preview',
                'url' => $withUrls ? '#creator-summary' : null,
            ];
        }

        return [
            'type' => 'curated-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function standoutNotesSection(string $heading, string $summary, array $media, string $url): array
    {
        $images = array_values(array_unique(array_merge($media['proof'], $media['detail'])));

        $notes = [
            ['title' => 'Restraint over reach', 'summary' => 'The best entries this week say less. One reel, three case studies, no autoplaying carousel of logos.'],
            ['title' => 'Speed is a statement', 'summary' => 'Every featured portfolio loaded under a second. Visitors read it as care, even if they never time it.'],
        ];

        $items = [];

        foreach ($notes as $index => $note) {
            $items[] = [
                ...$note,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $note['title'],
            ];
        }

        return [
            'type' => 'standout-notes',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Read the full notes',
            'url' => $url,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function relatedRecommendationsSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['cta'])));

        $entries = [
            ['meta' => 'Motion', 'title' => 'Ines Caldera', 'summary' => 'Title sequences and broadcast idents, presented as a single scrollable reel.'],
            ['meta' => 'Development', 'title' => 'Toby Fenn', 'summary' => 'A systems engineer who documents architecture decisions as a portfolio.'],
            ['meta' => 'Design', 'title' => 'Studio Maris', 'summary' => 'A two-person studio with packaging work that reads better than most case-study writing.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $entry['title'] . ' portfolio preview',
            ];
        }

        return [
            'type' => 'related-recommendations',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function creatorSummarySection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['proof'])));

        $entries = [
            ['title' => 'Mara Quinn — motion designer', 'summary' => 'Eight years in broadcast and brand motion. Ships reels for agencies and runs a small directing practice on the side.'],
            ['title' => 'Devon Park — front-end engineer', 'summary' => 'Builds design systems by day and component experiments by night. The personal site is the experiment that stuck.'],
            ['title' => 'Studio Lichen — design studio', 'summary' => 'Four people, one room, ten case studies a year. Known for portfolios that refuse to oversell.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'creator-summary',
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
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['value' => '480+', 'label' => 'Portfolios indexed and hand-reviewed since launch.'],
                ['value' => '1 / week', 'label' => 'New curated digest, every Thursday without fail.'],
                ['value' => '11k', 'label' => 'Subscribers reading the index across design and dev.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @param  list<array<string, string>>|null  $items
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media, ?array $items = null): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = $items ?? [
            ['category' => 'Personal · Writing', 'title' => 'Hana Velez', 'summary' => 'An essayist\'s site where the archive is the portfolio — clean, dated, searchable.'],
            ['category' => 'Studio · Brand', 'title' => 'Field & Form', 'summary' => 'A brand studio with a portfolio organised by problem, not by client.'],
            ['category' => 'Development · Open source', 'title' => 'Riku Sato', 'summary' => 'A maintainer whose portfolio is a living changelog of the tools people actually use.'],
        ];

        $resolved = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $resolved[] = [
                ...$entry,
                'image' => $image,
                'imageAlt' => data_get($entry, 'title', '') . ' portfolio preview',
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $resolved,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(
        string $heading,
        string $summary,
        string $primaryUrl,
        string $secondaryUrl,
        string $primaryLabel = 'Submit a portfolio',
        string $secondaryLabel = 'Browse the index',
    ): array {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
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
                ['label' => 'Portfolios', 'url' => '#curated-grid'],
                ['label' => 'Categories', 'url' => '#category-tabs'],
                ['label' => 'Standouts', 'url' => '#standout-notes'],
                ['label' => 'Creators', 'url' => '#creator-summary'],
            ],
            'ctaLabel' => 'Join the index',
            'ctaUrl' => '#newsletter',
            'consultationUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'The index',
                'title' => 'The index',
                'links' => [
                    ['label' => 'Browse portfolios', 'url' => '#curated-grid'],
                    ['label' => 'Categories', 'url' => '#category-tabs'],
                    ['label' => 'Standout notes', 'url' => '#standout-notes'],
                    ['label' => 'Creators', 'url' => '#creator-summary'],
                ],
            ],
            [
                'heading' => 'Take part',
                'title' => 'Take part',
                'links' => [
                    ['label' => 'Submit a portfolio', 'url' => 'mailto:' . self::EDITORS_EMAIL],
                    ['label' => 'Nominate a maker', 'url' => 'mailto:' . self::EDITORS_EMAIL],
                    ['label' => 'Weekly digest', 'url' => '#newsletter'],
                ],
            ],
            [
                'heading' => 'Connect',
                'title' => 'Connect',
                'links' => [
                    ['label' => self::EDITORS_EMAIL, 'url' => 'mailto:' . self::EDITORS_EMAIL],
                    ['label' => 'Back to home', 'url' => '/'],
                ],
            ],
        ];

        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'An editorial index of the portfolios worth bookmarking. Curated by hand, sent every Thursday.',
            'columns' => $columns,
            'items' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
