<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ExperimentalDirectory\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Experimental Directory theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature directory renderers (featured-today /
 * metadata-filters / latest-submissions / winners-collections / profiles-resources /
 * sponsor-modules / newsletter) alongside the shared hero/cta — giving every
 * surface a full, individual creative-directory site rather than a shared skeleton.
 *
 * Anchors are scoped per surface: navigation and footer render on every surface,
 * so they only ever link to real page paths (home-relative) or mailto: — never a
 * same-page hash that would be a dead click off the homepage. In-page hashes
 * (e.g. `#latest-submissions`) are only used from hero/cta actions on a surface
 * that actually renders that section.
 */
final class ExperimentalDirectoryDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Index of Practice';

    private const string CURATORS_EMAIL = 'curators@indexofpractice.example';

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
            title: self::BRAND . ' — A Directory of Experimental Creative Practice',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A directory experimental creative work can stand behind',
                'Index of Practice is a curated directory of studios, projects, and submissions across the creative industries — featured daily, filtered by metadata, never buried.',
            ),
            renderData: [
                'summary' => 'Index of Practice is a curated directory of studios, projects, and submissions across the creative industries — featured work, latest entries, winners, and collections in one confident index.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Featured today',
                        heading: 'The directory where experimental creative work gets seen',
                        summary: 'A curated index of submissions, winners, and studio profiles across brand, motion, type, and interactive work — featured daily, filtered by metadata, never buried in a scoreboard.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'Tidal Atlas identity system, the current featured project',
                        primaryLabel: 'Browse submissions',
                        primaryUrl: '#latest-submissions',
                        secondaryLabel: 'View winners',
                        secondaryUrl: '#winners-collections',
                    ),
                    $this->featuredTodaySection($media),
                    $this->metadataFiltersSection(),
                    $this->latestSubmissionsSection(
                        heading: 'Latest submissions',
                        summary: 'New work added to the index this week, newest first.',
                        media: $media,
                    ),
                    $this->winnersCollectionsSection(),
                    $this->profilesResourcesSection(),
                    $this->sponsorModulesSection(),
                    $this->ctaSection(
                        heading: 'Submit your studio to the index',
                        summary: 'Add a project, claim a profile, or put your work in front of the curators who watch this directory daily.',
                        primaryLabel: 'Submit a project',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Browse submissions',
                        secondaryUrl: '#latest-submissions',
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
            title: 'Project archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'An archive of submissions built to be scanned',
                'Browse the full index of projects across mediums, statuses, countries, and categories.',
            ),
            renderData: [
                'summary' => 'Browse the full index of submissions across mediums, statuses, countries, and categories — tight grid rules keep the archive legible at any depth.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Project archive',
                        heading: 'An archive of submissions built to be scanned',
                        summary: 'Every project in the index, filterable by medium, status, country, and category. Compact metadata up front, full credits one click away.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'The project archive, rendered as a filterable grid',
                        primaryLabel: 'Filter the archive',
                        primaryUrl: '#metadata-filters',
                        secondaryLabel: 'View latest',
                        secondaryUrl: '#latest-submissions',
                    ),
                    $this->metadataFiltersSection(),
                    $this->latestSubmissionsSection(
                        heading: 'Recently indexed',
                        summary: 'The newest entries to clear curation, in submission order.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Smaller entries, experiments, and collaborations across the index.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Cannot find the project you wanted?',
                        summary: 'Tell us what you are looking for, or submit the work yourself and we will get it into the index.',
                        primaryLabel: 'Submit a project',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Browse latest',
                        secondaryUrl: '#latest-submissions',
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
            name: self::BRAND . ' Project',
            title: 'Tidal Atlas — Project — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Tidal Atlas — an identity for a coastal research network',
                'A single project view with oversized title, compact metadata, status badge, credits, gallery, and related work.',
            ),
            renderData: [
                'summary' => 'A single project view pairs an oversized title, compact metadata, a status badge, and full credits so visitors can absorb the work without the theme owning project records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Project · Winner 2025',
                        heading: 'Tidal Atlas — an identity for a coastal research network',
                        summary: 'Studio Meridian · Brand & Editorial · United Kingdom · Awarded in the 2025 collection. A flexible identity system for a network mapping the changing coastline.',
                        media: $media,
                        mediaKey: 'detail',
                        mediaAlt: 'Tidal Atlas project imagery',
                        primaryLabel: 'View studio & credits',
                        primaryUrl: '#profiles-resources',
                        secondaryLabel: 'See related work',
                        secondaryUrl: '#latest-submissions',
                    ),
                    $this->featuredTodaySection($media, heading: 'Inside the project'),
                    $this->profilesResourcesSection(heading: 'Credits & studio'),
                    $this->latestSubmissionsSection(
                        heading: 'Related work',
                        summary: 'Other projects in the same medium and collection.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Run a studio like Meridian?',
                        summary: 'Claim your profile and submit your projects to the index so your work shows up alongside work like this.',
                        primaryLabel: 'Submit a project',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Back to the index',
                        secondaryUrl: '/',
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
                'Reach the directory through one confident path',
                'Submit a project, claim a profile, or subscribe to the weekly index of new work.',
            ),
            renderData: [
                'summary' => 'Submit a project, claim a studio profile, or subscribe to the weekly index — one confident path into the directory for studios and readers alike.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit & subscribe',
                        heading: 'Reach the directory through one confident path',
                        summary: 'Studios submit work for curation; readers subscribe to the weekly index. Email ' . self::CURATORS_EMAIL . ' or use the form below.',
                        media: $media,
                        mediaKey: 'contact',
                        mediaAlt: 'Submit work to the directory',
                        primaryLabel: 'Subscribe to the index',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Email the curators',
                        secondaryUrl: 'mailto:' . self::CURATORS_EMAIL,
                    ),
                    $this->newsletterSection(),
                    $this->metadataFiltersSection(heading: 'What we curate'),
                    $this->ctaSection(
                        heading: 'Ready to submit your project?',
                        summary: 'Send the work, the credits, and one strong image. We review submissions every Thursday and reply either way.',
                        primaryLabel: 'Email the curators',
                        primaryUrl: 'mailto:' . self::CURATORS_EMAIL,
                        secondaryLabel: 'Subscribe instead',
                        secondaryUrl: '#newsletter',
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
            title: 'No matching projects — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No projects match that filter yet',
                'A graceful empty state for a filtered directory with no matching submissions.',
            ),
            renderData: [
                'summary' => 'No submissions match that combination of medium, status, and country yet — clear the filter or widen the search.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Project archive',
                        heading: 'No projects match that filter — yet',
                        summary: 'Nothing in the index fits that combination of medium, status, and country. Clear the filter to see everything, or browse the latest entries.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: null,
                        primaryLabel: 'Clear the filter',
                        primaryUrl: '#metadata-filters',
                        secondaryLabel: 'Browse latest',
                        secondaryUrl: '#latest-submissions',
                    ),
                    $this->metadataFiltersSection(),
                    $this->latestSubmissionsSection(
                        heading: 'While you are here',
                        summary: 'A few recently indexed projects across every medium.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific project?',
                        summary: 'Tell us the studio or the title and we will point you to it — or add it to the index if it is missing.',
                        primaryLabel: 'Email the curators',
                        primaryUrl: 'mailto:' . self::CURATORS_EMAIL,
                        secondaryLabel: 'Back to the index',
                        secondaryUrl: '/',
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
                'That page is not in the index',
                'A not-found page that routes visitors back into submissions, winners, and the archive.',
            ),
            renderData: [
                'summary' => 'That page has moved or never made the index — here is the way back into the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That page is not in the index',
                        summary: 'The link is broken or the project has been unpublished. Head back to the directory home, or browse the winners.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: null,
                        primaryLabel: 'Back to home',
                        primaryUrl: '/',
                        secondaryLabel: 'View winners',
                        secondaryUrl: '#winners-collections',
                    ),
                    $this->winnersCollectionsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for a project?',
                        summary: 'Tell us the studio or title and we will point you to the right entry in the index.',
                        primaryLabel: 'Email the curators',
                        primaryUrl: 'mailto:' . self::CURATORS_EMAIL,
                        secondaryLabel: 'Back to home',
                        secondaryUrl: '/',
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
            title: 'Put your work in the index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Put your work in front of the people who curate',
                'A focused conversion page inviting studios to submit projects to the directory.',
            ),
            renderData: [
                'summary' => 'Put your work in front of the people who curate this directory — submit a project or claim your studio profile.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit to the index',
                        heading: 'Put your work in front of the people who curate',
                        summary: 'One project, one profile, one weekly index seen by editors, studios, and award juries. Submitting takes ten minutes.',
                        media: $media,
                        mediaKey: 'cta',
                        mediaAlt: 'Submit your studio work',
                        primaryLabel: 'Subscribe to the index',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'View winners',
                        secondaryUrl: '#winners-collections',
                    ),
                    $this->winnersCollectionsSection(),
                    $this->sponsorModulesSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'One submission away',
                        summary: 'Send the work and the credits. We review every Thursday and the strongest entries land in next week’s index.',
                        primaryLabel: 'Email the curators',
                        primaryUrl: 'mailto:' . self::CURATORS_EMAIL,
                        secondaryLabel: 'Subscribe instead',
                        secondaryUrl: '#newsletter',
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
        $mediaUrl = $media[$mediaKey][0] ?? $media['hero'][0];

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

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function featuredTodaySection(array $media, string $heading = 'Featured today'): array
    {
        $pool = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['proof'])));

        $items = [
            ['title' => 'Tidal Atlas', 'meta' => 'Studio Meridian · Brand & Editorial · United Kingdom', 'summary' => 'A flexible identity for a coastal research network mapping the changing shoreline.'],
            ['title' => 'Northwind Type', 'meta' => 'Foundry Verda · Typeface · Germany', 'summary' => 'A grotesque type family drawn for renewable-energy interfaces in the field.'],
            ['title' => 'Harbour Sessions', 'meta' => 'Atlas Studio · Motion · United States', 'summary' => 'A motion identity for a dockside music series, built to read at festival scale.'],
        ];

        return [
            'type' => 'featured-today',
            'kicker' => 'Featured today',
            'heading' => $heading,
            'summary' => 'Hand-picked projects the curators put at the top of the index today.',
            'items' => $this->withImages($items, $pool, 'Featured project', 'theme-experimental-directory-detail'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataFiltersSection(string $heading = 'Filter by medium, status, country, and category'): array
    {
        return [
            'type' => 'metadata-filters',
            'kicker' => 'Metadata filters',
            'heading' => $heading,
            'summary' => 'Compact metadata filters keep results across statuses, mediums, and countries fast and legible.',
            'items' => [
                [
                    'title' => 'Medium',
                    'chips' => [
                        ['label' => 'Brand', 'count' => 42],
                        ['label' => 'Editorial', 'count' => 27],
                        ['label' => 'Type', 'count' => 15],
                        ['label' => 'Motion', 'count' => 33],
                        ['label' => 'Product', 'count' => 19],
                    ],
                ],
                [
                    'title' => 'Status',
                    'chips' => [
                        ['label' => 'Submitted', 'count' => 58],
                        ['label' => 'Shortlisted', 'count' => 21],
                        ['label' => 'Winner', 'count' => 12],
                        ['label' => 'Collected', 'count' => 34],
                    ],
                ],
                [
                    'title' => 'Country',
                    'chips' => [
                        ['label' => 'United Kingdom', 'count' => 24],
                        ['label' => 'Germany', 'count' => 18],
                        ['label' => 'United States', 'count' => 31],
                        ['label' => 'Japan', 'count' => 14],
                    ],
                ],
                [
                    'title' => 'Category',
                    'chips' => [
                        ['label' => 'Identity', 'count' => 29],
                        ['label' => 'Packaging', 'count' => 11],
                        ['label' => 'Campaign', 'count' => 17],
                        ['label' => 'Design system', 'count' => 9],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function latestSubmissionsSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $items = [
            ['title' => 'Verda Field System', 'meta' => 'Foundry Verda · Design system', 'summary' => 'A component library unifying four renewable-energy product teams on one visual language.'],
            ['title' => 'Lumen Refresh', 'meta' => 'Studio Kindred · Identity', 'summary' => 'A lighter, warmer identity for a health-tech startup finding its public voice.'],
            ['title' => 'Coastline Report', 'meta' => 'Studio Meridian · Editorial', 'summary' => 'An annual print and web report for a marine conservation network.'],
            ['title' => 'Kindred Campaign', 'meta' => 'Atlas Studio · Campaign', 'summary' => 'A social-first launch campaign for a community lending app.'],
        ];

        return [
            'type' => 'latest-submissions',
            'kicker' => 'Latest submissions',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $this->withImages($items, $pool, 'Recent submission', 'theme-experimental-directory-detail'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function winnersCollectionsSection(): array
    {
        return [
            'type' => 'winners-collections',
            'kicker' => 'Winners & collections',
            'heading' => 'Winners & collections',
            'summary' => 'The standout entries from this cycle, grouped into the curators’ named collections.',
            'label' => 'See the current project',
            'url' => '/theme-experimental-directory-detail',
            'items' => [
                ['title' => 'Winner · Identity', 'summary' => 'Tidal Atlas by Studio Meridian — best in the brand & editorial medium.', 'url' => '/theme-experimental-directory-detail'],
                ['title' => 'Winner · Type', 'summary' => 'Northwind Type by Foundry Verda — best original typeface of the cycle.'],
                ['title' => 'Collection · Coastal', 'summary' => 'Twelve projects from studios working on coastline and climate themes.'],
                ['title' => 'Collection · In Motion', 'summary' => 'A set of motion identities the jury kept returning to.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profilesResourcesSection(string $heading = 'Profiles & resources'): array
    {
        return [
            'type' => 'profiles-resources',
            'kicker' => 'Profiles & resources',
            'heading' => $heading,
            'summary' => 'Studio profiles, credits, and the field guides curators recommend.',
            'items' => [
                ['title' => 'Studio Meridian', 'meta' => 'Profile · Bristol, UK', 'summary' => 'A six-person identity and editorial studio behind three winning entries this cycle.', 'url' => '/theme-experimental-directory-detail'],
                ['title' => 'Foundry Verda', 'meta' => 'Profile · Berlin, DE', 'summary' => 'An independent type foundry drawing faces for screens and signage.'],
                ['title' => 'Submitting work', 'meta' => 'Resource · Guide', 'summary' => 'How to prepare credits, imagery, and metadata so your project clears curation first time.', 'url' => '/theme-experimental-directory-contact'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sponsorModulesSection(): array
    {
        return [
            'type' => 'sponsor-modules',
            'kicker' => 'Sponsor modules',
            'heading' => 'Supported by the studios who back the index',
            'summary' => 'Sponsor modules keep the directory free for readers and open for submissions.',
            'items' => [
                ['title' => 'Meridian', 'summary' => 'Founding studio partner'],
                ['title' => 'Verda', 'summary' => 'Type & tooling partner'],
                ['title' => 'Atlas', 'summary' => 'Motion partner'],
                ['title' => 'Kindred', 'summary' => 'Community partner'],
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
            'kicker' => 'Directory digest',
            'heading' => 'Get featured projects and new winners by email',
            'summary' => 'One weekly email with the featured-today project, new submissions, and any winners the jury just named.',
            'action' => '#newsletter',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $items = [
            ['title' => 'Atlas Festival rebrand', 'category' => 'Campaign · Shortlisted', 'summary' => 'A launch campaign and motion toolkit that took a regional arts festival national.'],
            ['title' => 'Studio Verda portfolio', 'category' => 'Website · Winner', 'summary' => 'An editorial portfolio site for an architecture practice that lets the buildings speak.'],
            ['title' => 'Foundry catalogue', 'category' => 'Editorial · Collected', 'summary' => 'A print and web specimen catalogue for a forty-face type library.'],
        ];

        return [
            'type' => 'content-listing',
            'kicker' => 'Archive',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $this->withImages($items, $pool, 'Archive entry', 'theme-experimental-directory-detail'),
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
            'kicker' => 'Submit to the index',
            'heading' => $heading,
            'summary' => $summary,
            'label' => $primaryLabel,
            'url' => $primaryUrl,
            'actions' => [
                ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @param  list<array<string, string>>  $items
     * @param  list<string>  $pool
     * @return list<array<string, mixed>>
     */
    private function withImages(array $items, array $pool, string $altPrefix, ?string $linkSlug = null): array
    {
        $withImages = [];

        foreach ($items as $index => $item) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $withImages[] = [
                ...$item,
                'url' => filled($linkSlug) ? '/' . $linkSlug : null,
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $altPrefix . ': ' . data_get($item, 'title', ''),
            ];
        }

        return $withImages;
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
                ['label' => 'Today', 'url' => '/'],
                ['label' => 'Submissions', 'url' => '/theme-experimental-directory-directory'],
                ['label' => 'Winners', 'url' => '/theme-experimental-directory-cta'],
                ['label' => 'Profiles', 'url' => '/theme-experimental-directory-detail'],
            ],
            'ctaLabel' => 'Submit a project',
            'ctaUrl' => '/theme-experimental-directory-contact',
            'consultationUrl' => '/theme-experimental-directory-contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Index',
                'heading' => 'Index',
                'links' => [
                    ['label' => 'Today', 'url' => '/'],
                    ['label' => 'Submissions', 'url' => '/theme-experimental-directory-directory'],
                    ['label' => 'Winners', 'url' => '/theme-experimental-directory-cta'],
                    ['label' => 'Project archive', 'url' => '/theme-experimental-directory-directory'],
                ],
            ],
            [
                'title' => 'Studios',
                'heading' => 'Studios',
                'links' => [
                    ['label' => 'Profiles', 'url' => '/theme-experimental-directory-detail'],
                    ['label' => 'Submit work', 'url' => '/theme-experimental-directory-contact'],
                ],
            ],
            [
                'title' => 'Connect',
                'heading' => 'Connect',
                'links' => [
                    ['label' => 'Subscribe & submit', 'url' => '/theme-experimental-directory-contact'],
                    ['label' => self::CURATORS_EMAIL, 'url' => 'mailto:' . self::CURATORS_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A curated directory of experimental creative practice. Submissions reviewed every week.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
