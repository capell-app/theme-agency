<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LocalServices\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Local Services theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (services / service-areas
 * / locality-proof / quote-form / case-studies / reviews-testimonials /
 * resources / contact) alongside the standard hero/proof/cta — giving every
 * surface a full local-business site rather than the shared five-section
 * skeleton. Copy, palette intent, and section keys mirror the theme's
 * screenshot renderer verbatim.
 */
final class LocalServicesDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Meridian Local Services';

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
            title: self::BRAND . ' — Same-day local trades & service calls',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Local service you can book the same day',
                'Meridian Local Services connects households and businesses with vetted local tradespeople — fast quotes, clear pricing, and proof of work in your area.',
            ),
            renderData: [
                'summary' => 'A conversion-first homepage for local trades and service businesses, built around service-area coverage, click-to-call trust, and quote requests.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Local Services',
                        'heading' => 'Local service you can book the same day',
                        'summary' => 'A conversion-first home base for local trades and service businesses, built around service-area coverage, click-to-call trust, and quote requests. Tell us the job and get a fixed quote within the hour.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'primary'],
                            ['label' => 'Check your area', 'url' => '#service-areas', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Meridian engineer arriving on a same-day call-out',
                    ],
                    $this->servicesSection(
                        heading: 'Bookable service calls built to be compared',
                        summary: 'Every trade we cover, with response times and from-prices up front so you can decide before you pick up the phone.',
                    ),
                    $this->serviceAreasSection(
                        heading: 'Proof that we cover your area',
                        summary: 'Postcode and area cards make coverage obvious before you are ever asked for an enquiry.',
                    ),
                    $this->localityProofSection(),
                    $this->quoteFormSection(),
                    $this->caseStudiesSection(),
                    $this->reviewsSection(),
                    $this->resourcesSection(
                        heading: 'Service advice that keeps the quote path visible',
                        summary: 'Plain-English answers to the questions homeowners ask before booking a trade.',
                    ),
                    $this->proofSection(
                        heading: 'Trusted across the region',
                        summary: 'The numbers behind every same-day booking.',
                    ),
                    $this->ctaSection(
                        heading: 'Got a job that cannot wait?',
                        summary: 'Send the details now and a local engineer will be in touch within the hour with a fixed quote.',
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
            name: self::BRAND . ' Services',
            title: 'Services & service areas — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every trade we cover, area by area',
                'Browse bookable service calls and confirm coverage for your postcode before requesting a quote.',
            ),
            renderData: [
                'summary' => 'Browse bookable service calls and confirm coverage for your postcode before requesting a quote.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Our services',
                        'heading' => 'Every trade we cover, area by area',
                        'summary' => 'Bookable service calls carry urgency markers, from-pricing, and a quote action without feeling like an agency services grid. Filter by trade or check your postcode.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'primary'],
                            ['label' => 'Check your area', 'url' => '#service-areas', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Meridian service vans across the coverage region',
                    ],
                    $this->servicesSection(
                        heading: 'Bookable service calls built to be compared',
                        summary: 'Service cards carry urgency markers, pricing cues, and quote actions without feeling like an agency services grid.',
                    ),
                    $this->serviceAreasSection(
                        heading: 'Proof that we cover your area',
                        summary: 'Postcode and area cards make coverage obvious before the visitor is ever asked for an enquiry.',
                    ),
                    $this->resourcesSection(
                        heading: 'Service advice that keeps the quote path visible',
                        summary: 'Resource cards answer common service questions using static content or the optional blog feed when available.',
                    ),
                    $this->ctaSection(
                        heading: 'Found the service you need?',
                        summary: 'Tell us the job and your postcode and we will confirm coverage and a fixed quote within the hour.',
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
            name: self::BRAND . ' Job Proof',
            title: 'Riverside boiler swap — Job proof — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Riverside boiler swap, finished in a day',
                'A completed-job walkthrough showing the before, the work, and the result for a same-day emergency boiler replacement.',
            ),
            renderData: [
                'summary' => 'Completed-job proof visitors can trust — the before, the work, and the result, with the metrics that matter.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Job proof',
                        'heading' => 'Riverside boiler swap, finished in a day',
                        'summary' => 'A family left without heating on a Friday. We diagnosed, sourced, and replaced the boiler the same afternoon — here is exactly how it went.',
                        'actions' => [
                            ['label' => 'See all job proof', 'url' => '#case-studies', 'style' => 'secondary'],
                            ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Newly installed boiler after a same-day swap',
                    ],
                    $this->caseStudiesSection(),
                    $this->beforeAfterSection(),
                    $this->proofSection(
                        heading: 'Why homeowners book us back',
                        summary: 'The proof behind every completed job.',
                    ),
                    $this->reviewsSection(),
                    $this->ctaSection(
                        heading: 'Want a result like this?',
                        summary: 'Send the details of your job and we will come back within the hour with a fixed quote and a date.',
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
            name: self::BRAND . ' Quote',
            title: 'Request a quote — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request a quote in one obvious path',
                'Tell us the job, your postcode, and the best time to call. We reply within the hour during working days.',
            ),
            renderData: [
                'summary' => 'Tell us the job and your postcode in one obvious path. We reply within the hour during working days — no call centres, no chasing.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get a quote',
                        'heading' => 'Request a quote in one obvious path',
                        'summary' => 'A direct quote form keeps conversion simple after you have found your service. Call 0800 555 0142 for same-day jobs, or send the details below.',
                        'actions' => [
                            ['label' => 'Call 0800 555 0142', 'url' => 'tel:08005550142', 'style' => 'primary'],
                            ['label' => 'Check your area', 'url' => '#service-areas', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Meridian coordinator taking a quote request',
                    ],
                    $this->quoteFormSection(),
                    $this->trustBadgesSection(),
                    $this->contactSection(),
                    $this->proofSection(
                        heading: 'What happens after you ask',
                        summary: 'How a quote request turns into a booked job.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Call the team on 0800 555 0142 and we will scope the job with you on the spot.',
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
            name: self::BRAND . ' No Coverage',
            title: 'No coverage there yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'We do not cover that postcode yet',
                'A graceful empty state for a service-area search that returns no matching coverage.',
            ),
            renderData: [
                'summary' => 'No coverage for that postcode yet — but we can still point you to a trusted local option.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Service areas',
                        'heading' => 'We do not cover that postcode yet',
                        'summary' => 'Your area is not on our books today. Check a nearby postcode, or leave your details and we will tell you the moment we expand.',
                        'actions' => [
                            ['label' => 'See all areas', 'url' => '#service-areas', 'style' => 'primary'],
                            ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'service-areas',
                        'heading' => 'No coverage to show for that search',
                        'summary' => 'When we add your postcode it will appear here, with the local team that covers it.',
                        'items' => [],
                    ],
                    $this->servicesSection(
                        heading: 'While you are here',
                        summary: 'The service calls we are booked for most across the region.',
                    ),
                    $this->ctaSection(
                        heading: 'Want us in your area?',
                        summary: 'Leave your postcode and the job you need — we prioritise new areas by demand.',
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
                'A not-found page that routes visitors back into the services, coverage, and quote paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the fast way back to a quote.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page has clocked off',
                        'summary' => 'The link is broken or the page has moved. Head back to our services and coverage, or request a quote in one step.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still need the job done?',
                        summary: 'Tell us what you were looking for and we will point you to the right service.',
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
            name: self::BRAND . ' Book Now',
            title: 'Book a local engineer — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Book a local engineer today',
                'A focused conversion page inviting same-day quote requests and call-outs.',
            ),
            renderData: [
                'summary' => 'Book a vetted local engineer today — fixed quotes, same-day call-outs, no call centres.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Book now',
                        'heading' => 'Book a local engineer today',
                        'summary' => 'Whether it is an emergency repair or a planned job, you get the same vetted local team, fixed pricing, and a reply within the hour.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'primary'],
                            ['label' => 'Call 0800 555 0142', 'url' => 'tel:08005550142', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Meridian engineer ready for a same-day call-out',
                    ],
                    $this->proofSection(
                        heading: 'Why homeowners choose Meridian',
                        summary: 'The numbers behind every booking.',
                    ),
                    $this->trustBadgesSection(),
                    $this->ctaSection(
                        heading: 'One quote away',
                        summary: 'Send the job over and we will come back within the hour with a fixed quote and the next available date.',
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
                [
                    'category' => 'Plumbing',
                    'title' => 'Emergency plumbing & leaks',
                    'summary' => 'Burst pipes, blocked drains, and failed taps fixed same day. From £85 call-out, fixed before we start.',
                ],
                [
                    'category' => 'Heating',
                    'title' => 'Boiler repair & replacement',
                    'summary' => 'Gas Safe engineers diagnose, repair, or swap your boiler — most replacements completed within a day. From £120.',
                ],
                [
                    'category' => 'Electrical',
                    'title' => 'Electrical call-outs & rewires',
                    'summary' => 'NICEIC-registered electricians for faults, consumer units, and full rewires, with certificates on completion. From £95.',
                ],
                [
                    'category' => 'Property care',
                    'title' => 'Handyman & small jobs',
                    'summary' => 'The shorter list — shelving, fixtures, repairs, and odd jobs handled in a single visit. From £55 per hour.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceAreasSection(string $heading, string $summary): array
    {
        return [
            'type' => 'service-areas',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['label' => 'Riverside', 'postcode' => 'BS1', 'url' => '#quote-form'],
                ['label' => 'Harbourside', 'postcode' => 'BS8', 'url' => '#quote-form'],
                ['label' => 'Northgate', 'postcode' => 'BS7', 'url' => '#quote-form'],
                ['label' => 'Eastfield', 'postcode' => 'BS5', 'url' => '#quote-form'],
                ['label' => 'Kingswood', 'postcode' => 'BS15', 'url' => '#quote-form'],
                ['label' => 'Portishead', 'postcode' => 'BS20', 'url' => '#quote-form'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function localityProofSection(): array
    {
        return [
            'type' => 'locality-proof',
            'heading' => 'Booked across the region this week',
            'summary' => 'Real coverage, not a national franchise badge — here is where our engineers have been working.',
            'items' => [
                ['type' => 'Jobs completed', 'title' => '1,200+ local jobs', 'summary' => 'Completed across the BS postcodes in the last twelve months.'],
                ['type' => 'Response time', 'title' => 'Under 90 minutes', 'summary' => 'Average arrival time for same-day emergency call-outs in our core areas.'],
                ['type' => 'Local engineers', 'title' => '28 vetted trades', 'summary' => 'Every engineer is DBS-checked, insured, and lives within the area they cover.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quoteFormSection(): array
    {
        return [
            'type' => 'quote-form',
            'heading' => 'Request a quote in one obvious path',
            'summary' => 'A direct quote form keeps conversion simple after service discovery, ready for optional form-builder state. Tell us the job and your postcode.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseStudiesSection(): array
    {
        return [
            'type' => 'case-studies',
            'heading' => 'Completed-job proof visitors can trust',
            'summary' => 'Before/after-style job cards show practical proof without overlapping a studio case-study workflow.',
            'items' => [
                ['metric' => 'Same day', 'title' => 'Riverside boiler swap', 'summary' => 'Diagnosed and replaced a failed boiler before the weekend, heating restored by 5pm.'],
                ['metric' => '2 hours', 'title' => 'Harbourside burst pipe', 'summary' => 'Stopped a kitchen flood, fixed the joint, and dried the run-off the same morning.'],
                ['metric' => '1 visit', 'title' => 'Northgate fuse board', 'summary' => 'Replaced an unsafe consumer unit and issued the certificate on completion.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function beforeAfterSection(): array
    {
        return [
            'type' => 'before-after-gallery',
            'heading' => 'See the work, before and after',
            'summary' => 'The same job from arrival to sign-off, so you know exactly what you are paying for.',
            'items' => [
                ['title' => 'Riverside boiler swap', 'location' => 'BS1 Riverside', 'service' => 'Heating', 'summary' => 'Old back-boiler removed, modern combi fitted and commissioned in a day.'],
                ['title' => 'Harbourside bathroom leak', 'location' => 'BS8 Harbourside', 'service' => 'Plumbing', 'summary' => 'Hidden leak traced behind tiling, repaired and made good without a full strip-out.'],
                ['title' => 'Northgate consumer unit', 'location' => 'BS7 Northgate', 'service' => 'Electrical', 'summary' => 'Outdated fuse box replaced with a modern, certified consumer unit.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewsSection(): array
    {
        return [
            'type' => 'reviews-testimonials',
            'heading' => 'What local customers say',
            'summary' => 'Verified reviews from households and businesses across our coverage areas.',
            'items' => [
                ['rating' => 5, 'quote' => 'Phoned at 8am with no hot water, sorted by lunchtime. Honest price, no mess left behind.', 'name' => 'Sarah Whitlock', 'title' => 'Homeowner, BS1'],
                ['rating' => 5, 'quote' => 'They quoted a fixed price and stuck to it even when the job got fiddly. Rare these days.', 'name' => 'Daniel Osei', 'title' => 'Landlord, BS8'],
                ['rating' => 5, 'quote' => 'Used them for three call-outs across our cafés now. Reliable, tidy, and quick to respond.', 'name' => 'Priya Anand', 'title' => 'Café owner, BS5'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resourcesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'resources',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'How much does a boiler replacement cost?', 'summary' => 'A plain breakdown of what drives the price and when a repair beats a swap.', 'url' => '#resources'],
                ['title' => 'What to do first when a pipe bursts', 'summary' => 'The three steps that limit the damage before an engineer arrives.', 'url' => '#resources'],
                ['title' => 'Signs your fuse board needs replacing', 'summary' => 'When an old consumer unit stops being a nuisance and starts being a risk.', 'url' => '#resources'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trustBadgesSection(): array
    {
        return [
            'type' => 'trust-badges',
            'heading' => 'Accredited and insured',
            'summary' => 'The registrations that back every job we take on.',
            'items' => [
                ['title' => 'Gas Safe registered', 'summary' => 'Every gas and heating engineer is on the Gas Safe Register.', 'issuer' => 'Gas Safe', 'reference' => 'No. 512-880'],
                ['title' => 'NICEIC approved', 'summary' => 'Electrical work certified to current wiring regulations.', 'issuer' => 'NICEIC', 'reference' => 'Approved Contractor'],
                ['title' => '£5m public liability', 'summary' => 'Fully insured for work in your home or premises.', 'issuer' => 'Hiscox', 'reference' => 'Policy current'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contactSection(): array
    {
        return [
            'type' => 'contact',
            'heading' => 'Talk to the local team',
            'summary' => 'No call centres — you reach the people who do the work.',
            'phone' => '0800 555 0142',
            'email' => 'quotes@meridian-local.example',
            'address' => 'Unit 4, Riverside Works, Bristol, BS1 6QR',
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
                ['metric' => '90 min', 'name' => 'Average response', 'summary' => 'Typical arrival time for same-day emergency call-outs.'],
                ['metric' => '4.9/5', 'name' => 'Customer rating', 'summary' => 'Across 1,200+ verified reviews from local jobs.'],
                ['metric' => 'Fixed', 'name' => 'Quoted pricing', 'summary' => 'The price you are quoted is the price you pay — agreed before we start.'],
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
                ['label' => 'Request a quote', 'url' => '#quote-form', 'style' => 'primary'],
                ['label' => 'Check your area', 'url' => '#service-areas', 'style' => 'secondary'],
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
                ['label' => 'Service areas', 'url' => '#service-areas'],
                ['label' => 'Job proof', 'url' => '#case-studies'],
                ['label' => 'Reviews', 'url' => '#reviews-testimonials'],
                ['label' => 'Quote', 'url' => '#quote-form'],
            ],
            'ctaLabel' => 'Request a quote',
            'ctaUrl' => '#quote-form',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Vetted local trades for households and businesses. Bristol and surrounding postcodes, same-day where we can.',
            'columns' => [
                [
                    'heading' => 'Services',
                    'links' => [
                        ['label' => 'Plumbing', 'url' => '#services'],
                        ['label' => 'Heating & boilers', 'url' => '#services'],
                        ['label' => 'Electrical', 'url' => '#services'],
                        ['label' => 'Handyman', 'url' => '#services'],
                    ],
                ],
                [
                    'heading' => 'Coverage',
                    'links' => [
                        ['label' => 'Service areas', 'url' => '#service-areas'],
                        ['label' => 'Job proof', 'url' => '#case-studies'],
                        ['label' => 'Reviews', 'url' => '#reviews-testimonials'],
                        ['label' => 'Resources', 'url' => '#resources'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Request a quote', 'url' => '#quote-form'],
                        ['label' => '0800 555 0142', 'url' => 'tel:08005550142'],
                        ['label' => 'quotes@meridian-local.example', 'url' => 'mailto:quotes@meridian-local.example'],
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
