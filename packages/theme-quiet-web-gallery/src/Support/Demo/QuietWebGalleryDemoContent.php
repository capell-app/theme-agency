<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietWebGallery\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Quiet Web Gallery theme.
 *
 * Every surface is seeded as an ordered `render_data['sections']` list so the
 * live /theme-quiet-web-gallery render emits the theme's signature surfaces
 * (browse-panels / latest-showcase / style-type-categories / sponsor-space /
 * random-best-of / editorial-posts) alongside the shared
 * hero/proof/content-listing/newsletter/cta — giving each surface a full, calm
 * gallery rather than the generic skeleton. Copy is mined from the screenshot renderer.
 */
final class QuietWebGalleryDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Quiet Gallery';

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
            title: self::BRAND . ' — A calm gallery for the quiet web',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A calm gallery for the quiet web',
                'An understated homepage for browse panels, latest showcase entries, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
            ),
            renderData: [
                'summary' => 'An understated gallery for browse panels, latest showcase entries, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Web Gallery',
                        heading: 'A calm gallery for the quiet web',
                        summary: 'An understated homepage for browse panels, latest showcase entries, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
                        media: $media['hero'][0] ?? null,
                    ),
                    $this->browsePanelsSection(),
                    $this->latestShowcaseSection($media),
                    $this->styleTypeCategoriesSection(),
                    $this->sponsorSpaceSection(),
                    $this->randomBestOfSection(),
                    $this->editorialPostsSection($media),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Follow the gallery quietly',
                        summary: 'A non-submitting newsletter prompt proves the follow journey feels like part of the gallery.',
                    ),
                    $this->ctaSection(
                        heading: 'Submit your site to the gallery',
                        summary: 'A calm invitation to add work without the theme owning entry records.',
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
            title: 'Category archives built to be browsed calmly — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Category archives built to be browsed calmly',
                'Style, type, and category archives keep the listing legible without the theme owning entry records.',
            ),
            renderData: [
                'summary' => 'Style, type, and category archives keep the listing legible without the theme owning entry records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Categories',
                        heading: 'Category archives built to be browsed calmly',
                        summary: 'Style, type, and category archives keep the listing legible without the theme owning entry records.',
                        media: $media['listing'][0] ?? $media['hero'][0] ?? null,
                    ),
                    $this->styleTypeCategoriesSection(),
                    $this->contentListingSection(
                        heading: 'Browse entries without the noise',
                        summary: 'A quiet results grid keeps the archive legible while the theme stays free of entry records.',
                    ),
                    $this->randomBestOfSection(),
                    $this->ctaSection(
                        heading: 'Submit your site',
                        summary: 'Add your work to the gallery archive in one calm step.',
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
            title: 'A feature page that lets the work stay quiet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A feature page that lets the work stay quiet',
                'Latest showcase entries, sponsor space, and best-of lists sit in a calm grid so the gallery reads without shouting.',
            ),
            renderData: [
                'summary' => 'Latest showcase entries, sponsor space, and best-of lists sit in a calm grid so the gallery reads without shouting.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Feature',
                        heading: 'A feature page that lets the work stay quiet',
                        summary: 'Latest showcase entries, sponsor space, and best-of lists sit in a calm grid so the gallery reads without shouting.',
                        media: $media['detail'][0] ?? null,
                    ),
                    $this->latestShowcaseSection($media),
                    $this->editorialPostsSection($media),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Keep browsing',
                        summary: 'A clear path back into the gallery from a single feature.',
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
            title: 'One quiet path to follow or submit — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'One quiet path to follow or submit',
                'A non-submitting conversion form proves newsletter, submission, and sponsor journeys feel like part of the gallery.',
            ),
            renderData: [
                'summary' => 'A non-submitting conversion form proves newsletter, submission, and sponsor journeys feel like part of the gallery.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Contact',
                        heading: 'One quiet path to follow or submit',
                        summary: 'A non-submitting conversion form proves newsletter, submission, and sponsor journeys feel like part of the gallery.',
                        media: $media['contact'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Follow, submit, or sponsor',
                        summary: 'A calm prompt covers every way to stay close to the gallery.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Submit your site',
                        summary: 'A focused invitation to add work to the gallery.',
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
            title: 'No entries match that filter yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No entries match that filter yet',
                'A graceful empty state for a filtered archive with no matching entries.',
            ),
            renderData: [
                'summary' => 'No entries match that filter yet — the gallery stays calm and points somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Categories',
                        heading: 'No entries match that filter yet',
                        summary: 'Nothing matches the current style or category. Clear the filter to see every entry, or browse the panels.',
                        media: null,
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'When entries land, they appear here',
                        'summary' => 'New showcase entries show up in this quiet grid, newest first.',
                        'items' => [],
                    ],
                    $this->browsePanelsSection(),
                    $this->ctaSection(
                        heading: 'Be the first to submit here',
                        summary: 'Add your site and help seed the gallery for this category.',
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
            title: 'That entry moved — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That entry moved',
                'A not-found page that routes visitors back into the browse panels and best-of lists.',
            ),
            renderData: [
                'summary' => 'That entry moved or never existed — here is the way back into the gallery.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That entry moved',
                        summary: 'The link is broken or the entry has moved. Head back to the browse panels, or see the best-of lists.',
                        media: null,
                    ),
                    $this->ctaSection(
                        heading: 'Back to the gallery',
                        summary: 'A clear path home keeps a missing entry from ending the visit.',
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
            title: 'Submit your site to the gallery — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Submit your site to the gallery',
                'A focused conversion page inviting makers to add their work to the calm web gallery.',
            ),
            renderData: [
                'summary' => 'A focused conversion page inviting makers to add their work to the calm web gallery.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Submit',
                        heading: 'Submit your site to the gallery',
                        summary: 'Add your work to a calm, curated gallery that lets the design speak for itself.',
                        media: $media['cta'][0] ?? null,
                    ),
                    $this->newsletterSection(
                        heading: 'Get notified when submissions open',
                        summary: 'A single subscribe field keeps makers in the loop on new gallery rounds.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Submit your site now',
                        summary: 'A confident close that asks makers to add their work.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(string $eyebrow, string $heading, string $summary, ?string $media): array
    {
        $section = [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Browse the gallery', 'url' => '#browse-panels', 'style' => 'primary'],
                ['label' => 'See latest entries', 'url' => '#latest-showcase', 'style' => 'secondary'],
            ],
        ];

        if ($media !== null) {
            $section['mediaUrl'] = $media;
            $section['mediaAlt'] = 'A calm, understated web gallery surface';
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
            'heading' => 'Browse the gallery your way',
            'summary' => 'Calm entry points let visitors browse by latest, by category, or by editor picks without the page raising its voice.',
            'items' => [
                ['title' => 'Latest entries', 'summary' => 'The newest sites added to the gallery, refreshed often.'],
                ['title' => 'By category', 'summary' => 'Style, type, and category archives for focused browsing.'],
                ['title' => 'Best of', 'summary' => 'Editor-picked sites that define the quiet web.'],
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
            ['title' => 'Marlow Studio', 'meta' => 'Portfolio', 'summary' => 'A restrained studio site that lets the work lead.'],
            ['title' => 'Tideline Journal', 'meta' => 'Publication', 'summary' => 'A reading-first editorial site with calm typography.'],
            ['title' => 'Northglass', 'meta' => 'Product', 'summary' => 'A quiet product page that trusts whitespace.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'latest-showcase',
            'heading' => 'Latest showcase entries',
            'summary' => 'The newest sites in the gallery sit in a calm grid so the work reads without shouting.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function styleTypeCategoriesSection(): array
    {
        return [
            'type' => 'style-type-categories',
            'heading' => 'Browse by style, type, and category',
            'summary' => 'Category archives keep the listing legible without the theme owning entry records.',
            'items' => [
                ['title' => 'Minimal', 'summary' => 'Sites built on restraint, whitespace, and calm type.'],
                ['title' => 'Editorial', 'summary' => 'Reading-first sites where content leads the design.'],
                ['title' => 'Portfolio', 'summary' => 'Personal and studio sites that let the work speak.'],
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
            'heading' => 'A single, respectful sponsor space',
            'summary' => 'One calm sponsor slot keeps the gallery sustainable without breaking the quiet.',
            'label' => 'Become a sponsor',
            'url' => '#newsletter',
            'items' => [
                ['title' => 'This month', 'summary' => 'One sponsor, shown once, in keeping with the calm tone.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function randomBestOfSection(): array
    {
        return [
            'type' => 'random-best-of',
            'heading' => 'A random best-of, every visit',
            'summary' => 'A rotating pick from the best-of lists gives returning visitors something new without any noise.',
            'items' => [
                ['title' => 'Quiet portfolios', 'summary' => 'Ten personal sites that prove restraint wins.'],
                ['title' => 'Calm publications', 'summary' => 'Reading-first sites with standout typography.'],
                ['title' => 'Understated products', 'summary' => 'Product pages that trust whitespace and clarity.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function editorialPostsSection(array $media): array
    {
        return [
            'type' => 'editorial-posts',
            'heading' => 'Simple editorial posts',
            'summary' => 'Short, calm write-ups give context to the gallery without turning it into a busy blog.',
            'items' => [
                ['title' => 'On the quiet web', 'summary' => 'Why restraint is having a moment in web design.', 'image' => $media['proof'][0] ?? null],
                ['title' => 'Designing with whitespace', 'summary' => 'How calm layouts let the work breathe.'],
                ['title' => 'Curating a gallery', 'summary' => 'The thinking behind what gets featured.'],
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
            'heading' => 'Makers who trust the gallery',
            'summary' => 'A calm gallery earns trust by staying out of the way — here is what that looks like in practice.',
            'items' => [
                ['title' => '2,000 entries', 'quote' => 'Being featured here sent calm, qualified traffic.'],
                ['title' => '4.9/5 maker rating', 'quote' => 'The quiet layout let my site actually stand out.'],
                ['title' => '20 best-of lists', 'quote' => 'A best-of feature is a real badge of honour.'],
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
                ['title' => 'Marlow Studio', 'summary' => 'A restrained studio site that lets the work lead.'],
                ['title' => 'Tideline Journal', 'summary' => 'A reading-first editorial site with calm typography.'],
                ['title' => 'Northglass', 'summary' => 'A quiet product page that trusts whitespace.'],
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
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Submit your site', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Browse the gallery', 'url' => '#browse-panels', 'style' => 'secondary'],
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
                ['label' => 'Browse', 'url' => '#browse-panels'],
                ['label' => 'Latest', 'url' => '#latest-showcase'],
                ['label' => 'Categories', 'url' => '#style-type-categories'],
                ['label' => 'Best of', 'url' => '#random-best-of'],
            ],
            'ctaLabel' => 'Submit your site',
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
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'A calm, understated gallery for the quiet web.',
            'columns' => [
                [
                    'heading' => 'Browse',
                    'links' => [
                        ['label' => 'Browse', 'url' => '#browse-panels'],
                        ['label' => 'Latest', 'url' => '#latest-showcase'],
                    ],
                ],
                [
                    'heading' => 'Discover',
                    'links' => [
                        ['label' => 'Categories', 'url' => '#style-type-categories'],
                        ['label' => 'Best of', 'url' => '#random-best-of'],
                    ],
                ],
                [
                    'heading' => 'Join',
                    'links' => [
                        ['label' => 'Submit your site', 'url' => '#newsletter'],
                        ['label' => 'Sponsor', 'url' => '#sponsor-space'],
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
