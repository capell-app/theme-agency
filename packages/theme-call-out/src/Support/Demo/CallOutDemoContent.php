<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\ThemeStudio\CallOut\Enums\WidgetComponentEnum;

/**
 * Complete, vertical-authentic demo content for the Call Out theme: a
 * fictional plumbing-and-electrical local-service business, "Rapid Response
 * Plumbing & Electrical", consistent across all seven Foundation surfaces.
 *
 * Call Out is layout-native (see CallOutThemeServiceProvider): it registers
 * no ThemeRenderer or section renderers, so every surface renders through
 * the shared `x-capell::layout` + layout-builder container pipeline. Each
 * surface below seeds a `containers` payload (a 'main' container carrying
 * the shared `page-content` widget plus one bespoke Call Out widget instance
 * per signature section that surface's {@see sectionCopy()} carries) plus
 * the matching `widgets` blueprint `ThemeDemoPageInstaller` dispatches
 * through `Capell\LayoutBuilder\Support\Creator\WidgetCreator` before writing
 * the containers onto the page's Layout, mirroring
 * `NightShiftDemoContent`'s exact wiring shape.
 */
final class CallOutDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Rapid Response Plumbing & Electrical';

    private const string PHONE_NUMBER = '0800 555 0192';

    private const string EMAIL_ADDRESS = 'quotes@rapidresponse-plumbing.example';

    /**
     * Bespoke Call Out section types that {@see sectionCopy()} may contain
     * and that this class turns into real, seeded
     * `capell.widget.call-out.*` widget instances.
     *
     * @var array<string, WidgetComponentEnum>
     */
    private const array BESPOKE_SECTION_WIDGETS = [
        'emergency-availability-banner' => WidgetComponentEnum::EmergencyAvailabilityBanner,
        'service-area-map-grid' => WidgetComponentEnum::ServiceAreaMapGrid,
        'before-after-comparison' => WidgetComponentEnum::BeforeAfterComparison,
        'quote-path-stepper' => WidgetComponentEnum::QuotePathStepper,
        'accreditation-insurance-strips' => WidgetComponentEnum::AccreditationInsuranceStrips,
        'review-proof-wall' => WidgetComponentEnum::ReviewProofWall,
        'pricing-guide-table' => WidgetComponentEnum::PricingGuideTable,
        'team-on-the-road-cards' => WidgetComponentEnum::TeamOnTheRoadCards,
    ];

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
     * The real, per-surface section copy this demo seeds, keyed by
     * `surface`, in the order it renders as bespoke widgets (via
     * {@see bespokeSectionsForSurface()}).
     *
     * @return list<array<string, mixed>>
     */
    public function sectionCopy(string $surface): array
    {
        $themeKey = 'call-out';
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return match ($surface) {
            'homepage' => [
                $this->availabilitySection(
                    state: 'open',
                    phoneNumber: self::PHONE_NUMBER,
                ),
                $this->serviceAreaSection(),
                $this->beforeAfterSection(
                    heading: 'See the difference we make',
                    summary: 'Drag the slider to compare real jobs before and after our technicians finished.',
                    media: $media,
                ),
                $this->quotePathSection(
                    heading: 'How a quote works',
                    summary: 'Three steps from your first call to a technician on site.',
                ),
                $this->accreditationSection(),
                $this->reviewSection(
                    heading: 'What local customers say',
                    summary: 'Real reviews from jobs completed across our service area.',
                    variant: 'featured',
                ),
                $this->teamSection(
                    heading: 'Meet the team on the road',
                    summary: 'The licensed technicians who show up at your door.',
                ),
            ],
            'directory' => [
                $this->serviceAreaSection(variant: 'postcode'),
                $this->pricingSection(
                    heading: 'Guide prices by job',
                    summary: 'Typical prices for our most requested jobs across every area we cover.',
                ),
                $this->accreditationSection(variant: 'detailed'),
            ],
            'detail' => [
                $this->beforeAfterSection(
                    heading: 'Emergency burst pipe — Maple Street',
                    summary: 'A Tuesday-morning burst pipe under a kitchen sink, fixed and dried out the same day.',
                    media: $media,
                    variant: 'gallery',
                ),
                $this->teamSection(
                    heading: 'The technician on this job',
                    summary: 'Licensed, insured, and on site within 40 minutes of the call.',
                    variant: 'credentials',
                ),
                $this->reviewSection(
                    heading: 'What the customer said',
                    summary: 'Verified feedback from this job.',
                ),
            ],
            'contact' => [
                $this->availabilitySection(
                    state: 'after-hours',
                    phoneNumber: self::PHONE_NUMBER,
                    variant: 'compact',
                ),
                $this->quotePathSection(
                    heading: 'Request your quote',
                    summary: 'Tell us the job and we will call back with a price, usually within the hour.',
                    variant: 'horizontal',
                ),
                $this->accreditationSection(),
            ],
            'empty' => [
                $this->serviceAreaSection(
                    heading: 'No technicians free in that area right now',
                    summary: 'We could not find a free slot for that postcode today — here is where we currently cover.',
                ),
                $this->quotePathSection(
                    heading: 'Ask us to fit you in',
                    summary: 'Leave your details and the next available technician will call you back.',
                ),
            ],
            'not-found' => [
                $this->availabilitySection(
                    state: 'open',
                    phoneNumber: self::PHONE_NUMBER,
                    variant: 'compact',
                ),
            ],
            'cta' => [
                $this->quotePathSection(
                    heading: 'Get a same-day quote',
                    summary: 'Most customers get a firm price on the phone in under five minutes.',
                ),
                $this->reviewSection(
                    heading: 'Trusted by homeowners across the region',
                    summary: 'Over four hundred five-star reviews and counting.',
                    variant: 'featured',
                ),
            ],
            default => [],
        };
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Fast, licensed plumbing and electrical call-outs',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Plumbing and electrical call-outs you can trust',
                'Rapid Response Plumbing & Electrical sends licensed, insured technicians to homes and businesses across the region, same day, most days within the hour.',
            ),
            renderData: [
                'summary' => 'Rapid Response Plumbing & Electrical is the local call-out team for burst pipes, wiring faults, boiler breakdowns, and everything in between.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
            containers: $this->containers('homepage'),
            widgets: $this->widgets('homepage'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Service Areas',
            title: 'Service areas and guide prices — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Where we cover, and what it typically costs',
                'Browse the local areas Rapid Response Plumbing & Electrical covers and see guide prices for our most requested jobs.',
            ),
            renderData: [
                'summary' => 'A scannable index of the service areas and typical job prices for Rapid Response Plumbing & Electrical.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            layout: LayoutEnum::Results,
            containers: $this->containers('directory'),
            widgets: $this->widgets('directory'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Job Story',
            title: 'Emergency burst pipe, fixed same day — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A burst pipe under the kitchen sink, fixed before lunch',
                'A Maple Street homeowner called at 8am with water pooling under the sink. A Rapid Response technician was on site by 8:40am and the job was finished, tested, and dried out by noon.',
            ),
            renderData: [
                'summary' => 'How a Rapid Response Plumbing & Electrical technician fixed an emergency burst pipe on Maple Street the same morning it was reported.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('detail'),
            widgets: $this->widgets('detail'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Request A Quote',
            title: 'Request a quote — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request a quote',
                'Tell us about the job and a Rapid Response technician will call back with a price, usually within the hour.',
            ),
            renderData: [
                'summary' => 'Request a quote from Rapid Response Plumbing & Electrical and hear back from a real technician within the hour.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            layout: LayoutEnum::System,
            containers: $this->containers('contact'),
            widgets: $this->widgets('contact'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Availability',
            title: 'No technicians free in that area today — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No technicians free in that area right now',
                'A graceful empty state for a service-area search with no free technicians today.',
            ),
            renderData: [
                'summary' => 'No technicians are free in that area right now, but Rapid Response Plumbing & Electrical can still call you back.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('empty'),
            widgets: $this->widgets('empty'),
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
                'A not-found page that routes visitors back to Rapid Response Plumbing & Electrical\'s call-out line.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — call Rapid Response Plumbing & Electrical directly instead.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
            containers: $this->containers('not-found'),
            widgets: $this->widgets('not-found'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Get A Quote',
            title: 'Get a same-day quote — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Get a same-day quote',
                'A focused conversion page inviting homeowners to get a firm price from Rapid Response Plumbing & Electrical on the phone.',
            ),
            renderData: [
                'summary' => 'Get a same-day quote from Rapid Response Plumbing & Electrical — most customers get a firm price in under five minutes.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('cta'),
            widgets: $this->widgets('cta'),
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function containers(string $surface): array
    {
        $widgets = [
            ['widget_key' => 'page-content', 'occurrence' => 1],
        ];

        foreach ($this->bespokeSectionsForSurface($surface) as $bespokeSection) {
            $widgets[] = [
                'widget_key' => $bespokeSection['key'],
                'occurrence' => 1,
            ];
        }

        return [
            'main' => [
                'widgets' => $widgets,
            ],
        ];
    }

    /**
     * @return list<array{method: string, args?: array<array-key, mixed>}>
     */
    private function widgets(string $surface): array
    {
        $widgets = [
            ['method' => 'pageContentWidget'],
        ];

        foreach ($this->bespokeSectionsForSurface($surface) as $bespokeSection) {
            $widgets[] = [
                'method' => 'bespokeContentWidget',
                'args' => [
                    $bespokeSection['key'],
                    $bespokeSection['name'],
                    $bespokeSection['component'],
                    $bespokeSection['meta'],
                ],
            ];
        }

        return $widgets;
    }

    /**
     * @return list<array{key: string, name: string, component: string, meta: array<string, mixed>}>
     */
    private function bespokeSectionsForSurface(string $surface): array
    {
        $bespokeSections = [];
        $occurrenceByType = [];

        foreach ($this->sectionCopy($surface) as $section) {
            $type = $section['type'] ?? null;

            if (! is_string($type) || ! array_key_exists($type, self::BESPOKE_SECTION_WIDGETS)) {
                continue;
            }

            $occurrenceByType[$type] = ($occurrenceByType[$type] ?? 0) + 1;
            $occurrence = $occurrenceByType[$type];
            $component = self::BESPOKE_SECTION_WIDGETS[$type];

            $bespokeSections[] = [
                'key' => sprintf('call-out-%s-%s-%d', $type, $surface, $occurrence),
                'name' => sprintf('Call Out %s (%s)', ucfirst($type), $surface),
                'component' => $component->value,
                'meta' => $section,
            ];
        }

        return $bespokeSections;
    }

    /**
     * @return array<string, mixed>
     */
    private function availabilitySection(string $state, string $phoneNumber, string $variant = 'default'): array
    {
        return [
            'type' => 'emergency-availability-banner',
            'state' => $state,
            'phoneNumber' => $phoneNumber,
            'variant' => $variant,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceAreaSection(string $heading = 'Areas we cover', string $summary = 'Rapid Response Plumbing & Electrical sends a technician to every one of these local areas, usually the same day.', string $variant = 'default'): array
    {
        return [
            'type' => 'service-area-map-grid',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => $variant,
            'areas' => [
                ['name' => 'Riverside', 'postcode' => 'RS1'],
                ['name' => 'Maple Heights', 'postcode' => 'MH4'],
                ['name' => 'Old Town', 'postcode' => 'OT2'],
                ['name' => 'Harborview', 'postcode' => 'HV6'],
                ['name' => 'Fenwick Park', 'postcode' => 'FP3'],
                ['name' => 'Northgate', 'postcode' => 'NG9'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function beforeAfterSection(string $heading, string $summary, array $media, string $variant = 'default'): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['proof'])));

        return [
            'type' => 'before-after-comparison',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => $variant,
            'jobs' => [
                [
                    'title' => 'Under-sink pipe repair',
                    'summary' => 'Corroded copper pipe replaced and the cabinet dried out and resealed.',
                    'beforeImage' => $images[0] ?? null,
                    'afterImage' => $images[1] ?? ($images[0] ?? null),
                ],
                [
                    'title' => 'Consumer unit upgrade',
                    'summary' => 'Old fuse box replaced with a modern RCD consumer unit, fully certified.',
                    'beforeImage' => $images[2] ?? ($images[0] ?? null),
                    'afterImage' => $images[3] ?? ($images[1] ?? null),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quotePathSection(string $heading, string $summary, string $variant = 'default'): array
    {
        return [
            'type' => 'quote-path-stepper',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => $variant,
            'phoneNumber' => self::PHONE_NUMBER,
            'emailAddress' => self::EMAIL_ADDRESS,
            'quoteUrl' => '/theme-call-out-contact',
            'steps' => [
                ['title' => 'Call or send your details', 'summary' => 'Tell us what has happened and where you are — no callout charge for the quote itself.'],
                ['title' => 'Get a firm price', 'summary' => 'We quote a fixed price for the job before anyone leaves the yard.'],
                ['title' => 'Technician on site', 'summary' => 'A licensed technician arrives in your chosen window and the price never changes.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accreditationSection(string $variant = 'default'): array
    {
        return [
            'type' => 'accreditation-insurance-strips',
            'heading' => 'Licensed, insured, and accredited',
            'variant' => $variant,
            'badges' => [
                ['name' => 'Gas Safe Registered', 'summary' => 'Every gas job carried out by a Gas Safe registered engineer.'],
                ['name' => 'NICEIC Approved', 'summary' => 'Electrical work certified to NICEIC standards.'],
                ['name' => '£5m Public Liability', 'summary' => 'Fully insured on every job, every time.'],
                ['name' => 'Which? Trusted Trader', 'summary' => 'Vetted and endorsed by Which? Trusted Traders.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewSection(string $heading, string $summary, string $variant = 'default'): array
    {
        return [
            'type' => 'review-proof-wall',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => $variant,
            'reviews' => [
                ['author' => 'Priya N.', 'rating' => 5, 'quote' => 'Called at 7am about a burst pipe and someone was at my door before 8. Fixed, cleaned up, and gone within the hour.'],
                ['author' => 'Tom W.', 'rating' => 5, 'quote' => 'Rewired our consumer unit the same day we called. Tidy work and the price matched the quote exactly.'],
                ['author' => 'Grace L.', 'rating' => 5, 'quote' => 'Used them twice now for boiler issues. Same technician both times, which was a nice touch.'],
                ['author' => 'Marcus D.', 'rating' => 4, 'quote' => 'Quick and professional. Only reason it is not five stars is I had to wait until the next morning, but it was an evening call.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pricingSection(string $heading, string $summary, string $variant = 'default'): array
    {
        return [
            'type' => 'pricing-guide-table',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => $variant,
            'rows' => [
                ['job' => 'Emergency call-out (assessment)', 'price' => 'from £65', 'notes' => 'Waived if you go ahead with the job'],
                ['job' => 'Burst pipe repair', 'price' => 'from £120', 'notes' => 'Parts and drying quoted separately'],
                ['job' => 'Consumer unit upgrade', 'price' => 'from £480', 'notes' => 'Includes certification'],
                ['job' => 'Boiler service', 'price' => 'from £90', 'notes' => 'Same-day booking usually available'],
                ['job' => 'Tap or valve replacement', 'price' => 'from £75', 'notes' => 'Most common taps in stock on the van'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teamSection(string $heading, string $summary, string $variant = 'default'): array
    {
        return [
            'type' => 'team-on-the-road-cards',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => $variant,
            'members' => [
                ['name' => 'Daniel Okafor', 'role' => 'Lead Plumbing Technician', 'bio' => '14 years on the tools, Gas Safe registered since 2014.', 'credentials' => ['Gas Safe Registered', 'City & Guilds Level 3']],
                ['name' => 'Sam Whitfield', 'role' => 'Senior Electrician', 'bio' => 'NICEIC-approved and specialises in consumer unit upgrades.', 'credentials' => ['NICEIC Approved Contractor', '18th Edition Wiring Regulations']],
                ['name' => 'Aisha Bello', 'role' => 'Emergency Response Technician', 'bio' => 'Runs the after-hours call-out rota across the whole region.', 'credentials' => ['Gas Safe Registered', 'First Aid at Work']],
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
                ['label' => 'Service Areas', 'url' => '/theme-call-out-directory'],
                ['label' => 'Reviews', 'url' => '/#review-proof-wall'],
                ['label' => 'Pricing', 'url' => '/theme-call-out-directory'],
                ['label' => 'Our Team', 'url' => '/#team-on-the-road-cards'],
            ],
            'ctaLabel' => 'Get a quote',
            'ctaUrl' => '/theme-call-out-contact',
            'consultationUrl' => '/theme-call-out-contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Services',
                'title' => 'Services',
                'links' => [
                    ['label' => 'Emergency plumbing', 'url' => '/#emergency-availability-banner'],
                    ['label' => 'Electrical repairs', 'url' => '/theme-call-out-directory'],
                    ['label' => 'Guide prices', 'url' => '/theme-call-out-directory'],
                ],
            ],
            [
                'heading' => 'Trust',
                'title' => 'Trust',
                'links' => [
                    ['label' => 'Reviews', 'url' => '/#review-proof-wall'],
                    ['label' => 'Accreditations', 'url' => '/#accreditation-insurance-strips'],
                    ['label' => 'Our team', 'url' => '/#team-on-the-road-cards'],
                ],
            ],
            [
                'heading' => 'Company',
                'title' => 'Company',
                'links' => [
                    ['label' => 'Get a quote', 'url' => '/theme-call-out-contact'],
                    [
                        'label' => self::EMAIL_ADDRESS,
                        'url' => 'mailto:' . self::EMAIL_ADDRESS,
                    ],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'Licensed, insured plumbing and electrical call-outs across the region, same day, most days within the hour.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
