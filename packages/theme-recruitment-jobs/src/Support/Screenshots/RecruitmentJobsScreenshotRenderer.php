<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RecruitmentJobs\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class RecruitmentJobsScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-recruitment-jobs::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (RecruitmentJobsScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-recruitment-jobs::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f766e',
                accentColor: '#6366f1',
                neutralColor: '#0f172a',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f8fafc',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'recruitment-jobs',
        ])->render();

        return view('capell-theme-recruitment-jobs::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, RecruitmentJobsScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'recruitment-jobs-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('job-board'),
                $this->section('sector-specialisms'),
                $this->section('employer-services'),
                $this->section('candidate-advice'),
                $this->section('proof'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'recruitment-jobs-directory' => [
                $this->navigation(),
                $this->section('job-board', [
                    'heading' => 'A job board built to be scanned',
                    'summary' => 'Structured role cards keep openings legible and searchable without the theme owning vacancy records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'recruitment-jobs-detail' => [
                $this->navigation(),
                $this->section('job-board', [
                    'heading' => 'A single role that reads with clarity',
                    'summary' => 'A focused vacancy view pairs requirements and proof so candidates can apply with confidence.',
                ]),
                $this->section('application-panel'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'recruitment-jobs-contact' => [
                $this->navigation(),
                $this->section('application-panel', [
                    'heading' => 'Reach the team through one confident path',
                    'summary' => 'A non-submitting application panel proves the contact journey feels like part of the hiring experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'recruitment-jobs-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No roles published here yet',
                    'summary' => 'An empty listing state stays structured and trustworthy while the agency prepares its openings.',
                ]),
                $this->footer(),
            ],
            'recruitment-jobs-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the agency professional and routes visitors back into the hiring journey.',
                ]),
                $this->footer(),
            ],
            'recruitment-jobs-cta' => [
                $this->navigation(),
                $this->section('application-panel'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a submitted application',
                    'summary' => 'A conversion-focused CTA stack keeps the path to applying or hiring direct and confident.',
                ]),
                $this->footer(),
            ],
            'recruitment-jobs-jobs' => [
                $this->navigation(),
                $this->section('job-board', [
                    'heading' => 'Live roles built to be scanned and trusted',
                    'summary' => 'Structured job groupings keep openings legible and searchable without the theme owning vacancy records.',
                ]),
                $this->section('sector-specialisms'),
                $this->section('cta'),
                $this->footer(),
            ],
            'recruitment-jobs-employers' => [
                $this->navigation(),
                $this->section('employer-services', [
                    'heading' => 'Hiring support employers can stand behind',
                    'summary' => 'Editorial employer-service cards keep the agency credible without the theme owning client records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'recruitment-jobs-candidate-advice' => [
                $this->navigation(),
                $this->section('candidate-advice', [
                    'heading' => 'Guidance candidates can act on',
                    'summary' => 'Editorial advice cards keep candidate support helpful and legible without the theme owning article records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): RecruitmentJobsScreenshotSection
    {
        return new RecruitmentJobsScreenshotSection($sectionKey, $data);
    }

    private function navigation(): RecruitmentJobsScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Meridian Talent',
            'items' => [
                ['label' => 'Jobs', 'url' => '#job-board'],
                ['label' => 'Employers', 'url' => '#employer-services'],
                ['label' => 'Candidate advice', 'url' => '#candidate-advice'],
                ['label' => 'Sectors', 'url' => '#sector-specialisms'],
            ],
            'consultationUrl' => '#application-panel',
        ]);
    }

    private function hero(): RecruitmentJobsScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Recruitment a team can stand behind',
            'eyebrow' => 'Recruitment & Jobs',
            'summary' => 'A structured recruitment homepage for live roles, employer services, candidate advice, sector specialisms, and application-led journeys.',
            'actions' => [
                ['label' => 'Browse jobs', 'url' => '#job-board'],
                ['label' => 'Hire talent', 'url' => '#employer-services'],
            ],
        ]);
    }

    private function footer(): RecruitmentJobsScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Meridian Talent',
            'items' => [
                ['label' => 'Jobs', 'url' => '#job-board'],
                ['label' => 'Employers', 'url' => '#employer-services'],
                ['label' => 'Candidate advice', 'url' => '#candidate-advice'],
                ['label' => 'Sectors', 'url' => '#sector-specialisms'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'recruitment-jobs-directory' => 'Theme Recruitment & Jobs directory',
            'recruitment-jobs-detail' => 'Theme Recruitment & Jobs detail',
            'recruitment-jobs-contact' => 'Theme Recruitment & Jobs contact',
            'recruitment-jobs-empty' => 'Theme Recruitment & Jobs empty state',
            'recruitment-jobs-not-found' => 'Theme Recruitment & Jobs 404 state',
            'recruitment-jobs-cta' => 'Theme Recruitment & Jobs conversion CTA',
            'recruitment-jobs-jobs' => 'Theme Recruitment & Jobs jobs',
            'recruitment-jobs-employers' => 'Theme Recruitment & Jobs employers',
            'recruitment-jobs-candidate-advice' => 'Theme Recruitment & Jobs candidate advice',
            default => 'Theme Recruitment & Jobs homepage',
        };
    }
}
