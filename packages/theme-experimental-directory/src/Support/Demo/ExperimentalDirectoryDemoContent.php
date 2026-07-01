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
 * sponsor-modules / newsletter) alongside the standard hero/cta — giving every
 * surface a full, individual creative-directory site rather than a shared skeleton.
 */
final class ExperimentalDirectoryDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Index of Practice';

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
            title: self::BRAND . ' — A Directory of Creative Practice',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A directory creative work can stand behind',
                'Index of Practice is a curated directory of studios, projects, and submissions across the creative industries.',
            ),
            renderData: [
                'summary' => 'Index of Practice is a curated directory of studios, projects, and submissions across the creative industries — featured work, latest entries, winners, and collections in one tight index.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Index of Practice',
                        'heading' => 'A directory creative work can stand behind',
                        'summary' => 'A pale, grid-ruled index of featured projects, latest submissions, winners, collections, profiles, and resources — discovery built for oversized titles and image-led work.',
                        'primary_label' => 'Browse submissions',
                        'primary_url' => '#latest-submissions',
                        'secondary_label' => 'View winners',
                        'secondary_url' => '#winners-collections',
                        'actions' => [
                            ['label' => 'Browse submissions', 'url' => '#latest-submissions', 'style' => 'primary'],
                            ['label' => 'View winners', 'url' => '#winners-collections', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Featured creative project',
                    ],
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
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project archive',
                        'heading' => 'An archive of submissions built to be scanned',
                        'summary' => 'Every project in the index, filterable by medium, status, country, and category. Compact metadata up front, full credits one click away.',
                        'primary_label' => 'Filter the archive',
                        'primary_url' => '#metadata-filters',
                        'secondary_label' => 'View winners',
                        'secondary_url' => '#winners-collections',
                        'actions' => [
                            ['label' => 'Filter the archive', 'url' => '#metadata-filters', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Project archive grid',
                    ],
                    $this->metadataFiltersSection(),
                    $this->latestSubmissionsSection(
                        heading: 'Recently indexed',
                        summary: 'The newest entries to clear curation, in submission order.',
                        media: $media,
                    ),
                    $this->contentListingSection($media),
                    $this->ctaSection(
                        heading: 'Cannot find the project you wanted?',
                        summary: 'Tell us what you are looking for, or submit the work yourself and we will get it into the index.',
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
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project · Winner 2025',
                        'heading' => 'Tidal Atlas — an identity for a coastal research network',
                        'summary' => 'Studio Meridian · Brand & Editorial · United Kingdom · Awarded in the 2025 collection. A flexible identity system for a network mapping the changing coastline.',
                        'primary_label' => 'View the studio profile',
                        'primary_url' => '#profiles-resources',
                        'secondary_label' => 'Back to submissions',
                        'secondary_url' => '#latest-submissions',
                        'actions' => [
                            ['label' => 'View the studio profile', 'url' => '#profiles-resources', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Tidal Atlas project imagery',
                    ],
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
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Submit & subscribe',
                        'heading' => 'Reach the directory through one confident path',
                        'summary' => 'Studios submit work for curation; readers subscribe to the weekly index. Email curators@indexofpractice.example or use the form below.',
                        'primary_label' => 'Subscribe to the index',
                        'primary_url' => '#newsletter',
                        'secondary_label' => 'Email the curators',
                        'secondary_url' => 'mailto:curators@indexofpractice.example',
                        'actions' => [
                            ['label' => 'Subscribe to the index', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Email the curators', 'url' => 'mailto:curators@indexofpractice.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Submit work to the directory',
                    ],
                    $this->newsletterSection(),
                    $this->metadataFiltersSection(heading: 'What we curate'),
                    $this->ctaSection(
                        heading: 'Ready to submit your project?',
                        summary: 'Send the work, the credits, and one strong image. We review submissions every Thursday and reply either way.',
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
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project archive',
                        'heading' => 'No projects match that filter — yet',
                        'summary' => 'Nothing in the index fits that combination of medium, status, and country. Clear the filter to see everything, or browse the latest entries.',
                        'primary_label' => 'Clear the filter',
                        'primary_url' => '#metadata-filters',
                        'secondary_label' => 'Browse latest',
                        'secondary_url' => '#latest-submissions',
                        'actions' => [
                            ['label' => 'Clear the filter', 'url' => '#metadata-filters', 'style' => 'primary'],
                            ['label' => 'Browse latest', 'url' => '#latest-submissions', 'style' => 'secondary'],
                        ],
                    ],
                    $this->metadataFiltersSection(),
                    $this->latestSubmissionsSection(
                        heading: 'While you are here',
                        summary: 'A few recently indexed projects across every medium.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific project?',
                        summary: 'Tell us the studio or the title and we will point you to it — or add it to the index if it is missing.',
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
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page is not in the index',
                        'summary' => 'The link is broken or the project has been unpublished. Head back to the latest submissions or browse the winners.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Browse submissions',
                        'secondary_url' => '#latest-submissions',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse submissions', 'url' => '#latest-submissions', 'style' => 'secondary'],
                        ],
                    ],
                    $this->winnersCollectionsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for a project?',
                        summary: 'Tell us the studio or title and we will point you to the right entry in the index.',
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
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Submit to the index',
                        'heading' => 'Put your work in front of the people who curate',
                        'summary' => 'One project, one profile, one weekly index seen by editors, studios, and award juries. Submitting takes ten minutes.',
                        'primary_label' => 'Submit a project',
                        'primary_url' => '#newsletter',
                        'secondary_label' => 'View winners',
                        'secondary_url' => '#winners-collections',
                        'actions' => [
                            ['label' => 'Submit a project', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'View winners', 'url' => '#winners-collections', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Submit your studio work',
                    ],
                    $this->winnersCollectionsSection(),
                    $this->sponsorModulesSection(),
                    $this->ctaSection(
                        heading: 'One submission away',
                        summary: 'Send the work and the credits. We review every Thursday and the strongest entries land in next week’s index.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function featuredTodaySection(array $media, string $heading = 'Featured today'): array
    {
        $pool = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['proof'])));

        $items = [
            ['title' => 'Tidal Atlas', 'meta' => 'Studio Meridian · Brand & Editorial · UK', 'summary' => 'A flexible identity for a coastal research network mapping the changing shoreline.'],
            ['title' => 'Northwind Type', 'meta' => 'Foundry Verda · Typeface · Germany', 'summary' => 'A grotesque type family drawn for renewable-energy interfaces in the field.'],
            ['title' => 'Harbour Sessions', 'meta' => 'Atlas Studio · Motion · United States', 'summary' => 'A motion identity for a dockside music series, built to read at festival scale.'],
        ];

        return [
            'type' => 'featured-today',
            'heading' => $heading,
            'summary' => 'Hand-picked projects the curators put at the top of the index today.',
            'items' => $this->withImages($items, $pool, 'Featured project'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataFiltersSection(string $heading = 'Discovery across projects, studios, and categories'): array
    {
        return [
            'type' => 'metadata-filters',
            'heading' => $heading,
            'summary' => 'Compact metadata filters keep results across statuses, mediums, and countries fast and legible.',
            'items' => [
                ['title' => 'Medium', 'summary' => 'Brand · Editorial · Type · Motion · Product · Spatial'],
                ['title' => 'Status', 'summary' => 'Submitted · Shortlisted · Winner · Collected'],
                ['title' => 'Country', 'summary' => 'United Kingdom · Germany · United States · Japan'],
                ['title' => 'Category', 'summary' => 'Identity · Packaging · Campaign · Design system'],
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
            'heading' => $heading,
            'summary' => $summary,
            'items' => $this->withImages($items, $pool, 'Recent submission'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function winnersCollectionsSection(): array
    {
        return [
            'type' => 'winners-collections',
            'heading' => 'Winners & collections',
            'summary' => 'The standout entries from this cycle, grouped into the curators’ named collections.',
            'label' => 'See every winner',
            'url' => '#latest-submissions',
            'actions' => [
                ['title' => 'Winner · Identity', 'summary' => 'Tidal Atlas by Studio Meridian — best in the brand & editorial medium.'],
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
            'heading' => $heading,
            'summary' => 'Studio profiles, credits, and the field guides curators recommend.',
            'stories' => [
                ['title' => 'Studio Meridian', 'meta' => 'Profile · Bristol, UK', 'summary' => 'A six-person identity and editorial studio behind three winning entries this cycle.'],
                ['title' => 'Foundry Verda', 'meta' => 'Profile · Berlin, DE', 'summary' => 'An independent type foundry drawing faces for screens and signage.'],
                ['title' => 'Submitting work', 'meta' => 'Resource · Guide', 'summary' => 'How to prepare credits, imagery, and metadata so your project clears curation first time.'],
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
            'heading' => 'Subscribe to the weekly index',
            'summary' => 'One email each Friday with the week’s featured projects, new winners, and freshly added collections.',
            'action' => '#newsletter',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $items = [
            ['title' => 'Atlas Festival rebrand', 'category' => 'Campaign · Shortlisted', 'summary' => 'A launch campaign and motion toolkit that took a regional arts festival national.'],
            ['title' => 'Studio Verda portfolio', 'category' => 'Website · Winner', 'summary' => 'An editorial portfolio site for an architecture practice that lets the buildings speak.'],
            ['title' => 'Foundry catalogue', 'category' => 'Editorial · Collected', 'summary' => 'A print and web specimen catalogue for a forty-face type library.'],
        ];

        return [
            'type' => 'content-listing',
            'heading' => 'More from the archive',
            'summary' => 'Smaller entries, experiments, and collaborations across the index.',
            'items' => $this->withImages($items, $pool, 'Archive entry'),
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
            'label' => 'Submit a project',
            'url' => '#newsletter',
            'actions' => [
                ['label' => 'Submit a project', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse submissions', 'url' => '#latest-submissions', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @param  list<array<string, string>>  $items
     * @param  list<string>  $pool
     * @return list<array<string, mixed>>
     */
    private function withImages(array $items, array $pool, string $altPrefix): array
    {
        $withImages = [];

        foreach ($items as $index => $item) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $withImages[] = [
                ...$item,
                'url' => '#project-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $altPrefix,
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
                ['label' => 'Submissions', 'url' => '#latest-submissions'],
                ['label' => 'Winners', 'url' => '#winners-collections'],
                ['label' => 'Collections', 'url' => '#winners-collections'],
                ['label' => 'Profiles', 'url' => '#profiles-resources'],
                ['label' => 'Submit', 'url' => '#newsletter'],
            ],
            'ctaLabel' => 'Submit a project',
            'ctaUrl' => '#newsletter',
            'consultationUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A curated directory of creative practice. Submissions reviewed every week.',
            'items' => [
                ['label' => 'Submissions', 'url' => '#latest-submissions'],
                ['label' => 'Winners', 'url' => '#winners-collections'],
                ['label' => 'Profiles', 'url' => '#profiles-resources'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
            'columns' => [
                [
                    'title' => 'Index',
                    'heading' => 'Index',
                    'links' => [
                        ['label' => 'Latest submissions', 'url' => '#latest-submissions'],
                        ['label' => 'Winners', 'url' => '#winners-collections'],
                        ['label' => 'Collections', 'url' => '#winners-collections'],
                        ['label' => 'Project archive', 'url' => '#content-listing'],
                    ],
                ],
                [
                    'title' => 'Studios',
                    'heading' => 'Studios',
                    'links' => [
                        ['label' => 'Profiles', 'url' => '#profiles-resources'],
                        ['label' => 'Resources', 'url' => '#profiles-resources'],
                        ['label' => 'Submit work', 'url' => '#newsletter'],
                    ],
                ],
                [
                    'title' => 'Connect',
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Subscribe', 'url' => '#newsletter'],
                        ['label' => 'curators@indexofpractice.example', 'url' => 'mailto:curators@indexofpractice.example'],
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
