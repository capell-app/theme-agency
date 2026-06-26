<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Portfolio\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PortfolioScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-portfolio::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PortfolioScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-portfolio::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#7c2d12',
                accentColor: '#f43f5e',
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
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'portfolio',
        ])->render();

        return view('capell-theme-portfolio::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PortfolioScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'theme-admin-list-showing-portfolio',
            'frontend-page-rendered-with-portfolio-theme',
            'portfolio-homepage-layout',
            'theme-preview-url-output' => [
                $this->navigation(),
                $this->hero(),
                $this->section('work-grid'),
                $this->section('case-studies'),
                $this->section('services'),
                $this->section('proof'),
                $this->section('testimonials'),
                $this->section('speaking-media-kit'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-work-grid-layout' => [
                $this->navigation(),
                $this->section('work-grid', [
                    'heading' => 'Selected work built to be scanned',
                    'summary' => 'Dense work cards keep role, scope, and outcome legible without the theme becoming a blog, gallery, or services grid.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-case-study-layout' => [
                $this->navigation(),
                $this->section('case-study-detail', [
                    'heading' => 'A case study that proves measurable value',
                    'summary' => 'An outcome ledger pairs challenge, approach, and result so a studio proves premium worth through work rather than broad claims.',
                ]),
                $this->section('case-studies'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-services-layout' => [
                $this->navigation(),
                $this->section('services', [
                    'heading' => 'Studio capabilities you can actually buy',
                    'summary' => 'Engagement-shaped service cards make the offer legible without the theme becoming a local-services page.',
                ]),
                $this->section('process'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-media-kit-layout' => [
                $this->navigation(),
                $this->section('speaking-media-kit', [
                    'heading' => 'A media kit that sells authority and audience',
                    'summary' => 'Speaking, press, and audience paths carry compact credibility proof without needing a separate knowledge theme.',
                ]),
                $this->section('client-logos'),
                $this->section('newsletter'),
                $this->footer(),
            ],
            'portfolio-newsletter-layout' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Turn portfolio visitors into subscribers',
                    'summary' => 'A non-submitting newsletter path supports audience growth without becoming a resource archive.',
                ]),
                $this->section('testimonials'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): PortfolioScreenshotSection
    {
        return new PortfolioScreenshotSection($sectionKey, $data);
    }

    private function navigation(): PortfolioScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Marlow Studio',
            'items' => [
                ['label' => 'Work', 'url' => '#work'],
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Media kit', 'url' => '#media-kit'],
            ],
            'consultationUrl' => '#enquire',
        ]);
    }

    private function hero(): PortfolioScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Selected work, proven by outcomes',
            'eyebrow' => 'Portfolio',
            'summary' => 'A work-led portfolio for creators and consultants — case studies, services, media kit, and audience growth from one polished site.',
            'actions' => [
                ['label' => 'View selected work', 'url' => '#work'],
                ['label' => 'Start an enquiry', 'url' => '#enquire'],
            ],
        ]);
    }

    private function footer(): PortfolioScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Marlow Studio',
            'items' => [
                ['label' => 'Work', 'url' => '#work'],
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Media kit', 'url' => '#media-kit'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'portfolio-work-grid-layout' => 'Theme Portfolio selected work',
            'portfolio-case-study-layout' => 'Theme Portfolio case study',
            'portfolio-services-layout' => 'Theme Portfolio services',
            'portfolio-media-kit-layout' => 'Theme Portfolio media kit',
            'portfolio-newsletter-layout' => 'Theme Portfolio newsletter',
            default => 'Theme Portfolio homepage',
        };
    }
}
