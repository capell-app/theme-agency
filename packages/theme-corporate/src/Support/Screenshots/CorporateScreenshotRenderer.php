<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Corporate\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CorporateScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-corporate::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CorporateScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-corporate::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f172a',
                accentColor: '#0284c7',
                neutralColor: '#334155',
                headingFont: 'inter',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'standard',
                layoutPresentation: 'structured',
                mediaTreatment: 'natural',
                radius: 'sm',
                surfaceColor: '#f7f9fb',
                foregroundColor: '#191c1e',
                headingScale: 'compact',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'corporate',
        ])->render();

        return view('capell-theme-corporate::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CorporateScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'frontend-page-rendered-with-corporate-theme', 'corporate-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('features'),
                $this->section('proof'),
                $this->section('content-listing'),
                $this->section('investor-relations'),
                $this->section('careers'),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-services-layout' => [
                $this->navigation(),
                $this->section('features', [
                    'heading' => 'Services that read as boardroom-ready',
                    'summary' => 'Structured service blocks keep the organisation authoritative and legible without the theme owning service records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-governance-layout' => [
                $this->navigation(),
                $this->section('investor-relations', [
                    'heading' => 'Governance material published with restraint',
                    'summary' => 'Formal decision blocks keep board papers structured without the theme pretending to be an enterprise workflow.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-resources-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Formal resources built to be scanned',
                    'summary' => 'A structured resource listing stays restrained and trust-led without becoming a research archive.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-article-layout' => [
                $this->navigation(),
                $this->section('proof', [
                    'heading' => 'Thought leadership with a restrained corporate rhythm',
                    'summary' => 'A single article view pairs proof and supporting material so readers engage with confidence.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-contact-layout' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Route enquiries through one confident path',
                    'summary' => 'A non-submitting contact CTA proves the enquiry journey feels like part of the corporate experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'corporate-locations-layout' => [
                $this->navigation(),
                $this->section('locations', [
                    'heading' => 'Locations and contact routes without local-services styling',
                    'summary' => 'Structured location blocks keep a formal organisation authoritative and legible.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-search-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Find corporate material without product-search styling',
                    'summary' => 'A structured results listing stays restrained and trust-led without becoming an editorial archive.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'corporate-investor-event-layout' => [
                $this->navigation(),
                $this->section('investor-relations', [
                    'heading' => 'Stakeholder events published with restraint',
                    'summary' => 'A non-submitting event view keeps stakeholder communication structured without overlapping SaaS webinars.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): CorporateScreenshotSection
    {
        return new CorporateScreenshotSection($sectionKey, [...$this->defaultDataFor($sectionKey), ...$data]);
    }

    /**
     * Representative sample data so every strict section view renders with
     * real-looking content and never iterates over null.
     *
     * @return array<string, mixed>
     */
    private function defaultDataFor(string $sectionKey): array
    {
        return match ($sectionKey) {
            'features' => [
                'heading' => 'An operating model built for accountability',
                'summary' => 'Each practice is structured so stakeholders can see how policy, risk, and delivery connect.',
                'features' => [
                    [
                        'type' => 'Advisory',
                        'title' => 'Corporate strategy and governance',
                        'description' => 'Board-level advisory that keeps strategy, oversight, and reporting aligned.',
                    ],
                    [
                        'type' => 'Assurance',
                        'title' => 'Risk and controls assurance',
                        'description' => 'Independent review of controls so leadership can act with confidence.',
                    ],
                    [
                        'type' => 'Delivery',
                        'title' => 'Programme delivery oversight',
                        'description' => 'Structured delivery governance for complex, multi-year mandates.',
                    ],
                ],
            ],
            'proof' => [
                'heading' => 'Outcomes stakeholders can stand behind',
                'summary' => 'Measured results and considered statements show the firm earning its mandate.',
                'items' => [
                    [
                        'name' => 'Meridian Holdings',
                        'metric' => '+18% return on capital',
                        'summary' => 'A governance review that restructured oversight across three divisions.',
                        'type' => 'Advisory mandate',
                    ],
                    [
                        'name' => 'Caldwell Infrastructure',
                        'quote' => 'Harwell & Crane brought clarity to a board that had lost its rhythm.',
                        'summary' => 'A controls programme that rebuilt assurance confidence.',
                        'type' => 'Assurance mandate',
                    ],
                    [
                        'name' => 'Ashford Capital',
                        'metric' => '£240m programme',
                        'summary' => 'Delivery oversight that kept a multi-year programme on mandate.',
                        'type' => 'Delivery mandate',
                    ],
                ],
            ],
            'content-listing' => [
                'heading' => 'Resources written for considered readers',
                'summary' => 'Structured material keeps governance, risk, and delivery thinking legible.',
                'items' => [
                    [
                        'type' => 'Briefing',
                        'title' => 'Board oversight in volatile markets',
                        'summary' => 'How effective boards keep oversight disciplined under pressure.',
                        'url' => '#resources',
                    ],
                    [
                        'type' => 'Report',
                        'title' => 'Annual governance outlook',
                        'summary' => 'The structural shifts shaping corporate accountability this year.',
                        'url' => '#resources',
                    ],
                    [
                        'type' => 'Note',
                        'title' => 'Assurance that earns trust',
                        'summary' => 'Designing controls review that leadership actually relies on.',
                        'url' => '#resources',
                    ],
                ],
            ],
            'investor-relations' => [
                'heading' => 'Investor material published with restraint',
                'summary' => 'Reports, calendar, and disclosures kept structured for stakeholders.',
                'items' => [
                    [
                        'period' => 'Q4',
                        'title' => 'Full-year results statement',
                        'summary' => 'Audited results with commentary on capital and outlook.',
                        'type' => 'Results',
                        'url' => '#governance',
                    ],
                    [
                        'period' => 'Q3',
                        'title' => 'Interim trading update',
                        'summary' => 'A measured update on performance against guidance.',
                        'type' => 'Update',
                        'url' => '#governance',
                    ],
                ],
                'events' => [
                    ['title' => 'Annual General Meeting', 'date' => '14 May 2026'],
                    ['title' => 'Half-year results briefing', 'date' => '3 September 2026'],
                    ['title' => 'Capital markets day', 'date' => '19 November 2026'],
                ],
                'documents' => [
                    ['title' => 'Annual report 2025', 'url' => '#governance'],
                    ['title' => 'Governance framework', 'url' => '#governance'],
                    ['title' => 'Remuneration policy', 'url' => '#governance'],
                ],
            ],
            'careers' => [
                'heading' => 'Careers within a disciplined practice',
                'summary' => 'Roles for people who value rigour, judgement, and accountability.',
                'benefits' => [
                    ['label' => 'Hybrid working'],
                    ['label' => 'Chartered support'],
                    ['label' => 'Mentored progression'],
                    ['label' => 'Profit share'],
                ],
                'items' => [
                    [
                        'team' => 'Advisory',
                        'title' => 'Senior governance consultant',
                        'summary' => 'Lead board-level advisory mandates across regulated sectors.',
                        'location' => 'London',
                        'type' => 'Full-time',
                    ],
                    [
                        'team' => 'Assurance',
                        'title' => 'Risk and controls manager',
                        'summary' => 'Own controls review programmes for major clients.',
                        'location' => 'Manchester',
                        'type' => 'Full-time',
                    ],
                ],
            ],
            'locations' => [
                'heading' => 'Offices and contact routes',
                'summary' => 'Reach the right team at the right location.',
                'items' => [
                    [
                        'type' => 'Head office',
                        'title' => 'London',
                        'summary' => 'Our registered office and advisory practice.',
                        'addressLines' => ['18 Cornhill', 'London', 'EC3V 3ND'],
                        'phone' => '+44 20 7946 0100',
                        'email' => 'london@harwellcrane.example',
                        'hours' => ['Monday to Friday', '08:30 to 18:00'],
                    ],
                    [
                        'type' => 'Regional office',
                        'title' => 'Manchester',
                        'summary' => 'Assurance and delivery teams for the North.',
                        'addressLines' => ['2 St Peters Square', 'Manchester', 'M2 3AA'],
                        'phone' => '+44 161 200 0100',
                        'email' => 'manchester@harwellcrane.example',
                        'hours' => ['Monday to Friday', '09:00 to 17:30'],
                    ],
                ],
            ],
            'footer' => [
                'columns' => [
                    [
                        'heading' => 'Practice',
                        'links' => [
                            ['label' => 'Services', 'url' => '#services'],
                            ['label' => 'Governance', 'url' => '#governance'],
                            ['label' => 'Assurance', 'url' => '#services'],
                        ],
                    ],
                    [
                        'heading' => 'Company',
                        'links' => [
                            ['label' => 'Resources', 'url' => '#resources'],
                            ['label' => 'Careers', 'url' => '#careers'],
                            ['label' => 'Locations', 'url' => '#locations'],
                        ],
                    ],
                    [
                        'heading' => 'Contact',
                        'links' => [
                            ['label' => 'Enquiries', 'url' => '#contact'],
                            ['label' => 'Investor relations', 'url' => '#governance'],
                        ],
                    ],
                ],
            ],
            'cta' => [
                'heading' => 'Begin a considered conversation',
                'summary' => 'Route your enquiry to the team best placed to help.',
                'actions' => [
                    ['label' => 'Contact the team', 'url' => '#contact'],
                    ['label' => 'View services', 'url' => '#services'],
                ],
            ],
            default => [],
        };
    }

    private function navigation(): CorporateScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Harwell & Crane',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Governance', 'url' => '#governance'],
                ['label' => 'Resources', 'url' => '#resources'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'consultationUrl' => '#contact',
        ]);
    }

    private function hero(): CorporateScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A corporate site stakeholders can stand behind',
            'eyebrow' => 'Corporate',
            'summary' => 'A trust-led corporate homepage for services, governance, resources, locations, and stakeholder-led journeys.',
            'actions' => [
                ['label' => 'Contact the team', 'url' => '#contact'],
                ['label' => 'View services', 'url' => '#services'],
            ],
        ]);
    }

    private function footer(): CorporateScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Harwell & Crane',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Governance', 'url' => '#governance'],
                ['label' => 'Resources', 'url' => '#resources'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'corporate-services-layout' => 'Theme Corporate services',
            'corporate-governance-layout' => 'Theme Corporate governance',
            'corporate-resources-layout' => 'Theme Corporate resources',
            'corporate-article-layout' => 'Theme Corporate article',
            'corporate-contact-layout' => 'Theme Corporate contact',
            'corporate-locations-layout' => 'Theme Corporate locations',
            'corporate-search-layout' => 'Theme Corporate search results',
            'corporate-investor-event-layout' => 'Theme Corporate investor event',
            default => 'Theme Corporate homepage',
        };
    }
}
