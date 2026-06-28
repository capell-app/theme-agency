<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Agency\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Agency theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (services / project-showcase
 * / case-study / team / client-logos) alongside the standard hero/proof/cta —
 * giving every surface a full, individual studio site rather than the shared
 * five-section skeleton.
 */
final class AgencyDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Fieldwork';

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
            title: self::BRAND . ' — Brand & Digital Studio',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A brand and digital studio',
                'Fieldwork is an independent studio building brands, websites, and campaigns for teams who care about the details.',
            ),
            renderData: [
                'summary' => 'Fieldwork is an independent brand and digital studio. We build identities, websites, and campaigns that hold up in the real world.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Brand & digital studio',
                        'heading' => 'We build brands that earn their place in the world',
                        'summary' => 'Strategy, identity, and digital product under one roof. We partner with founders and in-house teams to make work that is sharp, useful, and built to last.',
                        'actions' => [
                            ['label' => 'See our work', 'url' => '#work', 'style' => 'primary'],
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Fieldwork studio at work',
                    ],
                    $this->servicesSection(
                        heading: 'What we do',
                        summary: 'Three practices that work as one team. Most engagements move across all three.',
                    ),
                    $this->projectShowcaseSection(
                        heading: 'Selected work',
                        summary: 'A few recent projects across brand, digital, and motion.',
                        media: $media,
                    ),
                    $this->caseStudySection($media),
                    $this->teamSection(
                        heading: 'The people behind the work',
                        summary: 'A small senior team, no account layers between you and the makers.',
                    ),
                    $this->clientLogosSection(),
                    $this->proofSection(
                        heading: 'Proof, not promises',
                        summary: 'Outcomes from the last two years of studio work.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a project in mind?',
                        summary: 'Tell us what you are building. We reply to every enquiry within one working day.',
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
            name: self::BRAND . ' Work',
            title: 'Work — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Selected projects and case studies',
                'A directory of studio work across brand identity, websites, product, and campaigns.',
            ),
            renderData: [
                'summary' => 'Selected projects and case studies across brand identity, digital product, and campaigns.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Our work',
                        'heading' => 'Selected projects & case studies',
                        'summary' => 'Browse the studio archive. Filter by discipline, or read the long-form story behind each engagement.',
                        'actions' => [
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Studio project work',
                    ],
                    $this->projectShowcaseSection(
                        heading: 'Featured projects',
                        summary: 'The work we keep coming back to.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Smaller engagements, experiments, and collaborations.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'See a project that fits your brief?',
                        summary: 'Tell us what you are planning and we will send the most relevant work, with context on scope and timing.',
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
            title: 'Meridian — Case Study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Meridian — a brand built for momentum',
                'How Fieldwork rebuilt the Meridian brand and website in twelve weeks ahead of a Series B raise.',
            ),
            renderData: [
                'summary' => 'How we rebuilt the Meridian brand and website in twelve weeks ahead of a Series B raise.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Case study',
                        'heading' => 'Meridian — a brand built for momentum',
                        'summary' => 'A fintech infrastructure company outgrowing its first identity. We gave it a system that scales from pitch deck to product.',
                        'actions' => [
                            ['label' => 'View all work', 'url' => '#work', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Meridian brand work',
                    ],
                    $this->caseStudySection($media),
                    $this->teamSection(
                        heading: 'Project team',
                        summary: 'The people who shipped this engagement.',
                    ),
                    $this->contentListingSection(
                        heading: 'Related work',
                        summary: 'Other projects in the same neighbourhood.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want results like these?',
                        summary: 'Most engagements start with a short paid discovery. Tell us about the work and we will map a path.',
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
                'Start a project',
                'Tell us what you are planning. We scope brand, web, and campaign work directly with the people who do it.',
            ),
            renderData: [
                'summary' => 'Tell us what you are planning. We scope every project directly with the senior team — no account managers in between.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Start a project',
                        'summary' => 'Studio in Bristol, working with clients across the UK, Europe, and the US. Email studio@fieldwork.example or use the details below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the studio', 'url' => 'mailto:studio@fieldwork.example', 'style' => 'primary'],
                            ['label' => 'See our work', 'url' => '#work', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Fieldwork studio',
                    ],
                    $this->servicesSection(
                        heading: 'How we can help',
                        summary: 'Pick the shape that fits. Most projects start with a short paid discovery.',
                    ),
                    $this->proofSection(
                        heading: 'What to expect',
                        summary: 'How we work once a project starts.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a 30-minute intro call and we will tell you honestly whether we are the right studio for the job.',
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
            title: 'No results — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing here yet',
                'A graceful empty state for a filtered work archive with no matching projects.',
            ),
            renderData: [
                'summary' => 'No projects match that filter yet — but the studio can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Work archive',
                        'heading' => 'No projects match that filter — yet',
                        'summary' => 'We have not published work in this category. Clear the filter to see everything, or tell us what you are looking for.',
                        'actions' => [
                            ['label' => 'View all work', 'url' => '#work', 'style' => 'primary'],
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'project-showcase',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When projects land in this category they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->servicesSection(
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
                'Page not found',
                'A not-found page that routes visitors back into the studio work and contact paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page took a different brief',
                        'summary' => 'The link is broken or the page has moved. Head back to the work, or start a conversation with the studio.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'See our work', 'url' => '#work', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place.',
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
            title: 'Work with us — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to make something that lasts?',
                'A focused conversion page inviting new project enquiries.',
            ),
            renderData: [
                'summary' => 'Ready to make something that lasts? Start a project with the studio.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => "Let's work together",
                        'heading' => 'Ready to make something that lasts?',
                        'summary' => 'Whether it is a full rebrand or a single landing page, we bring the same senior team and the same standard.',
                        'actions' => [
                            ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Email the studio', 'url' => 'mailto:studio@fieldwork.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Fieldwork studio',
                    ],
                    $this->proofSection(
                        heading: 'Why teams choose Fieldwork',
                        summary: 'The numbers behind the work.',
                    ),
                    $this->ctaSection(
                        heading: 'One brief away',
                        summary: 'Send the project over and we will come back within one working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function servicesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'services',
            'heading' => $heading,
            'summary' => $summary,
            'services' => [
                [
                    'discipline' => 'Brand',
                    'title' => 'Strategy & identity',
                    'summary' => 'Positioning, naming, and a visual system that works everywhere from the pitch deck to the storefront.',
                    'deliverables' => ['Brand strategy', 'Visual identity', 'Messaging & tone', 'Brand guidelines'],
                ],
                [
                    'discipline' => 'Digital',
                    'title' => 'Websites & product',
                    'summary' => 'Marketing sites and product interfaces designed and built to ship, not just to present.',
                    'deliverables' => ['UX & UI design', 'Design systems', 'Front-end build', 'CMS & content modelling'],
                ],
                [
                    'discipline' => 'Motion',
                    'title' => 'Campaigns & motion',
                    'summary' => 'Launch campaigns, social, and motion that carry the brand into the places people actually spend time.',
                    'deliverables' => ['Campaign concepts', 'Motion & animation', 'Art direction', 'Social toolkits'],
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function projectShowcaseSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $projects = [
            ['title' => 'Meridian', 'discipline' => 'Brand identity', 'year' => '2025', 'stage' => 'Brand + Web', 'summary' => 'A scalable identity and marketing site for a fintech infrastructure company heading into a Series B raise.'],
            ['title' => 'Harbour & Co.', 'discipline' => 'Packaging', 'year' => '2025', 'stage' => 'Brand', 'summary' => 'Naming, identity, and packaging for an independent coffee roaster opening its first three sites.'],
            ['title' => 'Northwind', 'discipline' => 'Digital product', 'year' => '2024', 'stage' => 'Product', 'summary' => 'A design system and product UI for a renewables platform used by field engineers daily.'],
            ['title' => 'Atlas Festival', 'discipline' => 'Campaign', 'year' => '2024', 'stage' => 'Motion', 'summary' => 'A launch campaign and motion toolkit that took a regional arts festival national.'],
            ['title' => 'Studio Verda', 'discipline' => 'Website', 'year' => '2024', 'stage' => 'Web', 'summary' => 'An editorial portfolio site for an architecture practice that lets the buildings speak.'],
        ];

        $items = [];

        foreach ($projects as $index => $project) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$project,
                'url' => '#case-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $project['title'] . ' project',
            ];
        }

        return [
            'type' => 'project-showcase',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function caseStudySection(array $media): array
    {
        $gallery = array_slice($media['listing'], 0, 3);

        return [
            'type' => 'case-study',
            'heading' => 'Rebuilding Meridian in twelve weeks',
            'summary' => 'Meridian had outgrown a brand built in a weekend three years earlier. With a raise on the horizon, they needed an identity that signalled scale.',
            'client' => 'Meridian',
            'discipline' => 'Brand + Web',
            'year' => '2025',
            'mediaUrl' => $media['detail'][0],
            'mediaAlt' => 'Meridian brand system',
            'challenge' => 'A trusted product wrapped in an identity that looked like a side project. Investors and enterprise buyers were judging the book by its cover.',
            'approach' => 'Twelve focused weeks: a week of strategy, a flexible identity system, then a marketing site built on a component library their team could own.',
            'result' => 'A confident brand that held up in the data room and the demo. The new site shipped the week before the round opened.',
            'metrics' => [
                ['label' => 'Time to launch', 'value' => '12 wks'],
                ['label' => 'Demo-to-signup lift', 'value' => '+38%'],
                ['label' => 'Series B raised', 'value' => '$24m'],
            ],
            'gallery' => array_map(
                static fn (string $url): array => ['url' => $url, 'alt' => 'Meridian brand detail'],
                $gallery,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teamSection(string $heading, string $summary): array
    {
        return [
            'type' => 'team',
            'heading' => $heading,
            'summary' => $summary,
            'people' => [
                ['name' => 'Priya Nadkarni', 'role' => 'Founder & ECD', 'summary' => 'Twenty years across agencies and in-house. Sets the creative bar and keeps it there.'],
                ['name' => 'Tom Becker', 'role' => 'Design Director', 'summary' => 'Identity systems and type. Believes the details are the work.'],
                ['name' => 'Lena Cruz', 'role' => 'Strategy Lead', 'summary' => 'Turns messy briefs into sharp positioning the whole team can build from.'],
                ['name' => 'Sam Okafor', 'role' => 'Technology Director', 'summary' => 'Ships the front end and makes sure the design survives contact with production.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clientLogosSection(): array
    {
        return [
            'type' => 'client-logos',
            'heading' => 'Trusted by teams who sweat the details',
            'summary' => 'A few of the brands we have shaped over the last two years.',
            'logos' => [
                ['name' => 'Meridian'],
                ['name' => 'Harbour & Co.'],
                ['name' => 'Northwind'],
                ['name' => 'Atlas'],
                ['name' => 'Verda'],
                ['name' => 'Lumen'],
                ['name' => 'Kindred'],
                ['name' => 'Foundry'],
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
                ['metric' => '12 wks', 'name' => 'Typical brand-to-launch', 'quote' => 'Most full engagements ship in a quarter, not a year.'],
                ['metric' => '40+', 'name' => 'Brands shipped', 'quote' => 'Across fintech, culture, product, and retail since 2019.'],
                ['metric' => '1 day', 'name' => 'Reply to every enquiry', 'quote' => 'You hear back from a real person on the team, fast.'],
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
            ['title' => 'Lumen — identity refresh', 'type' => 'Brand', 'summary' => 'A lighter, warmer identity for a health-tech startup finding its voice.'],
            ['title' => 'Kindred — campaign', 'type' => 'Motion', 'summary' => 'A social-first launch campaign for a community lending app.'],
            ['title' => 'Foundry — design system', 'type' => 'Product', 'summary' => 'A component library that unified four product teams on one language.'],
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
                ['label' => 'See our work', 'url' => '#work', 'style' => 'secondary'],
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
                ['label' => 'Work', 'url' => '#work'],
                ['label' => 'Studio', 'url' => '#studio'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Journal', 'url' => '#journal'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Start a project',
            'ctaUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An independent brand and digital studio. Bristol, working everywhere.',
            'columns' => [
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'About', 'url' => '#studio'],
                        ['label' => 'Team', 'url' => '#studio'],
                        ['label' => 'Journal', 'url' => '#journal'],
                        ['label' => 'Careers', 'url' => '#studio'],
                    ],
                ],
                [
                    'heading' => 'Work',
                    'links' => [
                        ['label' => 'Brand', 'url' => '#work'],
                        ['label' => 'Digital', 'url' => '#work'],
                        ['label' => 'Motion', 'url' => '#work'],
                        ['label' => 'Case studies', 'url' => '#work'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'studio@fieldwork.example', 'url' => 'mailto:studio@fieldwork.example'],
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
