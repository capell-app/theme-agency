<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EstateAgents\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class EstateAgentsScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-estate-agents::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (EstateAgentsScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-estate-agents::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#174c3f',
                accentColor: '#c7f464',
                neutralColor: '#1a1c1f',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'editorial',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f6f8f4',
                foregroundColor: '#1a1c1f',
                headingScale: 'expressive',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'estate-agents',
        ])->render();

        return view('capell-theme-estate-agents::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, EstateAgentsScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'estate-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('property-search'),
                $this->section('featured-properties'),
                $this->section('valuation-cta'),
                $this->section('local-guide'),
                $this->section('agent-team'),
                $this->section('market-proof'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'estate-search-layout' => [
                $this->navigation(),
                $this->section('property-search', [
                    'heading' => 'Search every instruction from the first screen',
                    'summary' => 'A high-intent search band helps buyers filter by area, budget, and beds without the theme owning listing records.',
                ]),
                $this->section('featured-properties'),
                $this->section('content-listing', [
                    'heading' => 'Buyer guides and market reports',
                    'summary' => 'Support search journeys with evergreen guides, recent market notes, and local proof.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'estate-valuation-layout' => [
                $this->navigation(),
                $this->section('valuation-cta', [
                    'heading' => 'Book a premium valuation in one focused path',
                    'summary' => 'Vendors can start with a postcode, understand the process, and move into a managed appraisal workflow.',
                ]),
                $this->section('market-proof'),
                $this->section('proof'),
                $this->section('agent-team'),
                $this->section('cta'),
                $this->footer(),
            ],
            'estate-local-guide-layout' => [
                $this->navigation(),
                $this->section('local-guide', [
                    'heading' => 'Turn area expertise into a conversion asset',
                    'summary' => 'Neighbourhood pages pair schools, commute context, market guidance, and branch confidence.',
                ]),
                $this->section('content-listing'),
                $this->section('market-proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'estate-viewing-layout' => [
                $this->navigation(),
                $this->section('featured-properties'),
                $this->section('viewing-request', [
                    'heading' => 'Request a viewing without losing property context',
                    'summary' => 'The enquiry path keeps buyer intent attached to the selected home and preferred viewing date.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): EstateAgentsScreenshotSection
    {
        return new EstateAgentsScreenshotSection($sectionKey, $data);
    }

    private function navigation(): EstateAgentsScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Oak & Field',
            'items' => [
                ['label' => 'Search', 'url' => '#property-search'],
                ['label' => 'Valuation', 'url' => '#valuation'],
                ['label' => 'Guides', 'url' => '#guides'],
                ['label' => 'Viewings', 'url' => '#viewing'],
            ],
            'valuationUrl' => '#valuation',
        ]);
    }

    private function hero(): EstateAgentsScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Move with a property team that knows the street',
            'eyebrow' => 'Estate Agents',
            'summary' => 'A premium agency homepage for search, valuations, featured homes, local proof, and viewing-led enquiries.',
            'actions' => [
                ['label' => 'Search homes', 'url' => '#property-search'],
                ['label' => 'Book valuation', 'url' => '#valuation'],
            ],
        ]);
    }

    private function footer(): EstateAgentsScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Oak & Field',
            'items' => [
                ['label' => 'Search', 'url' => '#property-search'],
                ['label' => 'Valuations', 'url' => '#valuation'],
                ['label' => 'Local guides', 'url' => '#guides'],
                ['label' => 'Viewings', 'url' => '#viewing'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'estate-search-layout' => 'Theme Estate Agents property search',
            'estate-valuation-layout' => 'Theme Estate Agents valuation',
            'estate-local-guide-layout' => 'Theme Estate Agents local guide',
            'estate-viewing-layout' => 'Theme Estate Agents viewing request',
            default => 'Theme Estate Agents homepage',
        };
    }
}
