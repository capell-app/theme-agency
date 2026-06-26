<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PortfolioDirectory\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PortfolioDirectoryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-portfolio-directory::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PortfolioDirectoryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-portfolio-directory::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#ff6b35',
                neutralColor: '#141414',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'portfolio-directory',
        ])->render();

        return view('capell-theme-portfolio-directory::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PortfolioDirectoryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'portfolio-directory-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('directory-hero'),
                $this->section('role-filters'),
                $this->section('portfolio-grid'),
                $this->section('resume-resources'),
                $this->section('curated-lists'),
                $this->section('profile-detail'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-directory-landing-page' => [
                $this->navigation(),
                $this->section('profile-detail', [
                    'heading' => 'A portfolio landing built to be admired',
                    'summary' => 'A single portfolio view pairs gallery stacks and resume resources so visitors can explore the work with confidence.',
                ]),
                $this->section('curated-lists'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-directory-list-page' => [
                $this->navigation(),
                $this->section('role-filters', [
                    'heading' => 'A directory of creators built to be scanned',
                    'summary' => 'Role and medium filters keep the portfolio archive legible without the theme owning creator records.',
                ]),
                $this->section('portfolio-grid'),
                $this->section('curated-lists'),
                $this->section('cta'),
                $this->footer(),
            ],
            'portfolio-directory-search-results' => [
                $this->navigation(),
                $this->section('role-filters', [
                    'heading' => 'Search the directory in one confident path',
                    'summary' => 'Discovery filters surface designers, developers, and studios so visitors find the right work fast.',
                ]),
                $this->section('portfolio-grid'),
                $this->section('content-listing'),
                $this->footer(),
            ],
            'portfolio-directory-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Stay close to the directory through one path',
                    'summary' => 'A non-submitting newsletter and conversion CTA proves the contact journey feels like part of the directory.',
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
    private function section(string $sectionKey, array $data = []): PortfolioDirectoryScreenshotSection
    {
        return new PortfolioDirectoryScreenshotSection($sectionKey, $data);
    }

    private function navigation(): PortfolioDirectoryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Folio Index',
            'items' => [
                ['label' => 'Portfolios', 'url' => '#portfolio-grid'],
                ['label' => 'Creators', 'url' => '#role-filters'],
                ['label' => 'Resources', 'url' => '#resume-resources'],
                ['label' => 'Curated lists', 'url' => '#curated-lists'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): PortfolioDirectoryScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A directory the best work can stand behind',
            'eyebrow' => 'Portfolio Directory',
            'summary' => 'A curated frontend theme for designers, developers, and studios with large preview tiles, role and medium filters, resume resources, and curated lists.',
            'actions' => [
                ['label' => 'Browse portfolios', 'url' => '#portfolio-grid'],
                ['label' => 'Filter by role', 'url' => '#role-filters'],
            ],
        ]);
    }

    private function footer(): PortfolioDirectoryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Folio Index',
            'items' => [
                ['label' => 'Portfolios', 'url' => '#portfolio-grid'],
                ['label' => 'Creators', 'url' => '#role-filters'],
                ['label' => 'Resources', 'url' => '#resume-resources'],
                ['label' => 'Curated lists', 'url' => '#curated-lists'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'portfolio-directory-landing-page' => 'Theme Portfolio Directory landing page',
            'portfolio-directory-list-page' => 'Theme Portfolio Directory list page',
            'portfolio-directory-search-results' => 'Theme Portfolio Directory search results',
            'portfolio-directory-contact-form' => 'Theme Portfolio Directory contact form',
            default => 'Theme Portfolio Directory homepage',
        };
    }
}
