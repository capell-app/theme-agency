<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DevtoolOss\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DevtoolOssScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-devtool-oss::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DevtoolOssScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-devtool-oss::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#4f46e5',
                accentColor: '#06b6d4',
                neutralColor: '#0f172a',
                headingFont: 'inter',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'minimal',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'devtool-oss',
        ])->render();

        return view('capell-theme-devtool-oss::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DevtoolOssScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'devtool-oss-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('install-hero'),
                $this->section('github-proof'),
                $this->section('features'),
                $this->section('self-host-vs-cloud'),
                $this->section('sdk-grid'),
                $this->section('changelog'),
                $this->section('contributors'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'devtool-oss-directory' => [
                $this->navigation(),
                $this->section('sdk-grid', [
                    'heading' => 'A catalog of SDKs built to be scanned',
                    'summary' => 'Structured SDK cards keep the project legible without the theme owning package records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'devtool-oss-detail' => [
                $this->navigation(),
                $this->section('github-proof', [
                    'heading' => 'A project page that reads with confidence',
                    'summary' => 'A single project view pairs proof and changelog so developers can adopt with confidence.',
                ]),
                $this->section('changelog'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'devtool-oss-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the maintainers through one clear path',
                    'summary' => 'A non-submitting contact CTA proves the support journey feels like part of the developer experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'devtool-oss-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays clean and structured while the project prepares its content.',
                ]),
                $this->footer(),
            ],
            'devtool-oss-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the project trustworthy and routes developers back into the docs journey.',
                ]),
                $this->footer(),
            ],
            'devtool-oss-cta' => [
                $this->navigation(),
                $this->section('install-hero'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a first install',
                    'summary' => 'A conversion-focused CTA stack keeps the path to adopting the tool direct and developer-friendly.',
                ]),
                $this->footer(),
            ],
            'devtool-oss-install' => [
                $this->navigation(),
                $this->section('install-hero', [
                    'heading' => 'Install in one confident command',
                    'summary' => 'A copy-ready install hero proves the onboarding journey feels like part of the developer experience.',
                ]),
                $this->section('sdk-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'devtool-oss-community' => [
                $this->navigation(),
                $this->section('contributors', [
                    'heading' => 'Meet the community behind the project',
                    'summary' => 'Editorial contributor cards keep the community legible without the theme owning people records.',
                ]),
                $this->section('github-proof'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'devtool-oss-cloud' => [
                $this->navigation(),
                $this->section('self-host-vs-cloud', [
                    'heading' => 'Cloud or self-host, side by side',
                    'summary' => 'A structured comparison keeps the hosting choice legible and confident for every team.',
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
    private function section(string $sectionKey, array $data = []): DevtoolOssScreenshotSection
    {
        return new DevtoolOssScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DevtoolOssScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Forgewright',
            'items' => [
                ['label' => 'Install', 'url' => '#install'],
                ['label' => 'SDKs', 'url' => '#sdks'],
                ['label' => 'Changelog', 'url' => '#changelog'],
                ['label' => 'Community', 'url' => '#community'],
            ],
            'consultationUrl' => '#install',
        ]);
    }

    private function hero(): DevtoolOssScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A developer tool teams can build on',
            'eyebrow' => 'Devtool OSS',
            'summary' => 'An open-source developer homepage for install flows, GitHub proof, SDKs, changelog, and self-host versus cloud journeys.',
            'actions' => [
                ['label' => 'Get started', 'url' => '#install'],
                ['label' => 'View on GitHub', 'url' => '#github'],
            ],
        ]);
    }

    private function footer(): DevtoolOssScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Forgewright',
            'items' => [
                ['label' => 'Install', 'url' => '#install'],
                ['label' => 'SDKs', 'url' => '#sdks'],
                ['label' => 'Changelog', 'url' => '#changelog'],
                ['label' => 'Community', 'url' => '#community'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'devtool-oss-directory' => 'Theme Devtool OSS directory',
            'devtool-oss-detail' => 'Theme Devtool OSS detail',
            'devtool-oss-contact' => 'Theme Devtool OSS contact',
            'devtool-oss-empty' => 'Theme Devtool OSS empty state',
            'devtool-oss-not-found' => 'Theme Devtool OSS 404 state',
            'devtool-oss-cta' => 'Theme Devtool OSS conversion CTA',
            'devtool-oss-install' => 'Theme Devtool OSS install',
            'devtool-oss-community' => 'Theme Devtool OSS community',
            'devtool-oss-cloud' => 'Theme Devtool OSS cloud vs self-host',
            default => 'Theme Devtool OSS homepage',
        };
    }
}
