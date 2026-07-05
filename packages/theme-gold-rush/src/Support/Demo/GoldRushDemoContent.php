<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GoldRush\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Gold Rush theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature design-awards renderers (winner-hero /
 * score-criteria / newest-nominees / previous-winners / voting-status /
 * creator-credits) alongside the shared hero/proof/cta — giving every surface a
 * full, individual awards-scoreboard site rather than the shared skeleton.
 *
 * Anchors are surface-scoped: navigation and footer render on every surface, so
 * their links use homepage-relative `/#anchor` targets or real page paths. Any
 * in-page `#anchor` used by a hero/cta on a given surface only points at a
 * section that actually renders on that same surface.
 */
final class GoldRushDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Scoreboard Awards';

    private const string JUDGES_EMAIL = 'judges@scoreboardawards.example';

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
            title: self::BRAND . ' — Design awards ranked by the numbers',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Design awards ranked by the numbers',
                'Scoreboard Awards ranks the best new product, design, and craft work with segmented judging scores, a winner of the day, and open public voting.',
            ),
            renderData: [
                'summary' => 'Scoreboard Awards ranks the best new product, design, and craft work each day with segmented judging scores, public voting, and full creator credits.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Daily design awards',
                        heading: 'Design awards ranked by the numbers',
                        summary: 'Every day we score the strongest new work on UI, UX, innovation, and overall craft — then open the floor to a public vote. One winner of the day, every day.',
                        media: $media['hero'][0] ?? null,
                        mediaAlt: 'Meridian Console, today\'s winner of the day, shown on the scoreboard',
                        primaryLabel: 'Cast a vote',
                        primaryUrl: '#voting-status',
                        secondaryLabel: 'View the scoreboard',
                        secondaryUrl: '#score-criteria',
                    ),
                    $this->winnerHeroSection($media, imageIndex: 0),
                    $this->scoreCriteriaSection(),
                    $this->newestNomineesSection(
                        heading: 'Newest nominees',
                        summary: 'Fresh work entered the scoreboard in the last twenty-four hours, scored and ready for the public vote.',
                        media: $media,
                    ),
                    $this->previousWinnersSection($media, url: 'theme-' . $themeKey . '-directory'),
                    $this->votingStatusSection(),
                    $this->creatorCreditsSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Enter the scoreboard',
                        summary: 'Submit your work for tomorrow\'s judging round, or cast your vote on today\'s nominees before the window closes at midnight.',
                        primaryLabel: 'Submit your work',
                        primaryUrl: 'theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Cast a vote',
                        secondaryUrl: '#voting-status',
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
            title: 'Awards archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full awards archive',
                'Every scored nominee and past winner, filterable by category, country, and judging year.',
            ),
            renderData: [
                'summary' => 'Every scored nominee and past winner, filterable by category, country, and judging year.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Awards archive',
                        heading: 'An awards archive built to be scanned',
                        summary: 'Browse every entry the scoreboard has scored. Filter by category, country, or judging year, then open any nominee for its full score breakdown.',
                        media: $media['listing'][0] ?? $media['hero'][0] ?? null,
                        mediaAlt: 'Rows of scored nominees in the Scoreboard Awards archive',
                        primaryLabel: 'Browse newest nominees',
                        primaryUrl: '#newest-nominees',
                        secondaryLabel: 'See more of the archive',
                        secondaryUrl: '#content-listing',
                    ),
                    $this->newestNomineesSection(
                        heading: 'Newest nominees',
                        summary: 'The most recent work to enter the scoreboard, newest first.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Older nominees and category runners-up, with scores and judging notes.',
                        media: $media,
                    ),
                    $this->previousWinnersSection($media, url: null),
                    $this->ctaSection(
                        heading: 'Looking for a specific category?',
                        summary: 'Filter the archive by product, interface, or craft — or submit work for the next judging round.',
                        primaryLabel: 'Submit your work',
                        primaryUrl: 'theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Back to the scoreboard',
                        secondaryUrl: '/theme-' . $themeKey,
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
            name: self::BRAND . ' Winner',
            title: 'Meridian Console — Winner — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Meridian Console — winner of the day',
                'The full score breakdown, judging notes, and creator credits behind today\'s highest-ranked entry.',
            ),
            renderData: [
                'summary' => 'The full score breakdown, judging notes, and creator credits behind today\'s highest-ranked entry.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Winner of the day',
                        heading: 'Meridian Console — a clean sweep on craft',
                        summary: 'A developer console redesign that scored 9.4 overall, taking the top mark on UI and innovation from a panel of nine judges.',
                        media: $media['detail'][0] ?? null,
                        mediaAlt: 'Meridian Console interface, winner of the day',
                        primaryLabel: 'See the score breakdown',
                        primaryUrl: '#score-criteria',
                        secondaryLabel: 'Meet the creators',
                        secondaryUrl: '#creator-credits',
                    ),
                    $this->winnerHeroSection($media, imageIndex: 1),
                    $this->scoreCriteriaSection(),
                    $this->creatorCreditsSection(),
                    $this->contentListingSection(
                        heading: 'Related winners',
                        summary: 'Other entries that topped the scoreboard in the same category.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Think you can score higher?',
                        summary: 'Submit your work for the next judging round and see how it ranks against today\'s winner.',
                        primaryLabel: 'Submit your work',
                        primaryUrl: 'theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Back to the scoreboard',
                        secondaryUrl: '/theme-' . $themeKey,
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
            title: 'Submit your work — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit your work',
                'Enter the next judging round, or reach the awards team about partnerships and press.',
            ),
            renderData: [
                'summary' => 'Enter the next judging round, or reach the awards team about partnerships and press.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit & contact',
                        heading: 'Reach the awards through one confident path',
                        summary: 'Submissions close at 6pm each day for the following morning\'s round. Email ' . self::JUDGES_EMAIL . ' for partnerships, press, or judging enquiries.',
                        media: $media['contact'][0] ?? null,
                        mediaAlt: 'Submitting work for Scoreboard Awards judging',
                        primaryLabel: 'Email the judges',
                        primaryUrl: 'mailto:' . self::JUDGES_EMAIL,
                        secondaryLabel: 'Join the digest',
                        secondaryUrl: '#newsletter',
                    ),
                    $this->newsletterSection(),
                    $this->votingStatusSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Ready to enter?',
                        summary: 'Send your work before today\'s 6pm cut-off and the panel will score it in tomorrow\'s round.',
                        primaryLabel: 'Email the judges',
                        primaryUrl: 'mailto:' . self::JUDGES_EMAIL,
                        secondaryLabel: 'Cast a vote instead',
                        secondaryUrl: '#voting-status',
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
            title: 'No nominees match — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No nominees match that filter yet',
                'A graceful empty state for a filtered awards archive with no matching nominees.',
            ),
            renderData: [
                'summary' => 'No nominees match that filter yet — but the scoreboard can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Awards archive',
                        heading: 'No nominees match that filter — yet',
                        summary: 'Nothing has been scored in this category for the selected year. Clear the filter to see the full scoreboard, or check today\'s open vote.',
                        media: null,
                        mediaAlt: null,
                        primaryLabel: 'Back to the scoreboard',
                        primaryUrl: '/theme-' . $themeKey,
                        secondaryLabel: 'Check the open vote',
                        secondaryUrl: '#voting-status',
                    ),
                    $this->contentListingSection(
                        heading: 'Nothing to score here',
                        summary: 'When work is judged in this category it will appear here, highest score first.',
                        media: $media,
                        items: [],
                    ),
                    $this->votingStatusSection(),
                    $this->ctaSection(
                        heading: 'Looking for a particular winner?',
                        summary: 'Clear the filter to browse every scored entry, or submit new work for the next round.',
                        primaryLabel: 'Submit your work',
                        primaryUrl: 'theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Back to the scoreboard',
                        secondaryUrl: '/theme-' . $themeKey,
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
                'Page not found',
                'A not-found page that routes visitors back into the scoreboard and the open vote.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the scoreboard.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This entry never made the scoreboard',
                        summary: 'The link is broken or the nominee has moved. Head back to today\'s winner, or reach the judges directly.',
                        media: null,
                        mediaAlt: null,
                        primaryLabel: 'Back to the scoreboard',
                        primaryUrl: '/theme-' . $themeKey,
                        secondaryLabel: 'Contact the judges',
                        secondaryUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    $this->ctaSection(
                        heading: 'Back to the scoreboard',
                        summary: 'Return to today\'s winner and newest nominees, or submit your own work for judging.',
                        primaryLabel: 'Back to the scoreboard',
                        primaryUrl: '/theme-' . $themeKey,
                        secondaryLabel: 'Submit your work',
                        secondaryUrl: 'theme-' . $themeKey . '-contact',
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
            name: self::BRAND . ' Enter',
            title: 'Enter the scoreboard — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to take the top score?',
                'A focused conversion page inviting new award submissions and votes.',
            ),
            renderData: [
                'summary' => 'Ready to take the top score? Enter the scoreboard or cast your vote.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Enter the scoreboard',
                        heading: 'Ready to take the top score?',
                        summary: 'Whether you are submitting product, interface, or craft work, the same panel scores every entry on the same four criteria.',
                        media: $media['cta'][0] ?? null,
                        mediaAlt: 'Submitting new work to Scoreboard Awards',
                        primaryLabel: 'Email the judges',
                        primaryUrl: 'mailto:' . self::JUDGES_EMAIL,
                        secondaryLabel: 'Get the winner digest',
                        secondaryUrl: '#newsletter',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'One submission away',
                        summary: 'Send your work before the 6pm cut-off and the panel will score it in tomorrow\'s round.',
                        primaryLabel: 'Email the judges',
                        primaryUrl: 'mailto:' . self::JUDGES_EMAIL,
                        secondaryLabel: 'Back to the scoreboard',
                        secondaryUrl: '/theme-' . $themeKey,
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
        ?string $media,
        ?string $mediaAlt,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
    ): array {
        $section = [
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
        ];

        if ($media !== null) {
            $section['mediaUrl'] = $media;
            $section['mediaAlt'] = $mediaAlt ?? $heading;
        }

        return $section;
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function winnerHeroSection(array $media, int $imageIndex): array
    {
        $pool = array_values(array_unique(array_merge($media['detail'], $media['hero'], $media['listing'])));
        $image = $pool[$imageIndex % max(count($pool), 1)] ?? null;

        return [
            'type' => 'winner-hero',
            'title' => 'Meridian Console',
            'heading' => 'Winner of the day: Meridian Console',
            'summary' => 'A judging panel of nine scored Meridian Console highest on overall craft, with the strongest innovation mark we have recorded this quarter.',
            'image' => $image,
            'imageAlt' => 'Meridian Console interface, winner of the day',
            'items' => [
                ['title' => 'Overall craft — 9.4', 'summary' => 'The highest overall mark on the board this quarter, scored across nine judges with almost no spread.'],
                ['title' => 'Interface — 9.6', 'summary' => 'A console layout judges called the cleanest information density they had scored all year.'],
                ['title' => 'Innovation — 9.1', 'summary' => 'A live query inspector that reframes how the panel expects developer tools to feel.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scoreCriteriaSection(): array
    {
        return [
            'type' => 'score-criteria',
            'heading' => 'How every entry is scored',
            'summary' => 'Four weighted criteria, scored independently by every judge, then averaged into the overall mark.',
            'items' => [
                ['title' => 'User interface', 'weight' => 35, 'summary' => 'Visual craft, hierarchy, and the confidence of the layout under real content.'],
                ['title' => 'User experience', 'weight' => 30, 'summary' => 'Flow, clarity, and how little friction stands between the visitor and the goal.'],
                ['title' => 'Innovation', 'weight' => 20, 'summary' => 'Whether the work reframes a familiar problem or only restates the obvious answer.'],
                ['title' => 'Overall craft', 'weight' => 15, 'summary' => 'The weighted average that decides the winner of the day and the archive ranking.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function newestNomineesSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['detail'], $media['proof'])));

        $nominees = [
            ['title' => 'Meridian Console', 'summary' => 'A developer console redesign that turns query history into a readable, navigable timeline.', 'meta' => 'Interface · United States', 'care_note' => '9.4'],
            ['title' => 'Harbour Atlas', 'summary' => 'A mapping interface for harbour logistics that keeps every vessel legible at a glance.', 'meta' => 'Product · Netherlands', 'care_note' => '9.0'],
            ['title' => 'Northwind Field', 'summary' => 'A field-service app for renewables engineers, scored high on experience under pressure.', 'meta' => 'Experience · United Kingdom', 'care_note' => '8.8'],
        ];

        $items = [];

        foreach ($nominees as $index => $nominee) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$nominee,
                'url' => '#nominee-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $nominee['title'],
            ];
        }

        return [
            'type' => 'newest-nominees',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function previousWinnersSection(array $media, ?string $url): array
    {
        $pool = array_values(array_unique(array_merge($media['proof'], $media['listing'], $media['cta'])));

        $winners = [
            ['title' => 'Studio Verda — 9.3', 'summary' => 'An architecture practice portfolio that let the buildings carry the score.'],
            ['title' => 'Atlas Festival — 9.1', 'summary' => 'A motion-led festival site that topped the board on innovation two days running.'],
        ];

        $items = [];

        foreach ($winners as $index => $winner) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$winner,
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $winner['title'],
            ];
        }

        return [
            'type' => 'previous-winners',
            'heading' => 'Recent winners of the day',
            'summary' => 'The last entries to take the top overall score, with their winning marks.',
            'label' => $url !== null ? 'Browse every past winner' : null,
            'url' => $url !== null ? '/' . $url : null,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function votingStatusSection(): array
    {
        return [
            'type' => 'voting-status',
            'heading' => 'Today\'s vote is open',
            'summary' => 'The public vote runs alongside the judges. Cast yours before midnight to shape the people\'s pick.',
            'items' => [
                ['meta' => '2,481 votes', 'title' => 'Meridian Console', 'summary' => 'Leading the public vote with a comfortable margin going into the evening.'],
                ['meta' => '1,940 votes', 'title' => 'Harbour Atlas', 'summary' => 'Second on the public vote, gaining ground on interface clarity.'],
                ['meta' => '1,602 votes', 'title' => 'Northwind Field', 'summary' => 'Holding third, with the strongest experience score in the round.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function creatorCreditsSection(): array
    {
        return [
            'type' => 'creator-credits',
            'heading' => 'Credit where the craft is due',
            'summary' => 'Every entry on the scoreboard ships with the people who made it — no anonymous winners.',
            'items' => [
                ['title' => 'Priya Nadkarni — Lead designer', 'summary' => 'Shaped the Meridian Console interface and led the redesign from research to ship.'],
                ['title' => 'Tom Becker — Motion director', 'summary' => 'Built the live query inspector animation the judges singled out for innovation.'],
                ['title' => 'Lena Cruz — Product strategist', 'summary' => 'Framed the problem the console solves and kept the team honest about scope.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'heading' => 'The scoreboard, in numbers',
            'summary' => 'What the awards have tracked since the first judging round.',
            'items' => [
                ['value' => '1,400+', 'label' => 'Entries scored across product, interface, and craft.'],
                ['value' => '9', 'label' => 'Judges scoring every entry on four weighted criteria.'],
                ['value' => '38', 'label' => 'Countries represented on the scoreboard so far.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @param  list<array<string, mixed>>|null  $items
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media, ?array $items = null): array
    {
        if ($items === null) {
            $pool = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

            $entries = [
                ['category' => 'Interface · 2024', 'title' => 'Lumen Dashboard — 8.9', 'summary' => 'A health-tech dashboard that scored high on clarity under dense clinical data.'],
                ['category' => 'Product · 2024', 'title' => 'Kindred Lending — 8.7', 'summary' => 'A community lending app the panel praised for an honest, low-friction flow.'],
                ['category' => 'Craft · 2023', 'title' => 'Foundry System — 8.6', 'summary' => 'A design system entry that unified four product teams on one language.'],
            ];

            $items = [];

            foreach ($entries as $index => $entry) {
                $image = $pool[$index % max(count($pool), 1)] ?? null;
                $items[] = [
                    ...$entry,
                    'url' => '#archive-' . ($index + 1),
                    'image' => $image,
                    'imageUrl' => $image,
                    'imageAlt' => $entry['title'],
                ];
            }
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
    private function newsletterSection(): array
    {
        return [
            'type' => 'newsletter',
            'heading' => 'Get the winner of the day',
            'summary' => 'One short email each morning with yesterday\'s winner, its scores, and the entries that came close.',
            'action' => '#newsletter',
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
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Winners', 'url' => '/#previous-winners'],
                ['label' => 'Nominees', 'url' => '/#newest-nominees'],
                ['label' => 'Scores', 'url' => '/#score-criteria'],
                ['label' => 'Vote', 'url' => '/#voting-status'],
                ['label' => 'Submit', 'url' => '/theme-gold-rush-contact'],
            ],
            'ctaLabel' => 'Cast a vote',
            'ctaUrl' => '/#voting-status',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Daily design awards ranked by the numbers. Scored by nine judges, voted by the public.',
            'columns' => [
                [
                    'heading' => 'Awards',
                    'links' => [
                        ['label' => 'Winner of the day', 'url' => '/#winner-hero'],
                        ['label' => 'Newest nominees', 'url' => '/#newest-nominees'],
                        ['label' => 'Previous winners', 'url' => '/#previous-winners'],
                        ['label' => 'Scoring criteria', 'url' => '/#score-criteria'],
                    ],
                ],
                [
                    'heading' => 'Participate',
                    'links' => [
                        ['label' => 'Submit your work', 'url' => '/theme-gold-rush-contact'],
                        ['label' => 'Cast a vote', 'url' => '/#voting-status'],
                        ['label' => 'Become a judge', 'url' => '/theme-gold-rush-contact'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '/theme-gold-rush-contact'],
                        ['label' => self::JUDGES_EMAIL, 'url' => 'mailto:' . self::JUDGES_EMAIL],
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
