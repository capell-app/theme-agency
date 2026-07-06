<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReelRoom\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Reel Room theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (archive-hero /
 * date-filter-rail / winner-list / featured-project / jury-score-explainer /
 * media-credits / newsletter) alongside the shared hero / proof / cta — giving
 * every surface a full, individual digital-awards archive rather than the
 * shared five-section skeleton. Every winner, featured project, and archive
 * still carries a real photograph via ThemeDemoMedia so the archive reads as
 * a cinematic motion catalogue rather than a placeholder grid.
 */
final class ReelRoomDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Frame Index';

    private const string ARCHIVE_EMAIL = 'archive@frameindex.example';

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
            title: self::BRAND . ' — The Motion & Digital Awards Archive',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'The motion and digital awards archive',
                'Frame Index is a date-led archive of award-winning motion and interactive work, filed by year, jury cycle, and category.',
            ),
            renderData: [
                'summary' => 'Frame Index is the standing archive of award-winning motion and digital craft — winners, jury scores, credits, and technology stacks kept legible year on year.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Motion & digital awards archive',
                        'heading' => 'Every award-winning frame, filed and kept',
                        'summary' => 'A date-led record of motion, interactive, and digital craft. Browse winners by year and jury cycle, read the scores behind each verdict, and trace the credits and technology stacks that made the work.',
                        'actions' => [
                            ['label' => 'Browse the archive', 'url' => '#archive', 'style' => 'primary'],
                            ['label' => 'View this cycle\'s winners', 'url' => '#winner-list', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'A still from the current cycle\'s Grand Prix title sequence',
                    ],
                    $this->dateFilterRailSection(
                        heading: 'Filter the archive by period and discipline',
                        summary: 'Years, jury cycles, and award categories stay on the left rail so the record is scanned, not searched.',
                    ),
                    $this->winnerListSection(
                        heading: 'Winners of the current cycle',
                        summary: 'Gold, silver, and jury commendations from the 2025 cycle, newest verdicts first.',
                        media: $media,
                    ),
                    $this->featuredProjectSection(
                        heading: 'Featured project: Tidal States',
                        summary: 'A long-form title sequence that took the 2025 Grand Prix for motion direction.',
                        media: $media,
                        mediaKey: 'detail',
                    ),
                    $this->juryScoreSection(
                        heading: 'How the jury scored it',
                        summary: 'Every winner carries the panel\'s reasoning across four scoring axes, kept on the record.',
                    ),
                    $this->mediaCreditsSection(
                        heading: 'Credits & technology behind the work',
                        summary: 'Studios, directors, and the stacks they shipped on — credited in full, the way an archive should.',
                    ),
                    $this->proofSection(
                        heading: 'The archive in numbers',
                        summary: 'What the record holds after nine award cycles.',
                    ),
                    $this->newsletterSection(
                        heading: 'New verdicts, when they land',
                        summary: 'A short note each cycle when fresh winners, scores, and credits are filed into the archive.',
                        action: '/#newsletter',
                    ),
                    $this->ctaSection(
                        heading: 'Submit work to the next cycle',
                        summary: 'Entries for the 2026 cycle open this autumn. Get your studio on the record.',
                        url: '/theme-' . $themeKey . '-cta',
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
            name: self::BRAND . ' Winners',
            title: 'Winner archive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The winner archive',
                'A date-led directory of every winning motion and digital project, scannable by year, category, and jury cycle.',
            ),
            renderData: [
                'summary' => 'The full winner archive — every gold, silver, and commendation filed by year and category since the first cycle.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Winner archive',
                        'heading' => 'The winner archive, built to be scanned by year',
                        'summary' => 'Nine cycles of award-winning motion and digital work in one date-led record. Filter by period, category, or jury cycle and open any verdict in full.',
                        'actions' => [
                            ['label' => 'Submit to the next cycle', 'url' => '/theme-' . $themeKey . '-cta', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'A grid of stills from the winner archive',
                    ],
                    $this->dateFilterRailSection(
                        heading: 'Filter winners by year and category',
                        summary: 'Pick a cycle on the left rail to narrow the record without losing the chronology.',
                    ),
                    $this->winnerListSection(
                        heading: 'Featured winners',
                        summary: 'The verdicts the jury keeps returning to across recent cycles.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'The full winner archive',
                        summary: 'Every gold, silver, and commendation filed since the first cycle, newest first.',
                        media: $media,
                    ),
                    $this->archiveWallIndexSection(
                        heading: 'The archive wall',
                        summary: 'Every filed still in one contact-sheet grid, oldest cycles mixed with the newest.',
                        media: $media,
                    ),
                    $this->timeCapsuleBrowserSection(
                        heading: 'Step into an earlier cycle',
                        summary: 'Each award cycle is filed as its own capsule — open one to preview the projects it holds.',
                    ),
                    $this->archiveHeroSection(
                        heading: 'Earlier cycles, still on the record',
                        summary: 'Winners from the founding cycles, kept legible with their original scores and credits intact.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific verdict?',
                        summary: 'Tell us the studio, project, or cycle and we will point you straight to its archive entry.',
                        url: '/theme-' . $themeKey . '-contact',
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
            name: self::BRAND . ' Project Record',
            title: 'Tidal States — Project record — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Tidal States — a project record',
                'The full archive entry for the 2025 Grand Prix winner: synopsis, jury scores, credits, technology stack, and awards.',
            ),
            renderData: [
                'summary' => 'The complete archive entry for Tidal States — synopsis, jury verdict, full credits, technology stack, and every award it carried.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '2025 cycle · Grand Prix',
                        'heading' => 'Tidal States — a project record',
                        'summary' => 'A four-minute title sequence for a coastal documentary, awarded the 2025 Grand Prix for motion direction. Read it the way the jury did.',
                        'actions' => [
                            ['label' => 'Back to winners', 'url' => '/theme-' . $themeKey . '-directory#winner-list', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Tidal States title sequence still, coastal storm front over water',
                    ],
                    $this->featuredProjectSection(
                        heading: 'A record that reads like an archive entry',
                        summary: 'Synopsis, credits, technology stack, and awards stay structured so a single project sits inside the historical record.',
                        media: $media,
                        mediaKey: 'detail',
                    ),
                    $this->mediaCreditsSection(
                        heading: 'Credits & technology stack',
                        summary: 'The studio, the direction team, and the pipeline that shipped Tidal States — credited in full.',
                    ),
                    $this->juryScoreSection(
                        heading: 'The jury verdict in detail',
                        summary: 'How the 2025 panel scored Tidal States across direction, craft, concept, and impact.',
                    ),
                    $this->ctaSection(
                        heading: 'Want your work on the record?',
                        summary: 'Entries for the next cycle open this autumn. Put your studio in front of the jury.',
                        url: '/theme-' . $themeKey . '-cta',
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
            title: 'Submit & contact — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit work & reach the archive',
                'One restrained path for entries, corrections, and press — the way the archive prefers to be reached.',
            ),
            renderData: [
                'summary' => 'Submit work to the next cycle, request a credit correction, or reach the archive team — all through one restrained path.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Submit & contact',
                        'heading' => 'Reach the archive through one restrained path',
                        'summary' => 'Entries, credit corrections, and press enquiries all run through the same desk. Email ' . self::ARCHIVE_EMAIL . ' or use the form below — we answer within two working days.',
                        'actions' => [
                            ['label' => 'Email the archive', 'url' => 'mailto:' . self::ARCHIVE_EMAIL, 'style' => 'primary'],
                            ['label' => 'Join the newsletter', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Frame Index archive submission desk',
                    ],
                    $this->newsletterSection(
                        heading: 'Stay on the cycle',
                        summary: 'A short note each cycle when new winners, scores, and credits are filed. No more than that.',
                        action: '#newsletter',
                    ),
                    $this->mediaCreditsSection(
                        heading: 'How submissions are credited',
                        summary: 'What the archive records when your work is entered, and how credits are verified.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to submit?',
                        summary: 'Send the project details and we will confirm your entry and its archive slug within two working days.',
                        url: 'mailto:' . self::ARCHIVE_EMAIL,
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
            title: 'No matching entries — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No entries match that filter yet',
                'A graceful empty state for a filtered winner archive with no matching records.',
            ),
            renderData: [
                'summary' => 'No archive entries match that period or category yet — but the record can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Winner archive',
                        'heading' => 'No entries match that filter — yet',
                        'summary' => 'No winners are filed for that period and category combination. Clear the filter to see the full record, or tell us what you are tracing.',
                        'actions' => [
                            ['label' => 'Back to the archive', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Submit work', 'url' => '/theme-' . $themeKey . '-cta', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'winner-list',
                        'heading' => 'Nothing filed here yet',
                        'summary' => 'When winners are awarded in this period and category they will appear here, newest verdict first.',
                        'items' => [],
                    ],
                    $this->dateFilterRailSection(
                        heading: 'Try a different period',
                        summary: 'Widen the year or category on the left rail to surface the nearest matching verdicts.',
                    ),
                    $this->ctaSection(
                        heading: 'Tracing a specific winner?',
                        summary: 'Tell us the studio or project and we will point you to its archive entry directly.',
                        url: '/theme-' . $themeKey . '-contact',
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
            title: 'Entry not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That entry is not on the record',
                'A not-found page that routes visitors back into the archive and submission paths.',
            ),
            renderData: [
                'summary' => 'That archive slug has moved or never existed — here is the way back into the record.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That entry is not on the record',
                        'summary' => 'The archive slug is broken or the entry has been re-filed. Head back to the winner archive, or reach the submission desk.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse the archive', 'url' => '/theme-' . $themeKey . '-directory', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still tracing something?',
                        summary: 'Tell us the project or cycle you were after and we will route you to the right archive entry.',
                        url: '/theme-' . $themeKey . '-contact',
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
            name: self::BRAND . ' Submit Work',
            title: 'Submit to the next cycle — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Put your work on the record',
                'A focused page inviting studios to submit motion and digital work to the next award cycle.',
            ),
            renderData: [
                'summary' => 'Put your work on the record. Submit motion and digital craft to the next award cycle and the standing archive.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Submit to the next cycle',
                        'heading' => 'Put your work on the record',
                        'summary' => 'Whether it is a title sequence, an interactive piece, or a full campaign, the jury reads every entry against the same scoring axes — and winners stay archived for good.',
                        'actions' => [
                            ['label' => 'Email the archive', 'url' => 'mailto:' . self::ARCHIVE_EMAIL, 'style' => 'primary'],
                            ['label' => 'Back to the archive', 'url' => '/', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'A still from a recent submission to Frame Index',
                    ],
                    $this->juryScoreSection(
                        heading: 'How the jury reads an entry',
                        summary: 'The four scoring axes every submission is measured against, published in advance.',
                    ),
                    $this->proofSection(
                        heading: 'Why studios enter Frame Index',
                        summary: 'What a place on the record is worth, across nine cycles.',
                    ),
                    $this->ctaSection(
                        heading: 'One entry away from the archive',
                        summary: 'Send the project over and we will confirm your entry and its archive slug within two working days.',
                        url: 'mailto:' . self::ARCHIVE_EMAIL,
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function archiveHeroSection(string $heading, string $summary, array $media): array
    {
        $image = $media['proof'][0] ?? $media['listing'][0] ?? $media['hero'][0];

        return [
            'type' => 'archive-hero',
            'heading' => $heading,
            'summary' => $summary,
            'image' => $image,
            'imageAlt' => 'A still from the founding cycle of the archive',
            'items' => [
                ['title' => '2021 · Founding cycle', 'summary' => 'The first verdicts on the record, awarded across motion, interactive, and craft categories.'],
                ['title' => '2022 · Second cycle', 'summary' => 'The cycle that introduced jury-score transparency and the four published axes.'],
                ['title' => '2023 · Third cycle', 'summary' => 'A record year for interactive work, with the first real-time pipeline Grand Prix.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dateFilterRailSection(string $heading, string $summary): array
    {
        return [
            'type' => 'date-filter-rail',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => '2025 cycle', 'summary' => 'The current jury cycle — 41 winners across nine categories, scores published.', 'url' => '#winner-list'],
                ['title' => 'Motion direction', 'summary' => 'Title sequences, idents, and broadcast craft, filtered to direction-led entries.', 'url' => '#winner-list'],
                ['title' => 'Interactive & real-time', 'summary' => 'Web, installation, and real-time pieces, sorted by the period they shipped.', 'url' => '#winner-list'],
                ['title' => 'Craft & technique', 'summary' => 'Compositing, type, and sound design recognised on their own terms.', 'url' => '#winner-list'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function winnerListSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['proof'])));

        $winners = [
            [
                'title' => 'Tidal States',
                'summary' => 'A four-minute documentary title sequence pairing hand-drawn cels with photographed water — the 2025 Grand Prix for motion direction.',
                'meta' => 'Gold · Motion direction · 2025',
                'tier' => 'gold',
                'care_note' => 'Atlas Motion · 94.2 jury score',
            ],
            [
                'title' => 'Signal Drift',
                'summary' => 'A real-time generative ident for a public broadcaster, rendered live on air across a full season.',
                'meta' => 'Gold · Interactive & real-time · 2025',
                'tier' => 'gold',
                'care_note' => 'Studio Halftone · 91.7 jury score',
            ],
            [
                'title' => 'Paper Cities',
                'summary' => 'A stop-motion campaign built from folded architectural models, scored for craft and technique.',
                'meta' => 'Silver · Craft & technique · 2025',
                'tier' => 'silver',
                'care_note' => 'Workshop Nine · 88.4 jury score',
            ],
        ];

        $items = [];

        foreach ($winners as $index => $winner) {
            $items[] = [
                ...$winner,
                'url' => '#winner-' . ($index + 1),
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $winner['title'] . ' award still',
            ];
        }

        return [
            'type' => 'winner-list',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function featuredProjectSection(string $heading, string $summary, array $media, string $mediaKey): array
    {
        $image = $media[$mediaKey][0] ?? $media['hero'][0];

        return [
            'type' => 'featured-project',
            'heading' => $heading,
            'summary' => $summary,
            'url' => '#tidal-states',
            'label' => 'Open the full project record',
            'image' => $image,
            'imageAlt' => 'Tidal States cover still, storm front over water',
            'items' => [
                ['title' => 'Synopsis', 'summary' => 'A coastal documentary opener that moves from charcoal storm fronts into photographed tidal footage across four minutes.'],
                ['title' => 'Technology stack', 'summary' => 'Hand-drawn cels composited over plate photography, finished in a Nuke and Houdini pipeline with a bespoke grain pass.'],
                ['title' => 'Awards carried', 'summary' => '2025 Grand Prix for motion direction, plus a craft commendation for the sound design partnership.'],
            ],
            'credits' => [
                ['role' => 'Direction', 'name' => 'Mara Quinteros'],
                ['role' => 'Studio', 'name' => 'Atlas Motion'],
                ['role' => 'Pipeline & finish', 'name' => 'Northroom'],
                ['role' => 'Sound & score', 'name' => 'Field Recordings Ltd'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function juryScoreSection(string $heading, string $summary): array
    {
        return [
            'type' => 'jury-score-explainer',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Axis 01 · weighted 30%', 'title' => 'Direction', 'summary' => 'Does the piece hold a clear authorial voice from first frame to last? Tidal States scored 96 here.'],
                ['meta' => 'Axis 02 · weighted 25%', 'title' => 'Craft', 'summary' => 'Technical execution across animation, compositing, and finish. The panel marked it 93.'],
                ['meta' => 'Axis 03 · weighted 25%', 'title' => 'Concept', 'summary' => 'Strength and originality of the underlying idea, judged blind to the studio. Scored 95.'],
                ['meta' => 'Axis 04 · weighted 20%', 'title' => 'Impact', 'summary' => 'How the work lands in its context and with its audience. Marked 92 by the cycle jury.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mediaCreditsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'media-credits',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Atlas Motion', 'summary' => 'Lead studio and animation. Direction by Mara Quinteros, with a six-person cel and compositing team.'],
                ['title' => 'Pipeline & finish', 'summary' => 'Houdini and Nuke pipeline by Atlas Motion, colour finish at Northroom, grain and texture passes in-house.'],
                ['title' => 'Sound & score', 'summary' => 'Original score and sound design by Field Recordings Ltd, mixed for broadcast and archive playback.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $entries = [
            ['title' => 'Glasshouse', 'category' => 'Gold · Craft & technique · 2024', 'summary' => 'A macro-photography title sequence for a botanical documentary, praised for its restraint.'],
            ['title' => 'Concrete Choir', 'category' => 'Silver · Motion direction · 2024', 'summary' => 'An architectural ident built entirely from drone plates of brutalist civic buildings.'],
            ['title' => 'Loom', 'category' => 'Gold · Interactive & real-time · 2023', 'summary' => 'A generative installation piece that redraws itself from live weather data.'],
            ['title' => 'Night Freight', 'category' => 'Silver · Craft & technique · 2023', 'summary' => 'A sound-led title sequence for a logistics documentary, scored for its audio layer.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $entry['title'] . ' archive still',
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            // Note: `videoSrc` is intentionally omitted here — this demo
            // catalogue has no hosted stock video pool yet, and the
            // video-preview-grid widget (Wave 4b) is designed to fall back
            // to a static poster whenever an item has no `videoSrc`, so the
            // widget still renders a complete, honest archive rather than
            // guessing at a placeholder video URL.
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function archiveWallIndexSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['proof'], $media['listing'], $media['detail'])));

        $entries = [
            ['title' => 'Tidal States', 'url' => '#tidal-states'],
            ['title' => 'Signal Drift', 'url' => '#winner-2'],
            ['title' => 'Paper Cities', 'url' => '#winner-3'],
            ['title' => 'Glasshouse', 'url' => '#archive-1'],
            ['title' => 'Concrete Choir', 'url' => '#archive-2'],
            ['title' => 'Loom', 'url' => '#archive-3'],
            ['title' => 'Night Freight', 'url' => '#archive-4'],
            ['title' => 'Founding cycle reel', 'url' => '#archive'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $entry['title'] . ' archive still',
            ];
        }

        return [
            'type' => 'archive-wall-index',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function timeCapsuleBrowserSection(string $heading, string $summary): array
    {
        return [
            'type' => 'time-capsule-browser',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '2025 cycle',
                    'summary' => 'The current jury cycle — 41 winners across nine categories.',
                    'previewItems' => [
                        ['title' => 'Tidal States'],
                        ['title' => 'Signal Drift'],
                        ['title' => 'Paper Cities'],
                    ],
                ],
                [
                    'title' => '2023 cycle',
                    'summary' => 'A record year for interactive work, with the first real-time pipeline Grand Prix.',
                    'previewItems' => [
                        ['title' => 'Loom'],
                        ['title' => 'Night Freight'],
                    ],
                ],
                [
                    'title' => '2021 · Founding cycle',
                    'summary' => 'The first verdicts on the record, awarded across motion, interactive, and craft categories.',
                    'previewItems' => [
                        ['title' => 'Founding cycle reel'],
                    ],
                ],
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
                ['value' => '9 cycles', 'label' => 'Of award-winning motion and digital work kept on the record.'],
                ['value' => '370+', 'label' => 'Winning projects filed with full scores and credits.'],
                ['value' => '100%', 'label' => 'Of verdicts published with the jury\'s scoring across all four axes.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary, string $action): array
    {
        return [
            'type' => 'newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => $action,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, string $url): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'url' => $url,
            'label' => str_starts_with($url, 'mailto:') ? 'Email the archive' : 'Submit work',
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
                ['label' => 'Archive', 'url' => '/#archive'],
                ['label' => 'Winners', 'url' => '/#winner-list'],
                ['label' => 'Jury scores', 'url' => '/#jury-score-explainer'],
                ['label' => 'Credits', 'url' => '/#media-credits'],
            ],
            'ctaLabel' => 'Submit work',
            'ctaUrl' => '/#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'The standing archive of award-winning motion and digital craft. Filed by year, scored by jury, credited in full.',
            'columns' => [
                [
                    'heading' => 'Archive',
                    'links' => [
                        ['label' => 'Browse by year', 'url' => '/#archive'],
                        ['label' => 'Winners', 'url' => '/#winner-list'],
                        ['label' => 'Categories', 'url' => '/#date-filter-rail'],
                        ['label' => 'Earlier cycles', 'url' => '/theme-reel-room-directory'],
                    ],
                ],
                [
                    'heading' => 'The awards',
                    'links' => [
                        ['label' => 'Jury & scoring', 'url' => '/#jury-score-explainer'],
                        ['label' => 'How to submit', 'url' => '/theme-reel-room-cta'],
                        ['label' => 'Credits policy', 'url' => '/#media-credits'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Submit work', 'url' => '/theme-reel-room-contact'],
                        ['label' => self::ARCHIVE_EMAIL, 'url' => 'mailto:' . self::ARCHIVE_EMAIL],
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
