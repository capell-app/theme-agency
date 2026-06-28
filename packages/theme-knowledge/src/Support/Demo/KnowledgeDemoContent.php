<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Knowledge\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Knowledge theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (featured-content /
 * topic-hubs / resource-library / reading-path / topic-index / authors /
 * newsletter / source-map) alongside the standard hero/proof/cta — giving every
 * surface a full knowledge-library page rather than the shared skeleton.
 */
final class KnowledgeDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Fieldnote Library';

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
            title: self::BRAND . ' — Research & documentation knowledge base',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A library a reader can actually navigate',
                'Fieldnote Library is an editorial knowledge base for topic hubs, featured research, a resource library, and search-led reader journeys.',
            ),
            renderData: [
                'summary' => 'An editorial knowledge base organised by topic, with featured research, a resource library, and prominent search.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Knowledge',
                        'heading' => 'A library a reader can actually navigate',
                        'summary' => 'An editorial knowledge-base homepage for topic hubs, featured research, a resource library, and search-led reader journeys. Find the answer first, read the long-form context second.',
                        'actions' => [
                            ['label' => 'Browse topics', 'url' => '#topic-hubs', 'style' => 'primary'],
                            ['label' => 'Search the library', 'url' => '#search', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Fieldnote Library knowledge base',
                    ],
                    $this->featuredContentSection(
                        heading: 'Featured research',
                        summary: 'Flagship guides and research notes promoted to the top of the library.',
                    ),
                    $this->topicHubsSection(
                        heading: 'Browse by topic',
                        summary: 'Deep archives made browseable by subject, so a large library never flattens into a single feed.',
                    ),
                    $this->resourceLibrarySection(
                        heading: 'The resource library',
                        summary: 'Guides, research notes, and templates collected into one searchable shelf.',
                    ),
                    $this->readingPathSection(
                        heading: 'Start here: a guided reading path',
                        summary: 'New to the library? Follow the path from fundamentals through to advanced practice.',
                    ),
                    $this->authorsSection(
                        heading: 'The people behind the research',
                        summary: 'An author bench that proves expertise without turning the library into a portfolio.',
                    ),
                    $this->newsletterSection(
                        heading: 'Turn reading intent into an owned audience',
                        summary: 'A research-digest signup converts engaged readers into subscribers as part of the library experience.',
                    ),
                    $this->proofSection(
                        heading: 'A reference teams come back to',
                        summary: 'How the library performs as a working reference, not a one-off read.',
                    ),
                    $this->ctaSection(
                        heading: 'Find the answer you came for',
                        summary: 'Search the full library or browse by topic — every article links onward to related research.',
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
            name: self::BRAND . ' Topics',
            title: 'Browse the library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse the full knowledge library',
                'Every guide, research note, and template, organised by topic and filterable by format.',
            ),
            renderData: [
                'summary' => 'Every guide, research note, and template in the library, organised by topic and filterable by format.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Library index',
                        'heading' => 'Browse the full knowledge library',
                        'summary' => 'Search the library without it collapsing into a list. Faceted results keep guides, research notes, and templates legible while the page stays a serious reading surface.',
                        'actions' => [
                            ['label' => 'Search the library', 'url' => '#search', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Knowledge library index',
                    ],
                    $this->topicIndexSection(
                        heading: 'Topics A–Z',
                        summary: 'Jump straight to the subject you need.',
                    ),
                    $this->topicHubsSection(
                        heading: 'Topic hubs',
                        summary: 'Structured collections that open a large archive without flattening it into a blog feed.',
                    ),
                    $this->contentListingSection(
                        heading: 'Latest in the library',
                        summary: 'Recently published and updated entries across every topic.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Cannot find the right topic?',
                        summary: 'Search the full library, or tell us what is missing and we will point you to the closest match.',
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
            name: self::BRAND . ' Article',
            title: 'Designing a retrieval-first knowledge base — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Designing a retrieval-first knowledge base',
                'A long-form guide on structuring a documentation library so readers find answers before they start reading.',
            ),
            renderData: [
                'summary' => 'A long-form guide on structuring a documentation library so readers find answers before they start reading.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Guide · 14 min read',
                        'heading' => 'Designing a retrieval-first knowledge base',
                        'summary' => 'How to structure topic hubs, search, and reading paths so a deep archive stays navigable — with the sources and further reading that back each recommendation.',
                        'actions' => [
                            ['label' => 'Back to the library', 'url' => '#resource-library', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Knowledge base architecture diagram',
                    ],
                    $this->sourceMapSection(
                        heading: 'Sources & further reading',
                        summary: 'Every claim in this guide traces back to a primary source.',
                    ),
                    $this->readingPathSection(
                        heading: 'Where to go next',
                        summary: 'Continue the path from this article into the rest of the topic.',
                    ),
                    $this->contentListingSection(
                        heading: 'Related research',
                        summary: 'Other entries in the same topic hub.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Keep reading the topic',
                        summary: 'Open the full topic hub or jump to the next article in the reading path.',
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
            name: self::BRAND . ' Contribute',
            title: 'Contribute to the library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Suggest a topic or submit research',
                'Tell the editors what is missing, or propose a guide for the library. Every submission is reviewed by the research team.',
            ),
            renderData: [
                'summary' => 'Suggest a topic, report a gap, or propose research for the library — reviewed directly by the editorial team.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contribute',
                        'heading' => 'Suggest a topic or submit research',
                        'summary' => 'Spotted a gap, or have research worth publishing? Email editors@fieldnote.example or use the details below. Submissions are reviewed within two working days.',
                        'actions' => [
                            ['label' => 'Email the editors', 'url' => 'mailto:editors@fieldnote.example', 'style' => 'primary'],
                            ['label' => 'Browse topics', 'url' => '#topic-hubs', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Fieldnote Library editorial desk',
                    ],
                    $this->featuresSection(
                        heading: 'How contributions work',
                        summary: 'The path from a suggestion to a published, sourced library entry.',
                    ),
                    $this->newsletterSection(
                        heading: 'Stay in the loop while you wait',
                        summary: 'Subscribe to the research digest and we will tell you when your topic lands.',
                    ),
                    $this->ctaSection(
                        heading: 'Have research ready to publish?',
                        summary: 'Send the draft and our editors will come back with sourcing notes and a publishing slot.',
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
            title: 'No results — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No entries match that search yet',
                'A graceful empty state for a filtered library search with no matching guides or research notes.',
            ),
            renderData: [
                'summary' => 'No entries match that search yet — but the library can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Search results',
                        'heading' => 'No entries match that search — yet',
                        'summary' => 'Nothing in the library matches those terms. Clear the filters to see everything, or browse by topic to find the nearest answer.',
                        'actions' => [
                            ['label' => 'Browse topics', 'url' => '#topic-hubs', 'style' => 'primary'],
                            ['label' => 'Clear search', 'url' => '#search', 'style' => 'secondary'],
                        ],
                    ],
                    $this->topicIndexSection(
                        heading: 'Try a topic instead',
                        summary: 'Open a subject and read from the top of its archive.',
                    ),
                    $this->featuredContentSection(
                        heading: 'Popular research while you are here',
                        summary: 'The entries readers open most often this month.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell the editors what you searched for and we will tell you whether it is on the way.',
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
                'That page has moved',
                'A not-found page that routes readers back into the library topics and search.',
            ),
            renderData: [
                'summary' => 'That entry has moved or never existed — here is the way back into the library.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page has been archived or moved',
                        'summary' => 'The link is broken or the entry has been retired. Head back to the library, or search for what you needed.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Search the library', 'url' => '#search', 'style' => 'secondary'],
                        ],
                    ],
                    $this->topicIndexSection(
                        heading: 'Pick up a topic instead',
                        summary: 'These hubs are the most-read starting points in the library.',
                    ),
                    $this->ctaSection(
                        heading: 'Still cannot find it?',
                        summary: 'Tell the editors what you were looking for and we will redirect you to the right entry.',
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
            title: 'Get the research digest — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Get the research digest',
                'A focused conversion page inviting readers to subscribe to the weekly knowledge digest.',
            ),
            renderData: [
                'summary' => 'Get the weekly research digest — the best new guides and notes from the library, in your inbox.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Research digest',
                        'heading' => 'The best of the library, every week',
                        'summary' => 'One short email: the standout new guides, updated research notes, and the topic hubs readers are leaning on right now.',
                        'actions' => [
                            ['label' => 'Subscribe free', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Browse topics', 'url' => '#topic-hubs', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Research digest email preview',
                    ],
                    $this->newsletterSection(
                        heading: 'Turn reading intent into an owned audience',
                        summary: 'A research-digest signup converts engaged readers into subscribers as part of the library experience.',
                    ),
                    $this->proofSection(
                        heading: 'Why readers subscribe',
                        summary: 'What the digest delivers, in numbers.',
                    ),
                    $this->ctaSection(
                        heading: 'One email away',
                        summary: 'Subscribe now and the next digest lands in your inbox this week.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function featuredContentSection(string $heading, string $summary): array
    {
        return [
            'type' => 'featured-content',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The retrieval-first documentation playbook', 'type' => 'Flagship guide', 'summary' => 'A field-tested framework for structuring docs so readers find answers before they start reading.'],
                ['title' => 'State of internal knowledge bases 2026', 'type' => 'Research report', 'summary' => 'Survey findings from 240 documentation teams on search, structure, and maintenance.'],
                ['title' => 'Writing reference docs that age well', 'type' => 'Editorial standard', 'summary' => 'The house style behind every entry in the library, with worked before-and-after examples.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topicHubsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'topic-hubs',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Information architecture', 'name' => 'Information architecture', 'summary' => 'Topic modelling, navigation, and search design for large reference libraries.', 'description' => '38 guides'],
                ['title' => 'Editorial standards', 'name' => 'Editorial standards', 'summary' => 'House style, sourcing, and review workflows that keep entries trustworthy.', 'description' => '24 guides'],
                ['title' => 'Search & retrieval', 'name' => 'Search & retrieval', 'summary' => 'Faceting, ranking, and query design for documentation search.', 'description' => '19 guides'],
                ['title' => 'Maintenance & freshness', 'name' => 'Maintenance & freshness', 'summary' => 'Review cycles, deprecation, and keeping a deep archive current.', 'description' => '21 guides'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resourceLibrarySection(string $heading, string $summary): array
    {
        return [
            'type' => 'resource-library',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Documentation audit checklist', 'type' => 'Template', 'summary' => 'A 40-point checklist for assessing the health of an existing knowledge base.'],
                ['title' => 'Topic hub planning worksheet', 'type' => 'Template', 'summary' => 'Map subjects to hubs before you write a single entry.'],
                ['title' => 'Sourcing & citation guide', 'type' => 'Guide', 'summary' => 'How the library cites primary research and flags secondary claims.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readingPathSection(string $heading, string $summary): array
    {
        return [
            'type' => 'reading-path',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Why structure beats search alone', 'type' => 'Step 1 · Foundations', 'summary' => 'How topic hubs and search reinforce each other in a reference library.'],
                ['title' => 'Modelling your first topic hub', 'type' => 'Step 2 · Practice', 'summary' => 'Turn a messy subject into a navigable hub with a clear reading order.'],
                ['title' => 'Keeping the archive trustworthy', 'type' => 'Step 3 · Maintenance', 'summary' => 'Review cycles and sourcing standards that survive contact with a growing library.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topicIndexSection(string $heading, string $summary): array
    {
        return [
            'type' => 'topic-index',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Information architecture', 'type' => 'Topic', 'summary' => '38 guides on structuring large reference libraries.'],
                ['title' => 'Editorial standards', 'type' => 'Topic', 'summary' => '24 guides on house style, sourcing, and review.'],
                ['title' => 'Search & retrieval', 'type' => 'Topic', 'summary' => '19 guides on faceting, ranking, and query design.'],
                ['title' => 'Maintenance & freshness', 'type' => 'Topic', 'summary' => '21 guides on review cycles and deprecation.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function authorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'authors',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Dr. Amara Osei', 'name' => 'Dr. Amara Osei', 'summary' => 'Lead researcher on information architecture. Fifteen years building reference systems for technical teams.', 'description' => 'Lead researcher'],
                ['title' => 'Jonas Reuter', 'name' => 'Jonas Reuter', 'summary' => 'Managing editor. Sets the sourcing standard and reviews every flagship guide before it ships.', 'description' => 'Managing editor'],
                ['title' => 'Mei-Ling Tan', 'name' => 'Mei-Ling Tan', 'summary' => 'Search and retrieval specialist. Writes the library\'s guidance on faceting and ranking.', 'description' => 'Search specialist'],
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
            'ctaLabel' => 'Subscribe to the digest',
            'ctaUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sourceMapSection(string $heading, string $summary): array
    {
        return [
            'type' => 'source-map',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'State of internal knowledge bases 2026', 'type' => 'Primary research', 'summary' => 'Fieldnote Library survey of 240 documentation teams, published February 2026.'],
                ['title' => 'Search behaviour in reference libraries', 'type' => 'Peer-reviewed study', 'summary' => 'Longitudinal study of query patterns across enterprise documentation portals.'],
                ['title' => 'Topic modelling for large archives', 'type' => 'Technical paper', 'summary' => 'The clustering approach behind the library\'s topic hub structure.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Suggest a topic', 'type' => 'Step 1', 'summary' => 'Tell us the gap. We check it against the existing archive and the editorial roadmap.'],
                ['title' => 'Editorial review', 'type' => 'Step 2', 'summary' => 'A managing editor scopes the entry, confirms sourcing, and assigns a reviewer.'],
                ['title' => 'Publish with sources', 'type' => 'Step 3', 'summary' => 'Your guide ships into the right topic hub, cited and linked into the reading path.'],
            ],
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
                ['metric' => '1,400+', 'name' => 'Published entries', 'summary' => 'Guides, research notes, and templates across every topic hub.'],
                ['metric' => '92%', 'name' => 'Answers found on first search', 'summary' => 'Readers reach the right entry without leaving the library.'],
                ['metric' => '21k', 'name' => 'Weekly digest subscribers', 'summary' => 'An owned audience built from reading intent.'],
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
            ['title' => 'Faceted search that stays legible', 'type' => 'Guide', 'summary' => 'Designing filters that narrow a large library without overwhelming the reader.'],
            ['title' => 'When to retire an archived entry', 'type' => 'Editorial note', 'summary' => 'A decision framework for deprecating content that no longer holds up.'],
            ['title' => 'Reading paths vs. free browsing', 'type' => 'Research note', 'summary' => 'What the data says about guided journeys through a reference library.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#entry-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'gallery',
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
            'actions' => [
                ['label' => 'Search the library', 'url' => '#search', 'style' => 'primary'],
                ['label' => 'Browse topics', 'url' => '#topic-hubs', 'style' => 'secondary'],
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
            'items' => [
                ['label' => 'Topics', 'url' => '#topic-hubs'],
                ['label' => 'Research', 'url' => '#featured-content'],
                ['label' => 'Library', 'url' => '#resource-library'],
                ['label' => 'Authors', 'url' => '#authors'],
                ['label' => 'Search', 'url' => '#search'],
            ],
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An editorial knowledge base for research, documentation, and content-led teams.',
            'columns' => [
                [
                    'heading' => 'Library',
                    'links' => [
                        ['label' => 'Topics', 'url' => '#topic-hubs'],
                        ['label' => 'Featured research', 'url' => '#featured-content'],
                        ['label' => 'Resource library', 'url' => '#resource-library'],
                        ['label' => 'Reading paths', 'url' => '#reading-path'],
                    ],
                ],
                [
                    'heading' => 'About',
                    'links' => [
                        ['label' => 'Authors', 'url' => '#authors'],
                        ['label' => 'Editorial standards', 'url' => '#topic-hubs'],
                        ['label' => 'Contribute', 'url' => '#contact'],
                    ],
                ],
                [
                    'heading' => 'Stay in touch',
                    'links' => [
                        ['label' => 'Research digest', 'url' => '#newsletter'],
                        ['label' => 'editors@fieldnote.example', 'url' => 'mailto:editors@fieldnote.example'],
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
