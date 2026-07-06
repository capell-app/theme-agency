<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeepBench\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Deep Bench theme.
 *
 * Folio Index is a curated talent directory for designers, developers, and
 * studios. Every surface is seeded as an ordered `render_data['sections']`
 * list so the live /theme-deep-bench render emits the theme's
 * signature surfaces (directory-hero / role-filters / portfolio-grid /
 * resume-resources / curated-lists / profile-detail) alongside the shared
 * hero/proof/content-listing/newsletter/cta. Real role-grouped photography
 * from ThemeDemoMedia is attached to every tile, avatar, and gallery entry.
 */
final class DeepBenchDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Folio Index';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        // The shared media catalogue has no 'deep-bench' entry; map to
        // its curated 'portfolio' group instead of the generic default set so
        // tiles, avatars, and galleries read as creative-studio photography.
        $media = ThemeDemoMedia::groupedForTheme('portfolio');

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
            title: self::BRAND . ' — The curated talent index for design and code',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Find the person whose work you already admire',
                'Folio Index is a curated directory of designers, developers, and studios — every profile hand-reviewed, every portfolio live.',
            ),
            renderData: [
                'summary' => 'Folio Index is a curated directory of designers, developers, and studios — hand-reviewed profiles, discipline filters, curated lists, and career resources.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The talent index',
                        heading: 'Find the person whose work you already admire',
                        summary: 'A curated directory of designers, developers, and studios. Every profile is hand-reviewed, every portfolio is live, and every listing is backed by shipped work.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'Recently listed creator portfolios from the Folio Index directory',
                        primaryLabel: 'Browse the directory',
                        primaryUrl: '#portfolio-grid',
                        secondaryLabel: 'Filter by discipline',
                        secondaryUrl: '#role-filters',
                    ),
                    $this->directoryHeroSection($media),
                    $this->directoryHeroRosterSection(),
                    $this->roleFiltersSection(),
                    $this->roleFiltersToolbarSection(),
                    $this->portfolioGridSection($media),
                    $this->portfolioGridCardsSection($media),
                    $this->resumeResourcesSection(),
                    $this->resumeResourcesSidebarSection(),
                    $this->curatedListsSection(),
                    $this->curatedListsCtaSection(),
                    $this->profileDetailSection($media),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Five new portfolios in your inbox, every Friday',
                        summary: 'The week\'s strongest new listings plus one career resource — chosen by the editors, never automated.',
                    ),
                    $this->ctaSection(
                        heading: 'Your work belongs in the directory',
                        summary: 'Submit your portfolio for review. If the work is live and the craft is there, you are in — no fee, no follower count.',
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
            name: self::BRAND . ' Creators',
            title: 'Browse every creator in the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse every creator in the index',
                'Discipline pills cut a thousand portfolios down to the craft you need — product, brand, motion, frontend, illustration, or full studios.',
            ),
            renderData: [
                'summary' => 'Browse every creator in the Folio Index directory, filtered by discipline, city, and availability.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The directory',
                        heading: 'Browse every creator in the index',
                        summary: 'Discipline pills cut a thousand portfolios down to the craft you need. Each card carries the name, the city, and a live link to the work.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'The Folio Index directory grid of creator profiles',
                        primaryLabel: 'Jump to creators',
                        primaryUrl: '#portfolio-grid',
                        secondaryLabel: 'See curated lists',
                        secondaryUrl: '#curated-lists',
                    ),
                    $this->roleFiltersSection(),
                    $this->roleFiltersToolbarSection(),
                    $this->portfolioGridSection($media),
                    $this->portfolioGridCardsSection($media),
                    $this->curatedListsSection(),
                    $this->resumeResourcesSection(),
                    $this->resumeResourcesSidebarSection(),
                    $this->newsletterSection(
                        heading: 'New creators in your inbox, every Friday',
                        summary: 'Five hand-picked additions to the directory plus one career resource — straight from the editors.',
                    ),
                    $this->ctaSection(
                        heading: 'Not listed yet?',
                        summary: 'Submit your portfolio for review and join the creators hiring teams are already shortlisting.',
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
            name: self::BRAND . ' Profile',
            title: 'Aria Wells, Product Designer — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Aria Wells — Product Designer, Amsterdam',
                'Interface and systems work for fintech and health products, with selected work, availability, and a live portfolio link.',
            ),
            renderData: [
                'summary' => 'Aria Wells — product designer in Amsterdam. Selected work, availability, and a live portfolio link, verified by the Folio Index editors.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Profile',
                        heading: 'Aria Wells makes interfaces that survive scale',
                        summary: 'Product designer in Amsterdam. Interface and systems work for fintech and health products — reviewed and listed in the index since 2023.',
                        media: $media,
                        mediaKey: 'detail',
                        mediaAlt: 'Selected interface work from the Aria Wells portfolio',
                        primaryLabel: 'View live portfolio',
                        primaryUrl: '#profile-detail',
                        secondaryLabel: 'More product designers',
                        secondaryUrl: '#portfolio-grid',
                    ),
                    $this->profileDetailSection($media),
                    $this->resumeResourcesSection(),
                    $this->curatedListsSection(),
                    $this->portfolioGridSection($media),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Get the next profile like this by email',
                        summary: 'Five new hand-reviewed portfolios every Friday — the next designer you shortlist might arrive in your inbox.',
                    ),
                    $this->ctaSection(
                        heading: 'Want your profile to read like this?',
                        summary: 'Submit your portfolio and the editors will build your listing with you — work first, context second.',
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
            name: self::BRAND . ' Contact',
            title: 'Stay close to the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Stay close to the index',
                'Join the weekly email or submit your portfolio — the editors read everything that arrives.',
            ),
            renderData: [
                'summary' => 'Join the weekly Folio Index email or submit your portfolio for review — the editors read everything.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Stay close',
                        heading: 'One email a week, or a listing of your own',
                        summary: 'Follow the directory through the Friday email, or put your own work in front of the hiring teams who browse it.',
                        media: $media,
                        mediaKey: 'contact',
                        mediaAlt: 'The Folio Index editorial desk reviewing new submissions',
                        primaryLabel: 'Join the weekly index',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Submit your portfolio',
                        secondaryUrl: '#cta',
                    ),
                    $this->newsletterSection(
                        heading: 'Get new portfolios in your inbox',
                        summary: 'Five hand-picked listings and one career resource every Friday. No job ads, no sponsors.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Ready to be listed?',
                        summary: 'Send the editors your live portfolio — reviews go out within two weeks.',
                        actions: [
                            ['label' => 'Submit your portfolio', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'See why it works', 'url' => '#proof', 'style' => 'secondary'],
                        ],
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
            title: 'No creators match that filter yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No creators match that filter yet',
                'A graceful empty state for a filtered directory with no matching profiles.',
            ),
            renderData: [
                'summary' => 'No creators match that filter yet — clear it to browse the full index, or start from a curated list.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The directory',
                        heading: 'No creators match that filter yet',
                        summary: 'Nothing in the index fits that combination of discipline and availability. Clear the filter to see everyone, or start from a curated list.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: null,
                        primaryLabel: 'Clear filters',
                        primaryUrl: '#role-filters',
                        secondaryLabel: 'Browse curated lists',
                        secondaryUrl: '#curated-lists',
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'New listings land here first',
                        'summary' => 'Profiles that pass review appear in this index within the week, newest first.',
                        'items' => [],
                    ],
                    $this->roleFiltersSection(resultsTarget: '#content-listing'),
                    $this->curatedListsSection(listUrl: '/'),
                    $this->newsletterSection(
                        heading: 'Be first to know when this filter fills',
                        summary: 'Leave your email and the editors will send the strongest new listings the moment they pass review.',
                    ),
                    $this->ctaSection(
                        heading: 'Be the first listing in this filter',
                        summary: 'Submit your portfolio and help seed the index for the discipline you work in.',
                        actions: [
                            ['label' => 'Submit your portfolio', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'See all disciplines', 'url' => '#role-filters', 'style' => 'secondary'],
                        ],
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
            title: 'That profile moved — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That profile moved',
                'A not-found page that routes visitors back into the directory and curated lists.',
            ),
            renderData: [
                'summary' => 'That profile moved or was never listed — here is the way back into the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That profile moved or was never listed',
                        summary: 'The link is broken, or the creator asked to be delisted. The directory and the curated lists are both one click away.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: null,
                        primaryLabel: 'Back to the directory',
                        primaryUrl: '/',
                        secondaryLabel: 'Browse curated lists',
                        secondaryUrl: '#curated-lists',
                    ),
                    $this->curatedListsSection(listUrl: '/'),
                    $this->ctaSection(
                        heading: 'Looking for someone specific?',
                        summary: 'Tell the editors who you were trying to reach and they will point you to the right listing.',
                        actions: [
                            ['label' => 'Email the editors', 'url' => 'mailto:desk@folioindex.example', 'style' => 'primary'],
                            ['label' => 'Back to the directory', 'url' => '/', 'style' => 'secondary'],
                        ],
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
            name: self::BRAND . ' Submit',
            title: 'Submit your portfolio — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Submit your portfolio for review',
                'A focused conversion page inviting creators to join the curated index.',
            ),
            renderData: [
                'summary' => 'Submit your portfolio to Folio Index — if the work is live and the craft is there, you are in.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Join the index',
                        heading: 'Put your work where hiring teams already look',
                        summary: 'Submissions are reviewed by working designers and engineers. If the portfolio is live and the craft is there, you are listed — no fee, no follower count.',
                        media: $media,
                        mediaKey: 'cta',
                        mediaAlt: 'A creator portfolio under review by the Folio Index editors',
                        primaryLabel: 'Submit your portfolio',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'See who is listed',
                        secondaryUrl: '/',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Get notified when reviews open',
                        summary: 'Review rounds open monthly. Leave your email and we will tell you the moment the next one starts.',
                    ),
                    $this->ctaSection(
                        heading: 'The next review round starts soon',
                        summary: 'Have the portfolio live, the case studies short, and the contact link working — that is all the editors ask.',
                        actions: [
                            ['label' => 'Submit your portfolio', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'See who is listed', 'url' => '/', 'style' => 'secondary'],
                        ],
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
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
    ): array {
        $mediaUrl = $media[$mediaKey][0] ?? $media['hero'][0] ?? null;

        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
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
    private function directoryHeroSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['proof'], $media['cta'])));

        $features = [
            [
                'title' => 'Studio Marlow',
                'summary' => 'Brand and product design for ambitious early-stage teams — four rebrands shipped this quarter.',
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Tideline Interactive',
                'summary' => 'Motion-led web experiences for culture and music clients, all still live and all still fast.',
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Northglass Labs',
                'summary' => 'Frontend engineering and component systems for scale-ups that outgrew their first codebase.',
                'url' => '#profile-detail',
            ],
        ];

        $items = [];

        foreach ($features as $index => $feature) {
            $items[] = [
                ...$feature,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => 'Selected work from ' . $feature['title'],
            ];
        }

        return [
            'type' => 'directory-hero',
            'eyebrow' => 'Featured this week',
            'heading' => 'Three portfolios worth your afternoon',
            'summary' => 'Every Monday the editors pick the profiles that made them stop scrolling — new work, new names, no sponsored placements.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roleFiltersSection(string $resultsTarget = '#portfolio-grid'): array
    {
        return [
            'type' => 'role-filters',
            'eyebrow' => 'Disciplines',
            'heading' => 'Cut the index down to the craft you need',
            'summary' => 'One click narrows a thousand portfolios to the discipline you are hiring for — no accounts, no search syntax.',
            'note' => 'Counts refresh as new profiles pass review. Combine a discipline with availability to see only people open to work right now.',
            'items' => [
                ['title' => 'All creators', 'meta' => '1,200', 'url' => $resultsTarget, 'active' => true],
                ['title' => 'Product', 'meta' => '324', 'url' => $resultsTarget],
                ['title' => 'Brand', 'meta' => '287', 'url' => $resultsTarget],
                ['title' => 'Motion', 'meta' => '142', 'url' => $resultsTarget],
                ['title' => 'Frontend', 'meta' => '253', 'url' => $resultsTarget],
                ['title' => 'Illustration', 'meta' => '96', 'url' => $resultsTarget],
                ['title' => 'Studios', 'meta' => '98', 'url' => $resultsTarget],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function portfolioGridSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'], $media['contact'])));

        $creators = [
            [
                'title' => 'Aria Wells',
                'meta' => 'Product Designer',
                'summary' => 'Interface and systems work for fintech and health products.',
                'location' => 'Amsterdam, NL',
                'tags' => ['Product', 'Systems'],
                'available' => true,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Mira Sol',
                'meta' => 'Frontend Engineer',
                'summary' => 'Accessible, fast interfaces built with care and craft.',
                'location' => 'Lisbon, PT',
                'tags' => ['Frontend', 'Accessibility'],
                'available' => true,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Devon Park Studio',
                'meta' => 'Brand Studio',
                'summary' => 'Identities and websites for culture-led organisations.',
                'location' => 'Glasgow, UK',
                'tags' => ['Brand', 'Web'],
                'available' => false,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Jonas Reff',
                'meta' => 'Motion Designer',
                'summary' => 'Title sequences and product film for teams that ship.',
                'location' => 'Copenhagen, DK',
                'tags' => ['Motion', 'Film'],
                'available' => true,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Lena Okafor',
                'meta' => 'Illustrator',
                'summary' => 'Editorial and brand illustration with a printmaker\'s hand.',
                'location' => 'Berlin, DE',
                'tags' => ['Illustration', 'Editorial'],
                'available' => false,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Halvard & Co',
                'meta' => 'Product Studio',
                'summary' => 'End-to-end product work for founders on their second act.',
                'location' => 'Oslo, NO',
                'tags' => ['Studio', 'Product'],
                'available' => true,
                'url' => '#profile-detail',
            ],
        ];

        $items = [];

        foreach ($creators as $index => $creator) {
            $items[] = [
                ...$creator,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => 'Portrait of ' . $creator['title'],
            ];
        }

        return [
            'type' => 'portfolio-grid',
            'eyebrow' => 'The directory',
            'heading' => 'Creators currently listed in the index',
            'summary' => 'Name, discipline, city, and a live link to the work — everything you need to shortlist someone in under a minute.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function directoryHeroRosterSection(): array
    {
        return [
            'type' => 'directory-hero-roster',
            'eyebrow' => 'The roster at a glance',
            'heading' => 'A directory that proves itself in numbers',
            'summary' => 'Every figure below reflects the current index — hand-reviewed profiles, disciplines covered, curated lists, and how fast the editors respond.',
            'stats' => [
                ['value' => 1200, 'label' => 'profiles listed', 'suffix' => '+'],
                ['value' => 6, 'label' => 'disciplines covered'],
                ['value' => 30, 'label' => 'curated lists'],
                ['value' => 48, 'label' => 'average review response', 'suffix' => 'h'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roleFiltersToolbarSection(): array
    {
        return [
            'type' => 'role-filters-toolbar',
            'eyebrow' => 'Sort the roster',
            'heading' => 'Narrow the roster to the creators you need',
            'summary' => 'Tick a discipline, tick availability, and the roster below updates instantly — no accounts, no search syntax, no page reload.',
            'target' => 'portfolio-grid-cards',
            'facets' => [
                ['key' => 'product', 'label' => 'Product', 'meta' => '324'],
                ['key' => 'brand', 'label' => 'Brand', 'meta' => '287'],
                ['key' => 'motion', 'label' => 'Motion', 'meta' => '142'],
                ['key' => 'frontend', 'label' => 'Frontend', 'meta' => '253'],
                ['key' => 'illustration', 'label' => 'Illustration', 'meta' => '96'],
                ['key' => 'studios', 'label' => 'Studios', 'meta' => '98'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function portfolioGridCardsSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'], $media['contact'])));

        $creators = [
            [
                'title' => 'Aria Wells',
                'meta' => 'Product Designer',
                'summary' => 'Interface and systems work for fintech and health products.',
                'location' => 'Amsterdam, NL',
                'discipline' => 'product',
                'available' => true,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Mira Sol',
                'meta' => 'Frontend Engineer',
                'summary' => 'Accessible, fast interfaces built with care and craft.',
                'location' => 'Lisbon, PT',
                'discipline' => 'frontend',
                'available' => true,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Devon Park Studio',
                'meta' => 'Brand Studio',
                'summary' => 'Identities and websites for culture-led organisations.',
                'location' => 'Glasgow, UK',
                'discipline' => 'brand',
                'available' => false,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Jonas Reff',
                'meta' => 'Motion Designer',
                'summary' => 'Title sequences and product film for teams that ship.',
                'location' => 'Copenhagen, DK',
                'discipline' => 'motion',
                'available' => true,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Lena Okafor',
                'meta' => 'Illustrator',
                'summary' => 'Editorial and brand illustration with a printmaker\'s hand.',
                'location' => 'Berlin, DE',
                'discipline' => 'illustration',
                'available' => false,
                'url' => '#profile-detail',
            ],
            [
                'title' => 'Halvard & Co',
                'meta' => 'Product Studio',
                'summary' => 'End-to-end product work for founders on their second act.',
                'location' => 'Oslo, NO',
                'discipline' => 'studios',
                'available' => true,
                'url' => '#profile-detail',
            ],
        ];

        $items = [];

        foreach ($creators as $index => $creator) {
            $items[] = [
                ...$creator,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => 'Portrait of ' . $creator['title'],
            ];
        }

        return [
            'type' => 'portfolio-grid-cards',
            'eyebrow' => 'The roster',
            'heading' => 'Creators matching your filters',
            'summary' => 'Every card updates live as you sort the roster above — name, discipline, city, and a link straight to the work.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resumeResourcesSidebarSection(): array
    {
        return [
            'type' => 'resume-resources-sidebar',
            'eyebrow' => 'Career resources',
            'heading' => 'Everything you need before you apply',
            'summary' => 'A compact rail of the same templates and guides the editors send creators before their profile goes live.',
            'label' => 'Browse all resources',
            'url' => '#resume-resources-sidebar',
            'items' => [
                ['title' => 'The one-page resume that gets read', 'meta' => 'Template', 'url' => '#resume-resources-sidebar'],
                ['title' => 'Writing a case study that closes', 'meta' => 'Guide', 'url' => '#resume-resources-sidebar'],
                ['title' => 'Freelance day-rate report', 'meta' => 'Data', 'url' => '#resume-resources-sidebar'],
                ['title' => 'Negotiating your first retainer', 'meta' => 'Guide', 'url' => '#resume-resources-sidebar'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function curatedListsCtaSection(): array
    {
        return [
            'type' => 'curated-lists-cta',
            'eyebrow' => 'Curated lists',
            'heading' => 'Start from a shortlist, then sort the roster your way',
            'summary' => 'Curated lists give you a strong starting point; the roster filters above let you refine it down to the exact person you need.',
            'label' => 'Submit your portfolio',
            'url' => '#newsletter',
            'featuredList' => [
                'title' => 'Frontend craftspeople',
                'summary' => 'Engineers whose public work is a masterclass in detail — the list hiring managers bookmark first.',
                'meta' => '9 profiles',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resumeResourcesSection(): array
    {
        return [
            'type' => 'resume-resources',
            'eyebrow' => 'Career resources',
            'heading' => 'The paperwork side of getting hired, handled',
            'summary' => 'Templates, guides, and salary data maintained by the editors — the same material we send creators before their profile goes live.',
            'label' => 'Browse all resources',
            'url' => '#resume-resources',
            'items' => [
                [
                    'title' => 'The one-page resume that gets read',
                    'summary' => 'A template built from what hiring managers actually skim first, with a filled-in example for each discipline.',
                    'meta' => 'Template',
                    'url' => '#resume-resources',
                ],
                [
                    'title' => 'Writing a case study that closes',
                    'summary' => 'How to structure process work so the outcome — not the artefact count — carries the story.',
                    'meta' => 'Guide',
                    'url' => '#resume-resources',
                ],
                [
                    'title' => 'Freelance day-rate report',
                    'summary' => 'Anonymised rates from the directory, split by discipline, seniority, and region. Refreshed each quarter.',
                    'meta' => 'Data',
                    'url' => '#resume-resources',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function curatedListsSection(string $listUrl = '#portfolio-grid'): array
    {
        return [
            'type' => 'curated-lists',
            'eyebrow' => 'Curated lists',
            'heading' => 'Shortlists the editors keep coming back to',
            'summary' => 'Named collections that group the directory by theme — a strong starting point when you know the feeling but not the name.',
            'items' => [
                [
                    'title' => 'Best portfolio sites for motion designers',
                    'summary' => 'Reels that load fast, autoplay politely, and still show range.',
                    'meta' => '12 profiles',
                    'url' => $listUrl,
                ],
                [
                    'title' => 'Frontend craftspeople',
                    'summary' => 'Engineers whose public work is a masterclass in detail.',
                    'meta' => '9 profiles',
                    'url' => $listUrl,
                ],
                [
                    'title' => 'New studios to watch',
                    'summary' => 'Young teams doing standout end-to-end product work.',
                    'meta' => '8 profiles',
                    'url' => $listUrl,
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function profileDetailSection(array $media): array
    {
        $galleryImages = array_values(array_unique(array_merge($media['detail'], $media['cta'], $media['proof'])));
        $avatarImage = $media['contact'][0] ?? $media['hero'][0] ?? null;

        $gallery = [];

        foreach (array_slice($galleryImages, 0, 3) as $index => $galleryImage) {
            $gallery[] = [
                'image' => $galleryImage,
                'imageAlt' => 'Selected work from the Aria Wells portfolio, piece ' . ($index + 1),
            ];
        }

        return [
            'type' => 'profile-detail',
            'eyebrow' => 'Profile',
            'heading' => 'What a listing looks like up close',
            'summary' => 'Every profile pairs the work with the context a hiring team needs — discipline, city, availability, and a live portfolio link.',
            'profile' => [
                'name' => 'Aria Wells',
                'role' => 'Product Designer',
                'bio' => 'Interface and systems work for fintech and health products. Previously led platform design at two scale-ups; now taking select freelance engagements.',
                'image' => $avatarImage,
                'imageAlt' => 'Portrait of Aria Wells',
                'tags' => ['Product', 'Systems', 'Fintech'],
                'url' => '#portfolio-grid',
                'label' => 'View portfolio',
                'facts' => [
                    ['label' => 'Based in', 'value' => 'Amsterdam, NL'],
                    ['label' => 'Availability', 'value' => 'Open from August'],
                    ['label' => 'Focus', 'value' => 'Interface systems'],
                    ['label' => 'Listed since', 'value' => '2023'],
                ],
            ],
            'gallery' => $gallery,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'eyebrow' => 'Why it works',
            'heading' => 'A directory both sides can trust',
            'summary' => 'Curation is the product. Every number below exists because we say no more often than yes.',
            'items' => [
                ['value' => '1,200+', 'label' => 'Hand-reviewed profiles listed across product, brand, motion, frontend, and illustration.'],
                ['value' => '4.8/5', 'label' => 'Average rating from hiring teams who shortlisted through the index last year.'],
                ['value' => '30', 'label' => 'Curated lists maintained by the editors, refreshed every month without exception.'],
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
            'eyebrow' => 'The weekly index',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
            'email_label' => 'Email address',
            'button' => 'Subscribe',
        ];
    }

    /**
     * @param  list<array{label: string, url: string, style: string}>|null  $actions
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, ?array $actions = null): array
    {
        $actions ??= [
            ['label' => 'Submit your portfolio', 'url' => '#newsletter', 'style' => 'primary'],
            ['label' => 'Browse the directory', 'url' => '#portfolio-grid', 'style' => 'secondary'],
        ];

        return [
            'type' => 'cta',
            'eyebrow' => 'Join the index',
            'heading' => $heading,
            'summary' => $summary,
            'label' => $actions[0]['label'],
            'url' => $actions[0]['url'],
            'actions' => $actions,
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
                ['label' => 'Directory', 'url' => '#portfolio-grid'],
                ['label' => 'Disciplines', 'url' => '#role-filters'],
                ['label' => 'Lists', 'url' => '#curated-lists'],
                ['label' => 'Resources', 'url' => '#resume-resources'],
            ],
            'ctaLabel' => 'List your work',
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
                'title' => 'Browse',
                'heading' => 'Browse',
                'links' => [
                    ['label' => 'Directory', 'url' => '#portfolio-grid'],
                    ['label' => 'Disciplines', 'url' => '#role-filters'],
                    ['label' => 'Curated lists', 'url' => '#curated-lists'],
                ],
            ],
            [
                'title' => 'For creators',
                'heading' => 'For creators',
                'links' => [
                    ['label' => 'Submit your portfolio', 'url' => '#newsletter'],
                    ['label' => 'Career resources', 'url' => '#resume-resources'],
                ],
            ],
            [
                'title' => 'Stay close',
                'heading' => 'Stay close',
                'links' => [
                    ['label' => 'The weekly index', 'url' => '#newsletter'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A curated directory of designers, developers, and studios — every profile hand-reviewed, every portfolio live.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
