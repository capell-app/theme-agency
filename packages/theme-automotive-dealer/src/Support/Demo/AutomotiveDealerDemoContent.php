<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AutomotiveDealer\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Automotive Dealer theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (inventory-grid /
 * vehicle-detail / finance-options / part-exchange / test-drive-panel)
 * alongside the standard hero/proof/cta — giving every surface a full,
 * individual dealership site rather than the shared five-section skeleton.
 */
final class AutomotiveDealerDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Velston Motors';

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
            title: self::BRAND . ' — Prestige & Performance Cars',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Prestige and performance cars, ready to drive away',
                'Velston Motors is an independent dealership pairing a hand-picked forecourt with finance, part exchange, and test-drive-led buying.',
            ),
            renderData: [
                'summary' => 'Velston Motors is an independent prestige dealership. Browse approved stock, arrange finance, value your part exchange, and book a test drive.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Prestige & performance dealership',
                        'heading' => 'Prestige and performance cars, ready to drive away',
                        'summary' => 'A hand-picked forecourt of approved-used cars, each one HPI-checked, fully serviced, and prepared to showroom standard before it reaches you.',
                        'actions' => [
                            ['label' => 'Browse inventory', 'url' => '#inventory', 'style' => 'primary'],
                            ['label' => 'Book a test drive', 'url' => '#test-drive', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Velston Motors showroom forecourt',
                    ],
                    $this->inventoryGridSection(
                        heading: 'This week on the forecourt',
                        summary: 'A rotating selection from our approved-used stock, ready to view and drive away.',
                    ),
                    $this->financeOptionsSection(
                        heading: 'Finance that fits the car and the budget',
                        summary: 'Representative plans from our lending panel. Settle your figures before you visit, with no impact on your credit score to check.',
                    ),
                    $this->partExchangeSection(
                        heading: 'Trade in, drive out',
                        summary: 'Bring your current car as part of the deal. We value honestly and settle outstanding finance directly.',
                    ),
                    $this->testDrivePanelSection(
                        heading: 'Book a test drive on your terms',
                        summary: 'Showroom, home, or out on the open road — choose the drive that tells you what you need to know.',
                    ),
                    $this->featuresSection(
                        heading: 'Why buyers choose Velston',
                        summary: 'The standards behind every car that leaves our forecourt.',
                    ),
                    $this->proofSection(
                        heading: 'A dealership buyers come back to',
                        summary: 'The numbers behind two decades on the same forecourt.',
                    ),
                    $this->ctaSection(
                        heading: 'Found the one? Reserve it today',
                        summary: 'A refundable deposit holds any car for seven days while you arrange finance or a test drive.',
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
            name: self::BRAND . ' Inventory',
            title: 'Approved-used inventory — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Approved-used inventory',
                'The full Velston Motors forecourt — every car HPI-checked, serviced, and prepared before it lists.',
            ),
            renderData: [
                'summary' => 'Browse the full approved-used forecourt by make, body style, and budget. Every car is HPI-checked and workshop-prepared.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Inventory',
                        'heading' => 'A showroom directory built to be scanned',
                        'summary' => 'Filter by make, body style, fuel, and monthly budget. Each card carries mileage, year, and finance from — so the right car finds you fast.',
                        'actions' => [
                            ['label' => 'Book a test drive', 'url' => '#test-drive', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Velston Motors approved-used stock',
                    ],
                    $this->inventoryGridSection(
                        heading: 'Approved-used stock',
                        summary: 'A structured forecourt of HPI-checked, workshop-prepared cars, newest arrivals first.',
                    ),
                    $this->contentListingSection(
                        heading: 'Just arrived & coming soon',
                        summary: 'Cars in preparation now — reserve ahead of them hitting the forecourt.',
                    ),
                    $this->ctaSection(
                        heading: 'Cannot see the spec you want?',
                        summary: 'Tell us the make, budget, and timeline and our buyers will source it through the trade for you.',
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
            name: self::BRAND . ' Vehicle',
            title: 'BMW M340i xDrive — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'BMW M340i xDrive Touring',
                'A single, fully-specified approved-used profile: history, specification, finance, and the path to reserve.',
            ),
            renderData: [
                'summary' => '2022 BMW M340i xDrive Touring — 14,200 miles, full BMW history, one owner. View the full specification and reserve.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Approved-used',
                        'heading' => 'BMW M340i xDrive Touring — a vehicle profile that reads with confidence',
                        'summary' => '2022 (72-plate) in Portimao Blue, 14,200 miles, full BMW main-dealer history and one owner from new. Finance from £589 per month.',
                        'actions' => [
                            ['label' => 'Reserve this car', 'url' => '#reserve', 'style' => 'primary'],
                            ['label' => 'Book a test drive', 'url' => '#test-drive', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'BMW M340i xDrive Touring in Portimao Blue',
                    ],
                    $this->vehicleDetailSection(),
                    $this->financeOptionsSection(
                        heading: 'Finance this BMW',
                        summary: 'Representative figures for this car. Adjust the deposit and term to settle a monthly payment that works.',
                    ),
                    $this->testDrivePanelSection(
                        heading: 'Drive the M340i before you decide',
                        summary: 'Book a solo or accompanied drive from the showroom, or have us bring it to your door.',
                    ),
                    $this->proofSection(
                        heading: 'Bought with confidence',
                        summary: 'Every approved-used car carries the same Velston standard.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to make it yours?',
                        summary: 'A £199 refundable deposit holds this car for seven days while you finalise finance or a test drive.',
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
            title: 'Visit the showroom — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Visit the showroom',
                'Find us, call the team, or book a test drive — one confident path to the dealership.',
            ),
            renderData: [
                'summary' => 'Velston Motors, Pittville Road, Cheltenham. Open seven days. Call the sales team or book a test drive online.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Visit & contact',
                        'heading' => 'Reach the dealership through one confident path',
                        'summary' => 'Velston Motors, Pittville Road, Cheltenham GL52 2HA. Sales 01242 555 070, open Monday to Saturday 9–6 and Sunday 10–4. We reply to every enquiry the same day.',
                        'actions' => [
                            ['label' => 'Call the sales team', 'url' => 'tel:+441242555070', 'style' => 'primary'],
                            ['label' => 'Book a test drive', 'url' => '#test-drive', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Velston Motors Cheltenham showroom',
                    ],
                    $this->testDrivePanelSection(
                        heading: 'Book a test drive',
                        summary: 'Choose a car, a date, and where you would like to drive — we confirm within the hour during opening times.',
                    ),
                    $this->featuresSection(
                        heading: 'What to expect when you visit',
                        summary: 'No pressure, no hidden admin fees, and a coffee while we bring the car round.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through first?',
                        summary: 'Call the team or drop us a message and we will line up the right cars before you arrive.',
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
            name: self::BRAND . ' No Matches',
            title: 'No cars match that search — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No cars match that search',
                'A graceful empty state for a filtered forecourt with no matching stock right now.',
            ),
            renderData: [
                'summary' => 'No cars match that search right now — clear your filters, or let our buyers source the exact spec for you.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Inventory',
                        'heading' => 'No cars match that search — yet',
                        'summary' => 'Nothing on the forecourt fits those filters today. Widen the budget or body style, or tell us the spec and we will source it through the trade.',
                        'actions' => [
                            ['label' => 'Clear filters', 'url' => '#inventory', 'style' => 'primary'],
                            ['label' => 'Source it for me', 'url' => '#test-drive', 'style' => 'secondary'],
                        ],
                    ],
                    $this->contentListingSection(
                        heading: 'Arriving on the forecourt soon',
                        summary: 'Cars in preparation now — one of these may be the match you are after.',
                    ),
                    $this->financeOptionsSection(
                        heading: 'Set your budget while you wait',
                        summary: 'Settle a monthly figure now so you can move fast when the right car lands.',
                    ),
                    $this->ctaSection(
                        heading: 'Let us find it for you',
                        summary: 'Send the make, budget, and timeline and our buyers will go to the trade on your behalf.',
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
                'That page has left the forecourt',
                'A not-found page that routes visitors back into the inventory and test-drive paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or the car has sold — here is the way back to the forecourt.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page has left the forecourt',
                        'summary' => 'The link is broken or the car has already sold. Head back to the inventory, or book a test drive with the team.',
                        'actions' => [
                            ['label' => 'Back to inventory', 'url' => '#inventory', 'style' => 'primary'],
                            ['label' => 'Book a test drive', 'url' => '#test-drive', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Looking for a particular car?',
                        summary: 'Tell us the make and budget and our buyers will find the right one for you.',
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
            name: self::BRAND . ' Reserve',
            title: 'Reserve your next car — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a reserved car',
                'A focused conversion page that takes a buyer from interest to a held vehicle.',
            ),
            renderData: [
                'summary' => 'Reserve any car for seven days with a refundable deposit while you arrange finance and a test drive.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reserve your car',
                        'heading' => 'Turn intent into a reserved vehicle',
                        'summary' => 'A refundable £199 deposit holds your car for seven days. Sort finance, line up a test drive, and complete in the showroom or have it delivered.',
                        'actions' => [
                            ['label' => 'Reserve a car', 'url' => '#reserve', 'style' => 'primary'],
                            ['label' => 'Call the sales team', 'url' => 'tel:+441242555070', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Velston Motors handover',
                    ],
                    $this->financeOptionsSection(
                        heading: 'Settle the finance first',
                        summary: 'Lock your monthly figure with a soft-search quote that leaves your credit score untouched.',
                    ),
                    $this->proofSection(
                        heading: 'Why buyers reserve with Velston',
                        summary: 'The standard behind every handover.',
                    ),
                    $this->ctaSection(
                        heading: 'Your next car is one deposit away',
                        summary: 'Reserve online in minutes and we will hold the car and call you to confirm the next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function inventoryGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'inventory-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'BMW M340i xDrive Touring', 'summary' => '2022 · 14,200 miles · Portimao Blue · full BMW history · £46,995 or £589/mo'],
                ['title' => 'Audi RS5 Sportback', 'summary' => '2021 · 22,800 miles · Nardo Grey · Carbon Black pack · £52,450 or £649/mo'],
                ['title' => 'Porsche Macan GTS', 'summary' => '2022 · 18,500 miles · Carmine Red · air suspension · £58,900 or £729/mo'],
                ['title' => 'Mercedes-AMG C43 Estate', 'summary' => '2021 · 27,100 miles · Obsidian Black · Premium Plus · £43,995 or £549/mo'],
                ['title' => 'Range Rover Velar D300', 'summary' => '2022 · 19,400 miles · Eiger Grey · 22" wheels · £48,750 or £609/mo'],
                ['title' => 'Volkswagen Golf R', 'summary' => '2023 · 9,800 miles · Lapiz Blue · Akrapovic exhaust · £39,495 or £489/mo'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function financeOptionsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'finance-options',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Personal Contract Purchase (PCP)', 'summary' => 'Lower monthly payments with a guaranteed future value. Change, keep, or hand the car back at the end of the term.'],
                ['title' => 'Hire Purchase (HP)', 'summary' => 'Fixed payments across two to five years and the car is yours outright at the end — no balloon, no mileage limits.'],
                ['title' => 'Low-rate APR offers', 'summary' => 'Representative 8.9% APR on selected approved-used stock, with deposit contributions on featured cars.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function partExchangeSection(string $heading, string $summary): array
    {
        return [
            'type' => 'part-exchange',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Honest valuation in minutes', 'summary' => 'Send your registration and mileage for a guide figure, confirmed when we see the car.'],
                ['title' => 'We settle outstanding finance', 'summary' => 'Still paying off your current car? We clear the settlement directly and offset the rest against your new one.'],
                ['title' => 'Drive away the same day', 'summary' => 'Hand over the keys, transfer the value to your purchase, and complete the deal in one visit.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function testDrivePanelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'test-drive-panel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Showroom drive', 'summary' => 'A guided route from our Pittville Road forecourt, accompanied or solo with a copy of your licence.'],
                ['title' => 'Home test drive', 'summary' => 'We bring the car to your door within 25 miles of Cheltenham so you can drive it on roads you know.'],
                ['title' => 'Extended overnight drive', 'summary' => 'Live with the car for 24 hours on selected stock to be sure it fits your life before you commit.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function vehicleDetailSection(): array
    {
        return [
            'type' => 'vehicle-detail',
            'heading' => 'BMW M340i xDrive Touring — full specification',
            'summary' => '3.0-litre turbocharged straight-six, 374bhp, 0–62 in 4.5 seconds, xDrive all-wheel drive and an eight-speed Steptronic gearbox.',
            'items' => [
                ['title' => 'History & ownership', 'summary' => 'One owner from new, full BMW main-dealer service history, HPI clear, two keys and the original book pack.'],
                ['title' => 'Specification highlights', 'summary' => 'M Sport Pro pack, Harman Kardon audio, panoramic roof, heated M Sport seats, adaptive LED headlights.'],
                ['title' => 'Preparation & warranty', 'summary' => 'Workshop-serviced, four matching Michelin tyres, fresh MOT, and a 12-month approved-used warranty included.'],
                ['title' => 'Running costs', 'summary' => '38.7 mpg combined, £190 annual road tax, insurance group 41, and a 60-litre tank for genuine touring range.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Every car HPI-checked', 'summary' => 'No write-offs, no outstanding finance, no mileage discrepancies — verified before a car ever lists.'],
                ['title' => 'Workshop-prepared', 'summary' => 'A full service, fresh MOT, and a multi-point inspection on every car before handover.'],
                ['title' => '12-month warranty', 'summary' => 'Approved-used cover as standard, extendable to three years, with UK-wide breakdown assistance.'],
                ['title' => 'No hidden admin fees', 'summary' => 'The price you see is the price you pay — preparation, valeting, and handover all included.'],
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
                ['name' => '4.9 / 5 from 1,200 reviews', 'quote' => 'Two decades of buyers rating us on Google and Auto Trader.'],
                ['name' => '120-point inspection', 'quote' => 'Every approved-used car passes the same checklist before it lists.'],
                ['name' => 'Same-day drive-away', 'quote' => 'Finance, part exchange, and handover wrapped up in a single visit.'],
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
                ['title' => 'Audi S4 Avant TDI', 'summary' => 'In preparation · 2021 · Daytona Grey · expected on the forecourt next week.'],
                ['title' => 'Jaguar F-Pace P400e', 'summary' => 'In preparation · 2022 · plug-in hybrid · reserve ahead of listing.'],
                ['title' => 'Mini John Cooper Works', 'summary' => 'Coming soon · 2023 · Rebel Green · workshop check booked in.'],
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
                ['label' => 'Reserve a car', 'url' => '#reserve', 'style' => 'primary'],
                ['label' => 'Book a test drive', 'url' => '#test-drive', 'style' => 'secondary'],
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
                ['label' => 'Inventory', 'url' => '#inventory'],
                ['label' => 'Finance', 'url' => '#finance'],
                ['label' => 'Part exchange', 'url' => '#part-exchange'],
                ['label' => 'Test drive', 'url' => '#test-drive'],
                ['label' => 'Visit us', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Book a test drive',
            'ctaUrl' => '#test-drive',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An independent prestige and performance dealership on Pittville Road, Cheltenham. Approved-used cars since 2004.',
            'columns' => [
                [
                    'heading' => 'Buy',
                    'links' => [
                        ['label' => 'Inventory', 'url' => '#inventory'],
                        ['label' => 'Reserve a car', 'url' => '#reserve'],
                        ['label' => 'Finance', 'url' => '#finance'],
                        ['label' => 'Part exchange', 'url' => '#part-exchange'],
                    ],
                ],
                [
                    'heading' => 'Visit',
                    'links' => [
                        ['label' => 'Book a test drive', 'url' => '#test-drive'],
                        ['label' => 'Opening hours', 'url' => '#contact'],
                        ['label' => 'Find the showroom', 'url' => '#contact'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Call 01242 555 070', 'url' => 'tel:+441242555070'],
                        ['label' => 'sales@velstonmotors.example', 'url' => 'mailto:sales@velstonmotors.example'],
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
