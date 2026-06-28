<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreatorNewsletter\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Creator Newsletter theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (subscribe-hero / features
 * / archive / testimonials / sponsors / about-author / content-listing / proof)
 * alongside the standard hero/cta — giving every surface a full newsletter site
 * rather than the shared skeleton.
 */
final class CreatorNewsletterDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'The Slow Dispatch';

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
            title: self::BRAND . ' — A weekly newsletter worth opening',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A weekly newsletter readers look forward to',
                'The Slow Dispatch is a Sunday-morning letter about working with intention — one essay, one field note, no noise.',
            ),
            renderData: [
                'summary' => 'The Slow Dispatch is a weekly letter about working with intention. One essay, one field note, delivered every Sunday morning to twenty-six thousand readers.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'A weekly newsletter',
                        'heading' => 'A newsletter readers actually look forward to',
                        'summary' => 'Every Sunday, one essay and one field note about working with intention. No growth hacks, no ten-tweet threads — just writing that earns the open.',
                        'actions' => [
                            ['label' => 'Subscribe free', 'url' => '#subscribe', 'style' => 'primary'],
                            ['label' => 'Read the archive', 'url' => '#archive', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Slow Dispatch newsletter',
                    ],
                    $this->subscribeHeroSection(
                        heading: 'Join twenty-six thousand Sunday readers',
                        summary: 'One letter a week, free forever. No spam, unsubscribe in a single click whenever you like.',
                    ),
                    $this->featuresSection(
                        heading: 'What lands in your inbox',
                        summary: 'A predictable rhythm readers can plan their Sunday around.',
                    ),
                    $this->archiveSection(
                        heading: 'Recent issues',
                        summary: 'Catch up on the last few letters before you subscribe.',
                        media: $media,
                    ),
                    $this->testimonialsSection(
                        heading: 'What readers say',
                        summary: 'The Slow Dispatch reaches founders, designers, and writers in forty countries.',
                    ),
                    $this->sponsorsSection(
                        heading: 'Trusted by thoughtful brands',
                        summary: 'A single, clearly marked sponsor each week — never more.',
                    ),
                    $this->aboutAuthorSection(
                        heading: 'Written by Mara Ellison',
                        summary: 'A former product lead who left to write full time. This is her only newsletter.',
                    ),
                    $this->proofSection(
                        heading: 'The numbers behind the letter',
                        summary: 'Five years of writing, every Sunday without fail.',
                    ),
                    $this->ctaSection(
                        heading: 'Start reading this Sunday',
                        summary: 'Subscribe now and the next issue lands in your inbox first thing Sunday morning.',
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
            title: 'Archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every issue, organised to be browsed',
                'The full back catalogue of The Slow Dispatch, from the first letter to last Sunday.',
            ),
            renderData: [
                'summary' => 'The full back catalogue of The Slow Dispatch — every essay and field note, organised so it stays legible and inviting.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The archive',
                        'heading' => 'Every issue, organised to be browsed',
                        'summary' => 'Two hundred and sixty letters and counting. Start with a recent issue, or wander back to where the writing began.',
                        'actions' => [
                            ['label' => 'Subscribe free', 'url' => '#subscribe', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The Slow Dispatch archive',
                    ],
                    $this->archiveSection(
                        heading: 'Recent issues',
                        summary: 'The latest letters, newest first.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'From the back catalogue',
                        summary: 'Reader favourites and the essays that found a second life.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want every issue in your inbox?',
                        summary: 'Subscribe free and never miss a Sunday letter again.',
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
            name: self::BRAND . ' Issue',
            title: 'Issue 261: The cost of always-on — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Issue 261 — The cost of always-on',
                'Why the most productive people you know guard their attention like it is the scarce resource it actually is.',
            ),
            renderData: [
                'summary' => 'Issue 261 — why the most productive people guard their attention like the scarce resource it actually is.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Issue 261 · Sunday',
                        'heading' => 'The cost of always-on',
                        'summary' => 'This week: why the most productive people you know treat their attention like the scarce resource it actually is — and the small ritual that protects it.',
                        'actions' => [
                            ['label' => 'Read the full issue', 'url' => '#issue', 'style' => 'primary'],
                            ['label' => 'Browse the archive', 'url' => '#archive', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Slow Dispatch issue 261',
                    ],
                    $this->archiveSection(
                        heading: 'A single issue that reads beautifully',
                        summary: 'The writing paired with the field note, the way it lands in your inbox.',
                        media: $media,
                    ),
                    $this->aboutAuthorSection(
                        heading: 'Written by Mara Ellison',
                        summary: 'The voice behind every Sunday letter since issue one.',
                    ),
                    $this->proofSection(
                        heading: 'Why readers stay',
                        summary: 'What keeps the open rate north of sixty per cent.',
                    ),
                    $this->ctaSection(
                        heading: 'Get the next issue first',
                        summary: 'Subscribe free and Issue 262 lands in your inbox this Sunday.',
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
            title: 'Subscribe — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the creator through one warm path',
                'Subscribe, reply to any issue, or pitch a sponsorship — every path leads to the same inbox.',
            ),
            renderData: [
                'summary' => 'Subscribe, reply to any issue, or pitch a sponsorship. Every path leads to the same inbox, and Mara reads them all.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get in touch',
                        'heading' => 'Reach the creator through one warm path',
                        'summary' => 'The simplest way to reach Mara is to subscribe and reply — every issue arrives from a real inbox she reads herself. Sponsorship enquiries welcome at hello@slowdispatch.example.',
                        'actions' => [
                            ['label' => 'Subscribe free', 'url' => '#subscribe', 'style' => 'primary'],
                            ['label' => 'Email the studio', 'url' => 'mailto:hello@slowdispatch.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Subscribe to The Slow Dispatch',
                    ],
                    $this->subscribeHeroSection(
                        heading: 'Subscribe in one warm, confident step',
                        summary: 'Free forever, one letter a week, unsubscribe whenever you like.',
                    ),
                    $this->featuresSection(
                        heading: 'What you can expect',
                        summary: 'A clear, honest picture before you hand over your email.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Pop in your email and the next Sunday letter is yours.',
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
            name: self::BRAND . ' No Issues',
            title: 'Nothing published yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A warm, structured empty state while the creator prepares the next issue.',
            ),
            renderData: [
                'summary' => 'No issues match that filter yet — but the next Sunday letter is already being written.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The archive',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'No issues match that filter for now. Clear it to see the full archive, or subscribe so the next letter finds you automatically.',
                        'actions' => [
                            ['label' => 'Browse all issues', 'url' => '#archive', 'style' => 'primary'],
                            ['label' => 'Subscribe free', 'url' => '#subscribe', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'No issues to show here',
                        'summary' => 'When letters land in this collection they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->featuresSection(
                        heading: 'While you are here',
                        summary: 'What every Sunday letter brings, the moment it lands.',
                    ),
                    $this->ctaSection(
                        heading: 'Be first to read the next one',
                        summary: 'Subscribe free and the next published issue lands in your inbox.',
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
                'That page could not be found',
                'A 404 state that keeps the newsletter inviting and routes readers back to the subscribe journey.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the letter.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the issue has moved. Head back to the archive, or subscribe so the next letter arrives without you hunting for it.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse the archive', 'url' => '#archive', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Lost the issue you wanted?',
                        summary: 'Subscribe free and every future letter lands in your inbox automatically.',
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
            title: 'Join the letter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn a reader into a subscriber',
                'A focused conversion page inviting new readers to join the Sunday letter.',
            ),
            renderData: [
                'summary' => 'Turn a reader into a subscriber. Join twenty-six thousand people who read The Slow Dispatch every Sunday.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Join the letter',
                        'heading' => 'Turn a quiet Sunday into a good one',
                        'summary' => 'One essay, one field note, free forever. The Slow Dispatch has reached inboxes every Sunday for five years running.',
                        'actions' => [
                            ['label' => 'Subscribe free', 'url' => '#subscribe', 'style' => 'primary'],
                            ['label' => 'Read a recent issue', 'url' => '#archive', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Join The Slow Dispatch',
                    ],
                    $this->subscribeHeroSection(
                        heading: 'One warm path to joining',
                        summary: 'No paywall, no trial, no card. Just your email and a letter each Sunday.',
                    ),
                    $this->proofSection(
                        heading: 'Why twenty-six thousand readers stay',
                        summary: 'The numbers behind a newsletter people genuinely open.',
                    ),
                    $this->ctaSection(
                        heading: 'Join before the next issue ships',
                        summary: 'Subscribe now and the next Sunday letter is the first one you receive.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function subscribeHeroSection(string $heading, string $summary): array
    {
        return [
            'type' => 'subscribe-hero',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Free forever', 'summary' => 'No paywall and no upsell. The letter stays free for every reader, every Sunday.'],
                ['title' => 'One click to leave', 'summary' => 'Every issue carries a one-click unsubscribe. Stay only as long as it earns the open.'],
                ['title' => 'Your inbox, respected', 'summary' => 'No list-selling, no tracking pixels, no third weekly email. One letter, that is the deal.'],
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
                ['title' => 'The Sunday essay', 'summary' => 'A single, finished piece of writing on working with intention — usually a six-minute read.'],
                ['title' => 'One field note', 'summary' => 'A small, practical idea you can try this week, drawn from what Mara is testing herself.'],
                ['title' => 'The week’s find', 'summary' => 'One book, tool, or essay worth your attention — recommended only when it genuinely earns it.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function archiveSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['detail'], $media['proof'])));

        $issues = [
            ['title' => 'Issue 261 — The cost of always-on', 'summary' => 'Why the most productive people guard their attention like the scarce resource it is.'],
            ['title' => 'Issue 260 — In praise of the boring habit', 'summary' => 'The unglamorous routines that quietly outperform every productivity system.'],
            ['title' => 'Issue 259 — Saying no without the apology', 'summary' => 'A short field note on protecting the work that actually matters.'],
            ['title' => 'Issue 258 — The first draft is allowed to be bad', 'summary' => 'How shipping rough beats polishing forever, with a ritual to make it stick.'],
            ['title' => 'Issue 257 — Attention is the whole game', 'summary' => 'What changed when Mara started measuring focus instead of hours.'],
        ];

        $items = [];

        foreach ($issues as $index => $issue) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$issue,
                'url' => '#issue-' . (261 - $index),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'archive',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function testimonialsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'testimonials',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['name' => 'Devon Park, founder', 'quote' => 'The only newsletter I read the moment it arrives. It has changed how I plan my whole week.'],
                ['name' => 'Aisha Rahman, designer', 'quote' => 'Six minutes every Sunday that consistently leave me thinking. That is a rare return.'],
                ['name' => 'Liam Brody, writer', 'quote' => 'No filler, no growth-hacking. Just honest writing from someone who clearly cares about the craft.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sponsorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sponsors',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Linen & Co.', 'summary' => 'Slow-made desk goods for people who work with their hands as well as their heads.'],
                ['title' => 'Marrow Coffee', 'summary' => 'A small-batch roaster that sponsors one issue a month and never more.'],
                ['title' => 'Fieldnotes Press', 'summary' => 'An independent publisher of quiet, well-made books on craft and attention.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aboutAuthorSection(string $heading, string $summary): array
    {
        return [
            'type' => 'about-author',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'From product to prose', 'summary' => 'Mara Ellison led product at a design studio for a decade before leaving to write full time in 2020.'],
                ['title' => 'One letter, no empire', 'summary' => 'The Slow Dispatch is her only newsletter. No courses, no cohort, no second list — just the Sunday letter.'],
                ['title' => 'Written, not generated', 'summary' => 'Every issue is drafted by hand on Friday and edited on Saturday. You are reading a person, not a pipeline.'],
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
                ['metric' => '26k', 'name' => 'Readers worldwide', 'quote' => 'Founders, designers, and writers across forty countries open it every Sunday.'],
                ['metric' => '61%', 'name' => 'Average open rate', 'quote' => 'More than double the industry norm, sustained over five years.'],
                ['metric' => '261', 'name' => 'Issues, never missed', 'quote' => 'One letter every Sunday since 2020, without a single skipped week.'],
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
            ['title' => 'The series on deep work', 'summary' => 'A four-part run on protecting focus that readers still share most often.'],
            ['title' => 'Letters on leaving a job well', 'summary' => 'The collection of issues written through Mara’s own career change.'],
            ['title' => 'Field notes, collected', 'summary' => 'Every practical idea from the past year, gathered in one place.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#collection-' . ($index + 1),
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
            'actions' => [
                ['label' => 'Subscribe free', 'url' => '#subscribe', 'style' => 'primary'],
                ['label' => 'Read the archive', 'url' => '#archive', 'style' => 'secondary'],
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
                ['label' => 'Latest issue', 'url' => '#latest'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'About', 'url' => '#about'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
            'ctaLabel' => 'Subscribe free',
            'ctaUrl' => '#subscribe',
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
            'summary' => 'A weekly letter about working with intention. One essay, one field note, every Sunday.',
            'columns' => [
                [
                    'heading' => 'The letter',
                    'links' => [
                        ['label' => 'Latest issue', 'url' => '#latest'],
                        ['label' => 'Archive', 'url' => '#archive'],
                        ['label' => 'Subscribe', 'url' => '#subscribe'],
                        ['label' => 'Field notes', 'url' => '#archive'],
                    ],
                ],
                [
                    'heading' => 'About',
                    'links' => [
                        ['label' => 'Mara Ellison', 'url' => '#about'],
                        ['label' => 'Sponsorship', 'url' => '#about'],
                        ['label' => 'Reader testimonials', 'url' => '#about'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Reply to any issue', 'url' => '#subscribe'],
                        ['label' => 'hello@slowdispatch.example', 'url' => 'mailto:hello@slowdispatch.example'],
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
