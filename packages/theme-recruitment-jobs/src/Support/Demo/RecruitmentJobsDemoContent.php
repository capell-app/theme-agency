<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RecruitmentJobs\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Recruitment & Jobs theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (job-board /
 * sector-specialisms / employer-services / candidate-advice / application-panel)
 * alongside the standard hero/proof/features/cta — giving every surface a full,
 * individual recruitment-agency site rather than the shared five-section
 * skeleton. Copy and section ordering are mined verbatim from the theme's
 * screenshot renderer.
 */
final class RecruitmentJobsDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Meridian Talent';

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
            title: self::BRAND . ' — Recruitment & Jobs',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Recruitment a team can stand behind',
                'A structured recruitment homepage for live roles, employer services, candidate advice, sector specialisms, and application-led journeys.',
            ),
            renderData: [
                'summary' => 'A structured recruitment homepage for live roles, employer services, candidate advice, sector specialisms, and application-led journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Recruitment & Jobs',
                        'heading' => 'Recruitment a team can stand behind',
                        'summary' => 'A structured recruitment homepage for live roles, employer services, candidate advice, sector specialisms, and application-led journeys.',
                        'actions' => [
                            ['label' => 'Browse jobs', 'url' => '#job-board', 'style' => 'primary'],
                            ['label' => 'Hire talent', 'url' => '#employer-services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Meridian Talent recruitment team',
                    ],
                    $this->jobBoardSection(
                        heading: 'Live roles built to be scanned and trusted',
                        summary: 'Structured job groupings keep openings legible and searchable without the theme owning vacancy records.',
                    ),
                    $this->sectorSpecialismsSection(
                        heading: 'Sector specialisms with real depth',
                        summary: 'Dedicated desks that know their markets, the people in them, and what good looks like.',
                    ),
                    $this->employerServicesSection(
                        heading: 'Hiring support employers can stand behind',
                        summary: 'Editorial employer-service cards keep the agency credible without the theme owning client records.',
                    ),
                    $this->candidateAdviceSection(
                        heading: 'Guidance candidates can act on',
                        summary: 'Editorial advice cards keep candidate support helpful and legible without the theme owning article records.',
                    ),
                    $this->proofSection(
                        heading: 'Placements that hold up',
                        summary: 'Outcomes from the last twelve months of recruitment work.',
                    ),
                    $this->featuresSection(
                        heading: 'Built for the way recruiters work',
                        summary: 'Everything an agency needs to publish roles, support candidates, and win employer trust.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to move on a role?',
                        summary: 'Browse live openings or talk to a consultant about your next hire.',
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
            name: self::BRAND . ' Jobs',
            title: 'Jobs — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A job board built to be scanned',
                'Structured role cards keep openings legible and searchable without the theme owning vacancy records.',
            ),
            renderData: [
                'summary' => 'Structured role cards keep openings legible and searchable without the theme owning vacancy records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Jobs',
                        'heading' => 'Live roles, ready to apply',
                        'summary' => 'Browse current openings across every sector desk, then apply through one confident path.',
                        'actions' => [
                            ['label' => 'Hire talent', 'url' => '#employer-services', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Recruitment job board',
                    ],
                    $this->jobBoardSection(
                        heading: 'A job board built to be scanned',
                        summary: 'Structured role cards keep openings legible and searchable without the theme owning vacancy records.',
                    ),
                    $this->contentListingSection(
                        heading: 'More roles across our desks',
                        summary: 'Smaller and specialist openings updated as our consultants take them on.',
                    ),
                    $this->ctaSection(
                        heading: 'See a role that fits?',
                        summary: 'Apply in minutes, or register your details and we will bring the right openings to you.',
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
            name: self::BRAND . ' Role',
            title: 'Senior Product Designer — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A single role that reads with clarity',
                'A focused vacancy view pairs requirements and proof so candidates can apply with confidence.',
            ),
            renderData: [
                'summary' => 'A focused vacancy view pairs requirements and proof so candidates can apply with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Open role',
                        'heading' => 'Senior Product Designer',
                        'summary' => 'A permanent role with a Series B fintech, hybrid in Bristol. Owns the design system and works directly with founders.',
                        'actions' => [
                            ['label' => 'Apply now', 'url' => '#application-panel', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Open recruitment role',
                    ],
                    $this->jobBoardSection(
                        heading: 'A single role that reads with clarity',
                        summary: 'A focused vacancy view pairs requirements and proof so candidates can apply with confidence.',
                    ),
                    $this->applicationPanelSection(
                        heading: 'Apply through one confident path',
                        summary: 'Send your details to the consultant handling this role and hear back within one working day.',
                    ),
                    $this->proofSection(
                        heading: 'Why candidates trust this desk',
                        summary: 'Outcomes from recent placements on the same team.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to apply?',
                        summary: 'Start your application now, or message the consultant with a question first.',
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
            title: 'Reach the team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the team through one confident path',
                'A non-submitting application panel proves the contact journey feels like part of the hiring experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting application panel proves the contact journey feels like part of the hiring experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Talk to a consultant',
                        'summary' => 'Whether you are hiring or job-hunting, reach the right desk directly. We reply within one working day.',
                        'actions' => [
                            ['label' => 'Browse jobs', 'url' => '#job-board', 'style' => 'primary'],
                            ['label' => 'Hire talent', 'url' => '#employer-services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Meridian Talent office',
                    ],
                    $this->applicationPanelSection(
                        heading: 'Reach the team through one confident path',
                        summary: 'A non-submitting application panel proves the contact journey feels like part of the hiring experience.',
                    ),
                    $this->featuresSection(
                        heading: 'How we work with you',
                        summary: 'What to expect once you reach out, whether you are a candidate or an employer.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a short intro call and we will point you to the right consultant.',
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
            name: self::BRAND . ' No Roles',
            title: 'No roles published — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No roles published here yet',
                'An empty listing state stays structured and trustworthy while the agency prepares its openings.',
            ),
            renderData: [
                'summary' => 'An empty listing state stays structured and trustworthy while the agency prepares its openings.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Job board',
                        'heading' => 'No roles match that filter — yet',
                        'summary' => 'We have not published openings in this category. Clear the filter to see everything, or register and we will alert you.',
                        'actions' => [
                            ['label' => 'Browse all jobs', 'url' => '#job-board', 'style' => 'primary'],
                            ['label' => 'Hire talent', 'url' => '#employer-services', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'No roles published here yet',
                        'summary' => 'An empty listing state stays structured and trustworthy while the agency prepares its openings.',
                        'items' => [],
                    ],
                    $this->sectorSpecialismsSection(
                        heading: 'While you are here',
                        summary: 'The sector desks our consultants know best.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the role and we will alert you the moment a match goes live.',
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
                'A 404 state keeps the agency professional and routes visitors back into the hiring journey.',
            ),
            renderData: [
                'summary' => 'A 404 state keeps the agency professional and routes visitors back into the hiring journey.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'A 404 state keeps the agency professional and routes visitors back into the hiring journey.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse jobs', 'url' => '#job-board', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for a role?',
                        summary: 'Head back to the job board or talk to a consultant about your next move.',
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
            name: self::BRAND . ' Apply',
            title: 'Turn intent into a submitted application — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a submitted application',
                'A conversion-focused CTA stack keeps the path to applying or hiring direct and confident.',
            ),
            renderData: [
                'summary' => 'A conversion-focused CTA stack keeps the path to applying or hiring direct and confident.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Apply or hire',
                        'heading' => 'One step from your next role',
                        'summary' => 'Whether you are applying or hiring, we keep the path direct, confident, and quick to act on.',
                        'actions' => [
                            ['label' => 'Browse jobs', 'url' => '#job-board', 'style' => 'primary'],
                            ['label' => 'Hire talent', 'url' => '#employer-services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Meridian Talent recruitment',
                    ],
                    $this->applicationPanelSection(
                        heading: 'Apply through one confident path',
                        summary: 'Send your details to the right consultant and hear back within one working day.',
                    ),
                    $this->proofSection(
                        heading: 'Why candidates and employers choose us',
                        summary: 'The numbers behind the placements.',
                    ),
                    $this->ctaSection(
                        heading: 'Turn intent into a submitted application',
                        summary: 'A conversion-focused CTA stack keeps the path to applying or hiring direct and confident.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function jobBoardSection(string $heading, string $summary): array
    {
        return [
            'type' => 'job-board',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Senior Product Designer', 'summary' => 'Permanent · Bristol (hybrid) · Series B fintech · Owns the design system.'],
                ['title' => 'Backend Engineer (Go)', 'summary' => 'Permanent · Remote (UK) · Renewables platform · Field-facing services.'],
                ['title' => 'Finance Business Partner', 'summary' => 'Permanent · London · Scale-up retail brand · Reports to the CFO.'],
                ['title' => 'Clinical Operations Lead', 'summary' => 'Contract · Manchester · Health-tech · Runs trial delivery across two sites.'],
                ['title' => 'Demand Generation Manager', 'summary' => 'Permanent · Remote (EU) · B2B SaaS · Owns the pipeline number.'],
                ['title' => 'Site Manager (Civils)', 'summary' => 'Permanent · Leeds · Infrastructure contractor · Major highways scheme.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sectorSpecialismsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sector-specialisms',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Technology & Product', 'summary' => 'Engineering, product, and design roles for scale-ups and platform teams.'],
                ['title' => 'Finance & Accounting', 'summary' => 'Qualified finance hires from analyst to director across industry and practice.'],
                ['title' => 'Health & Life Sciences', 'summary' => 'Clinical, regulatory, and operations talent for health-tech and pharma.'],
                ['title' => 'Construction & Infrastructure', 'summary' => 'Site, commercial, and engineering roles across major build programmes.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function employerServicesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'employer-services',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Permanent search', 'summary' => 'Shortlists of pre-qualified candidates, briefed and reference-checked before they reach you.'],
                ['title' => 'Contract & interim', 'summary' => 'Vetted contractors mobilised fast, with compliance and onboarding handled.'],
                ['title' => 'Executive & retained', 'summary' => 'Discreet, mapped searches for leadership and board-level appointments.'],
                ['title' => 'Talent advisory', 'summary' => 'Market mapping, salary benchmarking, and hiring strategy for in-house teams.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function candidateAdviceSection(string $heading, string $summary): array
    {
        return [
            'type' => 'candidate-advice',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Writing a CV that gets read', 'summary' => 'How to lead with outcomes and make the first ten seconds count.'],
                ['title' => 'Preparing for the interview', 'summary' => 'A practical framework for evidencing impact without overselling.'],
                ['title' => 'Negotiating an offer', 'summary' => 'What to ask for, when to ask, and how to keep the relationship warm.'],
                ['title' => 'Switching sectors', 'summary' => 'Translating your experience so a new market sees the value fast.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function applicationPanelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'application-panel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Send your details', 'summary' => 'Share a CV and a short note about what you are looking for.'],
                ['title' => 'Speak to a consultant', 'summary' => 'The specialist for your sector reviews and gets back within one working day.'],
                ['title' => 'Move forward with confidence', 'summary' => 'We brief you fully before every stage, with honest feedback throughout.'],
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
                ['name' => '1 day', 'quote' => 'Every application and enquiry hears back from a real consultant within one working day.'],
                ['name' => '92%', 'quote' => 'Of permanent placements pass probation and stay beyond their first year.'],
                ['name' => '500+', 'quote' => 'Roles filled across technology, finance, health, and infrastructure since 2019.'],
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
                ['title' => 'Structured job board', 'summary' => 'Legible, scannable role cards that stay searchable without owning vacancy records.'],
                ['title' => 'Sector desks', 'summary' => 'Specialist consultants who know their markets and the people in them.'],
                ['title' => 'Application-led journeys', 'summary' => 'One confident path from interest to submitted application.'],
                ['title' => 'Employer credibility', 'summary' => 'Editorial service cards that keep the agency trustworthy at a glance.'],
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
                ['title' => 'Data Analyst', 'summary' => 'Permanent · Birmingham · Insurance · SQL, dbt, and stakeholder reporting.'],
                ['title' => 'People Operations Partner', 'summary' => 'Permanent · Remote (UK) · Scaling SaaS · Owns the employee lifecycle.'],
                ['title' => 'Quantity Surveyor', 'summary' => 'Permanent · Glasgow · Main contractor · Mixed-use development.'],
                ['title' => 'Regulatory Affairs Associate', 'summary' => 'Contract · Cambridge · Medical devices · EU MDR submissions.'],
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
                ['label' => 'Browse jobs', 'url' => '#job-board', 'style' => 'primary'],
                ['label' => 'Hire talent', 'url' => '#employer-services', 'style' => 'secondary'],
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
                ['label' => 'Jobs', 'url' => '#job-board'],
                ['label' => 'Employers', 'url' => '#employer-services'],
                ['label' => 'Candidate advice', 'url' => '#candidate-advice'],
                ['label' => 'Sectors', 'url' => '#sector-specialisms'],
            ],
            'ctaLabel' => 'Book a consultation',
            'ctaUrl' => '#application-panel',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A specialist recruitment agency placing talent across technology, finance, health, and infrastructure.',
            'columns' => [
                [
                    'heading' => 'Candidates',
                    'links' => [
                        ['label' => 'Jobs', 'url' => '#job-board'],
                        ['label' => 'Candidate advice', 'url' => '#candidate-advice'],
                        ['label' => 'Register', 'url' => '#application-panel'],
                    ],
                ],
                [
                    'heading' => 'Employers',
                    'links' => [
                        ['label' => 'Employer services', 'url' => '#employer-services'],
                        ['label' => 'Sectors', 'url' => '#sector-specialisms'],
                        ['label' => 'Book a consultation', 'url' => '#application-panel'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#application-panel'],
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
