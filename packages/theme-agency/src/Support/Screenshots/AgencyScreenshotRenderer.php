<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Agency\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class AgencyScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-agency::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (AgencyScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-agency::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#be123c',
                accentColor: '#3b82f6',
                neutralColor: '#09090b',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'spacious',
                cardStyle: 'layered',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'lg',
                surfaceColor: '#09090b',
                foregroundColor: '#f8fafc',
                headingScale: 'expressive',
                cardDensity: 'spacious',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'agency',
        ])->render();

        return view('capell-theme-agency::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, AgencyScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'frontend-page-rendered-with-agency-theme', 'agency-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('services'),
                $this->section('project-showcase'),
                $this->section('case-study'),
                $this->section('proof'),
                $this->section('client-logos'),
                $this->section('team'),
                $this->section('cta'),
                $this->footer(),
            ],
            'agency-services-layout' => [
                $this->navigation(),
                $this->section('services', [
                    'heading' => 'Services built around campaign delivery',
                    'summary' => 'Focused studio offers keep the work centred on launches and creative output rather than broad consulting.',
                ]),
                $this->section('project-showcase'),
                $this->section('cta'),
                $this->footer(),
            ],
            'agency-portfolio-layout' => [
                $this->navigation(),
                $this->section('project-showcase', [
                    'heading' => 'A campaign work board built to be scanned',
                    'summary' => 'Recent campaign assets stay legible and bold without overlapping a premium case-file workflow.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'agency-case-study-layout' => [
                $this->navigation(),
                $this->section('case-study', [
                    'heading' => 'A launch recap that reads as delivery proof',
                    'summary' => 'A single campaign detail pairs results and creative so buyers can see impact at a glance.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'agency-insights-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Editorial that supports the campaign',
                    'summary' => 'Insights stay focused on creative thinking rather than becoming a knowledge-base archive.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'agency-event-landing-layout' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Promote a launch in one confident path',
                    'summary' => 'An event landing surface drives sign-ups for launches and webinars without a SaaS-specific layout.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'agency-lead-form-layout' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Turn campaign interest into a usable brief',
                    'summary' => 'A non-submitting lead surface proves the contact journey feels like part of the studio experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'agency-campaign-layout' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Agency\'s strongest lane: fast campaign pages',
                    'summary' => 'A conversion-focused campaign stack keeps launches direct, bold, and ready to ship.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'agency-search-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Find campaigns, briefs, and resources',
                    'summary' => 'A results surface keeps discovery fast without feeling like an archive search.',
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
    private function section(string $sectionKey, array $data = []): AgencyScreenshotSection
    {
        return new AgencyScreenshotSection($sectionKey, [...$this->defaultDataFor($sectionKey), ...$data]);
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
            'services' => [
                'heading' => 'Studio services built for the launch',
                'summary' => 'Field & Signal runs campaigns end to end, from the first brief through the moment it ships.',
                'services' => [
                    [
                        'discipline' => 'Strategy',
                        'title' => 'Campaign strategy',
                        'summary' => 'We shape the message, the audience, and the moment so the launch lands with force.',
                        'deliverables' => [
                            'Positioning and message map',
                            'Channel and timing plan',
                            'Launch narrative',
                        ],
                    ],
                    [
                        'discipline' => 'Creative',
                        'title' => 'Brand and creative',
                        'summary' => 'Bold visual systems and motion that make the work impossible to scroll past.',
                        'deliverables' => [
                            'Art direction',
                            'Motion and video',
                            'Social-first assets',
                        ],
                    ],
                    [
                        'discipline' => 'Production',
                        'title' => 'Launch production',
                        'summary' => 'We build the pages, ship the assets, and keep every channel in lockstep on launch day.',
                        'deliverables' => [
                            'Landing pages',
                            'Asset rollout',
                            'Launch-day command',
                        ],
                    ],
                ],
            ],
            'project-showcase' => [
                'heading' => 'Campaigns we shipped this season',
                'summary' => 'A filterable work board of recent launches, each built to be scanned in seconds.',
                'items' => [
                    [
                        'discipline' => 'Product launch',
                        'year' => '2026',
                        'url' => '#case-study',
                        'title' => 'Northwind goes to market',
                        'summary' => 'A three-week sprint that took a stealth product to a sold-out launch day.',
                        'stage' => 'Shipped',
                    ],
                    [
                        'discipline' => 'Rebrand',
                        'year' => '2025',
                        'url' => '#case-study',
                        'title' => 'Brightline rebrand',
                        'summary' => 'A bold new identity rolled out across every channel in a single week.',
                        'stage' => 'Shipped',
                    ],
                    [
                        'discipline' => 'Event',
                        'year' => '2025',
                        'url' => '#case-study',
                        'title' => 'Meridian summit',
                        'summary' => 'A flagship event campaign that doubled registrations year on year.',
                        'stage' => 'Shipped',
                    ],
                ],
            ],
            'case-study' => [
                'heading' => 'Northwind: a launch that sold out in a week',
                'summary' => 'A single campaign recap that pairs the creative with the numbers it moved.',
                'client' => 'Northwind',
                'discipline' => 'Product launch',
                'year' => '2026',
                'challenge' => 'A stealth product needed a market entrance loud enough to own launch week.',
                'approach' => 'We built a motion-led campaign with a daily drumbeat across social, email, and a focused landing page.',
                'result' => 'Launch day sold out the first allocation and earned three times the projected sign-ups.',
                'metrics' => [
                    ['label' => 'Sign-ups', 'value' => '12,400'],
                    ['label' => 'Launch-day revenue', 'value' => '3x target'],
                    ['label' => 'Earned reach', 'value' => '1.8M'],
                ],
            ],
            'proof' => [
                'heading' => 'Operators who trust the studio',
                'summary' => 'Outcome numbers and client quotes that show the work earning its keep.',
                'items' => [
                    [
                        'name' => 'Northwind',
                        'metric' => '3x target',
                        'summary' => 'Launch week beat every projection we set going in.',
                    ],
                    [
                        'name' => 'Brightline',
                        'quote' => 'Field & Signal made our rebrand feel like an event, not a memo.',
                        'summary' => 'A full identity rollout shipped in a single week.',
                    ],
                    [
                        'name' => 'Meridian',
                        'metric' => '2x registrations',
                        'summary' => 'Our flagship summit doubled its audience year on year.',
                    ],
                ],
            ],
            'client-logos' => [
                'heading' => 'Brands we have launched',
                'summary' => 'A short roster of the teams we have shipped campaigns with.',
                'logos' => [
                    ['name' => 'Northwind'],
                    ['name' => 'Brightline'],
                    ['name' => 'Meridian'],
                    ['name' => 'Parallel'],
                    ['name' => 'Outset'],
                    ['name' => 'Cohort'],
                ],
            ],
            'team' => [
                'heading' => 'The people behind the launches',
                'summary' => 'A small, senior crew that owns the work from brief to launch day.',
                'people' => [
                    [
                        'role' => 'Creative director',
                        'name' => 'Rae Donovan',
                        'summary' => 'Sets the visual direction and keeps every campaign bold.',
                    ],
                    [
                        'role' => 'Strategy lead',
                        'name' => 'Marcus Vale',
                        'summary' => 'Shapes the message and the moment behind each launch.',
                    ],
                    [
                        'role' => 'Production lead',
                        'name' => 'Priya Anand',
                        'summary' => 'Ships the pages and assets and runs launch-day command.',
                    ],
                    [
                        'role' => 'Motion designer',
                        'name' => 'Theo Krause',
                        'summary' => 'Builds the motion work that makes the campaign move.',
                    ],
                ],
            ],
            'cta' => [
                'heading' => 'Bring us your next launch',
                'summary' => 'Start a campaign with the studio and watch it move from brief to live.',
                'actions' => [
                    ['label' => 'Start a campaign', 'url' => '#cta'],
                    ['label' => 'View the work', 'url' => '#project-showcase'],
                ],
            ],
            'features' => [
                'heading' => 'A campaign system, scene by scene',
                'summary' => 'Every launch runs through the same bold, repeatable production system.',
                'features' => [
                    [
                        'icon' => 'Brief',
                        'title' => 'Sharp campaign briefs',
                        'description' => 'We pin the message and the moment before a single asset is made.',
                    ],
                    [
                        'icon' => 'Make',
                        'title' => 'Bold creative production',
                        'description' => 'Art direction and motion built to stop the scroll and hold attention.',
                    ],
                    [
                        'icon' => 'Launch',
                        'title' => 'Coordinated launch day',
                        'description' => 'Every channel ships in lockstep so the launch lands as one moment.',
                    ],
                ],
            ],
            'content-listing' => [
                'heading' => 'A campaign work board built to be scanned',
                'summary' => 'Recent campaign assets and resources, legible and bold.',
                'items' => [
                    [
                        'type' => 'Case file',
                        'title' => 'Northwind launch recap',
                        'summary' => 'How a stealth product owned its launch week.',
                        'url' => '#case-study',
                    ],
                    [
                        'type' => 'Playbook',
                        'title' => 'The launch-week drumbeat',
                        'summary' => 'Our day-by-day system for a coordinated launch.',
                        'url' => '#services',
                    ],
                    [
                        'type' => 'Field note',
                        'title' => 'Motion that stops the scroll',
                        'summary' => 'What makes campaign motion work on social-first feeds.',
                        'url' => '#features',
                    ],
                ],
            ],
            default => [],
        };
    }

    private function navigation(): AgencyScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Field & Signal',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Work', 'url' => '#project-showcase'],
                ['label' => 'Case studies', 'url' => '#case-study'],
                ['label' => 'Contact', 'url' => '#cta'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): AgencyScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Campaigns that move with confidence',
            'eyebrow' => 'Agency',
            'summary' => 'A bold, motion-led homepage for campaign launches, case-study proof, and a filterable work showcase.',
            'actions' => [
                ['label' => 'Start a campaign', 'url' => '#cta'],
                ['label' => 'View the work', 'url' => '#project-showcase'],
            ],
        ]);
    }

    private function footer(): AgencyScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Field & Signal',
            'summary' => 'A campaign studio that takes launches from brief to live.',
            'columns' => [
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'Services', 'url' => '#services'],
                        ['label' => 'Work', 'url' => '#project-showcase'],
                        ['label' => 'Case studies', 'url' => '#case-study'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Team', 'url' => '#team'],
                        ['label' => 'Insights', 'url' => '#content-listing'],
                        ['label' => 'Contact', 'url' => '#cta'],
                    ],
                ],
                [
                    'heading' => 'Start',
                    'links' => [
                        ['label' => 'Start a campaign', 'url' => '#cta'],
                        ['label' => 'View the work', 'url' => '#project-showcase'],
                    ],
                ],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'agency-services-layout' => 'Theme Agency services',
            'agency-portfolio-layout' => 'Theme Agency campaign work board',
            'agency-case-study-layout' => 'Theme Agency launch recap detail',
            'agency-insights-layout' => 'Theme Agency insights',
            'agency-event-landing-layout' => 'Theme Agency event landing',
            'agency-lead-form-layout' => 'Theme Agency lead form',
            'agency-campaign-layout' => 'Theme Agency campaign',
            'agency-search-layout' => 'Theme Agency search results',
            default => 'Theme Agency homepage',
        };
    }
}
