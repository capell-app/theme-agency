<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FinancialAdvisory\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class FinancialAdvisoryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-financial-advisory::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (FinancialAdvisoryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-financial-advisory::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#14532d',
                accentColor: '#b08d57',
                neutralColor: '#14211a',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'flat',
                radius: 'md',
                surfaceColor: '#f8faf8',
                foregroundColor: '#14211a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'financial-advisory',
        ])->render();

        return view('capell-theme-financial-advisory::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, FinancialAdvisoryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'financial-advisory-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('services'),
                $this->section('advisors'),
                $this->section('calculators'),
                $this->section('credentials'),
                $this->section('client-segments'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'financial-advisory-directory' => [
                $this->navigation(),
                $this->section('advisors', [
                    'heading' => 'Browse the advisory team directory',
                    'summary' => 'A scannable directory of advisors keeps every specialist discoverable without the theme owning people records.',
                ]),
                $this->section('client-segments'),
                $this->section('credentials'),
                $this->footer(),
            ],
            'financial-advisory-detail' => [
                $this->navigation(),
                $this->section('advisors', [
                    'heading' => 'A single advisor profile that builds trust',
                    'summary' => 'Credentials, specialisms, and proof sit together so a prospect can evaluate one advisor in depth.',
                ]),
                $this->section('credentials'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'financial-advisory-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Start a confident conversation',
                    'summary' => 'A non-submitting contact path proves the enquiry journey feels like part of the advisory experience.',
                ]),
                $this->section('client-segments'),
                $this->footer(),
            ],
            'financial-advisory-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No insights published yet',
                    'summary' => 'The empty state stays premium and on-brand while the firm builds out its insights library.',
                ]),
                $this->footer(),
            ],
            'financial-advisory-not-found' => [
                $this->navigation(),
                $this->section('hero', [
                    'heading' => 'We could not find that page',
                    'summary' => 'A branded 404 keeps lost visitors oriented and routes them back to advisory journeys.',
                ]),
                $this->footer(),
            ],
            'financial-advisory-cta' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Book an advisory consultation',
                    'summary' => 'A focused conversion CTA proves the booking path feels premium without owning scheduling records.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'financial-advisory-services' => [
                $this->navigation(),
                $this->section('services', [
                    'heading' => 'Advisory services built to be compared',
                    'summary' => 'Editorial service groupings keep wealth, tax, and planning offerings legible and premium.',
                ]),
                $this->section('client-segments'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'financial-advisory-advisors' => [
                $this->navigation(),
                $this->section('advisors', [
                    'heading' => 'Meet the advisory team',
                    'summary' => 'Advisor cards pair specialisms with credentials so prospects can self-select the right expert.',
                ]),
                $this->section('credentials'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'financial-advisory-calculators' => [
                $this->navigation(),
                $this->section('calculators', [
                    'heading' => 'Planning calculators that build engagement',
                    'summary' => 'Non-submitting calculator surfaces prove the planning tools feel like part of the advisory experience.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): FinancialAdvisoryScreenshotSection
    {
        return new FinancialAdvisoryScreenshotSection($sectionKey, $data);
    }

    private function navigation(): FinancialAdvisoryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Meridian Wealth Advisory',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Advisors', 'url' => '#advisors'],
                ['label' => 'Calculators', 'url' => '#calculators'],
                ['label' => 'Insights', 'url' => '#insights'],
            ],
            'consultationUrl' => '#consultation',
        ]);
    }

    private function hero(): FinancialAdvisoryScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Trusted advice for every financial decision',
            'eyebrow' => 'Financial Advisory',
            'summary' => 'A professional advisory homepage for services, advisors, planning calculators, credentials, and client-led journeys.',
            'actions' => [
                ['label' => 'Book a consultation', 'url' => '#consultation'],
                ['label' => 'Explore services', 'url' => '#services'],
            ],
        ]);
    }

    private function footer(): FinancialAdvisoryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Meridian Wealth Advisory',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Advisors', 'url' => '#advisors'],
                ['label' => 'Calculators', 'url' => '#calculators'],
                ['label' => 'Insights', 'url' => '#insights'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'financial-advisory-directory' => 'Theme Financial Advisory directory',
            'financial-advisory-detail' => 'Theme Financial Advisory advisor detail',
            'financial-advisory-contact' => 'Theme Financial Advisory contact',
            'financial-advisory-empty' => 'Theme Financial Advisory empty state',
            'financial-advisory-not-found' => 'Theme Financial Advisory not found',
            'financial-advisory-cta' => 'Theme Financial Advisory conversion CTA',
            'financial-advisory-services' => 'Theme Financial Advisory services',
            'financial-advisory-advisors' => 'Theme Financial Advisory advisors',
            'financial-advisory-calculators' => 'Theme Financial Advisory calculators',
            default => 'Theme Financial Advisory homepage',
        };
    }
}
