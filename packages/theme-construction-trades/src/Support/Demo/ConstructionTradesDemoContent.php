<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConstructionTrades\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Construction Trades theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (services / project-portfolio
 * / accreditations / process / service-areas / quote-cta) alongside the standard
 * hero/proof/cta — giving every surface a full, individual construction firm site
 * rather than the shared five-section skeleton.
 */
final class ConstructionTradesDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Brackford & Vale';

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
            title: self::BRAND . ' — Building & Civil Engineering Contractors',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Builders a project can stand behind',
                'Brackford & Vale is a family-run building and civil engineering contractor delivering extensions, new builds, and groundworks across the South West.',
            ),
            renderData: [
                'summary' => 'Brackford & Vale is a family-run building and civil engineering contractor. We deliver extensions, new builds, and groundworks to a standard that holds up on site and on paper.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Building & civil engineering',
                        'heading' => 'Builders a project can stand behind',
                        'summary' => 'Thirty years of extensions, new builds, and groundworks across the South West. Fixed quotes, a named site manager, and a finish you can put your name to.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#quote', 'style' => 'primary'],
                            ['label' => 'View our work', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Brackford & Vale site team on a residential build',
                    ],
                    $this->servicesSection(
                        heading: 'What we build',
                        summary: 'Four core trades, run by one team with one site manager — so nothing falls between the gaps.',
                    ),
                    $this->projectPortfolioSection(
                        heading: 'Recent projects',
                        summary: 'A few of the builds we have handed over in the last two years, on time and on budget.',
                    ),
                    $this->accreditationsSection(),
                    $this->processSection(),
                    $this->serviceAreasSection(),
                    $this->quoteCtaSection(
                        heading: 'Know what you want built?',
                        summary: 'Send us the plans or a sketch and we will come back with a fixed, itemised quote — no obligation, no sales call.',
                    ),
                    $this->proofSection(
                        heading: 'Proof in the groundwork',
                        summary: 'Numbers from the last two years on site.',
                    ),
                    $this->ctaSection(
                        heading: 'Planning a build this year?',
                        summary: 'Tell us the scope and timeline. We reply to every enquiry within one working day with honest next steps.',
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
                'A project record you can scan',
                'A directory of completed builds across extensions, new builds, renovations, and groundworks.',
            ),
            renderData: [
                'summary' => 'A directory of completed builds — extensions, new builds, renovations, and groundworks — with scope, value, and outcome on every card.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Our projects',
                        'heading' => 'A project record you can scan',
                        'summary' => 'Browse handed-over builds by trade and value. Every card carries the scope, the duration, and what the client got at the end.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#quote', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Completed Brackford & Vale building project',
                    ],
                    $this->projectPortfolioSection(
                        heading: 'Featured builds',
                        summary: 'The projects clients ask us about most often.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the site diary',
                        summary: 'Smaller jobs, repairs, and maintenance contracts from the books.',
                    ),
                    $this->ctaSection(
                        heading: 'See a build like yours?',
                        summary: 'Tell us what you are planning and we will send the most relevant projects, with real costs and timelines.',
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
            name: self::BRAND . ' Project',
            title: 'Oak Lane Barn Conversion — Project — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Oak Lane — a barn rebuilt for a generation',
                'How Brackford & Vale converted a derelict stone barn into a four-bedroom family home in twenty-two weeks.',
            ),
            renderData: [
                'summary' => 'How we converted a derelict stone barn at Oak Lane into a four-bedroom family home in twenty-two weeks, on a fixed price.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project profile',
                        'heading' => 'Oak Lane — a barn rebuilt for a generation',
                        'summary' => 'A Grade II listed stone barn, structurally unsound and open to the weather. We rebuilt it into a warm, watertight family home while keeping every original timber we could save.',
                        'actions' => [
                            ['label' => 'View all projects', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Oak Lane barn conversion exterior',
                    ],
                    $this->projectPortfolioSection(
                        heading: 'The scope of works',
                        summary: 'What the Oak Lane conversion involved, phase by phase.',
                    ),
                    $this->accreditationsSection(),
                    $this->processSection(),
                    $this->ctaSection(
                        heading: 'Have a barn or listed building?',
                        summary: 'Conversions and restorations are our specialism. Send us the plans and we will tell you honestly what is achievable.',
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
            title: 'Request a quote — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request a quote',
                'Tell us what you are planning. We survey, price, and run every job with our own directly employed team.',
            ),
            renderData: [
                'summary' => 'Tell us what you are planning. We survey, price, and run every job with our own directly employed team — no subcontracted unknowns.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get in touch',
                        'heading' => 'Request a quote',
                        'summary' => 'Yard and office in Wells, working across Somerset and the wider South West. Call 01749 555 0142 or send your plans and we will book a free site survey within the week.',
                        'actions' => [
                            ['label' => 'Email the office', 'url' => 'mailto:office@brackfordandvale.example', 'style' => 'primary'],
                            ['label' => 'View our work', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Brackford & Vale yard and office',
                    ],
                    $this->quoteCtaSection(
                        heading: 'How the quote works',
                        summary: 'One confident path from first call to a fixed, itemised price.',
                    ),
                    $this->serviceAreasSection(),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a free site visit and we will walk the job with you before a single number is written down.',
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
            title: 'No projects found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No projects in this trade yet',
                'A graceful empty state for a filtered project record with no matching builds.',
            ),
            renderData: [
                'summary' => 'No builds match that trade or value filter yet — but the team can still point you to the right work.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project record',
                        'heading' => 'No builds match that filter — yet',
                        'summary' => 'We have not published projects in this trade or value band. Clear the filter to see everything, or tell us what you are looking for.',
                        'actions' => [
                            ['label' => 'View all projects', 'url' => '#projects', 'style' => 'primary'],
                            ['label' => 'Request a quote', 'url' => '#quote', 'style' => 'secondary'],
                        ],
                    ],
                    $this->contentListingSection(
                        heading: 'Nothing published here yet',
                        summary: 'When builds in this trade are handed over they will appear here, newest first.',
                    ),
                    $this->servicesSection(
                        heading: 'While you are here',
                        summary: 'The four trades the firm is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the build and we will send relevant projects from the site diary.',
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
                'A not-found page that routes visitors back into the firm\'s projects and quote paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back onto site.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page is off the plans',
                        'summary' => 'The link is broken or the page has been taken down. Head back to the projects, or send us your build and we will pick it up from there.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View our work', 'url' => '#projects', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right page or the right trade.',
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
            title: 'Book your build — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to break ground?',
                'A focused conversion page inviting new build enquiries and quote requests.',
            ),
            renderData: [
                'summary' => 'Ready to break ground? Request a fixed quote from Brackford & Vale.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Book your build',
                        'heading' => 'Ready to break ground?',
                        'summary' => 'Whether it is a single-storey extension or a full new build, you get the same directly employed team, the same fixed quote, and the same named site manager.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#quote', 'style' => 'primary'],
                            ['label' => 'Email the office', 'url' => 'mailto:office@brackfordandvale.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Brackford & Vale team breaking ground on a new build',
                    ],
                    $this->quoteCtaSection(
                        heading: 'From enquiry to fixed price',
                        summary: 'A direct path to commissioning the firm — survey, price, start date.',
                    ),
                    $this->proofSection(
                        heading: 'Why clients choose us',
                        summary: 'The numbers behind the builds.',
                    ),
                    $this->ctaSection(
                        heading: 'One survey away',
                        summary: 'Send the job over and we will come back within one working day to book your free site visit.',
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
            'items' => [
                ['title' => 'Extensions & alterations', 'summary' => 'Single and double-storey extensions, knock-throughs, and loft conversions, fully managed from foundations to decoration.'],
                ['title' => 'New builds', 'summary' => 'Detached and self-build homes taken from cleared plot to handover, with building control signed off at every stage.'],
                ['title' => 'Renovations & conversions', 'summary' => 'Barn conversions, period restorations, and full refurbishments that respect the original fabric while bringing it up to spec.'],
                ['title' => 'Groundworks & civils', 'summary' => 'Foundations, drainage, retaining walls, and access — the unseen work that the rest of the build depends on.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectPortfolioSection(string $heading, string $summary): array
    {
        return [
            'type' => 'project-portfolio',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Oak Lane Barn Conversion', 'summary' => 'A derelict Grade II barn rebuilt into a four-bedroom family home in twenty-two weeks. £420k, handed over two weeks early.'],
                ['title' => 'Mendip View New Build', 'summary' => 'A detached four-bed self-build on a sloping plot, with retaining groundworks and a timber-frame upper floor. £510k, fixed price held.'],
                ['title' => 'Cathedral Green Extension', 'summary' => 'A double-storey rear extension and full kitchen reconfiguration on a tight city-centre site with restricted access. £165k.'],
                ['title' => 'Tannery Yard Renovation', 'summary' => 'A full strip-out and refurbishment of a former tannery into three rental units, including new drainage and party-wall works. £290k.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accreditationsSection(): array
    {
        return [
            'type' => 'accreditations',
            'heading' => 'Accredited, insured, and signed off',
            'summary' => 'The memberships and cover that mean a building inspector, a mortgage lender, and a homeowner can all trust the same paperwork.',
            'items' => [
                ['title' => 'FMB Master Builder', 'summary' => 'Vetted and inspected members of the Federation of Master Builders, with an independently backed workmanship warranty.'],
                ['title' => 'CHAS & Constructionline', 'summary' => 'Pre-qualified for health, safety, and competence so the firm can work on commercial and public framework jobs.'],
                ['title' => 'NHBC registered', 'summary' => 'New homes built and inspected to NHBC standards, with a ten-year structural warranty on every new build.'],
                ['title' => '£10m public liability', 'summary' => 'Full public and employers\' liability cover, with contract works and plant insurance on every site.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function processSection(): array
    {
        return [
            'type' => 'process',
            'heading' => 'How a build runs with us',
            'summary' => 'Five stages, one site manager, and a programme you can hold us to from the first survey to the final snag.',
            'items' => [
                ['title' => '01 — Site survey', 'summary' => 'We walk the job, take measurements, and talk through what is realistic before anyone commits to anything.'],
                ['title' => '02 — Fixed quote', 'summary' => 'An itemised, fixed-price quote with a programme — so you know the cost and the timeline before we lift a tool.'],
                ['title' => '03 — On site', 'summary' => 'Your directly employed crew arrives with a named site manager and a weekly update you can actually read.'],
                ['title' => '04 — Building control', 'summary' => 'Inspections booked and passed at every stage, with certificates kept and handed over in one pack.'],
                ['title' => '05 — Handover & snag', 'summary' => 'A joint walk-round, a written snag list closed off, and a workmanship warranty in writing.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceAreasSection(): array
    {
        return [
            'type' => 'service-areas',
            'heading' => 'Where we work',
            'summary' => 'Based in Wells, we run jobs across Somerset and the surrounding counties — close enough that the team is on site, not in traffic.',
            'items' => [
                ['title' => 'Somerset', 'summary' => 'Wells, Glastonbury, Shepton Mallet, Frome, and the surrounding villages — our home patch.'],
                ['title' => 'Bath & North East Somerset', 'summary' => 'Extensions, conversions, and listed-building work across Bath and the Chew Valley.'],
                ['title' => 'North Dorset', 'summary' => 'New builds and groundworks down to Sherborne, Gillingham, and the Blackmore Vale.'],
                ['title' => 'South Wiltshire', 'summary' => 'Renovations and civils up to Warminster, Westbury, and the Wylye Valley.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quoteCtaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'quote-cta',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Send your plans', 'summary' => 'Architect\'s drawings, a planning reference, or a sketch on the back of an envelope — whatever you have, we can work from it.'],
                ['title' => 'Free site survey', 'summary' => 'We visit, measure, and talk through the build in person, usually within a week of your first call.'],
                ['title' => 'A fixed, itemised price', 'summary' => 'You get a clear quote broken down by stage, with no provisional sums hiding the real cost.'],
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
                ['name' => '30 yrs', 'quote' => 'Three decades building across the South West, two generations of the same family.'],
                ['name' => '200+', 'quote' => 'Extensions, new builds, and conversions handed over since the yard opened.'],
                ['name' => '94%', 'quote' => 'Of clients who say they would use us again — and most of them already have.'],
                ['name' => '1 day', 'quote' => 'To reply to every enquiry, from a person at the office, not a call centre.'],
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
                ['title' => 'Priddy Farmhouse re-roof', 'summary' => 'Full strip and re-roof of a working farmhouse in natural slate, scaffolded and weather-tight in nine days.'],
                ['title' => 'High Street shopfront', 'summary' => 'A new structural opening and shopfront for a town-centre retailer, completed over two weekends to keep the shop trading.'],
                ['title' => 'Coombe Cottage drainage', 'summary' => 'New foul and surface-water drainage and a soakaway for a cottage that had flooded twice in a year.'],
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
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Request a quote', 'url' => '#quote', 'style' => 'primary'],
                ['label' => 'View our work', 'url' => '#projects', 'style' => 'secondary'],
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
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Accreditations', 'url' => '#accreditations'],
                ['label' => 'Service areas', 'url' => '#areas'],
                ['label' => 'Quote', 'url' => '#quote'],
            ],
            'ctaLabel' => 'Request a quote',
            'ctaUrl' => '#quote',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A family-run building and civil engineering contractor. Wells, working across the South West.',
            'columns' => [
                [
                    'heading' => 'Trades',
                    'links' => [
                        ['label' => 'Extensions', 'url' => '#services'],
                        ['label' => 'New builds', 'url' => '#services'],
                        ['label' => 'Renovations', 'url' => '#services'],
                        ['label' => 'Groundworks', 'url' => '#services'],
                    ],
                ],
                [
                    'heading' => 'The firm',
                    'links' => [
                        ['label' => 'Projects', 'url' => '#projects'],
                        ['label' => 'Accreditations', 'url' => '#accreditations'],
                        ['label' => 'Service areas', 'url' => '#areas'],
                        ['label' => 'The team', 'url' => '#projects'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Request a quote', 'url' => '#quote'],
                        ['label' => 'office@brackfordandvale.example', 'url' => 'mailto:office@brackfordandvale.example'],
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
