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
                        eyebrow: 'The current issue',
                        heading: 'A design magazine that reads like an exhibition',
                        summary: 'Homes, studios, collections, and exhibitions across architecture, interiors, fashion, and art — photographed slowly, edited carefully, and published one considered issue at a time.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'A light-filled coastal house photographed for the lead feature',
                        primaryLabel: 'Read the lead story',
                        primaryUrl: '#lead-story',
                        secondaryLabel: 'Browse galleries',
                        secondaryUrl: '#gallery-feature',
                    ),
                    $this->leadStorySection($media),
                    $this->editorPicksSection($media),
                    $this->verticalCategoriesSection(),
                    $this->galleryFeatureSection($media),
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
                        primaryLabel: 'Browse the archive',
                        primaryUrl: '#content-listing',
                        secondaryLabel: 'Follow the threads',
                        secondaryUrl: '#trend-list',
                    ),
                    $this->contentListingSection(
                        heading: 'Architecture, interiors, design, fashion, and art',
                        summary: 'Every feature we have published, newest first — from building studies to studio profiles to the gallery rehang of the season.',
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
                    $this->galleryFeatureSection($media),
                    $this->productCreditsSection(),
                    $this->editorPicksSection($media),
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
                'Pitch a story, send product credits, or join the newsletter — the desk reads everything that arrives.',
            ),
            renderData: [
                'summary' => 'Pitch a feature, send product credits, or subscribe — the Atrium & Field desk reads every message.',
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
                        secondaryLabel: 'Join the newsletter',
                        secondaryUrl: '#newsletter',
                    ),
                    $this->proofSection(
                        heading: 'How the desk works',
                        summary: 'What to expect once your pitch or credit lands in our inbox.',
                    ),
                    $this->newsletterSection(
                        heading: 'Or just read along with us',
                        summary: 'Not pitching yet? Join the weekly edition and see what the desk publishes first.',
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
                        primaryLabel: 'Back to the magazine',
                        primaryUrl: '/',
                        secondaryLabel: 'Browse editor picks',
                        secondaryUrl: '#editor-picks',
                    ),
                    $this->verticalCategoriesSection(),
                    $this->editorPicksSection($media),
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
                        secondaryLabel: 'Browse the verticals',
                        secondaryUrl: '#vertical-categories',
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
                        secondaryLabel: 'Back to the magazine',
                        secondaryUrl: '/',
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
            'mediaAlt' => $mediaAlt,
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
                'summary' => 'On a windswept headland, a young practice trades square metres for daylight and proves restraint is the hardest brief.',
            ],
            [
                'title' => 'Inside a studio making objects with patience',
                'summary' => 'Two makers, one kiln, and a decade of refusing to scale — a profile in doing less, better.',
            ],
            [
                'title' => 'The city guide as a design document',
                'summary' => 'Twelve addresses in Porto that explain how the city thinks about material, craft, and reuse.',
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
            'heading' => 'Three features from the current issue',
            'summary' => 'A coastal house, a patient studio, and a city read as a design document — the stories our editors keep returning to.',
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function editorPicksSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['contact'])));

        $picks = [
            [
                'title' => 'The apartment with a working library at its centre',
                'summary' => 'A collector rebuilds her flat around eleven metres of shelving — and the rooms fall into place behind it.',
                'meta' => 'Interiors. 7 min read.',
                'care_note' => 'Photographed by Mara Lindqvist',
            ],
            [
                'title' => 'A chair collection with architectural discipline',
                'summary' => 'A furniture maker borrows the logic of load-bearing walls and produces the calmest seating of the year.',
                'meta' => 'Design. Editor pick.',
                'care_note' => 'Credits: Halden Workshop, oak and wool',
            ],
            [
                'title' => 'Five exhibitions to see this month',
                'summary' => 'From a ceramics retrospective to a photography survey — the shows worth crossing town for.',
                'meta' => 'Art. City guide.',
                'care_note' => 'Updated weekly by the culture desk',
            ],
        ];

        $items = [];

        foreach ($picks as $index => $pick) {
            $items[] = [
                ...$pick,
                'url' => '#pick-' . ($index + 1),
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $pick['title'],
            ];
        }

        return [
            'type' => 'editor-picks',
            'kicker' => 'Editor picks',
            'heading' => 'What the desk is reading this week',
            'summary' => 'Three stories our editors keep sending each other — an apartment, a chair, and a month of exhibitions.',
            'items' => $items,
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
            'heading' => 'Four desks, one magazine',
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
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function galleryFeatureSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['cta'], $media['hero'])));

        $plates = [
            [
                'title' => 'The approach from the headland',
                'summary' => 'Photographed at first light, before the fog lifts off the water.',
            ],
            [
                'title' => 'The corridor of glazing',
                'summary' => 'Photograph by Mara Lindqvist. Joinery by Halden Workshop.',
            ],
        ];

        $items = [];

        foreach ($plates as $index => $plate) {
            $items[] = [
                ...$plate,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $plate['title'],
            ];
        }

        return [
            'type' => 'gallery-feature',
            'kicker' => 'Gallery feature',
            'heading' => 'The coastal house, plate by plate',
            'summary' => 'A photo essay from our lead feature — the headland at dusk, the long corridor of glazing, and the rooms that hold the light.',
            'label' => 'View the full gallery',
            'url' => '#feature-1',
            'items' => $items,
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
            'heading' => 'Sourced from this issue',
            'items' => [
                [
                    'title' => 'Lighting and objects',
                    'summary' => 'The brass pendant, the turned-oak bowls, and the reading lamp from the library apartment.',
                    'meta' => 'Product credits',
                ],
                [
                    'title' => 'Material notes',
                    'summary' => 'Lime plaster, fumed oak, and the Portuguese granite that anchors the coastal house.',
                    'meta' => 'Materials',
                ],
                [
                    'title' => 'Books and references',
                    'summary' => 'The monographs, exhibition catalogues, and studio visits behind this month\'s features.',
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
                    'summary' => 'Warm minerals and unbleached fibres keep appearing — in plaster, upholstery, and glaze.',
                ],
                [
                    'title' => 'Rooms worth studying',
                    'summary' => 'The working kitchen, the borrowed view, and the corridor treated as a gallery.',
                ],
                [
                    'title' => 'Objects with staying power',
                    'summary' => 'The pieces our editors would still buy in ten years — chairs, lamps, and one very good teapot.',
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
            'kicker' => 'From the desk',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['value' => 'Photo-led', 'label' => 'Every feature is commissioned with its photography — no story runs without its pictures.'],
                ['value' => 'Fully credited', 'label' => 'Designers, makers, materials, and stockists are named in every feature we publish.'],
                ['value' => 'Weekly edition', 'label' => 'One considered email a week — lead stories, galleries, and credits, nothing else.'],
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
            'kicker' => 'Keep reading',
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
