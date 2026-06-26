<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LawFirm\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class LawFirmScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-law-firm::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (LawFirmScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-law-firm::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1e293b',
                accentColor: '#b08d57',
                neutralColor: '#0f172a',
                headingFont: 'fraunces',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'flat',
                radius: 'sm',
                surfaceColor: '#f7f5f2',
                foregroundColor: '#1e293b',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'law-firm',
        ])->render();

        return view('capell-theme-law-firm::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, LawFirmScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'law-firm-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('practice-areas'),
                $this->section('attorneys'),
                $this->section('case-results'),
                $this->section('credentials'),
                $this->section('consultation-cta'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'law-firm-directory' => [
                $this->navigation(),
                $this->section('attorneys', [
                    'heading' => 'A directory of counsel built to be scanned',
                    'summary' => 'Structured attorney cards keep the practice authoritative and legible without the theme owning people records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'law-firm-detail' => [
                $this->navigation(),
                $this->section('attorneys', [
                    'heading' => 'A counsel profile that reads with authority',
                    'summary' => 'A single attorney view pairs credentials and proof so prospective clients can engage with confidence.',
                ]),
                $this->section('credentials'),
                $this->section('case-results'),
                $this->section('cta'),
                $this->footer(),
            ],
            'law-firm-contact' => [
                $this->navigation(),
                $this->section('consultation-cta', [
                    'heading' => 'Reach the firm through one confident path',
                    'summary' => 'A non-submitting consultation CTA proves the contact journey feels like part of the practice experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'law-firm-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays premium and structured while the practice prepares its content.',
                ]),
                $this->footer(),
            ],
            'law-firm-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the firm authoritative and routes visitors back into the practice journey.',
                ]),
                $this->footer(),
            ],
            'law-firm-cta' => [
                $this->navigation(),
                $this->section('consultation-cta'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a booked consultation',
                    'summary' => 'A conversion-focused CTA stack keeps the path to instructing the firm direct and premium.',
                ]),
                $this->footer(),
            ],
            'law-firm-practice-areas' => [
                $this->navigation(),
                $this->section('practice-areas', [
                    'heading' => 'Practice areas built to be scanned and trusted',
                    'summary' => 'Structured practice groupings keep specialisms authoritative and legible without owning matter records.',
                ]),
                $this->section('credentials'),
                $this->section('cta'),
                $this->footer(),
            ],
            'law-firm-attorneys' => [
                $this->navigation(),
                $this->section('attorneys', [
                    'heading' => 'Meet the counsel behind the practice',
                    'summary' => 'Editorial attorney cards keep the team authoritative and legible without the theme owning people records.',
                ]),
                $this->section('credentials'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'law-firm-consultation' => [
                $this->navigation(),
                $this->section('consultation-cta', [
                    'heading' => 'Book a consultation in one confident path',
                    'summary' => 'A non-submitting consultation CTA proves the booking journey feels like part of the practice experience.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): LawFirmScreenshotSection
    {
        return new LawFirmScreenshotSection($sectionKey, $data);
    }

    private function navigation(): LawFirmScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Whitlock & Hart',
            'items' => [
                ['label' => 'Practice areas', 'url' => '#practice-areas'],
                ['label' => 'Attorneys', 'url' => '#attorneys'],
                ['label' => 'Case results', 'url' => '#case-results'],
                ['label' => 'Consultation', 'url' => '#consultation'],
            ],
            'consultationUrl' => '#consultation',
        ]);
    }

    private function hero(): LawFirmScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Counsel a practice can stand behind',
            'eyebrow' => 'Law Firm',
            'summary' => 'An authoritative legal homepage for practice areas, attorneys, case results, credentials, and consultation-led journeys.',
            'actions' => [
                ['label' => 'Book a consultation', 'url' => '#consultation'],
                ['label' => 'View practice areas', 'url' => '#practice-areas'],
            ],
        ]);
    }

    private function footer(): LawFirmScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Whitlock & Hart',
            'items' => [
                ['label' => 'Practice areas', 'url' => '#practice-areas'],
                ['label' => 'Attorneys', 'url' => '#attorneys'],
                ['label' => 'Case results', 'url' => '#case-results'],
                ['label' => 'Consultation', 'url' => '#consultation'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'law-firm-directory' => 'Theme Law Firm directory',
            'law-firm-detail' => 'Theme Law Firm detail',
            'law-firm-contact' => 'Theme Law Firm contact',
            'law-firm-empty' => 'Theme Law Firm empty state',
            'law-firm-not-found' => 'Theme Law Firm 404 state',
            'law-firm-cta' => 'Theme Law Firm conversion CTA',
            'law-firm-practice-areas' => 'Theme Law Firm practice areas',
            'law-firm-attorneys' => 'Theme Law Firm attorneys',
            'law-firm-consultation' => 'Theme Law Firm consultation',
            default => 'Theme Law Firm homepage',
        };
    }
}
