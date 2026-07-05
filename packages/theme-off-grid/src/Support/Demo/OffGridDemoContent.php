<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OffGrid\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Off Grid theme.
 *
 * Off Grid is a brutalist zine / experimental-archive theme: monospace type,
 * high-contrast black-and-white surfaces with one harsh accent, and visibly
 * off-grid layout. Each surface is seeded as an ordered `render_data['sections']`
 * list so the page adapter emits the theme's signature renderers (archive-wall /
 * irregular-index / rough-links / submission-markers / archive-dates /
 * zine-annotations) alongside the shared hero/proof/cta/newsletter, giving every
 * surface a full archive page rather than the shared skeleton. Every archived
 * capture is wired to a real photograph from `ThemeDemoMedia`.
 */
final class OffGridDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Off Grid';

    private const string INTAKE_EMAIL = 'intake@offgrid.zine';

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
            title: self::BRAND . ' — A brutalist wall for independent culture',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A wall for what got left off the shelf',
                'Off Grid is a photocopied-zine archive of posters, tapes, interviews, and field notes — pinned up raw, dated, and linked out.',
            ),
            renderData: [
                'summary' => 'Off Grid is a brutalist archive for underground culture — posters, tape rips, interviews, and field notes, pinned up raw and dated on intake.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Issue 09. Xeroxed.',
                        heading: 'A wall for what got left off the shelf',
                        summary: 'Photocopied posters, tape rips, interview transcripts, and field notes — pinned up raw, dated, and linked out. No polish, no gatekeeping.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'A basement show poster torn and pinned to the archive wall',
                        primaryLabel: 'Open the wall',
                        primaryUrl: '#archive-wall',
                        secondaryLabel: 'Submit a scan',
                        secondaryUrl: '#submission-markers',
                    ),
                    $this->archiveWallSection($media),
                    $this->irregularIndexSection($media),
                    $this->roughLinksSection(),
                    $this->archiveDatesSection(),
                    $this->submissionMarkersSection(),
                    $this->zineAnnotationsSection(),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Got a scan, a link, or a correction?',
                        summary: 'Send it raw. We keep the formatting rough and the credit exact.',
                        primaryUrl: '#submission-markers',
                        secondaryUrl: '#archive-wall',
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
            name: self::BRAND . ' Wall',
            title: 'The full wall — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every scan, newest first',
                'Posters, tapes, interviews, and field notes filed in one long wall, newest first, with dates and markers intact.',
            ),
            renderData: [
                'summary' => 'The complete Off Grid wall: posters, tapes, interviews, and field notes, newest first, with dates and markers on every entry.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The full wall',
                        heading: 'Every scan filed, nothing smoothed over',
                        summary: 'Browse the whole wall or scan the irregular index below. Rows stay dated, marked, and linked — never rewritten.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'A dense wall of pinned archive scans',
                        primaryLabel: 'Browse the index',
                        primaryUrl: '#irregular-index',
                        secondaryLabel: 'Submit a scan',
                        secondaryUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    $this->archiveWallSection($media),
                    $this->irregularIndexSection($media),
                    $this->archiveDatesSection(),
                    $this->contentListingSection($media),
                    $this->ctaSection(
                        heading: 'Spotted a gap in the wall?',
                        summary: 'Send the missing scan with a date and a marker. We file it, raw.',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryUrl: '#archive-wall',
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
            title: 'Interview with an experimental publisher — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Interview with an experimental publisher',
                'A text-forward entry carrying an interview marker, an issue note, a related archive date, and a language label.',
            ),
            renderData: [
                'summary' => 'A single archive entry: interview marker, issue note, language label, related dates, and the plain links that go with it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Interview. Issue 08. FR.',
                        heading: 'Interview with an experimental publisher',
                        summary: 'A long-form entry kept raw: no decoration, just the transcript, the date stamp, the language note, and links out to the related archive.',
                        media: $media,
                        mediaKey: 'detail',
                        mediaAlt: 'The publisher photographed at their print studio',
                        primaryLabel: 'Back to the wall',
                        primaryUrl: '/#archive-wall',
                        secondaryLabel: 'Read the margin notes',
                        secondaryUrl: '#zine-annotations',
                    ),
                    $this->zineAnnotationsSection(),
                    $this->archiveDatesSection(),
                    $this->roughLinksSection(),
                    $this->ctaSection(
                        heading: 'Run an interview we should index?',
                        summary: 'Send the link with a date and a language note. We file it with an interview marker.',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryUrl: '/#archive-wall',
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
            title: 'Submit a scan — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit a scan to the wall',
                'Send a URL, a title, a date, a language note, and a marker. We keep the formatting raw and the link plain.',
            ),
            renderData: [
                'summary' => 'Submissions stay open. Send a URL, a title, a date, a language note, and a marker — corrections welcome.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submission markers',
                        heading: 'Submit a scan to the wall',
                        summary: 'One plain form, no account needed. Email ' . self::INTAKE_EMAIL . ' or use the form below. We log submissions, accepted scans, and corrections in the open.',
                        media: $media,
                        mediaKey: 'contact',
                        mediaAlt: 'The intake tray where submitted scans are sorted',
                        primaryLabel: 'Email a submission',
                        primaryUrl: 'mailto:' . self::INTAKE_EMAIL,
                        secondaryLabel: 'Read the intake stamps',
                        secondaryUrl: '#submission-markers',
                    ),
                    $this->submissionMarkersSection(),
                    $this->roughLinksSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Ready to send a scan?',
                        summary: 'Include a date and a marker. We reply in the open and pin accepted scans to the wall.',
                        primaryUrl: 'mailto:' . self::INTAKE_EMAIL,
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
            name: self::BRAND . ' No Entries',
            title: 'No entries for that filter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No entries for that filter',
                'A graceful empty state for a filtered archive wall with no matching rows.',
            ),
            renderData: [
                'summary' => 'No rows match that filter yet — clear it to see the whole wall, or submit the missing scan.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Filtered wall',
                        heading: 'No entries match that filter — yet',
                        summary: 'Nothing has been filed under this year, language, or marker. Clear the filter to see everything, or submit the scan yourself.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: null,
                        primaryLabel: 'Back to the wall',
                        primaryUrl: '/',
                        secondaryLabel: 'Submit a scan',
                        secondaryUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    [
                        'type' => 'archive-wall',
                        'heading' => 'Nothing pinned under this filter',
                        'summary' => 'When entries land here they appear on the wall, newest first, with dates and markers intact.',
                        'items' => [],
                    ],
                    $this->roughLinksSection(),
                    $this->submissionMarkersSection(),
                    $this->ctaSection(
                        heading: 'Know a scan that belongs here?',
                        summary: 'Send the URL with a date and a marker and we pin it to the wall, raw.',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
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
            title: 'That link rotted — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That link rotted',
                'A not-found page that routes visitors back into the wall and the submission form.',
            ),
            renderData: [
                'summary' => 'That link is broken or the entry has moved — here is the way back into the wall.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404. Dead link.',
                        heading: 'That link rotted',
                        summary: 'The entry has moved or never existed. Even raw archives lose links — head back to the wall or submit a working one.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: null,
                        primaryLabel: 'Back to home',
                        primaryUrl: '/',
                        secondaryLabel: 'Submit a working link',
                        secondaryUrl: '/theme-' . $themeKey . '-contact',
                    ),
                    $this->roughLinksSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific entry?',
                        summary: 'Send us the title or date and we point you at the right row on the wall.',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
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
            name: self::BRAND . ' Submit Work',
            title: 'Keep the wall alive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Keep the wall alive',
                'A focused page inviting new submissions, interviews, and corrections.',
            ),
            renderData: [
                'summary' => 'Keep the wall alive — submit scans, interviews, and corrections that belong on it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit work',
                        heading: 'Keep the wall alive',
                        summary: 'New submissions, interviews, date archives, and margin notes keep the index moving without adding polish.',
                        media: $media,
                        mediaKey: 'cta',
                        mediaAlt: 'A stack of submitted scans waiting for intake',
                        primaryLabel: 'Submit a scan',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryLabel: 'Read the intake stamps',
                        secondaryUrl: '#submission-markers',
                    ),
                    $this->proofSection(),
                    $this->submissionMarkersSection(),
                    $this->ctaSection(
                        heading: 'One scan away from the wall',
                        summary: 'Send the URL with a date and a marker. We reply in the open and pin accepted scans, raw.',
                        primaryUrl: '/theme-' . $themeKey . '-contact',
                        secondaryUrl: '#submission-markers',
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
    private function archiveWallSection(array $media): array
    {
        $images = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $entries = [
            ['title' => 'Poster tear from a basement show', 'meta' => '2026-04-12. Poster. EN.', 'summary' => 'Photocopied crop, hand-dated, city noted, link out to the source scan.'],
            ['title' => 'Tape rip with a field annotation', 'meta' => 'Release. 2026. Annotated.', 'summary' => 'A compact row with cover scan, release date, a handwritten-style note, and a direct link.'],
            ['title' => 'Interview marker, no decoration', 'meta' => 'Interview. Issue 08. FR.', 'summary' => 'A text-forward entry carrying an interview marker, issue note, and language label.'],
            ['title' => 'Zine scan with edition note', 'meta' => 'Zine. Edition 3. DE.', 'summary' => 'A scan entry with edition number, language note, and an underlined archive link.'],
            ['title' => 'Field note from a one-night show', 'meta' => 'Field note. 2025. EN.', 'summary' => 'A short text row stamped with a date and a marker, no image gloss, no polish.'],
            ['title' => 'Sponsored mix flagged in the open', 'meta' => 'Mix. Sponsor. 2025.', 'summary' => 'A mix entry with a visible sponsor marker and a plain link out to the source.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#entry-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'archive-wall',
            'heading' => 'Pinned newest first',
            'summary' => 'Posters, tapes, interviews, and one-night flyers — a controlled mess of sizes, kept legible on purpose.',
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function irregularIndexSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Poster archive from an independent venue', 'meta' => '2026-04-12. Poster. EN.', 'summary' => 'A rough entry with poster crop, date, city, language note, sponsor marker, and archive link.', 'care_note' => 'Title, date, marker, language, image size, and link supported'],
            ['title' => 'Interview with an experimental publisher', 'meta' => 'Interview. Issue 08. FR.', 'summary' => 'A text-forward entry with interview marker, issue note, related archive date, and language label.', 'care_note' => 'Interview marker, issue note, and language label supported'],
            ['title' => 'Tape rip with a field annotation', 'meta' => 'Release. 2026. Annotated.', 'summary' => 'A compact entry with cover scan, release date, note, direct link, and optional sponsor marker.', 'care_note' => 'Image, release date, annotation, and direct link supported'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#irregular-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'irregular-index',
            'heading' => 'Controlled inconsistency keeps a wall alive',
            'summary' => 'Long rows, short rows, a thumbnail here and none there — the sizes stay uneven, the metadata stays exact.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roughLinksSection(): array
    {
        return [
            'type' => 'rough-links',
            'heading' => 'Outbound, plain, underlined',
            'summary' => 'Familiar link treatment for submission routes, archive years, language filters, interviews, and RSS.',
            'items' => [
                ['title' => 'Underlined outbound links', 'summary' => 'Plain link treatment for submission routes, archive years, language filters, interviews, and RSS.'],
                ['title' => 'Visible focus outlines', 'summary' => 'Keyboard focus is unmistakable, with hard borders and no hidden interactive states.'],
                ['title' => 'Language and zine notes', 'summary' => 'Optional language labels, edition notes, issue numbers, and rough captions on every row.'],
                ['title' => 'Single-column fallback', 'summary' => 'The wall collapses to one readable column on small screens, metadata and link order intact.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function archiveDatesSection(): array
    {
        return [
            'type' => 'archive-dates',
            'heading' => 'The ledger, by month',
            'summary' => 'Browse the wall by month and year. Each entry keeps its date, marker, and language note.',
            'items' => [
                ['title' => 'April 2026', 'meta' => 'Posters. Releases. EN / FR.', 'summary' => 'Eleven entries: venue posters, two releases, and an interview marker.'],
                ['title' => 'March 2026', 'meta' => 'Zines. Interviews. DE.', 'summary' => 'Nine entries: zine scans with edition notes and one long interview.'],
                ['title' => 'February 2026', 'meta' => 'Field notes. Mixes.', 'summary' => 'Seven entries: short field notes and a sponsor-flagged mix.'],
                ['title' => 'January 2026', 'meta' => 'Corrections. Archive.', 'summary' => 'Five entries plus two logged corrections to older rows.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function submissionMarkersSection(): array
    {
        return [
            'type' => 'submission-markers',
            'heading' => 'How to get a scan on the wall',
            'summary' => 'Three stamps cover the whole intake: what we take, how we credit it, what we reject.',
            'label' => 'Submit a scan',
            'url' => 'mailto:' . self::INTAKE_EMAIL,
            'items' => [
                ['title' => 'Submission queue', 'summary' => 'Clear labels for submitted scans, review notes, archive placement, and correction status.', 'stamp' => 'Submitted'],
                ['title' => 'Interview and sponsor markers', 'summary' => 'Interview and sponsor entries stay obvious through text labels rather than decorative badges.', 'stamp' => 'Marked'],
                ['title' => 'Corrections logged in the open', 'summary' => 'Every correction keeps a visible note so the archive stays honest about what changed.', 'stamp' => 'Corrected'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function zineAnnotationsSection(): array
    {
        return [
            'type' => 'zine-annotations',
            'heading' => 'Written in the gutter, kept as-is',
            'summary' => 'Rough captions, edition notes, and field annotations sit beside entries without smoothing them out.',
            'items' => [
                ['title' => 'Edition 3, hand-numbered', 'summary' => 'A short annotation noting the print run, the language, and where the scan came from.'],
                ['title' => 'Field note from the show', 'summary' => 'A raw caption stamped with a date and a city, left exactly as it was written.'],
                ['title' => 'Correction added in margin', 'summary' => 'A visible margin note recording a fixed date and the reason it changed.'],
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
            'heading' => 'What keeps the wall fast',
            'summary' => 'The numbers behind a deliberately raw archive.',
            'items' => [
                ['value' => '20ms', 'label' => 'Public render budget per page'],
                ['value' => '1 col', 'label' => 'Readable fallback on small screens'],
                ['value' => '0 JS', 'label' => 'Required to read the wall'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Reissue of a lost cassette label', 'category' => 'Release. 2025.', 'summary' => 'A direct link to a reissue with original dates and a language note.'],
            ['title' => 'Walking interview, recorded on tape', 'category' => 'Interview. EN.', 'summary' => 'A long interview entry with a transcript link and an archive date.'],
            ['title' => 'Photocopied flyer index', 'category' => 'Poster. Archive.', 'summary' => 'A scanned set of flyers with rough crops and per-image notes.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => 'Smaller entries still worth a link',
            'summary' => 'Experiments and corrections that still earn a place on the wall.',
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
            'heading' => 'New scans, no design, no tracking',
            'summary' => 'One plain email when the wall changes. Unsubscribe by replying "out".',
            'action' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, string $primaryUrl, string $secondaryUrl): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Submit a scan',
            'url' => $primaryUrl,
            'actions' => [
                ['label' => 'Submit a scan', 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => 'Back to the wall', 'url' => $secondaryUrl, 'style' => 'secondary'],
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
            'tagline' => 'Zine / archive / signal',
            'items' => [
                ['label' => 'Wall', 'url' => '/#archive-wall'],
                ['label' => 'Index', 'url' => '/#irregular-index'],
                ['label' => 'Submit', 'url' => 'mailto:' . self::INTAKE_EMAIL],
                ['label' => 'Dates', 'url' => '/#archive-dates'],
                ['label' => 'Digest', 'url' => '/#newsletter'],
            ],
            'ctaLabel' => 'Submit a scan',
            'ctaUrl' => 'mailto:' . self::INTAKE_EMAIL,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'The wall',
                'heading' => 'The wall',
                'links' => [
                    ['label' => 'Wall', 'url' => '/#archive-wall'],
                    ['label' => 'Interviews', 'url' => '/#zine-annotations'],
                    ['label' => 'Digest', 'url' => '/#newsletter'],
                ],
            ],
            [
                'title' => 'Archive',
                'heading' => 'Archive',
                'links' => [
                    ['label' => 'By year', 'url' => '/#archive-dates'],
                    ['label' => 'Language notes', 'url' => '/#irregular-index'],
                    ['label' => 'Corrections', 'url' => '/#zine-annotations'],
                ],
            ],
            [
                'title' => 'Submit',
                'heading' => 'Submit',
                'links' => [
                    ['label' => 'Submit a scan', 'url' => '/#submission-markers'],
                    ['label' => self::INTAKE_EMAIL, 'url' => 'mailto:' . self::INTAKE_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A brutalist archive for underground culture. Plain type, hard borders, fast pages.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
