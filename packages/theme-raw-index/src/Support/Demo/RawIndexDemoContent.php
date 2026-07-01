<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RawIndex\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Raw Index theme.
 *
 * Raw Index is a deliberately raw independent-archive theme: monospace type,
 * plain grey surfaces, stark underlined links, and rough metadata. Each surface
 * is seeded as an ordered `render_data['sections']` list so the page adapter
 * emits the theme's signature renderers (archive-wall / irregular-index /
 * rough-links / submission-markers / archive-dates / zine-annotations) alongside
 * the standard hero / proof / cta, giving every surface a full archive page
 * rather than the shared five-section skeleton.
 */
final class RawIndexDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Raw Index';

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
            title: self::BRAND . ' — Independent culture archive',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A fast archive wall for culture, art, music, and zines',
                'Raw Index is a deliberately raw archive: monospace type, plain surfaces, stark underlined links, and rough metadata that keeps long irregular indexes legible.',
            ),
            renderData: [
                'summary' => 'A raw independent index for art, music, zines, interviews, and experiments. Plain type, blue underlined links, visible metadata, fast pages.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => 'Raw independent index',
                        'heading' => 'A fast archive wall for culture, art, music, zines, and experiments',
                        'summary' => 'No polish, no chrome. Just a long, controlled wall of entries with dates, language notes, interview markers, and plain links you can actually follow.',
                        'primary_label' => 'Open the index',
                        'primary_url' => '#index',
                        'secondary_label' => 'Submit work',
                        'secondary_url' => '#submit',
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Raw archive wall',
                    ],
                    $this->archiveWallSection($media),
                    $this->irregularIndexSection($media),
                    $this->roughLinksSection(),
                    $this->archiveDatesSection(),
                    $this->submissionMarkersSection(),
                    $this->zineAnnotationsSection(),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Add a link to the index',
                        summary: 'Submissions stay open. Send a URL, a date, a language note, and a marker — we keep the formatting raw.',
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
            name: self::BRAND . ' Index',
            title: 'The index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full index',
                'Every entry in one long wall — posters, releases, interviews, zines, and field notes, newest first, with dates and language labels.',
            ),
            renderData: [
                'summary' => 'The complete archive wall: posters, releases, interviews, zines, and field notes, newest first, with rough metadata on every row.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => 'The index',
                        'heading' => 'Every entry, newest first',
                        'summary' => 'Scroll the whole wall or jump by archive year. Each row keeps its date, language note, marker, and a plain underlined link.',
                        'primary_label' => 'Submit work',
                        'primary_url' => '#submit',
                        'secondary_label' => 'Browse by year',
                        'secondary_url' => '#years',
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The full archive index',
                    ],
                    $this->archiveWallSection($media),
                    $this->contentListingSection(),
                    $this->archiveDatesSection(),
                    $this->ctaSection(
                        heading: 'Spotted a gap in the index?',
                        summary: 'Send the missing link with a date and a marker. We add it to the wall, raw.',
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
            title: 'Interview — Issue 08 — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Interview with an experimental publisher',
                'A text-forward entry with an interview marker, issue note, related archive date, and a language label.',
            ),
            renderData: [
                'summary' => 'A single archive entry: interview marker, issue note, language label, related dates, and the plain links that go with it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => 'Interview. Issue 08. FR.',
                        'heading' => 'Interview with an experimental publisher',
                        'summary' => 'A long-form entry kept raw: no decoration, just the text, the date stamp, the language note, and links out to the related archive.',
                        'primary_label' => 'Back to the index',
                        'primary_url' => '#index',
                        'secondary_label' => 'RSS',
                        'secondary_url' => '#rss',
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Interview entry',
                    ],
                    $this->zineAnnotationsSection(),
                    $this->archiveDatesSection(),
                    $this->roughLinksSection(),
                    $this->ctaSection(
                        heading: 'Run an interview we should index?',
                        summary: 'Send the link with a date and a language note. We archive it with an interview marker.',
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
            title: 'Submit a link — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit a link to the index',
                'Send a URL, a title, a date, a language note, and a marker. We keep the formatting raw and the link plain.',
            ),
            renderData: [
                'summary' => 'Submissions stay open. Send a URL, a title, a date, a language note, and a marker — corrections and updates welcome.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => 'Submission markers',
                        'heading' => 'Submit a link to the index',
                        'summary' => 'One plain form, no account needed. Email index@rawindex.example or use the route below. We log submissions, accepted links, and corrections in the open.',
                        'primary_label' => 'Email a submission',
                        'primary_url' => 'mailto:index@rawindex.example',
                        'secondary_label' => 'Read the archive rules',
                        'secondary_url' => '#rules',
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Submission desk',
                    ],
                    $this->submissionMarkersSection(),
                    $this->roughLinksSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Ready to send a link?',
                        summary: 'Include a date and a marker. We reply in the open and add accepted links to the wall.',
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
            title: 'No entries — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No entries for that filter',
                'A graceful empty state for a filtered archive wall with no matching rows.',
            ),
            renderData: [
                'summary' => 'No rows match that filter yet — clear it to see the whole wall, or submit the missing link.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => 'Filtered index',
                        'heading' => 'No entries match that filter — yet',
                        'summary' => 'Nothing has been archived under this year, language, or marker. Clear the filter to see everything, or submit the link yourself.',
                        'primary_label' => 'Clear the filter',
                        'primary_url' => '#index',
                        'secondary_label' => 'Submit work',
                        'secondary_url' => '#submit',
                    ],
                    [
                        'type' => 'archive-wall',
                        'heading' => 'Nothing pinned here',
                        'summary' => 'When entries land under this filter they appear on the wall, newest first, with their dates and markers.',
                        'items' => [],
                    ],
                    $this->roughLinksSection(),
                    $this->submissionMarkersSection(),
                    $this->ctaSection(
                        heading: 'Know a link that belongs here?',
                        summary: 'Send the URL with a date and a marker and we add it to the wall, raw.',
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
                'A not-found page that routes visitors back into the index and the submission route.',
            ),
            renderData: [
                'summary' => 'That link is broken or the entry has moved — here is the way back into the index.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => '404. Dead link.',
                        'heading' => 'That link rotted',
                        'summary' => 'The entry has moved or never existed. Even raw archives lose links — head back to the index or submit a working one.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Open the index',
                        'secondary_url' => '#index',
                    ],
                    $this->roughLinksSection(),
                    $this->ctaSection(
                        heading: 'Looking for a specific entry?',
                        summary: 'Send us the title or date and we point you at the right row on the wall.',
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
            title: 'Submit work — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Keep the archive alive',
                'A focused page inviting new submissions, interviews, and corrections.',
            ),
            renderData: [
                'summary' => 'Keep the archive alive — submit links, interviews, and corrections that belong on the wall.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'kicker' => 'Submit work',
                        'heading' => 'Keep the archive alive',
                        'summary' => 'New submissions, interviews, date archives, and zine annotations keep the index moving without adding polish.',
                        'primary_label' => 'Submit a link',
                        'primary_url' => '#submit',
                        'secondary_label' => 'Open the index',
                        'secondary_url' => '#index',
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Submission queue',
                    ],
                    $this->proofSection(),
                    $this->submissionMarkersSection(),
                    $this->ctaSection(
                        heading: 'One link away from the wall',
                        summary: 'Send the URL with a date and a marker. We reply in the open and add accepted links, raw.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function archiveWallSection(array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $entries = [
            ['title' => 'Poster archive from an independent venue', 'meta' => '2026-04-12. Poster. EN.', 'summary' => 'A rough entry with poster crop, date, city, language note, and a plain archive link.'],
            ['title' => 'Music release page with field annotation', 'meta' => 'Release. 2026. Annotated.', 'summary' => 'A compact row with image, release date, a handwritten-style note, and a direct link.'],
            ['title' => 'Interview marker without decoration', 'meta' => 'Interview. Issue 08. FR.', 'summary' => 'A text-forward entry carrying an interview marker, issue note, and language label.'],
            ['title' => 'Zine scan with edition note', 'meta' => 'Zine. Edition 3. DE.', 'summary' => 'A scan entry with edition number, language note, and an underlined archive link.'],
            ['title' => 'Field note from a one-night show', 'meta' => 'Field note. 2025. EN.', 'summary' => 'A short text row stamped with a date and a marker, no image, no polish.'],
            ['title' => 'Sponsored mix flagged in the open', 'meta' => 'Mix. Sponsor. 2025.', 'summary' => 'A mix entry with a visible sponsor marker and a plain link out to the source.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
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
            'heading' => 'The wall, newest first',
            'summary' => 'A controlled wall of entries with irregular media sizes, visible metadata, and plain underlined links.',
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function irregularIndexSection(array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Poster archive from an independent venue', 'meta' => '2026-04-12. Poster. EN.', 'summary' => 'A rough entry with poster crop, date, city, language note, sponsor marker, and archive link.', 'care_note' => 'Title, date, marker, language, image size, and link supported'],
            ['title' => 'Interview with an experimental publisher', 'meta' => 'Interview. Issue 08. FR.', 'summary' => 'A text-forward entry with interview marker, issue note, related archive date, and language label.', 'care_note' => 'Interview marker, issue note, and language label supported'],
            ['title' => 'Music release page with field annotation', 'meta' => 'Release. 2026. Annotated.', 'summary' => 'A compact entry with image, release date, note, direct link, and optional sponsor marker.', 'care_note' => 'Image, release date, annotation, and direct link supported'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
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
            'heading' => 'Controlled inconsistency keeps the wall alive',
            'summary' => 'Entries with varied but bounded media sizes, long text rows, interview markers, date stamps, sponsor labels, and direct links.',
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
            'heading' => 'Keep navigation plain, obvious, and fast',
            'summary' => 'Familiar link treatment for submission routes, archive years, language filters, interviews, and RSS.',
            'items' => [
                ['title' => 'Blue underlined links', 'summary' => 'Plain link treatment for submission routes, archive years, language filters, interviews, and RSS.'],
                ['title' => 'Visible focus outlines', 'summary' => 'Keyboard focus is unmistakable, with rough borders and no hidden interactive states.'],
                ['title' => 'Language and zine notes', 'summary' => 'Optional language labels, edition notes, issue numbers, and rough captions on every row.'],
                ['title' => 'Responsive fallbacks', 'summary' => 'The archive collapses to one readable column on small screens, metadata and link order intact.'],
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
            'heading' => 'Date archives should feel like a real index',
            'summary' => 'Browse the wall by month and year. Each archive keeps its dates, markers, and language notes.',
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
            'heading' => 'Make submissions, ads, and interviews legible',
            'summary' => 'Markers for submitted, accepted, interview, sponsor, ad, language, correction, archive, and update states.',
            'label' => 'Submit a link',
            'url' => '#submit',
            'items' => [
                ['title' => 'Submission queue', 'summary' => 'Clear labels for submitted links, review notes, archive placement, and correction status.'],
                ['title' => 'Interview and sponsor markers', 'summary' => 'Interview and sponsor entries stay obvious through text labels rather than decorative badges.'],
                ['title' => 'Corrections logged in the open', 'summary' => 'Every correction keeps a visible note so the archive stays honest about what changed.'],
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
            'heading' => 'Annotations stay handwritten, not polished',
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
            'heading' => 'What keeps the index fast',
            'summary' => 'The numbers behind a deliberately raw archive.',
            'items' => [
                ['value' => '20ms', 'label' => 'Public render budget per page'],
                ['value' => '1 col', 'label' => 'Readable fallback on small screens'],
                ['value' => '0 JS', 'label' => 'Required to read the wall'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(): array
    {
        return [
            'type' => 'content-listing',
            'heading' => 'More from the archive',
            'summary' => 'Smaller rows, experiments, and corrections that still earn a place on the wall.',
            'items' => [
                ['title' => 'Reissue of a lost cassette label', 'category' => 'Release. 2025.', 'summary' => 'A direct link to a reissue with original dates and a language note.'],
                ['title' => 'Walking interview, recorded on tape', 'category' => 'Interview. EN.', 'summary' => 'A long interview entry with a transcript link and an archive date.'],
                ['title' => 'Photocopied flyer index', 'category' => 'Poster. Archive.', 'summary' => 'A scanned set of flyers with rough crops and per-image notes.'],
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
            'heading' => 'New entries, no noise',
            'summary' => 'A plain email digest of new submissions, interviews, and corrections. No tracking, no design.',
            'action' => '#newsletter',
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
            'label' => 'Submit a link',
            'url' => '#submit',
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
                ['label' => 'Index', 'url' => '#index'],
                ['label' => 'Submit', 'url' => '#submit'],
                ['label' => 'Interviews', 'url' => '#interviews'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'RSS', 'url' => '#rss'],
            ],
            'ctaLabel' => 'Submit a link',
            'ctaUrl' => '#submit',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Editorial',
                'heading' => 'Editorial',
                'links' => [
                    ['label' => 'Index', 'url' => '#index'],
                    ['label' => 'Interviews', 'url' => '#interviews'],
                    ['label' => 'Newsletter', 'url' => '#newsletter'],
                ],
            ],
            [
                'title' => 'Archive',
                'heading' => 'Archive',
                'links' => [
                    ['label' => 'Browse by year', 'url' => '#archive'],
                    ['label' => 'Language notes', 'url' => '#languages'],
                    ['label' => 'Corrections', 'url' => '#corrections'],
                ],
            ],
            [
                'title' => 'Submit',
                'heading' => 'Submit',
                'links' => [
                    ['label' => 'Submit a link', 'url' => '#submit'],
                    ['label' => 'index@rawindex.example', 'url' => 'mailto:index@rawindex.example'],
                    ['label' => 'RSS', 'url' => '#rss'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A raw independent index for art, music, zines, and experiments. Plain type, fast pages.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
