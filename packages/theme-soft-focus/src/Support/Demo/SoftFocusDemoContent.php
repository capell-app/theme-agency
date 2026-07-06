<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\SoftFocus\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Soft Focus theme.
 *
 * Every surface is seeded as an ordered `render_data['sections']` list so the
 * live /theme-soft-focus render emits the theme's signature calm
 * gallery-wall surfaces (browse-panels / latest-showcase /
 * style-type-categories / sponsor-space / random-best-of / editorial-posts)
 * alongside the shared hero/proof/content-listing/newsletter/cta — each
 * surface only links to sections that actually render on that surface.
 */
final class SoftFocusDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Soft Focus';

    private const string CURATOR_EMAIL = 'wall@quietwebgallery.studio';

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
            title: self::BRAND . ' — A calm wall for the quiet web',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A calm wall for the quiet web',
                'A hushed, curated gallery wall of restrained web design, hung two at a time with wide matting and short notes from the curator.',
            ),
            renderData: [
                'summary' => 'A hushed, curated gallery wall of restrained web design, hung two at a time with wide matting and short notes from the curator.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Web Gallery',
                        heading: 'A calm wall for the quiet web',
                        summary: 'Framed captures of restrained, well-made sites — hung two at a time, matted in warm neutrals, with a short curation note under each one.',
                        mediaUrl: $media['hero'][0] ?? null,
                        mediaAlt: 'A restrained site hung on the gallery wall, softly lit',
                        primaryLabel: 'Browse the wall',
                        primaryUrl: '#browse-panels',
                        secondaryLabel: 'See the latest hang',
                        secondaryUrl: '#latest-showcase',
                    ),
                    $this->browsePanelsSection(),
                    $this->latestShowcaseSection($media),
                    $this->styleTypeCategoriesSection(linksToArchive: false),
                    $this->sponsorSpaceSection(),
                    $this->randomBestOfSection($media),
                    $this->editorialPostsSection(),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Hear about the next hang',
                        summary: 'A quiet, occasional note when new sites are added to the wall.',
                    ),
                    $this->ctaSection(
                        heading: 'Submit a site for the wall',
                        summary: 'If your site is quiet, considered, and well made, we would like to hang it here.',
                        primaryUrl: '#newsletter',
                        secondaryUrl: '#browse-panels',
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
            name: self::BRAND . ' Categories',
            title: 'Every capture in the gallery — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every capture in the gallery',
                'The full archive of hung sites, browsable by style and type without the theme owning entry records.',
            ),
            renderData: [
                'summary' => 'The full archive of hung sites, browsable by style and type without the theme owning entry records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Archive',
                        heading: 'Every capture in the gallery',
                        summary: 'Browse the full wall by style or type, or read the archive in one long, calm scroll.',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0] ?? null,
                        mediaAlt: 'A row of framed captures along the archive wall',
                        primaryLabel: 'Browse by category',
                        primaryUrl: '#style-type-categories',
                        secondaryLabel: 'See the full archive',
                        secondaryUrl: '#content-listing',
                    ),
                    $this->styleTypeCategoriesSection(linksToArchive: true),
                    $this->contentListingSection(
                        heading: 'The full archive, newest first',
                        summary: 'Every site we have hung, in one quiet index — thumbnail, category, and a short note for each.',
                        media: $media,
                    ),
                    $this->randomBestOfSection($media),
                    $this->ctaSection(
                        heading: 'Submit a site for the wall',
                        summary: 'Add your work to the archive in one calm step.',
                        primaryUrl: 'mailto:' . self::CURATOR_EMAIL,
                        secondaryUrl: '#style-type-categories',
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
            title: 'Marlow Studio, hung and captioned — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Marlow Studio, hung and captioned',
                'A single-capture feature page: one framed site, a curation note, and a calm path back into the wall.',
            ),
            renderData: [
                'summary' => 'A single-capture feature page: one framed site, a curation note, and a calm path back into the wall.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Feature',
                        heading: 'Marlow Studio, hung and captioned',
                        summary: 'A restrained studio site that lets the work lead the page — this week\'s longer look from the curator.',
                        mediaUrl: $media['detail'][0] ?? null,
                        mediaAlt: 'Marlow Studio, the featured capture for this page',
                        primaryLabel: 'See the latest hang',
                        primaryUrl: '#latest-showcase',
                        secondaryLabel: 'Read the journal note',
                        secondaryUrl: '#editorial-posts',
                    ),
                    $this->latestShowcaseSection($media),
                    $this->editorialPostsSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Keep browsing the wall',
                        summary: 'A clear path back into the gallery from a single feature.',
                        primaryUrl: '#latest-showcase',
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
            name: self::BRAND . ' Contact',
            title: 'Write to the curator — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Write to the curator',
                'Follow the wall, submit a site, or ask about the single sponsor slot — one calm page for every path.',
            ),
            renderData: [
                'summary' => 'Follow the wall, submit a site, or ask about the single sponsor slot — one calm page for every path.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Contact',
                        heading: 'Write to the curator',
                        summary: 'Curated from a small studio. Write to ' . self::CURATOR_EMAIL . ' to submit a site, ask about sponsorship, or just say hello — every message is read by hand.',
                        mediaUrl: $media['contact'][0] ?? null,
                        mediaAlt: 'The curator\'s desk, papers and a single framed print',
                        primaryLabel: 'Email the curator',
                        primaryUrl: 'mailto:' . self::CURATOR_EMAIL,
                        secondaryLabel: 'Follow along instead',
                        secondaryUrl: '#newsletter',
                    ),
                    $this->newsletterSection(
                        heading: 'Or just follow along',
                        summary: 'Not submitting yet? Follow the wall and see what gets hung next.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Submit a site for the wall',
                        summary: 'A focused invitation to add your work to the gallery.',
                        primaryUrl: 'mailto:' . self::CURATOR_EMAIL,
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
            title: 'Nothing is hung in this category yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing is hung in this category yet',
                'A graceful empty state for a filtered archive with no matching entries.',
            ),
            renderData: [
                'summary' => 'Nothing is hung in this category yet — the wall stays calm and points you back toward the browse panels.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Categories',
                        heading: 'Nothing is hung in this category yet',
                        summary: 'This style or type is still empty. Clear the filter to see the whole wall, or pick a different way to browse.',
                        mediaUrl: null,
                        mediaAlt: null,
                        primaryLabel: 'Back to the wall',
                        primaryUrl: '/',
                        secondaryLabel: 'Try another way to browse',
                        secondaryUrl: '#browse-panels',
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'When entries land, they appear here',
                        'summary' => 'New captures show up in this quiet index, newest first.',
                        'items' => [],
                    ],
                    $this->browsePanelsSection(),
                    $this->ctaSection(
                        heading: 'Be the first hung in this category',
                        summary: 'Submit your site and help seed this part of the wall.',
                        primaryUrl: 'mailto:' . self::CURATOR_EMAIL,
                        secondaryUrl: '#browse-panels',
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
            title: 'That capture came down — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That capture came down',
                'A not-found page that routes visitors back to the homepage rather than into sections that only exist elsewhere.',
            ),
            renderData: [
                'summary' => 'That capture came down or never existed — here is the way back into the gallery.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That capture came down',
                        summary: 'The link is broken, or the entry has been taken off the wall. Head back to the gallery to keep looking.',
                        mediaUrl: null,
                        mediaAlt: null,
                        primaryLabel: 'Back to the gallery',
                        primaryUrl: '/',
                        secondaryLabel: 'Write to the curator',
                        secondaryUrl: 'mailto:' . self::CURATOR_EMAIL,
                    ),
                    $this->ctaSection(
                        heading: 'Keep browsing while you\'re here',
                        summary: 'The gallery adds new captures every week — start back at the homepage to see what\'s current.',
                        primaryUrl: '/',
                        secondaryUrl: 'mailto:' . self::CURATOR_EMAIL,
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
            title: 'Submit a site for the wall — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Submit a site for the wall',
                'A focused conversion page inviting makers to add their quiet, considered site to the gallery.',
            ),
            renderData: [
                'summary' => 'A focused conversion page inviting makers to add their quiet, considered site to the gallery.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit',
                        heading: 'Submit a site for the wall',
                        summary: 'If your site is quiet, considered, and well made, write to the curator and we will consider it for the next hang.',
                        mediaUrl: $media['cta'][0] ?? null,
                        mediaAlt: 'A newly framed site, ready to be hung on the wall',
                        primaryLabel: 'Email the curator',
                        primaryUrl: 'mailto:' . self::CURATOR_EMAIL,
                        secondaryLabel: 'Back to the gallery',
                        secondaryUrl: '/',
                    ),
                    $this->newsletterSection(
                        heading: 'Get notified when submissions open',
                        summary: 'A single follow field keeps makers in the loop on the next round of hangs.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Submit your site now',
                        summary: 'A confident close that asks makers to add their work to the wall.',
                        primaryUrl: 'mailto:' . self::CURATOR_EMAIL,
                        secondaryUrl: '#newsletter',
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
        ?string $mediaUrl,
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

        if ($mediaUrl !== null) {
            $section['mediaUrl'] = $mediaUrl;
            $section['mediaAlt'] = $mediaAlt;
        }

        return $section;
    }

    /**
     * @return array<string, mixed>
     */
    private function browsePanelsSection(): array
    {
        return [
            'type' => 'browse-panels',
            'kicker' => 'Ways to browse',
            'heading' => 'Three quiet ways into the gallery',
            'summary' => 'No loud hero banners here — just a few large, calm panels that let you choose how you want to look.',
            'items' => [
                ['title' => 'By style', 'summary' => 'Minimal, editorial, and portfolio hangs, grouped by the restraint they share.', 'url' => '#style-type-categories'],
                ['title' => 'By type', 'summary' => 'Studio sites, publications, and product pages, sorted by what they are for.', 'url' => '#style-type-categories'],
                ['title' => 'Random', 'summary' => 'One unexpected pick, reshuffled on every visit, for slow afternoons.', 'url' => '#random-best-of'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function latestShowcaseSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $entries = [
            ['title' => 'Marlow Studio', 'meta' => 'Portfolio', 'summary' => 'A restrained studio site that lets the work lead the page.'],
            ['title' => 'Tideline Journal', 'meta' => 'Publication', 'summary' => 'A reading-first editorial site with unhurried typography.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'url' => '#latest-showcase',
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'latest-showcase',
            'kicker' => 'Latest showcase',
            'heading' => 'Recently hung',
            'summary' => 'The newest captures added to the wall, each one matted with room to breathe and a short note from the curator.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function styleTypeCategoriesSection(bool $linksToArchive): array
    {
        $categoryUrl = $linksToArchive ? '#content-listing' : '#style-type-categories';

        return [
            'type' => 'style-type-categories',
            'kicker' => 'Style and type',
            'heading' => 'Browse by category, quietly',
            'summary' => 'Soft category links keep the archive easy to scan without turning it into a directory.',
            'items' => [
                ['title' => 'Minimal', 'summary' => 'Sites built on restraint, whitespace, and calm type.', 'count' => '38 sites', 'url' => $categoryUrl],
                ['title' => 'Editorial', 'summary' => 'Reading-first sites where the words set the pace.', 'count' => '21 sites', 'url' => $categoryUrl],
                ['title' => 'Portfolio', 'summary' => 'Personal and studio sites that let the work speak first.', 'count' => '29 sites', 'url' => $categoryUrl],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sponsorSpaceSection(): array
    {
        return [
            'type' => 'sponsor-space',
            'kicker' => 'Sponsor space',
            'heading' => 'One quiet sponsor placement',
            'summary' => 'A single, tasteful card sits here — never more than one, and never louder than the gallery around it.',
            'items' => [
                ['title' => 'Held Studio', 'summary' => 'Type foundry and small studio, supporting the wall this season.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function randomBestOfSection(array $media): array
    {
        $image = $media['proof'][0] ?? $media['listing'][0] ?? null;

        return [
            'type' => 'random-best-of',
            'kicker' => 'Random best of',
            'heading' => 'One pick, held a little longer',
            'summary' => 'Each visit surfaces a single spotlighted site from the best-of wall, given room to be looked at properly.',
            'items' => [
                [
                    'title' => 'Northglass',
                    'summary' => 'A quiet product page that trusts whitespace over noise.',
                    'image' => $image,
                    'imageAlt' => 'Northglass, the spotlighted capture',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editorialPostsSection(): array
    {
        return [
            'type' => 'editorial-posts',
            'kicker' => 'Editorial posts',
            'heading' => 'A few reflective notes',
            'summary' => 'Short, unhurried entries on what restraint in web design actually looks like in practice.',
            'items' => [
                ['title' => 'On the quiet web', 'summary' => 'Why restraint is having a moment, and why it is harder than it looks.'],
                ['title' => 'Designing with whitespace', 'summary' => 'How calm layouts let the work breathe instead of compete.'],
                ['title' => 'Curating a gallery', 'summary' => 'The quiet thinking behind what gets hung, and what gets left off the wall.'],
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
            'kicker' => 'From the curator',
            'items' => [
                ['value' => 'Calm by design', 'label' => 'Warm neutrals, wide matting, and unhurried type let the featured sites carry the room.'],
                ['value' => 'Hand-curated', 'label' => 'Every capture is chosen and captioned by hand, never auto-aggregated.'],
                ['value' => 'Slow archive', 'label' => 'Category, best-of, and random paths keep the wall browsable long after the newest hang.'],
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
            ['title' => 'Marlow Studio', 'category' => 'Portfolio', 'summary' => 'A restrained studio site that lets the work lead the page.'],
            ['title' => 'Tideline Journal', 'category' => 'Publication', 'summary' => 'A reading-first editorial site with unhurried typography.'],
            ['title' => 'Northglass', 'category' => 'Product', 'summary' => 'A quiet product page that trusts whitespace over noise.'],
            ['title' => 'Held Studio', 'category' => 'Studio', 'summary' => 'A type foundry site with slow, deliberate pacing.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#content-listing',
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'kicker' => 'Archive',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'kicker' => 'Follow along',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
            'email_label' => 'Email address',
            'button' => 'Follow the gallery',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, string $primaryUrl, string $secondaryUrl): array
    {
        return [
            'type' => 'cta',
            'kicker' => 'Add your site',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Submit your site', 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => 'Browse the gallery', 'url' => $secondaryUrl, 'style' => 'secondary'],
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
            'tagline' => 'A curated wall of restrained sites',
            'items' => [
                ['label' => 'Browse', 'url' => '/#browse-panels'],
                ['label' => 'Latest', 'url' => '/#latest-showcase'],
                ['label' => 'Categories', 'url' => '/#style-type-categories'],
                ['label' => 'Best of', 'url' => '/#random-best-of'],
            ],
            'ctaLabel' => 'Submit your site',
            'ctaUrl' => 'mailto:' . self::CURATOR_EMAIL,
            'consultationUrl' => 'mailto:' . self::CURATOR_EMAIL,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Browse',
                'heading' => 'Browse',
                'links' => [
                    ['label' => 'Browse panels', 'url' => '/#browse-panels'],
                    ['label' => 'Latest showcase', 'url' => '/#latest-showcase'],
                ],
            ],
            [
                'title' => 'Discover',
                'heading' => 'Discover',
                'links' => [
                    ['label' => 'Style and type', 'url' => '/#style-type-categories'],
                    ['label' => 'Random best of', 'url' => '/#random-best-of'],
                ],
            ],
            [
                'title' => 'Follow',
                'heading' => 'Follow',
                'links' => [
                    ['label' => 'Follow along', 'url' => '/#newsletter'],
                    ['label' => self::CURATOR_EMAIL, 'url' => 'mailto:' . self::CURATOR_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A hushed, curated gallery wall of restrained web design.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
