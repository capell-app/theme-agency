<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EditorialSerif\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Editorial Serif theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the publication's signature renderers (essay-index /
 * issue-archive / author-profiles / editorial-statement / subscription-panel)
 * alongside the standard hero/proof/cta — giving every surface a full,
 * print-grade editorial site rather than the shared skeleton.
 */
final class EditorialSerifDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'The Quire Review';

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
            title: self::BRAND . ' — Essays, Issues & Authors',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A publication a reader can settle into',
                'The Quire Review publishes long-form essays on craft, culture, and the slow art of thinking in print.',
            ),
            renderData: [
                'summary' => 'The Quire Review is a quarterly publication of long-form essays on craft, culture, and ideas worth a reader\'s full attention.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Quarterly essays in print',
                        heading: 'A publication a reader can settle into',
                        summary: 'Considered essays, a browsable archive of past issues, and the writers behind the work — gathered into one quiet, print-grade home.',
                    ),
                    $this->essayIndexSection(
                        heading: 'Essays from the current issue',
                        summary: 'The pieces our editors are reading this season, set in type built for the long form.',
                    ),
                    $this->issueArchiveSection(
                        heading: 'Issues from the archive',
                        summary: 'Every issue stays in print and online. Browse the back catalogue by season.',
                    ),
                    $this->authorProfilesSection(
                        heading: 'The writers behind the work',
                        summary: 'Essayists, critics, and reporters who write for the Review.',
                    ),
                    $this->editorialStatementSection(
                        heading: 'Why we publish slowly',
                        summary: 'A short note from the editors on what the Review is for.',
                    ),
                    $this->subscriptionPanelSection(
                        heading: 'Subscribe to the Review',
                        summary: 'Four issues a year, in print and online, with the full archive.',
                    ),
                    $this->proofSection(
                        heading: 'Read by people who read closely',
                        summary: 'A few numbers from a decade of quarterly publishing.',
                    ),
                    $this->ctaSection(
                        heading: 'Start reading the current issue',
                        summary: 'Subscribe today and the latest issue lands in your hands and your inbox.',
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
            name: self::BRAND . ' Essays',
            title: 'Essays — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'An index of essays built to be read',
                'Browse the full run of essays published in the Review, grouped by issue and subject.',
            ),
            renderData: [
                'summary' => 'An index of every essay the Review has published, grouped by issue and subject so readers can find their way in.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The essay index',
                        heading: 'An index of essays built to be read',
                        summary: 'The full run of the Review, from the current issue back to our first. Filter by subject, or start at the top and read down.',
                    ),
                    $this->essayIndexSection(
                        heading: 'Featured essays',
                        summary: 'The pieces our editors return to most often.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Shorter notes, reviews, and dispatches from past issues.',
                    ),
                    $this->ctaSection(
                        heading: 'Found an essay worth keeping?',
                        summary: 'Subscribers get every essay in print and the full searchable archive online.',
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
            name: self::BRAND . ' Essay',
            title: 'The Patience of Print — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Patience of Print',
                'An essay on why some ideas only land at the pace of a printed page.',
            ),
            renderData: [
                'summary' => 'The Patience of Print — an essay on why some ideas only land at the pace of a printed page.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Essay · Issue 41',
                        heading: 'The Patience of Print',
                        summary: 'By Eleanor Vance. A reflection on slowness as a feature, not a flaw — and on the writing that only reveals itself when nobody is rushing.',
                    ),
                    $this->essayIndexSection(
                        heading: 'Read on in this issue',
                        summary: 'Essays published alongside this one in Issue 41.',
                    ),
                    $this->authorProfilesSection(
                        heading: 'About the author',
                        summary: 'The writer behind this essay, and others in the Review.',
                    ),
                    $this->proofSection(
                        heading: 'Why readers stay with the Review',
                        summary: 'What a decade of close readers tells us.',
                    ),
                    $this->ctaSection(
                        heading: 'Keep reading the Review',
                        summary: 'Subscribe to receive every essay, in print and online, four times a year.',
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
            title: 'Subscribe & reach us — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the publication through one quiet path',
                'Subscribe, pitch an essay, or write to the editors — every message reaches a real person on the masthead.',
            ),
            renderData: [
                'summary' => 'Subscribe, pitch an essay, or write to the editors. Every message reaches a real person on the masthead, and we reply.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe & contact',
                        heading: 'Reach the publication through one quiet path',
                        summary: 'Write to editors@quire.example to pitch an essay or ask about a subscription. We read everything and reply within a week.',
                    ),
                    $this->subscriptionPanelSection(
                        heading: 'Choose how you read',
                        summary: 'Print and online, online only, or a gift subscription for a fellow reader.',
                    ),
                    $this->featuresSection(
                        heading: 'What a subscription includes',
                        summary: 'Everything that arrives once you join the Review.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a piece you want us to read?',
                        summary: 'Send a short pitch to editors@quire.example. We commission from the slush pile every issue.',
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
            name: self::BRAND . ' No Essays',
            title: 'Nothing published here yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A composed empty state for a filtered essay index with no matching pieces.',
            ),
            renderData: [
                'summary' => 'No essays match that filter yet — but the archive still has somewhere to send you.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The essay index',
                        heading: 'Nothing published here yet',
                        summary: 'No essays match that subject in this issue. Clear the filter to read the full run, or wander into the archive below.',
                    ),
                    $this->issueArchiveSection(
                        heading: 'Browse a past issue instead',
                        summary: 'Every back issue stays in print and online. Start anywhere.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a particular essay?',
                        summary: 'Subscribers can search the full archive by author, subject, and issue.',
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
            title: 'That page could not be found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That page could not be found',
                'A not-found page that routes readers back into the essays and the archive.',
            ),
            renderData: [
                'summary' => 'That page has been unbound or never set in type — here is the way back to the writing.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That page could not be found',
                        summary: 'The link is broken or the page has moved. Head back to the current issue, or pick up an essay from the archive.',
                    ),
                    $this->ctaSection(
                        heading: 'Find your way back to the writing',
                        summary: 'Return to the current issue, or subscribe to receive every essay the Review publishes.',
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
            title: 'Subscribe to the Review — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn a reader into a subscriber',
                'A focused conversion page inviting readers to join the Review.',
            ),
            renderData: [
                'summary' => 'Turn a reader into a subscriber — join the Review and never miss an issue.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Join the Review',
                        heading: 'Turn a reader into a subscriber',
                        summary: 'Four issues a year, in print and online, with the full archive and members-only essays. One subscription, every word.',
                    ),
                    $this->subscriptionPanelSection(
                        heading: 'Pick the subscription that fits',
                        summary: 'Print and online, digital only, or a gift for a fellow reader.',
                    ),
                    $this->proofSection(
                        heading: 'Why readers subscribe',
                        summary: 'A decade of quarterly publishing, by the numbers.',
                    ),
                    $this->ctaSection(
                        heading: 'Subscribe today',
                        summary: 'Join now and the current issue ships within the week, with full archive access from the moment you sign up.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(string $eyebrow, string $heading, string $summary): array
    {
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                ['label' => 'Read the essays', 'url' => '#essays', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function essayIndexSection(string $heading, string $summary): array
    {
        return [
            'type' => 'essay-index',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The Patience of Print', 'summary' => 'Eleanor Vance on slowness as a feature of reading, not a flaw — and the ideas that only land at the pace of a page.'],
                ['title' => 'Margins & Marginalia', 'summary' => 'A history of reading in the margins, from medieval scribes to the dog-eared paperbacks on a critic\'s nightstand.'],
                ['title' => 'The Last Letterpress', 'summary' => 'A reporter spends a season with the printers keeping a dying craft alive, one tray of metal type at a time.'],
                ['title' => 'On Re-reading', 'summary' => 'Why the books we return to change while standing still, and what a second reading asks of an older self.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function issueArchiveSection(string $heading, string $summary): array
    {
        return [
            'type' => 'issue-archive',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Issue 41 — Slowness', 'summary' => 'Spring 2026. Essays on patience, print, and the long form, anchored by our cover piece on the last letterpress.'],
                ['title' => 'Issue 40 — Attention', 'summary' => 'Winter 2025. A double issue on what we choose to notice, and what reading does to a wandering mind.'],
                ['title' => 'Issue 39 — Craft', 'summary' => 'Autumn 2025. Writers and makers on the discipline of doing one thing slowly and well.'],
                ['title' => 'Issue 38 — Memory', 'summary' => 'Summer 2025. Essays on archives, inheritance, and the stories we keep in the margins of our lives.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function authorProfilesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'author-profiles',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Eleanor Vance', 'summary' => 'Contributing essayist. Writes on reading, craft, and the quiet life of ideas. Author of three collections.'],
                ['title' => 'Marcus Whitfield', 'summary' => 'Critic at large. Twenty years reviewing fiction and the occasional dictionary for the love of a well-set sentence.'],
                ['title' => 'Priya Anand', 'summary' => 'Reporter. Files long-form dispatches on the people keeping old crafts and slow trades alive.'],
                ['title' => 'Tomás Reyes', 'summary' => 'Poetry editor. Reads everything aloud at least once before it goes to print.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editorialStatementSection(string $heading, string $summary): array
    {
        return [
            'type' => 'editorial-statement',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'We publish four times a year', 'summary' => 'Quarterly, on purpose. The schedule gives an essay room to be edited until it earns its place in the issue.'],
                ['title' => 'We edit for the long form', 'summary' => 'No hot takes, no listicles. Every piece is commissioned, edited closely, and set in type built to be read at length.'],
                ['title' => 'We pay our writers', 'summary' => 'Every essay is paid at a rate writers can live with, because good writing is work and we treat it that way.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function subscriptionPanelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'subscription-panel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Print & online — £60 / year', 'summary' => 'Four issues mailed to your door, plus full online access and the complete searchable archive.'],
                ['title' => 'Online only — £36 / year', 'summary' => 'Every essay the day it publishes, the full archive, and members-only pieces between issues.'],
                ['title' => 'Gift a subscription', 'summary' => 'A year of the Review for a fellow reader, with a hand-set card announcing your gift.'],
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
                ['title' => 'Four print issues', 'summary' => 'Mailed the week each issue goes to press, on paper chosen to last on a shelf for years.'],
                ['title' => 'The full archive', 'summary' => 'Every essay since Issue 1, searchable by author, subject, and season.'],
                ['title' => 'Members-only essays', 'summary' => 'Shorter pieces between issues, sent only to subscribers.'],
                ['title' => 'A say in the Review', 'summary' => 'Subscribers vote on the theme of one issue each year.'],
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
                ['title' => 'A Note on Footnotes', 'summary' => 'A short defence of the aside, the digression, and the line of type that runs along the bottom of the page.'],
                ['title' => 'Reviewed: Three Slim Novels', 'summary' => 'Marcus Whitfield on a season of books that say more in two hundred pages than most do in five.'],
                ['title' => 'Dispatch: The Paper Mill', 'summary' => 'Priya Anand reports from a mill that still makes paper by hand for a handful of stubborn publishers.'],
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
                ['title' => '41 issues', 'summary' => 'A decade of quarterly publishing, every issue still in print and online.'],
                ['title' => '18,000 readers', 'summary' => 'Subscribers in thirty countries who read to the end.'],
                ['title' => '93% renew', 'summary' => 'Nearly everyone who subscribes for a year stays for another.'],
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
            'actions' => [
                ['label' => 'Subscribe', 'url' => '#subscribe', 'style' => 'primary'],
                ['label' => 'Read the essays', 'url' => '#essays', 'style' => 'secondary'],
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
                ['label' => 'Essays', 'url' => '#essays'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Authors', 'url' => '#authors'],
                ['label' => 'About', 'url' => '#about'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#subscribe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A quarterly publication of long-form essays on craft, culture, and ideas worth a reader\'s full attention.',
            'columns' => [
                [
                    'heading' => 'Read',
                    'links' => [
                        ['label' => 'Current issue', 'url' => '#essays'],
                        ['label' => 'The archive', 'url' => '#archive'],
                        ['label' => 'Authors', 'url' => '#authors'],
                        ['label' => 'About the Review', 'url' => '#about'],
                    ],
                ],
                [
                    'heading' => 'Subscribe',
                    'links' => [
                        ['label' => 'Print & online', 'url' => '#subscribe'],
                        ['label' => 'Online only', 'url' => '#subscribe'],
                        ['label' => 'Gift a subscription', 'url' => '#subscribe'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Pitch an essay', 'url' => 'mailto:editors@quire.example'],
                        ['label' => 'editors@quire.example', 'url' => 'mailto:editors@quire.example'],
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
