<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConstructionTrades\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ConstructionTradesScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-construction-trades::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ConstructionTradesScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-construction-trades::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#334155',
                accentColor: '#f59e0b',
                neutralColor: '#1c1917',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'compact',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f4f4f5',
                foregroundColor: '#1c1917',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'construction-trades',
        ])->render();

        return view('capell-theme-construction-trades::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ConstructionTradesScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'construction-trades-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('services'),
                $this->section('project-portfolio'),
                $this->section('accreditations'),
                $this->section('process'),
                $this->section('service-areas'),
                $this->section('quote-cta'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'construction-trades-directory' => [
                $this->navigation(),
                $this->section('project-portfolio', [
                    'heading' => 'A project directory built to be scanned',
                    'summary' => 'Structured project cards keep the build record credible and legible without the theme owning project records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'construction-trades-detail' => [
                $this->navigation(),
                $this->section('project-portfolio', [
                    'heading' => 'A project profile that reads with confidence',
                    'summary' => 'A single project view pairs the build scope and proof so prospective clients can engage with confidence.',
                ]),
                $this->section('accreditations'),
                $this->section('process'),
                $this->section('cta'),
                $this->footer(),
            ],
            'construction-trades-contact' => [
                $this->navigation(),
                $this->section('quote-cta', [
                    'heading' => 'Reach the team through one confident path',
                    'summary' => 'A non-submitting quote CTA proves the contact journey feels like part of the build experience.',
                ]),
                $this->section('service-areas'),
                $this->footer(),
            ],
            'construction-trades-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays rugged and structured while the firm prepares its content.',
                ]),
                $this->footer(),
            ],
            'construction-trades-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the firm credible and routes visitors back into the build journey.',
                ]),
                $this->footer(),
            ],
            'construction-trades-cta' => [
                $this->navigation(),
                $this->section('quote-cta'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a booked quote',
                    'summary' => 'A conversion-focused CTA stack keeps the path to commissioning the firm direct and confident.',
                ]),
                $this->footer(),
            ],
            'construction-trades-services' => [
                $this->navigation(),
                $this->section('services', [
                    'heading' => 'Trade services built to be scanned and trusted',
                    'summary' => 'Structured service groupings keep specialisms credible and legible without owning job records.',
                ]),
                $this->section('process'),
                $this->section('accreditations'),
                $this->section('cta'),
                $this->footer(),
            ],
            'construction-trades-projects' => [
                $this->navigation(),
                $this->section('project-portfolio', [
                    'heading' => 'See the projects behind the firm',
                    'summary' => 'Editorial project cards keep the build record credible and legible without the theme owning project records.',
                ]),
                $this->section('accreditations'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'construction-trades-quote' => [
                $this->navigation(),
                $this->section('quote-cta', [
                    'heading' => 'Request a quote in one confident path',
                    'summary' => 'A non-submitting quote CTA proves the request journey feels like part of the build experience.',
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
    private function section(string $sectionKey, array $data = []): ConstructionTradesScreenshotSection
    {
        return new ConstructionTradesScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ConstructionTradesScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Brackford & Vale',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Accreditations', 'url' => '#accreditations'],
                ['label' => 'Quote', 'url' => '#quote'],
            ],
            'consultationUrl' => '#quote',
        ]);
    }

    private function hero(): ConstructionTradesScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Builders a project can stand behind',
            'eyebrow' => 'Construction Trades',
            'summary' => 'A rugged construction homepage for services, project portfolios, accreditations, process, service areas, and quote-led journeys.',
            'actions' => [
                ['label' => 'Request a quote', 'url' => '#quote'],
                ['label' => 'View services', 'url' => '#services'],
            ],
        ]);
    }

    private function footer(): ConstructionTradesScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Brackford & Vale',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Accreditations', 'url' => '#accreditations'],
                ['label' => 'Quote', 'url' => '#quote'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'construction-trades-directory' => 'Theme Construction Trades directory',
            'construction-trades-detail' => 'Theme Construction Trades detail',
            'construction-trades-contact' => 'Theme Construction Trades contact',
            'construction-trades-empty' => 'Theme Construction Trades empty state',
            'construction-trades-not-found' => 'Theme Construction Trades 404 state',
            'construction-trades-cta' => 'Theme Construction Trades conversion CTA',
            'construction-trades-services' => 'Theme Construction Trades services',
            'construction-trades-projects' => 'Theme Construction Trades projects',
            'construction-trades-quote' => 'Theme Construction Trades quote request',
            default => 'Theme Construction Trades homepage',
        };
    }
}
