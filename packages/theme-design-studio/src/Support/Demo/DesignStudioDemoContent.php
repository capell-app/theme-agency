<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignStudio\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Design Studio theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * adapter emits the theme's signature renderers (project-gallery / lookbook /
 * studio-services / awards / studio-statement) alongside the standard
 * hero/proof/cta — giving every surface a full interior-design studio site
 * rather than the shared five-section skeleton. Copy is mined from the theme
 * screenshot renderer and tuned for an editorial spatial-design practice.
 */
final class DesignStudioDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Marrow & Vale';

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
            title: self::BRAND . ' — Interior & Spatial Design Studio',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'An interior and spatial design studio',
                'Marrow & Vale is an editorial design studio shaping interiors, hospitality spaces, and architecture across the UK and Europe.',
            ),
            renderData: [
                'summary' => 'Marrow & Vale is an interior and spatial design studio. We shape considered interiors, hospitality spaces, and architecture that hold up to daily life.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Design Studio',
                        'heading' => 'Spaces a studio can stand behind',
                        'summary' => 'An editorial design practice for projects, lookbooks, studio services, awards, and enquiry-led journeys. We work with operators and homeowners who care about how a space feels, not only how it photographs.',
                        'actions' => [
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'View projects', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Marrow & Vale studio interior',
                    ],
                    $this->projectGallerySection(
                        heading: 'Projects built to be scanned and admired',
                        summary: 'Structured project cards keep the portfolio editorial and legible — residences, restaurants, and retail in one continuous voice.',
                        media: $media,
                    ),
                    $this->lookbookSection($media),
                    $this->studioServicesSection(
                        heading: 'How the studio works',
                        summary: 'Three practices that move as one. Most commissions cross all three before they open.',
                    ),
                    $this->awardsSection(),
                    $this->studioStatementSection(
                        heading: 'A studio that designs for the long stay',
                        summary: 'We design spaces to age well — materials chosen to wear in rather than out, details that reward a second look.',
                    ),
                    $this->proofSection(
                        heading: 'Proof, not mood boards',
                        summary: 'Outcomes from the last three years of studio work.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a space in mind?',
                        summary: 'Tell us about the building, the brief, and the timeline. We reply to every enquiry within one working day.',
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
            name: self::BRAND . ' Projects',
            title: 'Projects — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A directory of projects built to be scanned',
                'The studio archive across residential interiors, hospitality, and spatial commissions.',
            ),
            renderData: [
                'summary' => 'A directory of projects across residential interiors, hospitality, and spatial design — structured to be scanned, not just admired.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Projects',
                        'heading' => 'A directory of projects built to be scanned',
                        'summary' => 'Structured project cards keep the studio editorial and legible. Browse by discipline, or open a project to read the brief, the materials, and the result.',
                        'actions' => [
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Studio project archive',
                    ],
                    $this->projectGallerySection(
                        heading: 'Featured commissions',
                        summary: 'The projects the studio keeps coming back to.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Smaller fit-outs, styling commissions, and collaborations.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'See a project close to your brief?',
                        summary: 'Tell us what you are planning and we will send the most relevant work, with context on scope, budget, and timing.',
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
            name: self::BRAND . ' Case Study',
            title: 'The Larches — Case Study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Larches — a project profile that reads with intent',
                'How Marrow & Vale reworked a Victorian terrace into a calm, daylight-led family home.',
            ),
            renderData: [
                'summary' => 'A project profile that reads with intent: how we reworked a Victorian terrace into a calm, daylight-led family home.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Case study',
                        'heading' => 'The Larches — a project profile that reads with intent',
                        'summary' => 'A single project view pairs imagery and proof so prospective clients can engage with confidence. A tired Victorian terrace, opened up around light and a single material palette.',
                        'actions' => [
                            ['label' => 'View all projects', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Larches interior',
                    ],
                    $this->projectGallerySection(
                        heading: 'Inside The Larches',
                        summary: 'Room by room, the moves that opened the house up.',
                        media: $media,
                    ),
                    $this->studioStatementSection(
                        heading: 'The thinking behind the brief',
                        summary: 'A family of five, a north-facing plot, and a wish list longer than the budget. We made daylight the organising idea.',
                    ),
                    $this->awardsSection(),
                    $this->contentListingSection(
                        heading: 'Related projects',
                        summary: 'Other residences in the same neighbourhood of ideas.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want a home that works like this?',
                        summary: 'Most commissions begin with a short paid feasibility study. Tell us about the building and we will map a path.',
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
            title: 'Start a project — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the studio through one considered path',
                'Tell us about the space and the brief. We scope every commission directly with the people who design it.',
            ),
            renderData: [
                'summary' => 'Reach the studio through one considered path. We scope every commission directly with the senior team — no account managers in between.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Reach the studio through one considered path',
                        'summary' => 'Studio in Bath, working with clients across the UK and Europe. Email studio@marrowandvale.example or use the details below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the studio', 'url' => 'mailto:studio@marrowandvale.example', 'style' => 'primary'],
                            ['label' => 'View projects', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Marrow & Vale studio',
                    ],
                    $this->studioServicesSection(
                        heading: 'How we can help',
                        summary: 'Pick the shape that fits the commission. Most projects open with a short paid feasibility study.',
                    ),
                    $this->featuresSection(),
                    $this->proofSection(
                        heading: 'What to expect',
                        summary: 'How the studio works once a project is underway.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to walk the space together?',
                        summary: 'Book a site visit and we will tell you honestly whether we are the right studio for the building.',
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
            title: 'Nothing published here yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A graceful empty state for a filtered project archive with no matching commissions.',
            ),
            renderData: [
                'summary' => 'No projects match that filter yet — the studio can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project archive',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'An empty listing state stays editorial and structured while the studio prepares its work. Clear the filter to see everything, or tell us what you are looking for.',
                        'actions' => [
                            ['label' => 'View all projects', 'url' => '#projects', 'style' => 'primary'],
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'project-gallery',
                        'heading' => 'No commissions in this category',
                        'summary' => 'When projects land in this category they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->studioServicesSection(
                        heading: 'While you are here',
                        summary: 'The three things the studio is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the brief and we will send relevant work from the archive.',
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
                'A not-found page that routes visitors back into the studio portfolio and contact paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the studio.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'A 404 state keeps the studio editorial and routes visitors back into the portfolio journey. The link is broken or the page has moved.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View projects', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place in the studio.',
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
            name: self::BRAND . ' Enquire',
            title: 'Commission the studio — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn interest into a booked studio conversation',
                'A focused conversion page inviting new design commissions.',
            ),
            renderData: [
                'summary' => 'Turn interest into a booked studio conversation. Commission Marrow & Vale for your next space.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Commission the studio',
                        'heading' => 'Turn interest into a booked studio conversation',
                        'summary' => 'Whether it is a full interior architecture project or a single room, we bring the same senior team and the same standard of detail.',
                        'actions' => [
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Email the studio', 'url' => 'mailto:studio@marrowandvale.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Marrow & Vale studio space',
                    ],
                    $this->studioStatementSection(
                        heading: 'Why teams commission the studio',
                        summary: 'A small senior team, materials chosen for the long stay, and a process that respects the building you already have.',
                    ),
                    $this->proofSection(
                        heading: 'The numbers behind the work',
                        summary: 'A conversion-focused look at outcomes from recent commissions.',
                    ),
                    $this->ctaSection(
                        heading: 'One conversation away',
                        summary: 'Send the brief over and we will come back within one working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function studioServicesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'studio-services',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Interior architecture',
                    'summary' => 'Spatial planning, joinery, and structural moves that reshape how a building lives — from feasibility through to construction.',
                ],
                [
                    'title' => 'Interiors & styling',
                    'summary' => 'Material palettes, lighting schemes, furniture, and the finishing layer that turns a renovated shell into a room you want to stay in.',
                ],
                [
                    'title' => 'Hospitality design',
                    'summary' => 'Restaurants, hotels, and retail spaces designed to trade — front of house to back of house, brand to bricks.',
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function projectGallerySection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $projects = [
            ['title' => 'The Larches', 'summary' => 'A Victorian terrace reworked around daylight and a single oak-and-lime palette for a family of five.'],
            ['title' => 'Vellum', 'summary' => 'A 40-cover neighbourhood restaurant where the kitchen pass becomes the room\'s centrepiece.'],
            ['title' => 'Quarry House', 'summary' => 'A new-build country home set into a hillside, with stone drawn straight from the local ground.'],
            ['title' => 'The Reading Room', 'summary' => 'A members\' library and bar carved from a disused bank, all brass, leather, and low light.'],
            ['title' => 'Saltmarsh', 'summary' => 'A coastal guesthouse stripped back to its timber frame and rebuilt for slow weekends.'],
        ];

        $items = [];

        foreach ($projects as $index => $project) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$project,
                'url' => '#project-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $project['title'] . ' interior',
            ];
        }

        return [
            'type' => 'project-gallery',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function lookbookSection(array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['detail'])));

        $looks = [
            ['title' => 'Lime & oak', 'summary' => 'Warm minerals against pale timber — the palette behind our residential work.'],
            ['title' => 'Low light, brass detail', 'summary' => 'Evening rooms designed to glow rather than glare, for bars and members\' spaces.'],
            ['title' => 'Stone in the raw', 'summary' => 'Local stone left honest and unpolished, carried inside from the landscape.'],
        ];

        $items = [];

        foreach ($looks as $index => $look) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$look,
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $look['title'] . ' material study',
            ];
        }

        return [
            'type' => 'lookbook',
            'heading' => 'Lookbook — material studies from the studio',
            'summary' => 'A working library of palettes, textures, and lighting moods we return to across projects.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function awardsSection(): array
    {
        return [
            'type' => 'awards',
            'heading' => 'Recognition for the work',
            'summary' => 'A few of the moments the studio\'s projects have been noticed.',
            'items' => [
                ['title' => 'House & Garden Awards 2025', 'summary' => 'The Larches shortlisted for Residential Project of the Year.'],
                ['title' => 'Restaurant & Bar Design 2024', 'summary' => 'Vellum won Best Neighbourhood Restaurant, UK & Ireland.'],
                ['title' => 'Dezeen Awards 2024', 'summary' => 'Quarry House longlisted in the Rural House category.'],
                ['title' => 'AD100 Ones to Watch', 'summary' => 'Named among the studios to follow for spatial design.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function studioStatementSection(string $heading, string $summary): array
    {
        return [
            'type' => 'studio-statement',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'A small senior team', 'summary' => 'The people you meet at the first site visit are the people who draw, specify, and see the project through.'],
                ['title' => 'Materials for the long stay', 'summary' => 'We choose finishes to wear in, not out — oak, lime, stone, brass, and honest construction.'],
                ['title' => 'Respect for the building', 'summary' => 'We work with what is already there before we add. The best move is often a quieter one.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'What working with the studio looks like',
            'summary' => 'The shape of a typical commission, from first call to handover.',
            'items' => [
                ['title' => 'Feasibility study', 'summary' => 'A short paid study to test the brief, the budget, and what the building can take.'],
                ['title' => 'Design & specification', 'summary' => 'Concept through to detailed drawings, schedules, and a costed material palette.'],
                ['title' => 'On site', 'summary' => 'Regular site visits and direct contact with the trades through to a finished handover.'],
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
                ['title' => '60+ spaces delivered', 'summary' => 'Across residential, hospitality, and retail since the studio opened in 2018.'],
                ['title' => '1 day to reply', 'summary' => 'You hear back from a real person on the design team, fast.'],
                ['title' => '9 in 10 return', 'summary' => 'Most clients commission the studio again, or send the next one our way.'],
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
            ['title' => 'Marsh Lane — kitchen extension', 'summary' => 'A single-storey rear extension that brought the garden into the heart of the house.'],
            ['title' => 'The Apothecary — retail fit-out', 'summary' => 'A skincare brand\'s first shop, built around a long terrazzo counter.'],
            ['title' => 'Wren Cottage — styling commission', 'summary' => 'A holiday let dressed for photography and for the people who actually stay.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#archive-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'] . ' project',
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'gallery',
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
                ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                ['label' => 'View projects', 'url' => '#projects', 'style' => 'secondary'],
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
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Studio', 'url' => '#studio'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Start a project',
            'ctaUrl' => '#contact',
            'consultationUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An interior and spatial design studio. Bath, working across the UK and Europe.',
            'columns' => [
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'About', 'url' => '#studio'],
                        ['label' => 'Team', 'url' => '#studio'],
                        ['label' => 'Awards', 'url' => '#studio'],
                        ['label' => 'Careers', 'url' => '#studio'],
                    ],
                ],
                [
                    'heading' => 'Work',
                    'links' => [
                        ['label' => 'Projects', 'url' => '#projects'],
                        ['label' => 'Lookbook', 'url' => '#lookbook'],
                        ['label' => 'Services', 'url' => '#services'],
                        ['label' => 'Case studies', 'url' => '#projects'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'studio@marrowandvale.example', 'url' => 'mailto:studio@marrowandvale.example'],
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
