<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EditorialCrm\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class EditorialCrmScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-editorial-crm::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (EditorialCrmScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-editorial-crm::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#5f6df2',
                neutralColor: '#182033',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#f7f5f0',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'editorial-crm',
        ])->render();

        return view('capell-theme-editorial-crm::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, EditorialCrmScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'editorial-crm-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('product-dashboard'),
                $this->section('data-model'),
                $this->section('workflow-automation'),
                $this->section('collaboration'),
                $this->section('integrations-reporting'),
                $this->section('customer-stories'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-crm-landing-page' => [
                $this->navigation(),
                $this->section('product-dashboard', [
                    'heading' => 'A product landing that reads like a real dashboard',
                    'summary' => 'A focused feature landing pairs the product surface with proof so revenue teams can evaluate with confidence.',
                ]),
                $this->section('workflow-automation'),
                $this->section('integrations-reporting'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-crm-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'An archive of product updates built to be scanned',
                    'summary' => 'Structured listing cards keep updates, customers, and integrations legible without the theme owning records.',
                ]),
                $this->section('customer-stories'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-crm-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results across the entire workspace',
                    'summary' => 'A discovery state stays crisp and structured while surfacing records, workflows, integrations, and resources.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'editorial-crm-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Convert through one confident path',
                    'summary' => 'A non-submitting conversion module proves the demo and newsletter journey feels like part of the product.',
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
    private function section(string $sectionKey, array $data = []): EditorialCrmScreenshotSection
    {
        return new EditorialCrmScreenshotSection($sectionKey, $data);
    }

    private function navigation(): EditorialCrmScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Cadence CRM',
            'items' => [
                ['label' => 'Product', 'url' => '#product-dashboard'],
                ['label' => 'Automation', 'url' => '#workflow-automation'],
                ['label' => 'Integrations', 'url' => '#integrations-reporting'],
                ['label' => 'Customers', 'url' => '#customer-stories'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): EditorialCrmScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'The CRM revenue teams can stand behind',
            'eyebrow' => 'Editorial CRM',
            'summary' => 'A refined product homepage for dashboards, relationship records, workflow automation, collaboration, integrations, reporting, and customer stories.',
            'actions' => [
                ['label' => 'Request a demo', 'url' => '#newsletter'],
                ['label' => 'Explore the product', 'url' => '#product-dashboard'],
            ],
        ]);
    }

    private function footer(): EditorialCrmScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Cadence CRM',
            'items' => [
                ['label' => 'Product', 'url' => '#product-dashboard'],
                ['label' => 'Automation', 'url' => '#workflow-automation'],
                ['label' => 'Integrations', 'url' => '#integrations-reporting'],
                ['label' => 'Customers', 'url' => '#customer-stories'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'editorial-crm-landing-page' => 'Theme Editorial CRM landing page',
            'editorial-crm-list-page' => 'Theme Editorial CRM list page',
            'editorial-crm-search-results' => 'Theme Editorial CRM search results',
            'editorial-crm-contact-form' => 'Theme Editorial CRM contact form',
            default => 'Theme Editorial CRM homepage',
        };
    }
}
