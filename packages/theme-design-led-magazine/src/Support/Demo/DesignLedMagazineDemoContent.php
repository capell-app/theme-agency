<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignLedMagazine\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Design Led Magazine theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature editorial renderers (lead-story /
 * editor-picks / vertical-categories / gallery-feature / product-credits /
 * trend-list / newsletter) alongside the shared hero/cta — giving every surface
 * a full, individual design-magazine site rather than the shared skeleton.
 */
final class DesignLedMagazineDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Atrium & Field';

    private const string DESK_EMAIL = 'desk@atriumandfield.studio';

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
            title: self::BRAND . ' — A Design Magazine for Architecture & Culture',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A design magazine that reads like an exhibition',
                'Atrium & Field is a photography-led journal covering architecture, interiors, fashion, and art, with editor picks, gallery features, and product credits.',
            ),
            renderData: [
                'summary' => 'Atrium & Field is a photography-led design magazine covering architecture, interiors, fashion, and art — with lead stories, editor picks, galleries, and product credits.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Design Led Magazine',
                        heading: 'A design magazine that reads like an exhibition',
                        summary: 'Photography-led lead stories, editor picks, vertical categories, gallery features, and product credits — paced with editorial restraint across architecture, interiors, fashion, and art.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'A light-filled coastal house photographed for the lead feature',
                        primaryLabel: 'Read the lead story',
                        primaryUrl: '#lead-story',
                        secondaryLabel: 'Browse galleries',
                        secondaryUrl: '#gallery-feature',
                    ),
                    $this->leadStorySection($media),
                    $this->editorPicksSection(),
                    $this->verticalCategoriesSection(),
                    $this->galleryFeatureSection(),
                    $this->productCreditsSection(),
                    $this->trendListSection(
                        heading: 'Direction worth studying',
                        summary: 'Edited trend rows drawn from features, products, and the spaces we have photographed this season.',
                    ),
                    $this->newsletterSection(
                        heading: 'Send the week in design, carefully edited',
                        summary: 'One considered email of stories, galleries, and product credits — no filler, no daily noise.',
                    ),
                    $this->ctaSection(
                        heading: 'Read the magazine the way it was composed',
                        summary: 'Start with the current issue, then follow the threads through architecture, interiors, fashion, and art.',
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
                'The full archive of stories and galleries',
                'Architecture, interiors, design, fashion, and art features — built to be scanned, filtered, and explored.',
            ),
            renderData: [
                'summary' => 'The full Atrium & Field archive of features, galleries, and product credits across every vertical we cover.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'Every story, gallery, and credit in one place',
                        summary: 'Browse the back catalogue by vertical, or read long-form features in full. The archive stays editorial and legible at any depth.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'An interiors feature laid out across the archive index',
                        primaryLabel: 'Read the lead story',
                        primaryUrl: '#lead-story',
                        secondaryLabel: 'Browse galleries',
                        secondaryUrl: '#gallery-feature',
                    ),
                    $this->contentListingSection(
                        heading: 'Architecture, interiors, design, fashion, and art',
                        summary: 'Structured listing cards keep every vertical scannable without bespoke search records.',
                        media: $media,
                    ),
                    $this->verticalCategoriesSection(),
                    $this->trendListSection(
                        heading: 'Threads running through the archive',
                        summary: 'Recurring ideas across our features — colour direction, rooms worth studying, and objects with staying power.',
                    ),
                    $this->ctaSection(
                        heading: 'Found a thread to follow?',
                        summary: 'Subscribe and we will send the next feature in that vertical the moment it is published.',
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
            title: 'A Coastal House Shaped by Light — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A coastal house shaped by light and restraint',
                'A long-form architecture feature with landscape photography, a short standfirst, gallery sequence, and full product credits.',
            ),
            renderData: [
                'summary' => 'A coastal house shaped by light and restraint — our lead architecture feature, with gallery sequence and full product credits.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Architecture feature',
                        heading: 'A coastal house shaped by light and restraint',
                        summary: 'On a windswept headland, a young practice trades square metres for daylight — and proves that restraint is the most demanding brief of all.',
                        media: $media,
                        mediaKey: 'detail',
                        mediaAlt: 'The coastal house at dusk, glazing reflecting the last of the light',
                        primaryLabel: 'View the gallery',
                        primaryUrl: '#gallery-feature',
                        secondaryLabel: 'See product credits',
                        secondaryUrl: '#product-credits',
                    ),
                    $this->galleryFeatureSection(),
                    $this->productCreditsSection(),
                    $this->editorPicksSection(),
                    $this->newsletterSection(
                        heading: 'Follow the architecture desk',
                        summary: 'Get the next building study, studio profile, and materials note as soon as it runs.',
                    ),
                    $this->ctaSection(
                        heading: 'Read more from the architecture desk',
                        summary: 'This feature is one of dozens. Browse the full vertical or subscribe for what comes next.',
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
            title: 'Reach the Desk — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the desk',
                'Pitch a story, send product credits, or join the newsletter — one calm conversion path that feels like part of the magazine.',
            ),
            renderData: [
                'summary' => 'Pitch a feature, send product credits, or subscribe — reach the Atrium & Field desk through one calm, editorial path.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Reach the desk',
                        heading: 'Pitch a story, or join the newsletter',
                        summary: 'Editorial desk in Lisbon, commissioning across Europe. Write to ' . self::DESK_EMAIL . ' with a feature, a gallery, or a product credit — we read every pitch.',
                        media: $media,
                        mediaKey: 'contact',
                        mediaAlt: 'The Atrium & Field editorial desk',
                        primaryLabel: 'Email the desk',
                        primaryUrl: 'mailto:' . self::DESK_EMAIL,
                        secondaryLabel: 'Read the lead story',
                        secondaryUrl: '#lead-story',
                    ),
                    $this->newsletterSection(
                        heading: 'Reach the desk through one calm conversion path',
                        summary: 'A single newsletter and inquiry path keeps the contact journey part of the editorial experience.',
                    ),
                    $this->proofSection(
                        heading: 'How the desk works',
                        summary: 'What to expect once your pitch or credit lands in our inbox.',
                    ),
                    $this->ctaSection(
                        heading: 'Working on something we should see?',
                        summary: 'Send the feature, the gallery, or the product credit. We reply to every pitch within a week.',
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
            title: 'Nothing in this vertical yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing in this vertical yet',
                'A graceful empty state for a filtered archive with no matching features.',
            ),
            renderData: [
                'summary' => 'No features match that filter yet — but the desk can still point you somewhere worth reading.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The archive',
                        heading: 'No features in this vertical yet',
                        summary: 'We have not published in this category. Clear the filter to read everything, or follow a category that is already running.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: null,
                        primaryLabel: 'Read the lead story',
                        primaryUrl: '#lead-story',
                        secondaryLabel: 'Browse galleries',
                        secondaryUrl: '#gallery-feature',
                    ),
                    $this->verticalCategoriesSection(),
                    $this->editorPicksSection(),
                    $this->ctaSection(
                        heading: 'Looking for a particular vertical?',
                        summary: 'Subscribe and we will tell you the moment we publish in the category you came here for.',
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
                'This page has gone to print',
                'A not-found page that routes readers back into the lead story and the archive.',
            ),
            renderData: [
                'summary' => 'That page has moved or never ran — here is the way back into the magazine.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This page has gone to print',
                        summary: 'The link is broken or the feature has been archived. Head back to the lead story, or explore the verticals.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: null,
                        primaryLabel: 'Back to the magazine',
                        primaryUrl: '/',
                        secondaryLabel: 'Read the lead story',
                        secondaryUrl: '#lead-story',
                    ),
                    $this->verticalCategoriesSection(),
                    $this->ctaSection(
                        heading: 'Still looking for a story?',
                        summary: 'Tell the desk what you needed and we will point you to the right feature.',
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
            title: 'Subscribe to the magazine — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Read design the way it was composed',
                'A focused conversion page inviting readers to subscribe to the magazine.',
            ),
            renderData: [
                'summary' => 'Read design the way it was composed — subscribe to Atrium & Field.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Subscribe',
                        heading: 'Read design the way it was composed',
                        summary: 'One considered email a week — lead stories, galleries, and product credits, edited with the same restraint as the printed page.',
                        media: $media,
                        mediaKey: 'cta',
                        mediaAlt: 'A gallery feature spread from the current issue',
                        primaryLabel: 'Subscribe to the magazine',
                        primaryUrl: '#newsletter',
                        secondaryLabel: 'Read the lead story',
                        secondaryUrl: '#lead-story',
                    ),
                    $this->proofSection(
                        heading: 'Why readers stay subscribed',
                        summary: 'What the weekly edition actually delivers.',
                    ),
                    $this->newsletterSection(
                        heading: 'Send the week in design, carefully edited',
                        summary: 'Join readers across architecture, interiors, fashion, and art who let us do the editing.',
                    ),
                    $this->ctaSection(
                        heading: 'One email away from the next issue',
                        summary: 'Subscribe now and the current edition lands in your inbox within the hour.',
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
            'mediaAlt' => $mediaAlt ?? $heading,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function leadStorySection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['proof'])));

        $stories = [
            [
                'title' => 'A coastal house shaped by light and restraint',
                'summary' => 'A long-form architecture lead with landscape photography, a short standfirst, and links to materials and credits.',
            ],
            [
                'title' => 'Inside a studio making objects with patience',
                'summary' => 'A maker profile pairing portrait photography with material notes and full product credits.',
            ],
            [
                'title' => 'The city guide as a design document',
                'summary' => 'A travel and culture feature with precise categories, captions, and practical context.',
            ],
        ];

        $items = [];

        foreach ($stories as $index => $story) {
            $items[] = [
                ...$story,
                'url' => '#feature-' . ($index + 1),
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $story['title'],
            ];
        }

        return [
            'type' => 'lead-story',
            'kicker' => 'Lead story',
            'heading' => 'Photography first, hierarchy second, clutter last',
            'summary' => 'A strong lead package for an architecture home, interior profile, design launch, fashion editorial, or art feature.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editorPicksSection(): array
    {
        return [
            'type' => 'editor-picks',
            'kicker' => 'Editor picks',
            'heading' => 'Varied story cards for a magazine rhythm',
            'summary' => 'Cards carry image-led features, compact captions, category labels, author, and product-credit notes.',
            'items' => [
                [
                    'title' => 'The apartment with a working library at its centre',
                    'summary' => 'An interiors feature with image, caption, category, and product-source context.',
                    'meta' => 'Interiors. 7 min read.',
                    'care_note' => 'Photographed by Mara Lindqvist',
                ],
                [
                    'title' => 'A chair collection with architectural discipline',
                    'summary' => 'A design story carrying product credits, designer notes, and related reading.',
                    'meta' => 'Design. Editor pick.',
                    'care_note' => 'Credits: Halden Workshop, oak and wool',
                ],
                [
                    'title' => 'Five exhibitions to see this month',
                    'summary' => 'A culture list with dates, location notes, and gallery links.',
                    'meta' => 'Art. City guide.',
                    'care_note' => 'Updated weekly by the culture desk',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function verticalCategoriesSection(): array
    {
        return [
            'type' => 'vertical-categories',
            'kicker' => 'Verticals',
            'heading' => 'Clear sections for broad cultural coverage',
            'items' => [
                [
                    'title' => 'Architecture',
                    'summary' => 'Homes, civic buildings, adaptive reuse, studios, materials, and urban ideas.',
                ],
                [
                    'title' => 'Interiors',
                    'summary' => 'Rooms, residences, hospitality spaces, furniture, lighting, and objects.',
                ],
                [
                    'title' => 'Fashion',
                    'summary' => 'Editorial shoots, product launches, designers, materials, and style notes.',
                ],
                [
                    'title' => 'Art',
                    'summary' => 'Exhibitions, artists, galleries, installations, photography, and criticism.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function galleryFeatureSection(): array
    {
        return [
            'type' => 'gallery-feature',
            'kicker' => 'Gallery feature',
            'heading' => 'Image sequences with enough editorial control',
            'summary' => 'A dark feature band for image galleries, portrait crops, installation views, room tours, and photo essays.',
            'label' => 'View the full gallery',
            'url' => '#feature-1',
            'items' => [
                [
                    'title' => 'Landscape lead with detail crops',
                    'summary' => 'A wide hero photograph paired with secondary crops, captions, and credit lines.',
                ],
                [
                    'title' => 'Captions that matter',
                    'summary' => 'Photographer, stylist, location, product, architect, and material notes carried in full.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productCreditsSection(): array
    {
        return [
            'type' => 'product-credits',
            'kicker' => 'Product credits',
            'heading' => 'Editorial commerce without shouting',
            'items' => [
                [
                    'title' => 'Lighting and objects',
                    'summary' => 'A compact module for sourced items, designers, makers, materials, and stockist links.',
                    'meta' => 'Product credits',
                ],
                [
                    'title' => 'Material notes',
                    'summary' => 'Finishes, fabrics, stone, timber, ceramics, glass, and craft processes, explained.',
                    'meta' => 'Materials',
                ],
                [
                    'title' => 'Books and references',
                    'summary' => 'A row for books, exhibitions, venues, studios, and related editorial resources.',
                    'meta' => 'References',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trendListSection(string $heading, string $summary): array
    {
        return [
            'type' => 'trend-list',
            'kicker' => 'Trend list',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Colour and material direction',
                    'summary' => 'Short, useful trend copy with evidence from features, products, and spaces.',
                ],
                [
                    'title' => 'Rooms worth studying',
                    'summary' => 'A list format for recurring interior ideas, details, and design decisions.',
                ],
                [
                    'title' => 'Objects with staying power',
                    'summary' => 'A curated row for furniture, lighting, tabletop, books, and art editions.',
                ],
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
            'kicker' => 'Newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
            'email_label' => 'Email address',
            'button' => 'Subscribe',
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
            ['title' => 'A house that frames the harbour', 'category' => 'Architecture', 'summary' => 'A waterfront renovation that trades wall space for sightlines.'],
            ['title' => 'The quiet luxury of a working kitchen', 'category' => 'Interiors', 'summary' => 'How one studio designed a room to be used, not just admired.'],
            ['title' => 'A collection built around a single fabric', 'category' => 'Fashion', 'summary' => 'A designer profile on restraint, sourcing, and the long lead time of doing it well.'],
            ['title' => 'The gallery rehang everyone is talking about', 'category' => 'Art', 'summary' => 'A curator reorders a permanent collection and changes how it reads.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'kicker' => 'Latest',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'kicker' => 'Editorial control',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['value' => 'Photo-led', 'label' => 'Lead photography, galleries, captions, and precise crops carry every story.'],
                ['value' => 'Credit ready', 'label' => 'Product credits, materials, designers, makers, and references have clear modules.'],
                ['value' => 'Archive ready', 'label' => 'Categories, editor picks, trend lists, and archives stay scannable at any depth.'],
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
            'kicker' => 'Magazine ready',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Subscribe to the magazine',
            'url' => '#newsletter',
            'actions' => [
                ['label' => 'Subscribe to the magazine', 'url' => '#newsletter', 'style' => 'primary'],
                ['label' => 'Read the lead story', 'url' => '#lead-story', 'style' => 'secondary'],
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
                ['label' => 'Architecture', 'url' => '#lead-story'],
                ['label' => 'Interiors', 'url' => '#editor-picks'],
                ['label' => 'Galleries', 'url' => '#gallery-feature'],
                ['label' => 'Product credits', 'url' => '#product-credits'],
                ['label' => 'Trends', 'url' => '#trend-list'],
            ],
            'consultationUrl' => '#newsletter',
            'ctaLabel' => 'Subscribe',
            'ctaUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Sections',
                'heading' => 'Sections',
                'links' => [
                    ['label' => 'Architecture', 'url' => '#lead-story'],
                    ['label' => 'Interiors', 'url' => '#editor-picks'],
                    ['label' => 'Fashion', 'url' => '#content-listing'],
                    ['label' => 'Art', 'url' => '#content-listing'],
                ],
            ],
            [
                'title' => 'The magazine',
                'heading' => 'The magazine',
                'links' => [
                    ['label' => 'Galleries', 'url' => '#gallery-feature'],
                    ['label' => 'Product credits', 'url' => '#product-credits'],
                    ['label' => 'Trends', 'url' => '#trend-list'],
                    ['label' => 'Editor picks', 'url' => '#editor-picks'],
                ],
            ],
            [
                'title' => 'Connect',
                'heading' => 'Connect',
                'links' => [
                    ['label' => 'Newsletter', 'url' => '#newsletter'],
                    ['label' => self::DESK_EMAIL, 'url' => 'mailto:' . self::DESK_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A photography-led design magazine for architecture, interiors, fashion, and art. Edited in Lisbon, read everywhere.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
