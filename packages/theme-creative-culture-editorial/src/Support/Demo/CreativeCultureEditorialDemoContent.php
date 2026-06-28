<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeCultureEditorial\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Creative Culture Editorial theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature editorial renderers (must-reads /
 * project-stories / opinion-block / advice-culture / events-tags /
 * discipline-browsing / newsletter) alongside the standard hero/proof/cta — giving
 * every surface a full art-directed publication rather than the shared skeleton.
 */
final class CreativeCultureEditorialDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Maker & Margin';

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
            title: self::BRAND . ' — Culture, Craft & Creative Practice',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'An editorial home for the people who make culture',
                'Maker & Margin is an independent magazine about design, craft, and the working lives behind the work — projects, opinion, advice, and culture, art-directed to be read.',
            ),
            renderData: [
                'summary' => 'Maker & Margin is an independent magazine about design, craft, and the working lives behind the work. Projects, opinion, advice, and culture — art-directed to be read.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'This week',
                        'heading' => 'Culture, projects, and opinion worth your attention',
                        'summary' => 'An independent publication for designers, makers, and the curious — long reads on the work, the people behind it, and the decisions that shaped it.',
                        'primary_label' => 'Read the latest projects',
                        'primary_url' => '#project-stories',
                        'secondary_label' => 'Join the newsletter',
                        'secondary_url' => '#newsletter',
                        'notes' => [
                            'New issue every Thursday, free to read',
                            'Long-form features, no listicles',
                            '12,000 readers across studios and schools',
                        ],
                        'actions' => [
                            ['label' => 'Read the latest projects', 'url' => '#project-stories', 'style' => 'primary'],
                            ['label' => 'Join the newsletter', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Maker & Margin cover feature',
                    ],
                    $this->mustReadsSection(),
                    $this->projectStoriesSection(
                        heading: 'Project stories',
                        summary: 'Long features on the work and the working lives behind it — process, false starts, and the call that made it land.',
                    ),
                    $this->opinionSection(),
                    $this->adviceCultureSection(
                        heading: 'Advice & culture',
                        summary: 'Practical counsel for working makers, told through the people living it.',
                    ),
                    $this->eventsTagsSection(),
                    $this->disciplineBrowsingSection(),
                    $this->proofSection(
                        heading: 'Why readers stay',
                        summary: 'A small, loyal readership of people who actually make things.',
                    ),
                    $this->newsletterSection(
                        heading: 'A letter worth opening',
                        summary: 'One considered email each Thursday — the issue, plus the cuts that did not make it.',
                    ),
                    $this->ctaSection(
                        heading: 'Read the magazine that respects your attention',
                        summary: 'Free to read, no walls. Start with this week\'s issue or browse the archive.',
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
            title: 'The Archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'An archive built to be browsed',
                'Every project story, opinion piece, and culture feature, kept legible and art-directed across disciplines and tags.',
            ),
            renderData: [
                'summary' => 'Every project story, opinion piece, and culture feature — kept legible and art-directed across disciplines and tags.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The archive',
                        'heading' => 'An archive built to be browsed',
                        'summary' => 'Structured listing cards keep projects, opinion, advice, and culture stories scannable — filter by discipline or follow a tag.',
                        'primary_label' => 'Browse disciplines',
                        'primary_url' => '#discipline-browsing',
                        'secondary_label' => 'Popular tags',
                        'secondary_url' => '#events-tags',
                        'notes' => [
                            'Four years of features',
                            'Sorted by discipline and tag',
                            'New stories added weekly',
                        ],
                        'actions' => [
                            ['label' => 'Browse disciplines', 'url' => '#discipline-browsing', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Maker & Margin archive grid',
                    ],
                    $this->contentListingSection(
                        heading: 'Recent from the archive',
                        summary: 'The latest features across every section of the magazine.',
                    ),
                    $this->disciplineBrowsingSection(),
                    $this->eventsTagsSection(),
                    $this->ctaSection(
                        heading: 'Found a thread worth following?',
                        summary: 'Subscribe and the next story in that discipline lands in your inbox first.',
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
            name: self::BRAND . ' Feature',
            title: 'The Kiln Room — Feature — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Kiln Room: a studio that rebuilt itself around its waste',
                'How a small ceramics studio turned its offcuts and seconds into the heart of its practice — and its margin.',
            ),
            renderData: [
                'summary' => 'How a small ceramics studio turned its offcuts and seconds into the heart of its practice — and its margin.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Feature',
                        'heading' => 'The Kiln Room: a studio that rebuilt itself around its waste',
                        'summary' => 'A feature page art-directed to be read — image-led storytelling paired with opinion and advice, so a project reads as culture, not just a record.',
                        'primary_label' => 'Read more features',
                        'primary_url' => '#project-stories',
                        'secondary_label' => 'Join the newsletter',
                        'secondary_url' => '#newsletter',
                        'notes' => [
                            'A 3,000-word read',
                            'Photography by the studio',
                            'Part of the Craft & Margin series',
                        ],
                        'actions' => [
                            ['label' => 'Read more features', 'url' => '#project-stories', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Inside the Kiln Room studio',
                    ],
                    $this->projectStoriesSection(
                        heading: 'How the piece came together',
                        summary: 'The process behind the feature, told in the maker\'s own order — material first, market second.',
                    ),
                    $this->opinionSection(),
                    $this->adviceCultureSection(
                        heading: 'What this studio would tell you',
                        summary: 'Hard-won advice for makers weighing craft against margin.',
                    ),
                    $this->contentListingSection(
                        heading: 'Keep reading',
                        summary: 'More features in the same neighbourhood.',
                    ),
                    $this->ctaSection(
                        heading: 'Stories like this, every Thursday',
                        summary: 'Subscribe for one long read a week from studios you have not heard of yet.',
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
            name: self::BRAND . ' Subscribe',
            title: 'Subscribe & pitch — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Turn readers into subscribers and contributors',
                'Join the newsletter, pitch a story, or bring the magazine to your studio — every path starts here.',
            ),
            renderData: [
                'summary' => 'Join the newsletter, pitch a story, or bring the magazine to your studio. Every path into Maker & Margin starts here.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get involved',
                        'heading' => 'Turn readers into subscribers and contributors',
                        'summary' => 'Whether you want the weekly letter, a pitch read, or a partnership for an event — write to hello@makerandmargin.example and a real editor replies.',
                        'primary_label' => 'Join the newsletter',
                        'primary_url' => '#newsletter',
                        'secondary_label' => 'Pitch a story',
                        'secondary_url' => 'mailto:hello@makerandmargin.example',
                        'notes' => [
                            'Edited in London, read everywhere',
                            'We answer every pitch',
                            'Contributors paid on acceptance',
                        ],
                        'actions' => [
                            ['label' => 'Join the newsletter', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Pitch a story', 'url' => 'mailto:hello@makerandmargin.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Maker & Margin editorial desk',
                    ],
                    $this->newsletterSection(
                        heading: 'A letter worth opening',
                        summary: 'A non-submitting signup that proves the subscribe journey feels like part of the editorial experience.',
                    ),
                    $this->eventsTagsSection(),
                    $this->proofSection(
                        heading: 'What you are joining',
                        summary: 'The readership, in numbers.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a story the magazine should tell?',
                        summary: 'Send a paragraph to hello@makerandmargin.example. We read everything and reply within a week.',
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
            title: 'No stories here yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No stories match that filter yet',
                'A graceful empty state for a filtered archive with no matching features.',
            ),
            renderData: [
                'summary' => 'No stories match that filter yet — but the magazine can still point you somewhere worth reading.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The archive',
                        'heading' => 'No stories match that filter — yet',
                        'summary' => 'We have not published in this corner of the archive. Clear the filter to see everything, or follow a tag and we will tell you when one lands.',
                        'primary_label' => 'Browse all stories',
                        'primary_url' => '#content-listing',
                        'secondary_label' => 'Popular tags',
                        'secondary_url' => '#events-tags',
                        'notes' => [
                            'Nothing in this filter right now',
                            'New features added weekly',
                            'Follow a tag to be notified',
                        ],
                        'actions' => [
                            ['label' => 'Browse all stories', 'url' => '#content-listing', 'style' => 'primary'],
                            ['label' => 'Popular tags', 'url' => '#events-tags', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When features land in this filter they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->mustReadsSection(),
                    $this->eventsTagsSection(),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the subject and we will point you to the closest features in the archive.',
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
                'This page slipped past the editors',
                'A not-found page that routes readers back into the features and the newsletter.',
            ),
            renderData: [
                'summary' => 'That page has moved or never ran — here is the way back into the magazine.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page slipped past the editors',
                        'summary' => 'The link is broken or the story has moved. Head back to the latest features, or start with the newsletter.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Read the latest',
                        'secondary_url' => '#project-stories',
                        'notes' => [
                            'The link may be out of date',
                            'Every issue stays in the archive',
                            'Search is on the archive page',
                        ],
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Read the latest', 'url' => '#project-stories', 'style' => 'secondary'],
                        ],
                    ],
                    $this->mustReadsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right feature.',
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
            name: self::BRAND . ' Subscribe CTA',
            title: 'Read the magazine — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'One long read a week, from makers worth knowing',
                'A focused conversion page inviting readers to subscribe to the weekly letter.',
            ),
            renderData: [
                'summary' => 'One long read a week, from makers worth knowing. Join the Maker & Margin newsletter.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Subscribe',
                        'heading' => 'One long read a week, from makers worth knowing',
                        'summary' => 'No walls, no spam — a single considered email each Thursday with the issue and the cuts that did not make it.',
                        'primary_label' => 'Join the newsletter',
                        'primary_url' => '#newsletter',
                        'secondary_label' => 'Read a sample issue',
                        'secondary_url' => '#project-stories',
                        'notes' => [
                            'Free, every Thursday',
                            'Unsubscribe in one click',
                            '12,000 readers and counting',
                        ],
                        'actions' => [
                            ['label' => 'Join the newsletter', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Read a sample issue', 'url' => '#project-stories', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Maker & Margin issue spread',
                    ],
                    $this->proofSection(
                        heading: 'Why readers subscribe',
                        summary: 'The case for one more email in your week.',
                    ),
                    $this->newsletterSection(
                        heading: 'Start with this Thursday',
                        summary: 'Drop your email and the next issue lands first.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Join the letter and the next long read is on its way.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mustReadsSection(): array
    {
        return [
            'type' => 'must-reads',
            'heading' => 'Must-reads this month',
            'summary' => 'The features readers kept coming back to — start here if you are new to the magazine.',
            'items' => [
                ['title' => 'The studio that priced its time and survived', 'summary' => 'A two-person practice rebuilt its rate card around honesty and stopped dreading invoices.'],
                ['title' => 'Why we still photograph the seconds', 'summary' => 'An essay on flaws, offcuts, and the quiet case for showing the work that did not sell.'],
                ['title' => 'The week a font ate a brand', 'summary' => 'How one typographic decision rippled across a packaging launch — and what it taught the team.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectStoriesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'project-stories',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The Kiln Room', 'meta' => 'Ceramics · London', 'care_note' => '3,000-word read', 'summary' => 'A ceramics studio turned its offcuts and seconds into the centre of its practice — and its margin.'],
                ['title' => 'Press & Fold', 'meta' => 'Bookbinding · Glasgow', 'care_note' => 'Photo essay', 'summary' => 'A bindery that teaches by daylight and ships by candlelight, on keeping a craft slow on purpose.'],
                ['title' => 'Counterweight Type', 'meta' => 'Type design · Berlin', 'care_note' => 'Process diary', 'summary' => 'Two designers drawing a typeface for signage no one will read up close — and getting it perfect anyway.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function opinionSection(): array
    {
        return [
            'type' => 'opinion-block',
            'heading' => 'Opinion',
            'summary' => 'Sharp, signed arguments from people doing the work — the column readers reply to.',
            'url' => '#content-listing',
            'label' => 'Read all opinion',
            'items' => [
                ['title' => '"Stop apologising for charging properly"', 'summary' => 'A working illustrator on why undercharging is not humility — it is a tax on the next person in the queue.'],
                ['title' => '"The portfolio is dead; long live the practice"', 'summary' => 'Why showing how you think now beats showing what you shipped three years ago.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adviceCultureSection(string $heading, string $summary): array
    {
        return [
            'type' => 'advice-culture',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Raising your rates without losing your nerve', 'summary' => 'A four-step script for the conversation every freelancer dreads, from people who have had it.'],
                ['title' => 'Building a studio culture of two', 'summary' => 'How the smallest practices protect taste, rest, and the will to keep making.'],
                ['title' => 'The reading list behind the work', 'summary' => 'The books, exhibitions, and arguments shaping how this month\'s makers think.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventsTagsSection(): array
    {
        return [
            'type' => 'events-tags',
            'heading' => 'Events & popular tags',
            'summary' => 'Where the community is gathering, and the threads readers are following.',
            'items' => [
                ['meta' => 'Talk · 14 Mar', 'title' => 'Pricing the unpriceable: a night on craft economics', 'summary' => 'Three studio owners on what it really costs to make beautiful things slowly.'],
                ['meta' => 'Workshop · 28 Mar', 'title' => 'Photographing your own work, badly then well', 'summary' => 'A hands-on session on lighting the things you make with what you already own.'],
                ['meta' => 'Tag · #margin', 'title' => 'Following the money behind the making', 'summary' => 'Every feature where the numbers, not just the craft, take centre stage.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function disciplineBrowsingSection(): array
    {
        return [
            'type' => 'discipline-browsing',
            'heading' => 'Browse by discipline',
            'summary' => 'Follow the craft you care about — every section is its own running story.',
            'items' => [
                ['title' => 'Ceramics & material', 'summary' => 'Clay, glaze, and the studios betting their margin on doing it the hard way.'],
                ['title' => 'Type & graphic design', 'summary' => 'Letterforms, identities, and the arguments behind the kerning.'],
                ['title' => 'Print & binding', 'summary' => 'Paper, ink, and the slow trades keeping the physical object alive.'],
                ['title' => 'Studio & practice', 'summary' => 'Rates, rest, and the business of staying independent.'],
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
                ['value' => '12k', 'label' => 'Readers across studios, schools, and agencies'],
                ['value' => '64%', 'label' => 'Open rate on the Thursday letter'],
                ['value' => '4 yrs', 'label' => 'Of features, free to read in the archive'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['category' => 'Project story', 'title' => 'The Kiln Room', 'summary' => 'A ceramics studio that built its practice around its waste.'],
                ['category' => 'Opinion', 'title' => 'Stop apologising for charging properly', 'summary' => 'Undercharging is not humility — it is a tax on the next maker.'],
                ['category' => 'Advice', 'title' => 'Raising your rates without losing your nerve', 'summary' => 'The conversation every freelancer dreads, scripted by people who have had it.'],
                ['category' => 'Culture', 'title' => 'The reading list behind the work', 'summary' => 'What this month\'s makers are reading, watching, and arguing about.'],
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
            'heading' => $heading,
            'summary' => $summary,
            'url' => '#newsletter',
            'label' => 'Join the newsletter',
            'actions' => [
                ['label' => 'Join the newsletter', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse the archive', 'url' => '#content-listing', 'style' => 'secondary'],
            ],
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
                ['label' => 'Projects', 'url' => '#project-stories'],
                ['label' => 'Opinion', 'url' => '#opinion-block'],
                ['label' => 'Advice', 'url' => '#advice-culture'],
                ['label' => 'Culture', 'url' => '#advice-culture'],
                ['label' => 'Events', 'url' => '#events-tags'],
                ['label' => 'Archive', 'url' => '#content-listing'],
            ],
            'ctaLabel' => 'Join the newsletter',
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
            'summary' => 'An independent magazine about design, craft, and the working lives behind the work. Edited in London, read everywhere.',
            'items' => [
                [
                    'title' => 'Read',
                    'heading' => 'Read',
                    'links' => [
                        ['label' => 'Project stories', 'url' => '#project-stories'],
                        ['label' => 'Opinion', 'url' => '#opinion-block'],
                        ['label' => 'Advice & culture', 'url' => '#advice-culture'],
                        ['label' => 'The archive', 'url' => '#content-listing'],
                    ],
                ],
                [
                    'title' => 'Discover',
                    'heading' => 'Discover',
                    'links' => [
                        ['label' => 'Browse disciplines', 'url' => '#discipline-browsing'],
                        ['label' => 'Events', 'url' => '#events-tags'],
                        ['label' => 'Popular tags', 'url' => '#events-tags'],
                        ['label' => 'Must-reads', 'url' => '#must-reads'],
                    ],
                ],
                [
                    'title' => 'The magazine',
                    'heading' => 'The magazine',
                    'links' => [
                        ['label' => 'Newsletter', 'url' => '#newsletter'],
                        ['label' => 'Pitch a story', 'url' => 'mailto:hello@makerandmargin.example'],
                        ['label' => 'hello@makerandmargin.example', 'url' => 'mailto:hello@makerandmargin.example'],
                    ],
                ],
            ],
            'columns' => [
                [
                    'heading' => 'Read',
                    'links' => [
                        ['label' => 'Project stories', 'url' => '#project-stories'],
                        ['label' => 'Opinion', 'url' => '#opinion-block'],
                        ['label' => 'Advice & culture', 'url' => '#advice-culture'],
                        ['label' => 'The archive', 'url' => '#content-listing'],
                    ],
                ],
                [
                    'heading' => 'Discover',
                    'links' => [
                        ['label' => 'Browse disciplines', 'url' => '#discipline-browsing'],
                        ['label' => 'Events', 'url' => '#events-tags'],
                        ['label' => 'Popular tags', 'url' => '#events-tags'],
                        ['label' => 'Must-reads', 'url' => '#must-reads'],
                    ],
                ],
                [
                    'heading' => 'The magazine',
                    'links' => [
                        ['label' => 'Newsletter', 'url' => '#newsletter'],
                        ['label' => 'Pitch a story', 'url' => 'mailto:hello@makerandmargin.example'],
                        ['label' => 'hello@makerandmargin.example', 'url' => 'mailto:hello@makerandmargin.example'],
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
